<?php

namespace LastCall\Crawler\Queue\Driver;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\DBAL\LockMode;
use Doctrine\DBAL\Schema\Table;
use LastCall\Crawler\Queue\Job;

class DoctrineDriver implements DriverInterface, UniqueJobInterface
{

    private $connection;

    private $table;

    private $_cache = array();

    public function __construct(Connection $connection, $table = 'Job')
    {
        $this->connection = $connection;
        $this->table = $table;
    }

    public function createTable()
    {
        $table = new Table($this->table);
        $table->addColumn('id', 'integer')->setAutoincrement(true);
        $table->addColumn('identifier', 'binary', ['nullable' => true]);
        $table->addColumn('queue', 'string');
        $table->addColumn('status', 'integer');
        $table->addColumn('expire', 'integer');
        $table->addColumn('data', 'object');

        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['identifier', 'queue'], 'queue_identifier');
        $this->connection->getSchemaManager()->dropAndCreateTable($table);
    }

    public function pushUnique(Job $job, $key)
    {
        try {
            if (!$this->exists($job, $key)) {
                return $this->doPush($job, $key);
            }
        } catch (UniqueConstraintViolationException $e) {
            // This is OK.  The record wasn't inserted.
        }

        return false;
    }

    public function push(Job $job)
    {
        return $this->doPush($job, uniqid());
    }

    private function exists(Job $job, $key)
    {
        $channel = $job->getQueue();
        if (!isset($this->_cache[$channel])) {
            $this->_cache[$channel] = array();
        }
        if (!isset($this->_cache[$channel][$key])) {
            $this->_cache[$channel][$key] = (bool) $this->connection->executeQuery("SELECT 1 FROM {$this->table} WHERE identifier = ? AND queue = ?",
                array(
                    $key,
                    $job->getQueue()
                ))->fetchColumn();
        }

        return $this->_cache[$channel][$key];
    }

    private function doPush(Job $job, $key)
    {
        return 1 === $this->connection->insert($this->table, [
            'expire' => $job->getExpire(),
            'identifier' => $key,
            'status' => $job->getStatus(),
            'queue' => $job->getQueue(),
            'data' => serialize($job->getData()),
        ]) && $job->setIdentifier($key) && ($this->_cache[$job->getQueue()][$key] = true);
    }

    public function pop($channel, $leaseTime = 30)
    {
        $conn = $this->connection;
        $sql = "SELECT * FROM " . $conn->getDatabasePlatform()
                ->appendLockHint($this->table,
                    LockMode::PESSIMISTIC_READ) . " WHERE queue = ? AND status = ? AND expire <= ? LIMIT 1";

        $return = null;

        $this->connection->transactional(function () use (
            $channel,
            $sql,
            $conn,
            &$return,
            $leaseTime
        ) {
            if ($res = $conn->executeQuery($sql, [$channel, Job::FREE, time()])
                ->fetch()
            ) {
                $expire = time() + $leaseTime;
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

    private function hydrateRecord(array $record)
    {
        $refl = new \ReflectionClass('LastCall\Crawler\Queue\Job');
        $job = $refl->newInstanceWithoutConstructor();
        $record['data'] = unserialize($record['data']);
        foreach (array(
                     'data',
                     'id',
                     'queue',
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

    public function complete(Job $job)
    {
        $ret = $this->connection->update($this->table, [
            'expire' => 0,
            'status' => Job::COMPLETE,
        ], [
            'id' => $job->getId(),
        ]);

        return $ret === 1 && $job->setStatus(Job::COMPLETE)->setExpire(0);
    }

    public function release(Job $job)
    {
        $ret = $this->connection->update($this->table, [
            'expire' => 0,
        ], [
            'id' => $job->getId(),
        ]);

        return $ret === 1 && $job->setStatus(Job::FREE)->setExpire(0);
    }

    public function count($channel, $status = Job::FREE)
    {
        $table = $this->table;
        switch ($status) {
            case Job::FREE:
                return (int) $this->connection->executeQuery("SELECT COUNT(*) FROM $table WHERE queue = ? AND status = ? AND expire <= ?",
                    array(
                        $channel,
                        Job::FREE,
                        time()
                    ))->fetchColumn();
            case Job::CLAIMED:
                return (int) $this->connection->executeQuery("SELECT COUNT(*) FROM $table WHERE queue = ? AND status = ? AND expire > ?",
                    array(
                        $channel,
                        Job::FREE,
                        time()
                    ))->fetchColumn();
            default:
                return (int) $this->connection->executeQuery("SELECT COUNT(*) FROM $table WHERE queue = ? AND status = ?",
                    array(
                        $channel,
                        Job::COMPLETE
                    ))->fetchColumn();
        }
    }


}