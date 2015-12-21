<?php


namespace LastCall\Crawler\Uri;


interface MatcherInterface
{
    public function matches($uri);

    public function matchesFile($uri);

    public function matchesHtml($uri);

}