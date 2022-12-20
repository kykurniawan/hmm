<?php

namespace Kykurniawan\Hmm\Helpers;

class Module
{
    public static function get(string $moduleName)
    {
        return Hmm::instance()->module($moduleName);
    }

    public static function load(string $moduleClassName, mixed ...$constructArguments)
    {
        return Hmm::instance()->loadModule($moduleClassName, ...$constructArguments);
    }
}
