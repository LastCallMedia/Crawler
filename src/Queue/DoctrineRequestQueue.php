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

    private $table = 'queue';

    public function __construct(Connection $connection, $table = 'queue')
    {
        $this->connection = $connection;
        $this->table = $table;
    }

    private function getKey(RequestInterface $request)
    {
        return $request->getMethod() . $request->getUri();
    }

    public function push(RequestInterface $request)
    {
        $key = $this->getKey($request);
        try {
            if (!$this->exists($key)) {
                $ret = $this->connection->insert($this->table, [
                    'expire' => 0,
                    'identifier' => $key,
                    'status' => self::FREE,
                    'data' => serialize($request),
                ]);

                return $ret === 1;
            }

            return false;
        } catch (UniqueConstraintViolationException $e) {
            return false;
        }
    }

    public function pop($leaseTime = 30)
    {
        $conn = $this->connection;
        $sql = "SELECT * FROM " . $conn->getDatabasePlatform()
                ->appendLockHint($this->table,
                    LockMode::PESSIMISTIC_READ) . " WHERE status = ? AND expire <= ? LIMIT 1";

        $return = null;

        $conn->transactional(function () use (
            $sql,
            $conn,
            &$return,
            $leaseTime
        ) {
            if ($res = $conn->executeQuery($sql, [self::FREE, time()])
                ->fetch()
            ) {
                $expire = time() + $leaseTime;
                $conn->update($this->table, [
                    'expire' => $expire
                ], [
                    'identifier' => $res['identifier']
                ]);
                $return = unserialize($res['data']);
            }
        });

        return $return;
    }

    public function complete(RequestInterface $request)
    {
        $key = $this->getKey($request);

        return $this->updateIfExistsAndIsPending($key, [
            'expire' => 0,
            'status' => self::COMPLETE,
        ]);
    }

    public function release(RequestInterface $request)
    {
        $key = $this->getKey($request);

        return $this->updateIfExistsAndIsPending($key, [
            'expire' => 0,
            'status' => self::FREE,
        ]);
    }

    public function count($status = self::FREE)
    {
        $table = $this->table;
        switch ($status) {
            case self::FREE:
                return (int)$this->connection->executeQuery("SELECT COUNT(*) FROM $table WHERE status = ? AND expire <= ?",
                    array(
                        self::FREE,
                        time()
                    ))->fetchColumn();
            case self::PENDING:
                return (int)$this->connection->executeQuery("SELECT COUNT(*) FROM $table WHERE status = ? AND expire > ?",
                    array(
                        self::FREE,
                        time()
                    ))->fetchColumn();
            case self::COMPLETE:
                return (int)$this->connection->executeQuery("SELECT COUNT(*) FROM $table WHERE status = ?",
                    array(
                        self::COMPLETE
                    ))->fetchColumn();
        }
        throw new \RuntimeException(sprintf('Unexpected status %s',
            (string)$status));
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

    private function updateIfExistsAndIsPending(
        $key,
        array $data
    ) {
        $sql = "UPDATE {$this->table} SET status = ?, expire = ? WHERE identifier = ? AND status = ? AND expire > ?";
        $ret = $this->connection->executeUpdate($sql, [$data['status'], $data['expire'], $key, self::FREE, time()]);
        if($ret === 1) {
            return TRUE;
        }
        throw new \RuntimeException('This request is not managed by this queue.');
    }

}