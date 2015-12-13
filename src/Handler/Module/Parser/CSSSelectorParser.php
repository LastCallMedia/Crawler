<?php


namespace LastCall\Crawler\Handler\Module\Parser;

use Symfony\Component\DomCrawler\Crawler as DomCrawler;
use Psr\Http\Message\ResponseInterface;

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