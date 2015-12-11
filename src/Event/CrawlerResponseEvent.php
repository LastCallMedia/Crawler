<?php

namespace LastCall\Crawler\Event;

use LastCall\Crawler\Queue\RequestQueueInterface;
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
        RequestQueueInterface $queue,
        URLHandler $handler
    ) {
        parent::__construct($request, $queue, $handler);
        $this->response = $response;
    }

    public function getResponse()
    {
        return $this->response;
    }

    public function getDom()
    {
        if (!isset($this->dom)) {
            $this->dom = new DomCrawler((string)$this->response->getBody(),
                $this->getRequest()->getUri());
        }

        return $this->dom;
    }


}