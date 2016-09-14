<?php

namespace LastCall\Crawler\Test\Handler;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use LastCall\Crawler\CrawlerEvents;
use LastCall\Crawler\Event\CrawlerHtmlResponseEvent;
use LastCall\Crawler\Event\CrawlerRequestEvent;
use LastCall\Crawler\Event\CrawlerResponseEvent;
use LastCall\Crawler\Handler\HtmlRedispatcher;
use Symfony\Component\EventDispatcher\EventDispatcher;

class HtmlRedispatcherTest extends \PHPUnit_Framework_TestCase
{
    public function getEventTypes()
    {
        return [
            [CrawlerEvents::SUCCESS],
            [CrawlerEvents::FAILURE],
        ];
    }

    /**
     * @dataProvider getEventTypes
     */
    public function testIgnoresRequestEvents($eventName)
    {
        $dispatcher = new EventDispatcher();
        $dispatcher->addSubscriber(new HtmlRedispatcher());
        $dispatcher->addListener($eventName.'.html', function () use ($eventName) {
            $this->fail($eventName.'.html called');
        });
        $dispatcher->dispatch($eventName, new CrawlerRequestEvent(
            new Request('GET', 'http://google.com')
        ));
    }

    /**
     * @dataProvider getEventTypes
     */
    public function testDispatchesOnHtmlResponses($eventName)
    {
        $dispatcher = new EventDispatcher();
        $dispatcher->addSubscriber(new HtmlRedispatcher());
        $dispatcher->addListener($eventName.'.html', function (CrawlerHtmlResponseEvent $event) use (&$called) {
            ++$called;
            $event->addAdditionalRequest(new Request('GET', 'http://google.com/2'));
        });
        $event = new CrawlerResponseEvent(
            new Request('GET', 'http://google.com'),
            new Response(200)
        );
        $dispatcher->dispatch($eventName, $event);
        $this->assertEquals(1, $called);
        $this->assertCount(1, $event->getAdditionalRequests());
    }
}
