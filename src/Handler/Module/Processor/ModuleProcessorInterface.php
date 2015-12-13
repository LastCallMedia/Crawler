<?php


namespace LastCall\Crawler\Handler\Module\Processor;


interface ModuleProcessorInterface
{

    /**
     * @return \LastCall\Crawler\Handler\Module\ModuleSubscription[]
     */
    public function getSubscribedMethods();


}