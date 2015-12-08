<?php

namespace LastCall\Crawler\Module;

interface ModuleProcessor
{

    /**
     * @return array
     *  A list of module types that this processor handles.
     */
    public function getModuleTypes();

    public function process(array $module);
}