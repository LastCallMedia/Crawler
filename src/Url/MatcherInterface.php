<?php


namespace LastCall\Crawler\Url;


interface MatcherInterface
{
    public function matches($uri);

    public function matchesFile($uri);

    public function matchesHtml($uri);

}