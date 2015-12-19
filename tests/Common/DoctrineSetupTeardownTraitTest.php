<?php


namespace LastCall\Crawler\Test\Common;

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Schema\Table;
use LastCall\Crawler\Common\DoctrineSetupTeardownTrait;


class DoctrineSetupTeardownTraitTest extends \PHPUnit_Framework_TestCase
{
    private function getTraitMock($tables, $connection)
    {
        $mock = $this->getMockForTrait(DoctrineSetupTeardownTrait::class);
        $mock->expects($this->once())->method('getTables')->willReturn($tables);
        $mock->expects($this->once())
            ->method('getConnection')
            ->willReturn($connection);

        return $mock;
    }

    public function testSetupCreatesTable()
    {
        $table = new Table('foo');
        $table->addColumn('id', 'integer');
        $connection = DriverManager::getConnection([
            'driver' => 'pdo_sqlite',
            'memory' => true,
        ]);
        $mock = $this->getTraitMock([$table], $connection);
        $mock->onSetup();
        $this->assertTrue($connection->getSchemaManager()
            ->tablesExist(['foo']));
    }

    public function testTeardownRemovesTable()
    {
        $table = new Table('foo');
        $table->addColumn('id', 'integer');
        $connection = DriverManager::getConnection([
            'driver' => 'pdo_sqlite',
            'memory' => true,
        ]);
        $connection->exec("CREATE TABLE foo(id INTEGER)");
        $mock = $this->getTraitMock([$table], $connection);
        $mock->onTeardown();
        $this->assertFalse($connection->getSchemaManager()
            ->tablesExist(['foo']));
    }

    public function testSetupOverwritesTable()
    {
        $table = new Table('foo');
        $table->addColumn('id', 'integer');
        $connection = DriverManager::getConnection([
            'driver' => 'pdo_sqlite',
            'memory' => true,
        ]);
        $connection->exec("CREATE TABLE foo(id INTEGER, key INTEGER)");
        $mock = $this->getTraitMock([$table], $connection);
        $mock->onSetup();
        $this->assertTrue($connection->getSchemaManager()
            ->tablesExist(['foo']));
        $columns = $connection->getSchemaManager()->listTableColumns('foo');
        $this->assertEquals(['id'], array_keys($columns));
    }

    public function testTeardownDoesntFailOnNonexistentTable()
    {
        $table = new Table('foo');
        $table->addColumn('id', 'integer');
        $connection = DriverManager::getConnection([
            'driver' => 'pdo_sqlite',
            'memory' => true,
        ]);
        $mock = $this->getTraitMock([$table], $connection);
        $mock->onTeardown();
    }

}