<?php

namespace LastCall\Crawler\Common;

/**
 * Wires setup and teardown handlers to creation/destruction of tables
 * in a Doctrine connection.
 */
trait DoctrineSetupTeardownTrait
{

    public function onSetup()
    {
        $connection = $this->getConnection();
        foreach ($this->getTables() as $table) {
            $connection->getSchemaManager()->dropAndCreateTable($table);
        }
    }

    /**
     * @return \Doctrine\DBAL\Connection
     */
    abstract protected function getConnection();

    /**
     * @return \Doctrine\DBAL\Schema\Table[]
     */
    abstract protected function getTables();

    public function onTeardown()
    {
        $connection = $this->getConnection();
        foreach ($this->getTables() as $table) {
            $connection->getSchemaManager()->dropTable($table->getName());
        }
    }

}