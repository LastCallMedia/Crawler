<?php


namespace LastCall\Crawler\Module\Processor;


interface ModuleProcessorInterface
{

    /**
     * @return \LastCall\Crawler\Module\ModuleSubscription[]
     */
    public function getSubscribedMethods();


}