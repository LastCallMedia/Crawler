<?php

namespace LastCall\Crawler\Test\Handler;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;
use LastCall\Crawler\CrawlerEvents;
use LastCall\Crawler\Event\CrawlerRequestEvent;
use LastCall\Crawler\Handler\MatchingProxy;
use LastCall\Crawler\Test\Resources\DummyEventSubscriber;
use LastCall\Crawler\Uri\Matcher;
use Psr\Http\Message\UriInterface;

class MatchingProxyTest extends \PHPUnit_Framework_TestCase
{
    use HandlerTestTrait;

    public function getUncheckedCalls()
    {
        return [
            [CrawlerEvents::SETUP],
            [CrawlerEvents::TEARDOWN],
            [CrawlerEvents::FINISH],
        ];
    }

    /**
     * @dataProvider getUncheckedCalls
     */
    public function testSendsEventsWithoutCheckingMatch($eName)
    {
        $match = Matcher::all()->never();
        $sub = new DummyEventSubscriber();
        $proxy = new MatchingProxy($sub, $match);
        $this->invokeEvent($proxy, $eName);
        $this->assertEquals([$eName => 1], $sub->getCalls());
    }

    public function getCheckedCalls()
    {
        return [
            [CrawlerEvents::SENDING, new Uri('foo')],
            [CrawlerEvents::SUCCESS, new Uri('foo')],
            [CrawlerEvents::FAILURE, new Uri('foo')],
            [CrawlerEvents::EXCEPTION, new Uri('foo')],
        ];
    }

    /**
     * @dataProvider getCheckedCalls
     */
    public function testSendsEventOnMatch($eName, UriInterface $uri)
    {
        $match = Matcher::all()->always();
        $sub = new DummyEventSubscriber();
        $proxy = new MatchingProxy($sub, $match);
        $event = new CrawlerRequestEvent(new Request('GET', $uri));
        $this->invokeEvent($proxy, $eName, $event);
        $this->assertEquals([$eName => 1], $sub->getCalls());
    }

    /**
     * @dataProvider getCheckedCalls
     */
    public function testDoesNotSendEventOnNoMatch($eName, UriInterface $uri)
    {
        $match = Matcher::all()->never();
        $sub = new DummyEventSubscriber();
        $proxy = new MatchingProxy($sub, $match);
        $event = new CrawlerRequestEvent(new Request('GET', $uri));
        $this->invokeEvent($proxy, $eName, $event);
        $this->assertEquals([], $sub->getCalls());
    }
}
