<?php

namespace LastCall\Crawler\RequestData;

interface RequestDataStore
{
    /**
     * @param       $uri
     * @param array $data
     */
    public function merge($uri, array $data);

    /**
     * @param $uri
     *
     * @return array
     */
    public function fetch($uri);

    /**
     * @return \Traversable
     */
    public function fetchAll();
}
