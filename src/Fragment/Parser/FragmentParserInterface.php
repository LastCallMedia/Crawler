<?php

namespace LastCall\Crawler\Fragment\Parser;

use Psr\Http\Message\ResponseInterface;

/**
 * Designates that a class is a ModuleParser.
 */
interface FragmentParserInterface
{
    public function getId();

    public function prepareResponse(ResponseInterface $response);

    public function parseFragments($node, $selector);
}
