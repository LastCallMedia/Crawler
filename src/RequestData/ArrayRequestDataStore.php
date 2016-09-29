<?php

namespace LastCall\Crawler\RequestData;

class ArrayRequestDataStore implements RequestDataStore
{
    private $data = [];

    public function merge($url, array $data)
    {
        if (isset($this->data[$url])) {
            $this->data[$url] = $data + $this->data[$url];
        } else {
            $this->data[$url] = $data;
        }
    }

    public function fetch($url)
    {
        return isset($this->data[$url])
            ? $this->prepareRow($url, $this->data[$url])
            : null;
    }

    public function fetchAll()
    {
        foreach ($this->data as $uri => $row) {
            yield $uri => $this->prepareRow($uri, $row);
        }
    }

    protected function prepareRow($uri, $row)
    {
        return ['uri' => $uri] + $row;
    }
}
