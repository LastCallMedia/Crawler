<?php


namespace LastCall\Crawler\Test\Handler\Module;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use LastCall\Crawler\CrawlerEvents;
use LastCall\Crawler\Event\CrawlerResponseEvent;
use LastCall\Crawler\Handler\Module\DomModule;
use LastCall\Crawler\Listener\ModuleSubscriber;
use LastCall\Crawler\Module\ModuleParser;
use LastCall\Crawler\Test\Handler\HandlerTestTrait;
use LastCall\Crawler\Url\URLHandler;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;

class DomModuleTest extends \PHPUnit_Framework_TestCase
{
    use HandlerTestTrait;

    public function testCallsParse()
    {
        $parser = $this->prophesize('LastCall\Crawler\Module\ModuleParser');
        $parser->parse(Argument::type('Symfony\Component\DomCrawler\Crawler'))
            ->shouldBeCalled()
            ->willReturn([]);

        $this->dispatchEvent($parser->reveal());
    }


    public function testProcessesModule()
    {
        $parser = $this->prophesize('LastCall\Crawler\Module\ModuleParser');
        $parser->parse(Argument::type('Symfony\Component\DomCrawler\Crawler'))
            ->shouldBeCalled()
            ->willReturn([
                ['type' => 'foo']
            ]);
        $processor = $this->prophesize('LastCall\Crawler\Module\ModuleProcessor');
        $processor->getModuleTypes()->shouldBeCalled()->willReturn(['foo']);
        $processor->process(['type' => 'foo'])->shouldBeCalled();

        $this->dispatchEvent($parser->reveal(), [$processor->reveal()]);
    }

    public function testLogsUnknownModule()
    {
        $parser = $this->prophesize('LastCall\Crawler\Module\ModuleParser');
        $logger = $this->prophesize('PSR\Log\LoggerInterface');

        $parser->parse(Argument::type('Symfony\Component\DomCrawler\Crawler'))
            ->shouldBeCalled()
            ->willReturn([
                ['type' => 'foo']
            ]);

        $logger->warning('Unknown module type: foo')->shouldBeCalled();

        $this->dispatchEvent($parser->reveal(), [], $logger->reveal());
    }

    private function dispatchEvent(
        ModuleParser $parser,
        array $processors = [],
        LoggerInterface $logger = null
    ) {
        $urlHandler = $this->prophesize(URLHandler::class);
        $request = new Request('GET', 'http://google.com');
        $event = new CrawlerResponseEvent($request, new Response(),
            $urlHandler->reveal());
        $handler = new DomModule($parser, $processors, $logger);
        $this->invokeEvent($handler, CrawlerEvents::SUCCESS, $event);
    }

}