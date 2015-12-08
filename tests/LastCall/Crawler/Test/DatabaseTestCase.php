<?php

namespace LastCall\Crawler\Test;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use LastCall\DoctrineServiceProvider\Doctrine\PimpleManagerRegistry;
use Pimple\Container;

abstract class DatabaseTestCase extends \PHPUnit_Framework_TestCase
{

    /**
     * @return \Doctrine\ORM\EntityManager
     */
    protected function getEM()
    {
        $doctrine = $this->getDoctrine();

        return $doctrine->getManager();
    }

    /**
     * @return \Doctrine\ORM\Configuration
     */
    protected function getConfiguration()
    {
        $config = new Configuration();
        $config->setProxyDir(sys_get_temp_dir() . '/phpunit-proxies');
        $config->setProxyNamespace('DoctrineProxy');

        $driver = $config->newDefaultAnnotationDriver(array(
          __DIR__ . '/../../../../src/LastCall/Crawler/Queue',
        ), false);
        $config->setMetadataDriverImpl($driver);

        return $config;
    }

    protected function getConnection()
    {
        $connectionParams = array(
          'driver' => 'pdo_sqlite',
          'memory' => true,
        );

        return DriverManager::getConnection($connectionParams);
    }

    protected function getManager(Connection $connection, Configuration $config)
    {
        return EntityManager::create($connection, $config);
    }

    protected function createSchemas(EntityManager $manager)
    {
        $metadatas = $manager->getMetadataFactory()->getAllMetadata();
        $tool = new SchemaTool($manager);
        $tool->createSchema($metadatas);
    }

    /**
     * @return \Doctrine\Common\Persistence\ManagerRegistry
     */
    protected function getDoctrine()
    {
        $pimple = new Container();
        $pimple['connection'] = function () {
            return $this->getConnection();
        };
        $pimple['configuration'] = function () {
            return $this->getConfiguration();
        };
        $pimple['manager'] = function () use ($pimple) {
            $manager = $this->getManager($pimple['connection'],
              $pimple['configuration']);
            $this->createSchemas($manager);

            return $manager;
        };

        return new PimpleManagerRegistry($pimple,
          array('connection' => 'connection'), array('manager' => 'manager'),
          'connection', 'manager');
    }
}