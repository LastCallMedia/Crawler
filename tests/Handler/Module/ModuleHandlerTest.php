<?php


namespace LastCall\Crawler\Test\Handler\Module;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use LastCall\Crawler\Common\SetupTeardownInterface;
use LastCall\Crawler\CrawlerEvents;
use LastCall\Crawler\Event\CrawlerResponseEvent;
use LastCall\Crawler\Fragment\Parser\XPathParser;
use LastCall\Crawler\Fragment\Processor\FragmentProcessorInterface;
use LastCall\Crawler\Handler\Fragment\FragmentHandler;
use LastCall\Crawler\Test\Handler\HandlerTestTrait;
use LastCall\Crawler\Test\Resources\DummyProcessor;
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
        $processor = $this->prophesize(FragmentProcessorInterface::class);
        $processor->getSubscribedMethods()->willReturn(false);
        new \LastCall\Crawler\Handler\Fragment\FragmentHandler([],
            [$processor->reveal()]);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid module subscription
     */
    public function testChecksForValidSubscription()
    {
        $processor = $this->prophesize(FragmentProcessorInterface::class);
        $processor->getSubscribedMethods()->willReturn([false]);
        new \LastCall\Crawler\Handler\Fragment\FragmentHandler([],
            [$processor->reveal()]);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage No parser was specified
     */
    public function testChecksForSetSubscriptionParser()
    {
        $processor = new DummyProcessor(null, null);
        new \LastCall\Crawler\Handler\Fragment\FragmentHandler([],
            [$processor]);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid parser foo
     */
    public function testChecksForValidParser()
    {
        $processor = new DummyProcessor('foo', 'bar');
        new \LastCall\Crawler\Handler\Fragment\FragmentHandler([],
            [$processor]);
    }

    public function testCallsParsersAndSubscribers()
    {
        $processor = new DummyProcessor('xpath', 'descendant-or-self::a');
        $parser = new XPathParser();

        $handler = new \LastCall\Crawler\Handler\Fragment\FragmentHandler([$parser],
            [$processor]);

        $html = '<html><a>Foo</a></html>';
        $req = new Request('GET', 'https://lastcallmedia.com');
        $res = new Response(200, [], $html);
        $event = new CrawlerResponseEvent($req, $res);
        $this->invokeEvent($handler, CrawlerEvents::SUCCESS, $event);

        $this->assertCount(1, $processor->getCalls());
        $this->assertEquals($event, $processor->getCalls()[0][0]);
        $this->assertInstanceOf(DomCrawler::class,
            $processor->getCalls()[0][1]);
    }

    public function testCallsSetup()
    {
        $processor = $this->prophesize(FragmentProcessorInterface::class);
        $processor->willImplement(SetupTeardownInterface::class);
        $processor->getSubscribedMethods()->willReturn([]);
        $processor->onSetup()->shouldBeCalled();

        $handler = new FragmentHandler([], [$processor->reveal()]);
        $handler->onSetup();
    }

    public function testCallsTeardown()
    {
        $processor = $this->prophesize(FragmentProcessorInterface::class);
        $processor->willImplement(SetupTeardownInterface::class);
        $processor->getSubscribedMethods()->willReturn([]);
        $processor->onTeardown()->shouldBeCalled();

        $handler = new FragmentHandler([], [$processor->reveal()]);
        $handler->onTeardown();
    }
}