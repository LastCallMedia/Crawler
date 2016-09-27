<?php


namespace LastCall\Crawler\Test\RequestData;


trait RequestDataStoreTestTrait {

    abstract function getStore();

    public function testSimpleSet() {
        $store = $this->getStore();
        $store->merge('foo', ['baz' => 'bar']);
        $this->assertEquals(['baz' => 'bar'], $store->fetch('foo'));
        $this->assertEquals(['foo' => ['baz' => 'bar']], $store->fetchAll());
    }

    public function testMergeDifferentKeys() {
        $store = $this->getStore();
        $store->merge('foo', ['foo' => 'bar']);
        $store->merge('foo', ['baz' => 'bar']);
        $this->assertEquals(['foo' => 'bar', 'baz' => 'bar'], $store->fetch('foo'));
        $this->assertEquals([
            'foo' => ['foo' => 'bar', 'baz' => 'bar'],
        ], $store->fetchAll());
    }

    public function testMergeSameKeys() {
        $store = $this->getStore();
        $store->merge('foo', ['foo' => 'bar']);
        $store->merge('foo', ['foo' => 'baz']);
        $this->assertEquals(['foo' => 'baz'], $store->fetch('foo'));
        $this->assertEquals([
            'foo' => ['foo' => 'baz'],
        ], $store->fetchAll());
    }

    public function testMergeMultiple() {
        $store = $this->getStore();
        $store->merge('foo', []);
        $store->merge('baz', []);
        $this->assertEquals([], $store->fetch('foo'));
        $this->assertEquals([], $store->fetch('baz'));
        $this->assertEquals([
            'foo' => [], 'baz' => []
        ], $store->fetchAll());
    }

    public function testFetchNonexistent() {
        $store = $this->getStore();
        $this->assertNull($store->fetch('foo'));
    }

    public function testFetchAllEmpty() {
        $store = $this->getStore();
        $this->assertEquals([], $store->fetchAll());
    }
}