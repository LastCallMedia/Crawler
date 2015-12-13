<?php


namespace LastCall\Crawler\Module\Parser;

use Psr\Http\Message\ResponseInterface;

interface ModuleParserInterface
{
    public function getId();

    public function parseResponse(ResponseInterface $response);

    public function parseNodes($node, $selector);
}