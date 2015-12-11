<?php

namespace LastCall\Crawler\Module;

use Symfony\Component\DomCrawler\Crawler as DomCrawler;

interface ModuleParser
{

    public function parse(DomCrawler $dom);
}