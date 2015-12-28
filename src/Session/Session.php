<?php

namespace LastCall\Crawler\Session;

use GuzzleHttp\Psr7\Request;
use LastCall\Crawler\Common\SetupTeardownInterface;
use LastCall\Crawler\Configuration\ConfigurationInterface;
use LastCall\Crawler\CrawlerEvents;
use LastCall\Crawler\Event\CrawlerEvent;
use LastCall\Crawler\Event\CrawlerExceptionEvent;
use LastCall\Crawler\Event\CrawlerResponseEvent;
use LastCall\Crawler\Queue\ArrayRequestQueue;
use LastCall\Crawler\Queue\RequestQueueInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Concrete implementation of the session.
 *
 * @see SessionInterface
 */
class Session implements SessionInterface
{
    /**
     * @var string
     */
    private $baseUrl;

    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var \LastCall\Crawler\Queue\RequestQueueInterface
     */
    private $queue;

    public static function createFromConfig(
        ConfigurationInterface $config,
        EventDispatcherInterface $dispatcher
    ) {
        if ($listenersArr = $config->getListeners()) {
            foreach ($listenersArr as $eventName => $listeners) {
                foreach ($listeners as $listenerData) {
                    $dispatcher->addListener($eventName, $listenerData[0],
                        $listenerData[1]);
                }
            }
        }
        if ($subscribersArr = $config->getSubscribers()) {
            foreach ($subscribersArr as $subscriber) {
                $dispatcher->addSubscriber($subscriber);
            }
        }

        return new self($config->getBaseUrl(), $config->getQueue(),
            $dispatcher);
    }

    /**
     * Session constructor.
     *
     * @param string                                                           $baseUrl
     * @param \LastCall\Crawler\Queue\RequestQueueInterface|null               $queue
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface|null $dispatcher
     */
    public function __construct(
        $baseUrl,
        RequestQueueInterface $queue = null,
        EventDispatcherInterface $dispatcher = null
    ) {
        $this->baseUrl = $baseUrl;
        $this->dispatcher = $dispatcher ?: new EventDispatcher();
        $this->queue = $queue ?: new ArrayRequestQueue();
    }

    public function init($baseUrl = null)
    {
        $baseUrl = $baseUrl ?: $this->baseUrl;
        $this->queue->push(new Request('GET', $baseUrl));
    }

    public function next()
    {
        return $this->queue->pop();
    }

    public function complete(RequestInterface $request)
    {
        return $this->queue->complete($request);
    }

    public function release(RequestInterface $request)
    {
        return $this->queue->release($request);
    }

    public function addRequest(RequestInterface $request)
    {
        return $this->queue->push($request);
    }

    public function isFinished()
    {
        return $this->queue->count() === 0;
    }

    public function setup()
    {
        if ($this->queue instanceof SetupTeardownInterface) {
            $this->queue->onSetup();
        }
        $this->dispatcher->dispatch(CrawlerEvents::SETUP);
    }

    public function teardown()
    {
        if ($this->queue instanceof SetupTeardownInterface) {
            $this->queue->onTeardown();
        }
        $this->dispatcher->dispatch(CrawlerEvents::TEARDOWN);
    }

    private function dispatch($eventName, CrawlerEvent $event)
    {
        $this->dispatcher->dispatch($eventName, $event);
        $this->queue->pushMultiple($event->getAdditionalRequests());
    }

    public function onRequestSending(RequestInterface $request)
    {
        $event = new CrawlerEvent($request);
        $this->dispatch(CrawlerEvents::SENDING, $event);
    }

    public function onRequestSuccess(
        RequestInterface $request,
        ResponseInterface $response
    ) {
        $event = new CrawlerResponseEvent($request, $response);
        $this->dispatch(CrawlerEvents::SUCCESS, $event);
    }

    public function onRequestFailure(
        RequestInterface $request,
        ResponseInterface $response
    ) {
        $event = new CrawlerResponseEvent($request, $response);
        $this->dispatch(CrawlerEvents::FAILURE, $event);
    }

    public function onRequestException(
        RequestInterface $request,
        \Exception $exception,
        ResponseInterface $response = null
    ) {
        $event = new CrawlerExceptionEvent($request, $response, $exception);
        $this->dispatch(CrawlerEvents::EXCEPTION, $event);
    }
}
