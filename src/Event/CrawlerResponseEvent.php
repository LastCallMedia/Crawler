<?php

namespace LastCall\Crawler\Event;

use LastCall\Crawler\Url\URLHandler;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;

class CrawlerResponseEvent extends CrawlerEvent
{

    private $response;

    /**
     * @var DomCrawler
     */
    private $dom;

    public function __construct(
        RequestInterface $request,
        ResponseInterface $response,
        URLHandler $handler
    ) {
        parent::__construct($request, $handler);
        $this->response = $response;
    }

    public function getResponse()
    {
        return $this->response;
    }

}