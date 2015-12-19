<?php


namespace LastCall\Crawler\Module\Parser;

use Psr\Http\Message\ResponseInterface;

/**
 * Designates that a class is a ModuleParser.
 */
interface ModuleParserInterface
{
    public function getId();

    public function parseResponse(ResponseInterface $response);

    public function parseNodes($node, $selector);
}