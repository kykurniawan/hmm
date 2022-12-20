<?php

namespace Kykurniawan\Hmm\Helpers;

use Exception;

class Route
{
    public static function url(string $name, array $params = null)
    {
        $routeItems = Hmm::instance()->routeItems();

        foreach ($routeItems as $routeItem) {
            if ($routeItem->getName() !== $name) {
                continue;
            }

            return $routeItem->getRealPath($params);
        }

        throw new Exception('Route ' . $name . ' is not found');
    }
}
