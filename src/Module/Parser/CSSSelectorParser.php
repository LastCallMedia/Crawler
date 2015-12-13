<?php


namespace LastCall\Crawler\Module\Parser;

use Psr\Http\Message\ResponseInterface;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;

class CSSSelectorParser implements ModuleParserInterface
{
    public function getId()
    {
        return 'css';
    }

    public function parseResponse(ResponseInterface $response)
    {
        return new DomCrawler((string)$response->getBody());
    }

    public function parseNodes($node, $selector)
    {
        return $node->filter($selector);
    }
}