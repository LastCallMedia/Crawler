<?php

namespace LastCall\Crawler\Common;

use Doctrine\DBAL\Schema\Synchronizer\SingleDatabaseSynchronizer;

/**
 * Wires setup and teardown handlers to creation/destruction of tables
 * in a Doctrine connection.
 */
trait DoctrineSetupTeardownTrait
{
    public function onSetup()
    {
        $connection = $this->getConnection();
        $synchronizer = new SingleDatabaseSynchronizer($connection);
        $schema = $this->getSchema();
        $synchronizer->dropSchema($schema);
        $synchronizer->createSchema($schema);
    }

    /**
     * @return \Doctrine\DBAL\Connection
     */
    abstract protected function getConnection();

    /**
     * @return \Doctrine\DBAL\Schema\Schema
     */
    abstract protected function getSchema();

    public function onTeardown()
    {
        $connection = $this->getConnection();
        $synchronizer = new SingleDatabaseSynchronizer($connection);
        $synchronizer->dropSchema($this->getSchema());
    }
}
