<?php


namespace LastCall\Crawler\Test\Performance;


use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use LastCall\Crawler\Configuration\Configuration;
use LastCall\Crawler\Configuration\ConfigurationInterface;
use LastCall\Crawler\Crawler;
use LastCall\Crawler\Handler\Logging\ExceptionLoggingHandler;
use LastCall\Crawler\Handler\Logging\RequestLogger;
use LastCall\Crawler\Module\ModuleHandler;
use LastCall\Crawler\Module\Parser\XPathParser;
use LastCall\Crawler\Module\Processor\LinkProcessor;
use LastCall\Crawler\Queue\Driver\ArrayDriver;
use LastCall\Crawler\Queue\RequestQueue;
use LastCall\Crawler\Session\Session;
use Psr\Log\NullLogger;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Stopwatch\Stopwatch;

/**
 * @group performance
 */
class PerformanceTest extends \PHPUnit_Framework_TestCase
{

    private function getClient()
    {
        $handler = $this->handler();
        $client = new Client(['handler' => HandlerStack::create($handler)]);

        return $client;
    }

    private function handler()
    {
        return function (Request $request, array $options) {
            $status = 404;
            $body = null;
            $headers = [];

            $path = $request->getUri()->getPath();
            $file = __DIR__ . '/../Resources/html' . $path;
            if (file_exists($file)) {
                $status = 200;
                $handle = fopen($file, 'r');
                $body = \GuzzleHttp\Psr7\stream_for($handle);
            }
            $response = new Response($status, $headers, $body);

            return new FulfilledPromise($response);
        };
    }

    private function getQueue()
    {
        $queue = new RequestQueue(new ArrayDriver(), 'request');
        $queue->push(new Request('GET', 'http://example.com/index.html'));
        $queue->push(new Request('GET', 'http://example.com/1.html'));
        $queue->push(new Request('GET', 'http://example.com/2.html'));
        $queue->push(new Request('GET', 'http://example.com/nonexistent.html'));

        return $queue;
    }


    public function testLogging()
    {
        $configuration = new Configuration('http://example.com/index.html');
        $configuration->setQueue($this->getQueue());
        $configuration->setClient($this->getClient());
        $configuration->addSubscriber(new RequestLogger(new NullLogger()));
        $configuration->addSubscriber(new ExceptionLoggingHandler(new NullLogger()));
        $event = $this->runConfiguration($configuration, 'logging');
        $this->assertLessThan(12, $event->getDuration());
    }

    public function testLinkDiscovery()
    {
        $configuration = new Configuration('http://example.com/index.html');
        $configuration->setQueue($this->getQueue());
        $configuration->setClient($this->getClient());
        $configuration->addSubscriber(new ModuleHandler([new XPathParser()],
            [new LinkProcessor()]));
        $event = $this->runConfiguration($configuration, 'links');
        $this->assertLessThan(12, $event->getDuration());
    }

    private function runConfiguration(
        ConfigurationInterface $configuration,
        $category
    ) {
        $session = new Session($configuration, new EventDispatcher());
        $crawler = new Crawler($session);
        $stopwatch = new Stopwatch();
        $stopwatch->start(__FUNCTION__, $category);
        $promise = $crawler->start();
        $promise->wait();
        $stopwatch->stop(__FUNCTION__);

        return $stopwatch->getEvent(__FUNCTION__, $category);
    }

}