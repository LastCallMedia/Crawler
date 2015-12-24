<?php

namespace LastCall\Crawler\Fragment\Parser;

use Psr\Http\Message\ResponseInterface;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;

class XPathParser implements FragmentParserInterface
{
    public function getId()
    {
        return 'xpath';
    }

    public function prepareResponse(ResponseInterface $response)
    {
        return new DomCrawler((string) $response->getBody());
    }

    public function parseFragments($node, $selector)
    {
        return $node->filterXPath($selector);
    }
}
