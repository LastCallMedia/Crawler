<?php

namespace LastCall\Crawler\Module;

class NullModuleProcessor implements ModuleProcessor
{

    private $types;

    public function __construct($types)
    {
        $this->types = $types;
    }

    public function getModuleTypes()
    {
        return $this->types;
    }

    public function process(array $module)
    {
        // Nothing...
    }
}