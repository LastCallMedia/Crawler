<?php

namespace LastCall\Crawler\Event;

use LastCall\Crawler\Crawler;
use LastCall\Crawler\CrawlerSession;
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

    public function __construct(Crawler $crawler, RequestInterface $request)
    {
        $this->crawler = $crawler;
        $this->request = $request;
    }

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
        if (!isset($this->urlHandler)) {
            $this->urlHandler = $this->crawler->getUrlHandler($this->request->getUri());
        }

        return $this->urlHandler;
    }
}