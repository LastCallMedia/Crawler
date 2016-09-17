<?php

namespace LastCall\Crawler\Queue;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\DBAL\LockMode;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use LastCall\Crawler\Common\DoctrineSetupTeardownTrait;
use LastCall\Crawler\Common\SetupTeardownInterface;
use Psr\Http\Message\RequestInterface;

/**
 * Database backed request queue.
 */
class DoctrineRequestQueue implements RequestQueueInterface, SetupTeardownInterface
{
    use DoctrineSetupTeardownTrait;

    /**
     * @var \Doctrine\DBAL\Connection
     */
    private $connection;

    private $table = 'queue';

    private $lockHint;

    public function __construct(Connection $connection, $table = 'queue')
    {
        $this->connection = $connection;
        $this->table = $table;
    }

    private function getKey(RequestInterface $request)
    {
        return $request->getMethod().$request->getUri();
    }

    private function lockHint() {
        if(!$this->lockHint) {
            $this->lockHint = $this->connection->getDatabasePlatform()
                ->appendLockHint($this->table,
                    LockMode::PESSIMISTIC_READ);
        }
        return $this->lockHint;
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

    public function pushMultiple(array $requests)
    {
        $return = array_fill_keys(array_keys($requests), false);
        $keys = array_unique(array_map([$this, 'getKey'], $requests));
        $requests = array_intersect_key($requests, $keys);
        $exists = $this->multipleExists($keys);
        $requests = array_diff_key($requests, $exists);
        if (count($requests)) {
            try {
                $params = $clauses = [];
                $sql = "INSERT INTO {$this->table} (expire, identifier, status, data) VALUES ";
                foreach ($requests as $i => $request) {
                    $clauses[] = '(0, ?, 1, ?)';
                    $params[] = $keys[$i];
                    $params[] = serialize($request);
                    $return[$i] = true;
                }
                $sql .= implode(', ', $clauses);
                $this->connection->executeUpdate($sql, $params);
            } catch (UniqueConstraintViolationException $e) {
                return $return;
            }
        }

        return $return;
    }

    public function pop($leaseTime = 30)
    {
        $conn = $this->connection;
        $sql = 'SELECT * FROM '.$this->lockHint().' WHERE status = 1 AND expire <= '.time().' LIMIT 1';

        $return = null;

        $this->connection->beginTransaction();
        try {
            if ($res = $conn->query($sql)->fetch()) {
                $expire = (int) time() + $leaseTime;
                $conn->exec("UPDATE {$this->table} SET expire = $expire WHERE identifier = ".$this->connection->quote($res['identifier']));
                $return = unserialize($res['data']);
            }
            $this->connection->commit();
        } catch (\Exception $e) {
            $this->connection->rollBack();
            throw $e;
        }

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
                return (int) $this->connection->query("SELECT COUNT(*) FROM {$table} WHERE status = 1 AND expire <= ".time())->fetchColumn();
            case self::PENDING:
                return (int) $this->connection->query("SELECT COUNT(*) FROM {$table} WHERE status = 1 AND expire > ".time())->fetchColumn();
            case self::COMPLETE:
                return (int) $this->connection->query("SELECT COUNT(*) FROM {$table} WHERE status = 3")->fetchColumn();
        }
        throw new \RuntimeException(sprintf('Unexpected status %s',
            (string) $status));
    }

    protected function getConnection()
    {
        return $this->connection;
    }

    protected function getSchema()
    {
        $table = new Table($this->table);
        $table->addColumn('identifier', 'binary');
        $table->addColumn('status', 'integer');
        $table->addColumn('expire', 'integer');
        $table->addColumn('data', 'object');
        $table->setPrimaryKey(['identifier']);
        $table->addIndex(['status', 'expire'], 'status_expire');

        return new Schema([$table]);
    }

    private function exists($identifier)
    {
        return $this->connection->executeQuery("SELECT 1 FROM {$this->table} WHERE identifier = ?",
            [
                $identifier,
            ])->fetchColumn();
    }

    private function multipleExists(array $identifiers)
    {
        $conn = $this->connection;
        $sql = "SELECT identifier FROM {$this->table} WHERE identifier IN(?)";
        $existing = $conn->executeQuery($sql, [$identifiers],
            [Connection::PARAM_STR_ARRAY])->fetchAll(\PDO::FETCH_COLUMN);

        // Re-key the identifiers using the same scheme that was passed in.
        return array_intersect($identifiers, $existing);
    }

    private function updateIfExistsAndIsPending(
        $key,
        array $data
    ) {
        $sql = "UPDATE {$this->table} SET status = ?, expire = ? WHERE identifier = ? AND status = ? AND expire > ?";
        $ret = $this->connection->executeUpdate($sql,
            [$data['status'], $data['expire'], $key, self::FREE, time()]);
        if ($ret === 1) {
            return true;
        }
        throw new \RuntimeException('This request is not managed by this queue.');
    }
}
