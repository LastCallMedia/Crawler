<?php

namespace LastCall\Crawler\Test\Configuration;

use Doctrine\DBAL\DriverManager;
use GuzzleHttp\ClientInterface;
use LastCall\Crawler\Configuration\Configuration;
use LastCall\Crawler\Handler\Fragment\FragmentHandler;
use LastCall\Crawler\Handler\Logging\ExceptionLogger;
use LastCall\Crawler\Handler\Logging\RequestLogger;
use LastCall\Crawler\Queue\ArrayRequestQueue;
use LastCall\Crawler\Queue\DoctrineRequestQueue;
use LastCall\Crawler\Uri\Normalizer;
use Psr\Log\NullLogger;

class ContainerConfigurationTest extends \PHPUnit_Framework_TestCase
{
    public function getBaseContainer()
    {
        return [[new Configuration('https://lastcallmedia.com')]];
    }

    public function getDoctrineContainer()
    {
        $config = new Configuration('https://lastcallmedia.com');
        $config['doctrine'] = function () {
            return DriverManager::getConnection([
                'driver' => 'pdo_sqlite',
                'memory' => true,
            ]);
        };

        return [[$config]];
    }

    public function getLoggingContainer()
    {
        $config = new Configuration('https://lastcallmedia.com');
        $config['logger'] = function () {
            return new NullLogger();
        };

        return [[$config]];
    }

    /**
     * @dataProvider getBaseContainer
     */
    public function testGetQueue($config)
    {
        $this->assertInstanceOf(ArrayRequestQueue::class, $config->getQueue());
    }

    /**
     * @dataProvider getDoctrineContainer
     */
    public function testGetQueueWithDoctrine($config)
    {
        $this->assertInstanceOf(DoctrineRequestQueue::class,
            $config->getQueue());
    }

    /**
     * @dataProvider getBaseContainer
     */
    public function testGetClient($config)
    {
        $this->assertInstanceOf(ClientInterface::class, $config->getClient());
    }

    /**
     * @dataProvider getBaseContainer
     */
    public function testGetSubscribers(Configuration $config)
    {
        $subscribers = $config->getSubscribers();
        $this->assertCount(1, $subscribers);
        $this->assertInstanceOf(FragmentHandler::class,
            $subscribers['moduleHandler']);
    }

    /**
     * @dataProvider getLoggingContainer
     */
    public function testGetSubscribersWithLogger(Configuration $config)
    {
        $subscribers = $config->getSubscribers();
        $this->assertInstanceOf(RequestLogger::class,
            $subscribers['requestLogger']);
        $this->assertInstanceOf(ExceptionLogger::class,
            $subscribers['exceptionLogger']);
    }

    /**
     * @dataProvider getBaseContainer
     */
    public function testGetListeners(Configuration $config)
    {
        $listeners = $config->getListeners();
        $this->assertTrue(is_array($listeners));
    }

    /**
     * @dataProvider getBaseContainer
     */
    public function testGetNormalizer(Configuration $config)
    {
        $this->assertInstanceOf(Normalizer::class, $config['normalizer']);
    }
}
