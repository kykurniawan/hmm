<?php

namespace Kykurniawan\Hmm\Helpers;

class Config
{
    public static function get(string $key, $default = null)
    {
        return Hmm::instance()->config($key, $default);
    }
}
