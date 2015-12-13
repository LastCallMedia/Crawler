<?php


namespace LastCall\Crawler\Module\Parser;

use Psr\Http\Message\ResponseInterface;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;

class XPathParser implements ModuleParserInterface
{
    public function getId()
    {
        return 'xpath';
    }

    public function parseResponse(ResponseInterface $response)
    {
        return new DomCrawler((string)$response->getBody());
    }

    public function parseNodes($node, $selector)
    {
        return $node->filterXPath($selector);
    }
}