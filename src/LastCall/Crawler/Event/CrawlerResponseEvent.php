<?php

namespace LastCall\Crawler\Event;

use LastCall\Crawler\Crawler;
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
      Crawler $crawler,
      RequestInterface $request,
      ResponseInterface $response
    ) {
        parent::__construct($crawler, $request);
        $this->response = $response;
    }

    public function getResponse()
    {
        return $this->response;
    }

    public function getDom()
    {
        if (!isset($this->dom)) {
            $this->dom = new DomCrawler((string) $this->response->getBody(),
              $this->getRequest()->getUri());
        }

        return $this->dom;
    }


}