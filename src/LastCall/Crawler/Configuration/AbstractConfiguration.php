<?php

namespace LastCall\Crawler\Configuration;

use LastCall\Crawler\CrawlerEvents;
use LastCall\Crawler\Event\CrawlerEvent;
use LastCall\Crawler\Event\CrawlerExceptionEvent;
use LastCall\Crawler\Event\CrawlerResponseEvent;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

abstract class AbstractConfiguration implements ConfigurationInterface
{

    /**
     * @var string
     */
    protected $baseUrl;

    /**
     * @var \GuzzleHttp\Client
     */
    protected $client;

    /**
     * @var \LastCall\Crawler\Url\UrlHandler
     */
    protected $urlHandler;

    /**
     * @var \LastCall\Crawler\Queue\RequestQueueInterface
     */
    protected $queue;

    /**
     * @var array
     */
    protected $listeners = array();

    abstract protected function getDispatcher();

    public function getClient()
    {
        return $this->client;
    }

    public function getUrlHandler()
    {
        return $this->urlHandler;
    }

    public function getQueue()
    {
        return $this->queue;
    }

    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    public function onSetup() {
        $this->getDispatcher()->dispatch(CrawlerEvents::SETUP);
    }

    public function onTeardown() {
        $this->getDispatcher()->dispatch(CrawlerEvents::TEARDOWN);
    }

    public function onRequestSending(RequestInterface $request) {
        $urlHandler = $this->urlHandler->forUrl($request->getUri());
        $event = new CrawlerEvent($request, $this->queue, $urlHandler);
        $this->getDispatcher()->dispatch(CrawlerEvents::SENDING, $event);
    }

    public function onRequestFailure(RequestInterface $request, ResponseInterface $response) {
        $urlHandler = $this->urlHandler->forUrl($request->getUri());
        $event = new CrawlerResponseEvent($request, $response, $this->queue, $urlHandler);
        $this->getDispatcher()->dispatch(CrawlerEvents::FAILURE, $event);
    }

    public function onRequestSuccess(RequestInterface $request, ResponseInterface $response) {
        $urlHandler = $this->urlHandler->forUrl($request->getUri());
        $event = new CrawlerResponseEvent($request, $response, $this->queue, $urlHandler);
        $this->getDispatcher()->dispatch(CrawlerEvents::SUCCESS, $event);
    }

    public function onRequestException(
        RequestInterface $request,
        \Exception $exception,
        ResponseInterface $response = NULL
    ) {
        $urlHandler = $this->urlHandler->forUrl($request->getUri());
        $event = new CrawlerExceptionEvent($request, $response, $exception, $this->queue, $urlHandler);
        $this->getDispatcher()->dispatch(CrawlerEvents::EXCEPTION, $event);
    }
}