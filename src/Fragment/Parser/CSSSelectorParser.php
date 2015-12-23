<?php


namespace LastCall\Crawler\Fragment\Parser;

use Psr\Http\Message\ResponseInterface;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;

class CSSSelectorParser implements FragmentParserInterface
{
    public function getId()
    {
        return 'css';
    }

    public function prepareResponse(ResponseInterface $response)
    {
        return new DomCrawler((string)$response->getBody());
    }

    public function parseFragments($node, $selector)
    {
        return $node->filter($selector);
    }
}