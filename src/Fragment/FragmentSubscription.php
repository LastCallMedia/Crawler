<?php

namespace LastCall\Crawler\Fragment;

use LastCall\Crawler\Fragment\Processor\FragmentProcessorInterface;

/**
 * FragmentSubscription registers a callback when a named parser
 * encounters a given selector.
 */
class FragmentSubscription
{
    private $processor;
    private $parserId;
    private $selector;
    private $callback;

    public function __construct(
        FragmentProcessorInterface $processor,
        $parserId,
        $selector,
        $callback
    ) {
        $this->processor = $processor;
        $this->parserId = $parserId;
        $this->selector = $selector;
        $this->callback = $callback;
    }

    public function getParserId()
    {
        return $this->parserId;
    }

    public function getSelector()
    {
        return $this->selector;
    }

    public function getProcessor()
    {
        return $this->processor;
    }

    public function getCallable()
    {
        return [$this->processor, $this->callback];
    }
}
