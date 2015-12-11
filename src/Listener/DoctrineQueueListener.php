<?php

namespace LastCall\Crawler\Listener;


use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Table;
use LastCall\Crawler\Crawler;
use LastCall\Crawler\CrawlerEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DoctrineQueueListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents() {
        return array(
            CrawlerEvents::SETUP => 'onSetup',
            CrawlerEvents::TEARDOWN => 'onTeardown',
        );
    }

    private $connection;

    private $tableName = 'Job';

    public function __construct(Connection $connection, $tableName = 'Job')
    {
        $this->connection = $connection;
        $this->tableName = $tableName;
    }

    public function onSetup() {
        $table = new Table($this->tableName);
        $table->addColumn('id', 'integer')->setAutoincrement(TRUE);
        $table->addColumn('identifier', 'binary', ['nullable' => TRUE]);
        $table->addColumn('queue', 'string');
        $table->addColumn('status', 'integer');
        $table->addColumn('expire', 'integer');
        $table->addColumn('data', 'object');

        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['identifier', 'queue'], 'queue_identifier');
        $this->connection->getSchemaManager()->dropAndCreateTable($table);
    }

    public function onTeardown() {
        if ($this->connection->getSchemaManager()->tablesExist([$this->tableName])) {
            $this->connection->getSchemaManager()->dropTable($this->tableName);
        }
    }

}