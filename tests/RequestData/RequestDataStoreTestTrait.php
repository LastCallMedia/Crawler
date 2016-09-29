<?php

namespace LastCall\Crawler\Test\RequestData;

use LastCall\Crawler\RequestData\RequestDataStore;

trait RequestDataStoreTestTrait
{
    /**
     * @return RequestDataStore
     */
    abstract public function getStore();

    public function testSimpleSet()
    {
        $store = $this->getStore();
        $store->merge('foo', ['baz' => 'bar']);
        $this->assertEquals(['uri' => 'foo', 'baz' => 'bar'], $store->fetch('foo'));
    }

    public function testMergeDifferentKeys()
    {
        $store = $this->getStore();
        $store->merge('foo', ['foo' => 'bar']);
        $store->merge('foo', ['baz' => 'bar']);
        $this->assertEquals(['uri' => 'foo', 'foo' => 'bar', 'baz' => 'bar'], $store->fetch('foo'));
    }

    public function testMergeSameKeys()
    {
        $store = $this->getStore();
        $store->merge('foo', ['foo' => 'bar']);
        $store->merge('foo', ['foo' => 'baz']);
        $this->assertEquals(['uri' => 'foo', 'foo' => 'baz'], $store->fetch('foo'));
    }

    public function testMergeMultiple()
    {
        $store = $this->getStore();
        $store->merge('foo', []);
        $store->merge('baz', []);
        $this->assertEquals(['uri' => 'foo'], $store->fetch('foo'));
        $this->assertEquals(['uri' => 'baz'], $store->fetch('baz'));
    }

    public function testFetchAll()
    {
        $store = $this->getStore();
        $store->merge('foo', ['bar' => 'baz']);
        $this->assertTrue($store->fetchAll() instanceof \Traversable);
        $this->assertEquals([
            'foo' => ['uri' => 'foo', 'bar' => 'baz'],
        ], iterator_to_array($store->fetchAll()));
    }

    public function testFetchNonexistent()
    {
        $store = $this->getStore();
        $this->assertNull($store->fetch('foo'));
    }

    public function testFetchAllEmpty()
    {
        $store = $this->getStore();
        $this->assertEquals([], iterator_to_array($store->fetchAll()));
    }
}
