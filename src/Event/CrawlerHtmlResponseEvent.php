<?php

namespace LastCall\Crawler\Event;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;

/**
 * Wraps data for request/response cycle that contains HTML.
 */
class CrawlerHtmlResponseEvent extends CrawlerResponseEvent
{
    private $crawler;

    public function __construct(
        RequestInterface $request,
        ResponseInterface $response
    ) {
        parent::__construct($request, $response);
    }

    /**
     * Get a Symfony Crawler for the HTML response body.
     *
     * @return \Symfony\Component\DomCrawler\Crawler
     */
    public function getDomCrawler()
    {
        if (!$this->crawler) {
            $this->crawler = new DomCrawler((string) $this->getResponse()->getBody());
        }

        return $this->crawler;
    }
}
