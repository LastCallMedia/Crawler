<?php

namespace LastCall\Crawler\Session;

use LastCall\Crawler\Configuration\ConfigurationInterface;
use LastCall\Crawler\CrawlerEvents;
use LastCall\Crawler\Event\CrawlerEvent;
use LastCall\Crawler\Event\CrawlerExceptionEvent;
use LastCall\Crawler\Event\CrawlerResponseEvent;
use LastCall\Crawler\Event\CrawlerStartEvent;
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
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    private $dispatcher;

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

        return new self($dispatcher);
    }

    /**
     * Session constructor.
     *
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface|null $dispatcher
     */
    public function __construct(
        EventDispatcherInterface $dispatcher = null
    ) {
        $this->dispatcher = $dispatcher ?: new EventDispatcher();
    }

    public function start()
    {
        $this->dispatcher->dispatch(CrawlerEvents::START, new CrawlerStartEvent($this));
    }

    public function setup()
    {
        $this->dispatcher->dispatch(CrawlerEvents::SETUP);
    }

    public function teardown()
    {
        $this->dispatcher->dispatch(CrawlerEvents::TEARDOWN);
    }

    public function finish()
    {
        $this->dispatcher->dispatch(CrawlerEvents::FINISH);
    }

    /**
     * @param                                      $eventName
     * @param \LastCall\Crawler\Event\CrawlerEvent $event
     *
     * @return \Psr\Http\Message\RequestInterface[]
     */
    private function dispatch($eventName, CrawlerEvent $event)
    {
        $this->dispatcher->dispatch($eventName, $event);

        return $event->getAdditionalRequests();
    }

    public function onRequestSending(RequestInterface $request)
    {
        $event = new CrawlerEvent($request);

        return $this->dispatch(CrawlerEvents::SENDING, $event);
    }

    public function onRequestSuccess(
        RequestInterface $request,
        ResponseInterface $response
    ) {
        $event = new CrawlerResponseEvent($request, $response);

        return $this->dispatch(CrawlerEvents::SUCCESS, $event);
    }

    public function onRequestFailure(
        RequestInterface $request,
        ResponseInterface $response
    ) {
        $event = new CrawlerResponseEvent($request, $response);

        return $this->dispatch(CrawlerEvents::FAILURE, $event);
    }

    public function onRequestException(
        RequestInterface $request,
        \Exception $exception,
        ResponseInterface $response = null
    ) {
        $event = new CrawlerExceptionEvent($request, $response, $exception);

        return $this->dispatch(CrawlerEvents::EXCEPTION, $event);
    }
}
