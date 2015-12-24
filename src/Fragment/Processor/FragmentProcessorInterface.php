<?php

namespace LastCall\Crawler\Fragment\Processor;

/**
 * Designates that a class is a ModuleProcessor.
 */
interface FragmentProcessorInterface
{
    /**
     * Get a list of the "modules" that this processor subscribes to.
     *
     * @return \LastCall\Crawler\Fragment\FragmentSubscription[]
     */
    public function getSubscribedMethods();
}
