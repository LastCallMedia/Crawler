<?php


namespace LastCall\Crawler\Module\Processor;

/**
 * Designates that a class is a ModuleProcessor.
 */
interface ModuleProcessorInterface
{

    /**
     * Get a list of the "modules" that this processor subscribes to.
     *
     * @return \LastCall\Crawler\Module\ModuleSubscription[]
     */
    public function getSubscribedMethods();


}