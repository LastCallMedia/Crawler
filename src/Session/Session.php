<?php

namespace LastCall\Crawler\Session;


use GuzzleHttp\Psr7\Request;
use LastCall\Crawler\Common\SetupTeardownInterface;
use LastCall\Crawler\Configuration\ConfigurationInterface;
use LastCall\Crawler\CrawlerEvents;
use LastCall\Crawler\Event\CrawlerEvent;
use LastCall\Crawler\Event\CrawlerExceptionEvent;
use LastCall\Crawler\Event\CrawlerResponseEvent;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Concrete implementation of the session.
 * @see SessionInterface
 */
class Session implements SessionInterface
{

    /**
     * @var \LastCall\Crawler\Configuration\ConfigurationInterface
     */
    private $configuration;

    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var \LastCall\Crawler\Queue\RequestQueueInterface
     */
    private $queue;

    /**
     * Session constructor.
     *
     * @param \LastCall\Crawler\Configuration\ConfigurationInterface      $configuration
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher
     */
    public function __construct(
        ConfigurationInterface $configuration,
        EventDispatcherInterface $dispatcher
    ) {
        $this->attachListeners($configuration, $dispatcher);
        $this->configuration = $configuration;
        $this->dispatcher = $dispatcher;
        $this->queue = $configuration->getQueue();
    }

    private function attachListeners(
        ConfigurationInterface $configuration,
        EventDispatcherInterface $dispatcher
    ) {
        if ($listenersArr = $configuration->getListeners()) {
            foreach ($listenersArr as $eventName => $listeners) {
                foreach ($listeners as $listenerData) {
                    $dispatcher->addListener($eventName, $listenerData[0],
                        $listenerData[1]);
                }
            }
        }
        if ($subscribersArr = $configuration->getSubscribers()) {
            foreach ($subscribersArr as $subscriber) {
                $dispatcher->addSubscriber($subscriber);
            }
        }
    }

    public function init($baseUrl = null)
    {
        $baseUrl = $baseUrl ?: $this->configuration->getBaseUrl();
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

    private function getHandler(UriInterface $uri)
    {
        return $this->configuration->getUrlHandler()->forUrl($uri);
    }

    public function onSetup()
    {
        if ($this->queue instanceof SetupTeardownInterface) {
            $this->queue->onSetup();
        }
        $this->dispatcher->dispatch(CrawlerEvents::SETUP);
    }

    public function onTeardown()
    {
        if ($this->queue instanceof SetupTeardownInterface) {
            $this->queue->onTeardown();
        }
        $this->dispatcher->dispatch(CrawlerEvents::TEARDOWN);
    }

    private function dispatch($eventName, CrawlerEvent $event)
    {
        $this->dispatcher->dispatch($eventName, $event);
        foreach ($event->getAdditionalRequests() as $request) {
            $this->queue->push($request);
        }
    }

    public function onRequestSending(RequestInterface $request)
    {
        $handler = $this->getHandler($request->getUri());
        $event = new CrawlerEvent($request, $handler);
        $this->dispatch(CrawlerEvents::SENDING, $event);
    }

    public function onRequestSuccess(
        RequestInterface $request,
        ResponseInterface $response
    ) {
        $handler = $this->getHandler($request->getUri());
        $event = new CrawlerResponseEvent($request, $response, $handler);
        $this->dispatch(CrawlerEvents::SUCCESS, $event);
    }

    public function onRequestFailure(
        RequestInterface $request,
        ResponseInterface $response
    ) {
        $handler = $this->getHandler($request->getUri());
        $event = new CrawlerResponseEvent($request, $response, $handler);
        $this->dispatch(CrawlerEvents::FAILURE, $event);
    }

    public function onRequestException(
        RequestInterface $request,
        \Exception $exception,
        ResponseInterface $response = null
    ) {
        $handler = $this->getHandler($request->getUri());
        $event = new CrawlerExceptionEvent($request, $response, $exception,
            $handler);
        $this->dispatch(CrawlerEvents::EXCEPTION, $event);
    }


}