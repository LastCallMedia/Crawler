<?php

namespace LastCall\Crawler\Test\Configuration;

use Doctrine\DBAL\DriverManager;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use LastCall\Crawler\Configuration\Configuration;
use LastCall\Crawler\Event\CrawlerResponseEvent;
use LastCall\Crawler\Handler\Fragment\FragmentHandler;
use LastCall\Crawler\Handler\Logging\ExceptionLogger;
use LastCall\Crawler\Handler\Logging\RequestLogger;
use LastCall\Crawler\Queue\ArrayRequestQueue;
use LastCall\Crawler\Queue\DoctrineRequestQueue;
use LastCall\Crawler\Session\Session;
use LastCall\Crawler\Uri\Matcher;
use LastCall\Crawler\Uri\Normalizer;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Psr\Log\LoggerInterface;

class ContainerConfigurationTest extends \PHPUnit_Framework_TestCase
{
    public function getBaseContainer()
    {
        return [[new Configuration('https://lastcallmedia.com')]];
    }

    /**
     * @dataProvider getBaseContainer
     */
    public function testGetQueue($config)
    {
        $this->assertInstanceOf(ArrayRequestQueue::class, $config->getQueue());
    }

    /**
     * @dataProvider getBaseContainer
     */
    public function testGetQueueWithDoctrine($config)
    {
        $config['doctrine'] = DriverManager::getConnection([
            'driver' => 'pdo_sqlite',
            'memory' => true,
        ]);
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
        $this->assertInstanceOf(FragmentHandler::class, $subscribers['fragment']);
        $this->assertInstanceOf(RequestLogger::class, $subscribers['requestLogger']);
        $this->assertInstanceOf(ExceptionLogger::class, $subscribers['exceptionLogger']);
    }

    /**
     * @dataProvider getBaseContainer
     */
    public function testUsesLogger(Configuration $config)
    {
        $logger = $this->prophesize(LoggerInterface::class);
        $logger->warning(Argument::any(), Argument::any())->shouldBeCalled();
        $config['logger'] = $logger->reveal();
        $subscribers = $config->getSubscribers();
        $subscribers['requestLogger']->onFailure(new CrawlerResponseEvent(
            new Request('GET', 'http://google.com'),
            new Response()
        ));
    }

    public function testHasMatcher()
    {
        $config = new Configuration('https://lastcallmedia.com');
        $expected = Matcher::all()
            ->schemeIs(['http', 'https'])
            ->hostIs('lastcallmedia.com');
        $this->assertEquals($expected, $config['matcher']);
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

    /**
     * @dataProvider getBaseContainer
     */
    public function testAddsInitialRequestOnStart(Configuration $config)
    {
        $queue = $config->getQueue();
        $this->assertEquals(0, $queue->count());
        $session = Session::createFromConfig($config, new EventDispatcher());
        $session->start();
        $this->assertEquals(1, $queue->count());
    }
}
