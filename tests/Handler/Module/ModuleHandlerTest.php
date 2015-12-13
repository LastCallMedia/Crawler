<?php


namespace LastCall\Crawler\Test\Handler\Module;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use LastCall\Crawler\CrawlerEvents;
use LastCall\Crawler\Event\CrawlerResponseEvent;
use LastCall\Crawler\Module\Parser\XPathParser;
use LastCall\Crawler\Module\Processor\ModuleProcessorInterface;
use LastCall\Crawler\Test\Handler\HandlerTestTrait;
use LastCall\Crawler\Test\Resources\DummyProcessor;
use LastCall\Crawler\Url\URLHandler;
use Prophecy\Argument;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;

class ModuleHandlerTest extends \PHPUnit_Framework_TestCase
{
    use HandlerTestTrait;

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage ::getSubscribedMethods must return an array.
     */
    public function testChecksForValidSubscriptionArray()
    {
        $processor = $this->prophesize(ModuleProcessorInterface::class);
        $processor->getSubscribedMethods()->willReturn(false);
        new \LastCall\Crawler\Handler\Module\ModuleHandler([], [$processor->reveal()]);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid module subscription
     */
    public function testChecksForValidSubscription()
    {
        $processor = $this->prophesize(ModuleProcessorInterface::class);
        $processor->getSubscribedMethods()->willReturn([false]);
        new \LastCall\Crawler\Handler\Module\ModuleHandler([], [$processor->reveal()]);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage No parser was specified
     */
    public function testChecksForSetSubscriptionParser()
    {
        $processor = new DummyProcessor(null, null);
        new \LastCall\Crawler\Handler\Module\ModuleHandler([], [$processor]);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid parser foo
     */
    public function testChecksForValidParser()
    {
        $processor = new DummyProcessor('foo', 'bar');
        new \LastCall\Crawler\Handler\Module\ModuleHandler([], [$processor]);
    }

    public function testCallsParsersAndSubscribers()
    {
        $processor = new DummyProcessor('xpath', 'descendant-or-self::a');
        $parser = new XPathParser();

        $handler = new \LastCall\Crawler\Handler\Module\ModuleHandler([$parser], [$processor]);

        $html = '<html><a>Foo</a></html>';
        $req = new Request('GET', 'https://lastcallmedia.com');
        $res = new Response(200, [], $html);
        $urlHandler = new URLHandler('https://lastcallmedia.com');
        $event = new CrawlerResponseEvent($req, $res, $urlHandler);
        $this->invokeEvent($handler, CrawlerEvents::SUCCESS, $event);

        $this->assertCount(1, $processor->getCalls());
        $this->assertEquals($event, $processor->getCalls()[0][0]);
        $this->assertInstanceOf(DomCrawler::class,
            $processor->getCalls()[0][1]);
    }
}