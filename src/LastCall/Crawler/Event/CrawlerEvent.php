<?php

namespace LastCall\Crawler\Event;

use LastCall\Crawler\Crawler;
use LastCall\Crawler\CrawlerSession;
use LastCall\Crawler\Queue\QueueInterface;
use LastCall\Crawler\Url\URLHandler;
use Psr\Http\Message\RequestInterface;
use Symfony\Component\EventDispatcher\Event;


class CrawlerEvent extends Event
{

    /**
     * @var \LastCall\Crawler\Crawler
     */
    private $crawler;

    /**
     * @var \Psr\Http\Message\RequestInterface
     */
    private $request;

    /**
     * @var \LastCall\Crawler\Url\URLHandler
     */
    private $urlHandler;

    /**
     * @var
     */
    private $queue;

    public function __construct(RequestInterface $request, QueueInterface $queue, URLHandler $handler)
    {
        $this->request = $request;
        $this->urlHandler = $handler;
        $this->queue = $queue;
    }

    public function setUrlHandler(URLHandler $handler) {
        $this->urlHandler = $handler;
    }

    public function setQueue(QueueInterface $queue) {
        $this->queue = $queue;
    }

    public function getQueue() {
        return $this->queue;
    }

    /**
     * @return \LastCall\Crawler\Crawler
     * @deprecated
     */
    public function getCrawler()
    {
        return $this->crawler;
    }

    public function getRequest()
    {
        return $this->request;
    }

    public function getUrlHandler()
    {
        return $this->urlHandler;
    }
}