<?php


namespace LastCall\Crawler\Test\Common;

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Schema\Schema;
use LastCall\Crawler\Common\DoctrineSetupTeardownTrait;


class DoctrineSetupTeardownTraitTest extends \PHPUnit_Framework_TestCase
{
    private function getTraitMock(Schema $schema, $connection)
    {
        $mock = $this->getMockForTrait(DoctrineSetupTeardownTrait::class);
        $mock->expects($this->once())->method('getSchema')->willReturn($schema);
        $mock->expects($this->once())
            ->method('getConnection')
            ->willReturn($connection);

        return $mock;
    }

    public function testSetupCreatesTable()
    {
        $schema = new Schema();
        $table = $schema->createTable('foo');
        $table->addColumn('id', 'integer');

        $connection = DriverManager::getConnection([
            'driver' => 'pdo_sqlite',
            'memory' => true,
        ]);

        $mock = $this->getTraitMock($schema, $connection);
        $mock->onSetup();
        $this->assertTrue($connection->getSchemaManager()
            ->tablesExist(['foo']));
    }

    public function testTeardownRemovesTable()
    {
        $schema = new Schema();
        $table = $schema->createTable('foo');
        $table->addColumn('id', 'integer');

        $connection = DriverManager::getConnection([
            'driver' => 'pdo_sqlite',
            'memory' => true,
        ]);
        $connection->exec("CREATE TABLE foo(id INTEGER)");
        $mock = $this->getTraitMock($schema, $connection);
        $mock->onTeardown();
        $this->assertFalse($connection->getSchemaManager()
            ->tablesExist(['foo']));
    }

    public function testSetupOverwritesTable()
    {
        $schema = new Schema();
        $table = $schema->createTable('foo');
        $table->addColumn('id', 'integer');

        $connection = DriverManager::getConnection([
            'driver' => 'pdo_sqlite',
            'memory' => true,
        ]);
        $connection->exec("CREATE TABLE foo(id INTEGER, key INTEGER)");
        $mock = $this->getTraitMock($schema, $connection);
        $mock->onSetup();
        $this->assertTrue($connection->getSchemaManager()
            ->tablesExist(['foo']));
        $columns = $connection->getSchemaManager()->listTableColumns('foo');
        $this->assertEquals(['id'], array_keys($columns));
    }

    public function testTeardownDoesntFailOnNonexistentTable()
    {
        $schema = new Schema();
        $table = $schema->createTable('foo');
        $table->addColumn('id', 'integer');

        $connection = DriverManager::getConnection([
            'driver' => 'pdo_sqlite',
            'memory' => true,
        ]);
        $mock = $this->getTraitMock($schema, $connection);
        $mock->onTeardown();
    }

}