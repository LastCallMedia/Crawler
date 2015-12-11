<?php

namespace LastCall\Crawler\Session;


use LastCall\Crawler\Configuration\ConfigurationInterface;
use LastCall\Crawler\CrawlerEvents;
use LastCall\Crawler\Event\CrawlerEvent;
use LastCall\Crawler\Event\CrawlerExceptionEvent;
use LastCall\Crawler\Event\CrawlerResponseEvent;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class Session implements SessionInterface {

    /**
     * @var \LastCall\Crawler\Configuration\ConfigurationInterface
     */
    private $configuration;
    
    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    private $dispatcher;

    public function __construct(ConfigurationInterface $configuration, EventDispatcherInterface $dispatcher) {
        $this->attachListeners($configuration, $dispatcher);
        $this->configuration = $configuration;
        $this->dispatcher = $dispatcher;
    }

    private function attachListeners(ConfigurationInterface $configuration, EventDispatcherInterface $dispatcher) {
        if($listenersArr = $configuration->getListeners()) {
            foreach($listenersArr as $eventName => $listeners) {
                foreach($listeners as $listenerData) {
                    $dispatcher->addListener($eventName, $listenerData[0], $listenerData[1]);
                }
            }
        }
        if($subscribersArr = $configuration->getSubscribers()) {
            foreach($subscribersArr as $subscriber) {
                $dispatcher->addSubscriber($subscriber);
            }
        }
    }

    public function getStartUrl($startUrl = NULL) {
        return $startUrl ?: $this->configuration->getBaseUrl();
    }

    public function addRequest(RequestInterface $request) {
        $this->configuration->getQueue()->push($request);
    }

    /**
     * @inheritDoc
     */
    public function getQueue() {
        return $this->configuration->getQueue();
    }

    /**
     * @inheritDoc
     */
    public function getClient() {
        return $this->configuration->getClient();
    }

    /**
     * @inheritDoc
     */
    public function isFinished() {
        return $this->getQueue()->count() === 0;
    }

    private function getHandler(UriInterface $uri) {
        return $this->configuration->getUrlHandler()->forUrl($uri);
    }

    public function onSetup() {
        $this->dispatcher->dispatch(CrawlerEvents::SETUP);
    }

    public function onTeardown() {
        $this->dispatcher->dispatch(CrawlerEvents::TEARDOWN);
    }

    public function onRequestSending(RequestInterface $request) {
        $handler = $this->getHandler($request->getUri());
        $event = new CrawlerEvent($request, $this->getQueue(), $handler);
        $this->dispatcher->dispatch(CrawlerEvents::SENDING, $event);
    }

    public function onRequestSuccess(
        RequestInterface $request,
        ResponseInterface $response
    ) {
        $handler = $this->getHandler($request->getUri());
        $event = new CrawlerResponseEvent($request, $response, $this->getQueue(), $handler);
        $this->dispatcher->dispatch(CrawlerEvents::SUCCESS, $event);
    }

    public function onRequestFailure(
        RequestInterface $request,
        ResponseInterface $response
    ) {
        $handler = $this->getHandler($request->getUri());
        $event = new CrawlerResponseEvent($request, $response, $this->getQueue(), $handler);
        $this->dispatcher->dispatch(CrawlerEvents::FAILURE, $event);
    }

    public function onRequestException(
        RequestInterface $request,
        \Exception $exception,
        ResponseInterface $response = NULL
    ) {
        $handler = $this->getHandler($request->getUri());
        $event = new CrawlerExceptionEvent($request, $response, $exception, $this->getQueue(), $handler);
        $this->dispatcher->dispatch(CrawlerEvents::EXCEPTION, $event);
    }


}