<?php


namespace LastCall\Crawler\Url;


interface NormalizerInterface
{
    public function normalize($uri);
}