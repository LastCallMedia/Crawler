<?php

namespace LastCall\Crawler\Test;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use LastCall\Crawler\Configuration\Configuration;
use LastCall\Crawler\Crawler;
use LastCall\Crawler\Event\CrawlerResponseEvent;
use LastCall\Crawler\Queue\Job;
use Prophecy\Argument;

class CrawlerTest extends \PHPUnit_Framework_TestCase
{


    protected function mockClient(array $requests)
    {
        $handler = new MockHandler($requests);

        return new Client(['handler' => HandlerStack::create($handler)]);
    }

    protected function mockConfiguration(array $responses = [], array $requests = []) {
        $handler = new MockHandler($responses);
        $client = new Client(['handler' => HandlerStack::create($handler)]);
        $config = new Configuration('http://google.com');
        $config->setClient($client);

        foreach($requests as $request) {
            $config->getQueueDriver()->push(new Job('request', $request, $request->getMethod() . $request->getUri()));
        }

        return $config;
    }

    public function testTeardownEventIsDispatched() {
        $config = $this->mockConfiguration();
        $success = FALSE;
        $config->addListener(Crawler::TEARDOWN, function() use (&$success) {
            $success = TRUE;
        });
        $crawler = new Crawler($config);
        $crawler->teardown();
        $this->assertTrue($success);
    }

    public function testSetupEventIsDispatched() {
        $config = $this->mockConfiguration();
        $success = FALSE;
        $config->addListener(Crawler::SETUP, function() use (&$success) {
            $success = TRUE;
        });
        $crawler = new Crawler($config);
        $crawler->setUp();
        $this->assertTrue($success);
    }

    public function testItemIsCompletedOnSuccess()
    {
        $config = $this->mockConfiguration([new Response()]);
        $crawler = new Crawler($config);
        $crawler->start(1, 'http://google.com')->wait();
        $this->assertEquals(1, $config->getQueueDriver()->count('request', Job::COMPLETE));
    }

    public function testItemIsCompletedOnFailure()
    {
        $config = $this->mockConfiguration([new Response(400)]);
        $crawler = new Crawler($config);
        $crawler->start(1, 'http://google.com')->wait(FALSE);
        $this->assertEquals(1, $config->getQueueDriver()->count('request', Job::COMPLETE));
    }

    public function testSuccessEventIsFiredOnSuccess()
    {
        $config = $this->mockConfiguration([new Response(200)]);
        $success = FALSE;
        $config->addListener(Crawler::SUCCESS, function() use (&$success) {
            $success = TRUE;
        });

        $crawler = new Crawler($config);
        $crawler->start(1, 'http://google.com')->wait();
        $this->assertTrue($success);
    }

    public function testFailureEventIsFiredOnFailure()
    {
        $success = FALSE;
        $config = $this->mockConfiguration([new Response(400)]);
        $config->addListener(Crawler::FAIL, function() use (&$success) {
            $success = TRUE;
        });
        $crawler = new Crawler($config);
        $crawler->start(1, 'http://google.com')->wait(FALSE);
        $this->assertTrue($success);
    }

    public function testExceptionEventIsFiredOnSuccesfulResponseException()
    {
        $success = FALSE;
        $config = $this->mockConfiguration([new Response(200)]);
        $config->addListener(Crawler::SUCCESS, function() {
           throw new \Exception('Foo');
        });
        $config->addListener(Crawler::EXCEPTION, function($e) use (&$success) {
            $success = TRUE;
        });

        $crawler = new Crawler($config);
        $crawler->start(1, 'http://google.com')->wait();
        $this->assertTrue($success);
    }

    public function testExceptionEventIsFiredOnFailureResponseException()
    {
        $success = FALSE;
        $config = $this->mockConfiguration([new Response(400)]);
        $config->addListener(Crawler::FAIL, function() { throw new \Exception('foo'); });
        $config->addListener(Crawler::EXCEPTION, function() use (&$success) {
            $success = TRUE;
        });

        $crawler = new Crawler($config);
        $crawler->start(1, 'http://google.com')->wait();
        $this->assertTrue($success);
    }

    public function testExceptionEventIsFiredOnFailureSendingException()
    {
        $success = FALSE;
        $config = $this->mockConfiguration([new Response(400)]);
        $config->addListener(Crawler::SENDING, function() { throw new \Exception('foo'); });
        $config->addListener(Crawler::EXCEPTION, function() use (&$success) {
            $success = TRUE;
        });

        $crawler = new Crawler($config);
        $crawler->start(1, 'http://google.com')->wait();
        $this->assertTrue($success);
    }

    /**
     * @group failing
     */
    public function testQueueIsWorkedUntilEmpty() {
        $responses = array_fill(0, 2, new Response(200));
        $config = $this->mockConfiguration($responses);

        $count = 0;
        $config->addListener(Crawler::SUCCESS, function(CrawlerResponseEvent $event) use (&$count, &$crawler) {
            if($event->getRequest()->getUri() == 'http://google.com/1') {
                $crawler->addRequest(new Request('GET', 'http://google.com/2'));
            }

            $count++;
        });
        $crawler = new Crawler($config);

        $promise = $crawler->start(5, 'http://google.com/1');

        $promise->wait();
        $this->assertEquals(2, $count);
    }
}