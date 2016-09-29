<?php

namespace LastCall\Crawler\RequestData;

class ArrayRequestDataStore implements RequestDataStore
{
    private $data = [];

    public function merge($url, array $data)
    {
        $this->prepareRowForWrite($data);
        if (isset($this->data[$url])) {
            $this->data[$url] = $data + $this->data[$url];
        } else {
            $this->data[$url] = $data;
        }
    }

    public function fetch($url)
    {
        return isset($this->data[$url])
            ? $this->prepareRowForRead($url, $this->data[$url])
            : null;
    }

    public function fetchAll()
    {
        foreach ($this->data as $uri => $row) {
            yield $uri => $this->prepareRowForRead($uri, $row);
        }
    }

    private function prepareRowForWrite($data) {
        if(is_array($data) && isset($data['uri'])) {
            unset($data['uri']);
        }
        return $data;
    }

    protected function prepareRowForRead($uri, $row)
    {
        return ['uri' => $uri] + $row;
    }
}
