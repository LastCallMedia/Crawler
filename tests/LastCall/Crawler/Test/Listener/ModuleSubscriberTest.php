<?php

namespace LastCall\Crawler\Test\Listener;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use LastCall\Crawler\Configuration\Configuration;
use LastCall\Crawler\Crawler;
use LastCall\Crawler\Event\CrawlerResponseEvent;
use LastCall\Crawler\Listener\ModuleSubscriber;
use LastCall\Crawler\Module\ModuleParser;
use LastCall\Crawler\Url\URLHandler;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use LastCall\Crawler\Queue\QueueInterface;

class ModuleSubscriberTest extends \PHPUnit_Framework_TestCase
{

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

        $logger->warning('Unknown module type: foo')
          ->shouldBeCalled();

        $this->dispatchEvent($parser->reveal(), [], $logger->reveal());
    }

    private function dispatchEvent(ModuleParser $parser, array $processors = [], LoggerInterface $logger = NULL)
    {
        $queue = $this->prophesize(QueueInterface::class);
        $urlHandler = $this->prophesize(URLHandler::class);
        $request = new Request('GET', 'http://google.com');
        $event = new CrawlerResponseEvent($request, new Response(), $queue->reveal(), $urlHandler->reveal());
        $subscriber = new ModuleSubscriber($parser, $processors, $logger);
        $subscriber->onCrawlerSuccess($event);
    }
}