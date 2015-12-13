<?php


namespace LastCall\Crawler\Queue;


use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\DBAL\LockMode;
use Doctrine\DBAL\Schema\Table;
use LastCall\Crawler\Common\DoctrineSetupTeardownTrait;
use LastCall\Crawler\Common\SetupTeardownInterface;
use Psr\Http\Message\RequestInterface;

class DoctrineRequestQueue implements RequestQueueInterface, SetupTeardownInterface
{
    use DoctrineSetupTeardownTrait;

    /**
     * @var \Doctrine\DBAL\Connection
     */
    private $connection;

    private $table = 'Job';

    public function __construct(Connection $connection, $table = 'Job')
    {
        $this->connection = $connection;
        $this->table = $table;
    }

    /**
     * @inheritDoc
     */
    public function push(RequestInterface $request)
    {
        $key = $request->getMethod() . $request->getUri();
        try {
            if (!$this->exists($key)) {
                $ret = $this->connection->insert($this->table, [
                    'expire' => 0,
                    'identifier' => $key,
                    'status' => Job::FREE,
                    'data' => serialize($request),
                ]);

                return $ret === 1;
            }

            return false;
        } catch (UniqueConstraintViolationException $e) {
            return false;
        }
    }

    /**
     * @inheritDoc
     */
    public function pop()
    {
        $conn = $this->connection;
        $sql = "SELECT * FROM " . $conn->getDatabasePlatform()
                ->appendLockHint($this->table,
                    LockMode::PESSIMISTIC_READ) . " WHERE status = ? AND expire <= ? LIMIT 1";

        $return = null;

        $this->connection->transactional(function () use (
            $sql,
            $conn,
            &$return
        ) {
            if ($res = $conn->executeQuery($sql, [Job::FREE, time()])
                ->fetch()
            ) {
                $expire = time() + 30;
                $conn->update($this->table, [
                    'expire' => $expire
                ], [
                    'identifier' => $res['identifier']
                ]);
                $res['expire'] = $expire;
                $return = $this->hydrateFromArray($res);
            }
        });

        return $return;
    }

    /**
     * @inheritDoc
     */
    public function complete(Job $job)
    {
        if ($this->exists($job->getIdentifier())) {
            $ret = $this->connection->update($this->table, [
                'expire' => 0,
                'status' => Job::COMPLETE,
            ], [
                'identifier' => $job->getIdentifier(),
                'expire' => $job->getExpire(),
                'status' => $job->getStatus()
            ]);

            if ($ret === 1) {
                $job->setStatus(Job::COMPLETE)->setExpire(0);
            }

            return;
        }
        $this->throwNotManaged();
    }

    /**
     * @inheritDoc
     */
    public function release(Job $job)
    {
        if ($this->exists($job->getIdentifier())) {
            $ret = $this->connection->update($this->table, [
                'expire' => 0,
                'status' => Job::FREE,
            ], [
                'identifier' => $job->getIdentifier(),
                'expire' => $job->getExpire(),
                'status' => $job->getStatus(),
            ]);
            if ($ret === 1) {
                $job->setStatus(Job::COMPLETE)->setExpire(0);
            }

            return;
        }
        $this->throwNotManaged();
    }

    public function count($status = Job::FREE)
    {
        $table = $this->table;
        switch ($status) {
            case Job::FREE:
                return (int)$this->connection->executeQuery("SELECT COUNT(*) FROM $table WHERE status = ? AND expire <= ?",
                    array(
                        Job::FREE,
                        time()
                    ))->fetchColumn();
            case Job::CLAIMED:
                return (int)$this->connection->executeQuery("SELECT COUNT(*) FROM $table WHERE status = ? AND expire > ?",
                    array(
                        Job::FREE,
                        time()
                    ))->fetchColumn();
            default:
                return (int)$this->connection->executeQuery("SELECT COUNT(*) FROM $table WHERE status = ?",
                    array(
                        Job::COMPLETE
                    ))->fetchColumn();
        }
    }

    protected function getConnection()
    {
        return $this->connection;
    }

    protected function getTables()
    {
        $table = new Table($this->table);
        $table->addColumn('identifier', 'binary');
        $table->addColumn('status', 'integer');
        $table->addColumn('expire', 'integer');
        $table->addColumn('data', 'object');
        $table->setPrimaryKey(['identifier']);

        return [$table];
    }

    private function exists($identifier)
    {
        return $this->connection->executeQuery("SELECT 1 FROM {$this->table} WHERE identifier = ?",
            array(
                $identifier
            ))->fetchColumn();
    }

    private function throwNotManaged()
    {
        throw new \RuntimeException('This job is not managed by this queue');
    }

    private function hydrateFromArray(array $record)
    {
        $record += array(
            'data' => null,
            'status' => Job::FREE,
            'expire' => 0,
            'identifier' => null,
        );
        $job = new Job(unserialize($record['data']), $record['identifier']);
        foreach (['status', 'expire'] as $prop) {
            $refl = new \ReflectionProperty(Job::class, $prop);
            $refl->setAccessible(true);
            $refl->setValue($job, $record[$prop]);
        }

        return $job;
    }
}