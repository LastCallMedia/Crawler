<?php


namespace LastCall\Crawler\Handler\Module\Parser;

use Symfony\Component\DomCrawler\Crawler as DomCrawler;
use Psr\Http\Message\ResponseInterface;

class XPathParser implements ModuleParserInterface
{
    public function getId()
    {
        return 'xpath';
    }

    public function parseResponse(ResponseInterface $response)
    {
        return new DomCrawler((string) $response->getBody());
    }

    public function parseNodes($node, $selector)
    {
        return $node->filterXPath($selector);
    }
}