<?php


namespace LastCall\Crawler\Test\Performance;


use Doctrine\DBAL\DriverManager;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use LastCall\Crawler\Common\SetupTeardownInterface;
use LastCall\Crawler\Configuration\Configuration;
use LastCall\Crawler\Configuration\ConfigurationInterface;
use LastCall\Crawler\Crawler;
use LastCall\Crawler\Handler\Logging\ExceptionLogger;
use LastCall\Crawler\Handler\Logging\RequestLogger;
use LastCall\Crawler\Queue\ArrayRequestQueue;
use LastCall\Crawler\Queue\DoctrineRequestQueue;
use LastCall\Crawler\Queue\RequestQueue;
use LastCall\Crawler\Queue\RequestQueueInterface;
use LastCall\Crawler\Session\Session;
use Psr\Log\NullLogger;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Stopwatch\Stopwatch;
use Symfony\Component\Stopwatch\StopwatchEvent;

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
        $queue = new ArrayRequestQueue();
        $queue->push(new Request('GET', 'http://example.com/index.html'));
        $queue->push(new Request('GET', 'http://example.com/1.html'));
        $queue->push(new Request('GET', 'http://example.com/2.html'));
        $queue->push(new Request('GET', 'http://example.com/nonexistent.html'));

        return $queue;
    }


    public function testLogging()
    {
        $configuration = new Configuration('http://example.com/index.html');
        $configuration['queue'] = $this->getQueue();
        $configuration['client'] = $this->getClient();
        $configuration['subscribers'] = function () {
            return [
                new RequestLogger(new NullLogger()),
                new ExceptionLogger(new NullLogger())
            ];
        };
        $event = $this->runConfiguration($configuration, 'Logging');

        $this->logDataPoint($event);
    }

    public function testLinkDiscovery()
    {
        $configuration = new Configuration('http://example.com/index.html');
        $configuration['queue'] = $this->getQueue();
        $configuration['client'] = $this->getClient();
        $event = $this->runConfiguration($configuration, 'Link Discovery');

        $this->logDataPoint($event);
    }

    public function getQueues()
    {
        $conn = DriverManager::getConnection([
            'driver' => 'pdo_sqlite',
            'memory' => true,
        ]);

        return [
            [new ArrayRequestQueue(), 240],
            [new DoctrineRequestQueue($conn, 'new'), 600]
        ];
    }

    /**
     * @dataProvider getQueues
     */
    public function testQueuePush(RequestQueueInterface $queue, $expectedTime)
    {
        if ($queue instanceof SetupTeardownInterface) {
            $queue->onSetup();
        }
        $stopwatch = new Stopwatch();
        $stopwatch->start('queue', get_class($queue) . '::push()');
        for ($i = 0; $i < 1000; $i++) {
            $queue->push(new Request('GET', 'https://lastcallmedia.com/' . $i));
            $queue->push(new Request('GET', 'https://lastcallmedia.com/' . $i));
            $queue->push(new Request('GET', 'https://lastcallmedia.com/' . $i));
            $queue->push(new Request('GET', 'https://lastcallmedia.com/' . $i));
            $queue->push(new Request('GET', 'https://lastcallmedia.com/a/' . $i));
            $queue->push(new Request('GET', 'https://lastcallmedia.com/b/' . $i));
        }
        $stopwatch->stop('queue');
        if ($queue instanceof SetupTeardownInterface) {
            $queue->onTeardown();
        }
        $event = $stopwatch->getEvent('queue');
        $this->logDataPoint($event);
    }

    /**
     * @dataProvider getQueues
     */
    public function testQueuePushMultiple(RequestQueueInterface $queue) {
        if ($queue instanceof SetupTeardownInterface) {
            $queue->onSetup();
        }
        $stopwatch = new Stopwatch();
        $stopwatch->start('queue', get_class($queue) . '::pushMultiple()');
        for ($i = 0; $i < 1000; $i++) {
            $requests = array();
            $requests[] = new Request('GET', 'https://lastcallmedia.com/' . $i);
            $requests[] = new Request('GET', 'https://lastcallmedia.com/' . $i);
            $requests[] = new Request('GET', 'https://lastcallmedia.com/' . $i);
            $requests[] = new Request('GET', 'https://lastcallmedia.com/' . $i);
            $requests[] = new Request('GET', 'https://lastcallmedia.com/a/' . $i);
            $requests[] = new Request('GET', 'https://lastcallmedia.com/b/' . $i);
            $queue->pushMultiple($requests);
        }
        $stopwatch->stop('queue');
        if ($queue instanceof SetupTeardownInterface) {
            $queue->onTeardown();
        }
        $event = $stopwatch->getEvent('queue');
        $this->logDataPoint($event);
    }

    /**
     * @dataProvider getQueues
     */
    public function testQueueCount(RequestQueueInterface $queue)
    {
        if ($queue instanceof SetupTeardownInterface) {
            $queue->onSetup();
        }
        for ($i = 0; $i < 1000; $i++) {
            $queue->push(new Request('GET', 'https://lastcallmedia.com/' . $i));
            if ($i % 3) {
                $queue->complete($queue->pop());
            }
            if ($i % 5) {
                $queue->pop();
            }
        }
        $stopwatch = new Stopwatch();
        $stopwatch->start('queue', get_class($queue) . '::count()');
        for ($i = 0; $i < 1000; $i++) {
            $queue->count($queue::FREE);
            $queue->count($queue::COMPLETE);
            $queue->count($queue::PENDING);
        }
        $stopwatch->stop('queue');
        if ($queue instanceof SetupTeardownInterface) {
            $queue->onTeardown();
        }
        $event = $stopwatch->getEvent('queue');
        $this->logDataPoint($event);
    }

    /**
     * @dataProvider getQueues
     */
    public function testQueueComplete(RequestQueueInterface $queue)
    {
        if ($queue instanceof SetupTeardownInterface) {
            $queue->onSetup();
        }
        $stopwatch = new Stopwatch();
        $stopwatch->start('queue', get_class($queue) . '::complete()');
        for ($i = 0; $i < 1000; $i++) {
            $queue->push(new Request('GET', 'https://lastcallmedia.com/' . $i));
            $job = $queue->pop();
            $queue->complete($job);
        }
        $stopwatch->stop('queue');
        if ($queue instanceof SetupTeardownInterface) {
            $queue->onTeardown();
        }
        $event = $stopwatch->getEvent('queue');
        $this->logDataPoint($event);
    }

    private function runConfiguration(
        ConfigurationInterface $configuration,
        $category
    ) {
        $session = Session::createFromConfig($configuration,
            new EventDispatcher());
        $crawler = new Crawler($session, $configuration->getClient());
        $stopwatch = new Stopwatch();
        $stopwatch->start(__FUNCTION__, $category);
        $promise = $crawler->start();
        $promise->wait();
        $stopwatch->stop(__FUNCTION__);

        return $stopwatch->getEvent(__FUNCTION__, $category);
    }

    private function logDataPoint(StopwatchEvent $event)
    {
        print $event . PHP_EOL;
    }
}