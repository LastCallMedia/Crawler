<?php


namespace LastCall\Crawler\RequestData;


interface RequestDataStore {

    public function merge($uri, array $data);

    public function fetch($uri);

    public function fetchAll();

}