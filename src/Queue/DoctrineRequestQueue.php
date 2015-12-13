<?php


namespace LastCall\Crawler\Queue;


use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use LastCall\Crawler\Common\DoctrineSetupTeardownTrait;
use LastCall\Crawler\Common\SetupTeardownInterface;
use Psr\Http\Message\RequestInterface;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\LockMode;

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
            if(!$this->exists($key)) {
                $ret = $this->connection->insert($this->table, [
                    'expire' => 0,
                    'identifier' => $key,
                    'status' => Job::FREE,
                    'data' => serialize($request),
                ]);
                return $ret === 1;
            }
            return FALSE;
        }
        catch(UniqueConstraintViolationException $e) {
            return FALSE;
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
                    'id' => $res['id']
                ]);
                $res['expire'] = $expire;
                $return = $this->hydrateRecord($res);
            }
        });

        return $return;
    }

    /**
     * {@inheritDoc}
     */
    private function hydrateRecord(array $record)
    {
        $refl = new \ReflectionClass('LastCall\Crawler\Queue\Job');
        $job = $refl->newInstanceWithoutConstructor();
        $record['data'] = unserialize($record['data']);
        foreach (array(
                     'data',
                     'id',
                     'status',
                     'expire',
                     'identifier'
                 ) as $property) {
            $prop = $refl->getProperty($property);
            $prop->setAccessible(true);
            $prop->setValue($job, $record[$property]);
        }

        return $job;
    }

    /**
     * @inheritDoc
     */
    public function complete(Job $job)
    {
        $ret = $this->connection->update($this->table, [
            'expire' => 0,
            'status' => Job::COMPLETE,
        ], [
            'id' => $job->getId(),
            'expire' => $job->getExpire(),
            'status' => $job->getStatus()
        ]);

        if($ret === 1) {
            $job->setStatus(Job::COMPLETE)
                ->setExpire(0);
            return TRUE;
        }
        return FALSE;
    }

    /**
     * @inheritDoc
     */
    public function release(Job $job)
    {
        $ret = $this->connection->update($this->table, [
            'expire' => 0,
            'status' => Job::FREE,
        ], [
            'id' => $job->getId(),
            'expire' => $job->getExpire(),
            'status' => $job->getStatus(),
        ]);

        if($ret === 1) {
            $job->setStatus(Job::COMPLETE)
                ->setExpire(0);
            return TRUE;
        }
        return FALSE;
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

    private function exists($identifier) {
        return $this->connection->executeQuery("SELECT 1 FROM {$this->table} WHERE identifier = ?",
            array(
                $identifier
            ))->fetchColumn();
    }

    protected function getConnection() {
        return $this->connection;
    }

    protected function getTables() {
        $table = new Table($this->table);
        $table->addColumn('id', 'integer')->setAutoincrement(true);
        $table->addColumn('identifier', 'binary', ['nullable' => true]);
        $table->addColumn('status', 'integer');
        $table->addColumn('expire', 'integer');
        $table->addColumn('data', 'object');
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['identifier']);

        return [$table];
    }

}