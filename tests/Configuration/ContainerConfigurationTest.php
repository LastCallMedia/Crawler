<?php

namespace LastCall\Crawler\Test\Configuration;

use GuzzleHttp\ClientInterface;
use LastCall\Crawler\Configuration\Configuration;
use LastCall\Crawler\Session\Session;
use Symfony\Component\EventDispatcher\EventDispatcher;

class ContainerConfigurationTest extends \PHPUnit_Framework_TestCase
{
    public function testHasClient()
    {
        $config = new Configuration();
        $this->assertInstanceOf(ClientInterface::class, $config->getClient());
    }

    public function testHasListenersArray()
    {
        $config = new Configuration();
        $this->assertTrue(is_array($config['listeners']));
    }

    public function testHasSubscribersArray()
    {
        $config = new Configuration();
        $this->assertTrue(is_array($config['subscribers']));
    }

    public function testAddsInitialRequestOnStart()
    {
        $config = new Configuration('https://lastcallmedia.com');
        $queue = $config->getQueue();
        $this->assertEquals(0, $queue->count());
        $session = Session::createFromConfig($config, new EventDispatcher());
        $session->start();
        $this->assertEquals(1, $queue->count());
    }
}
