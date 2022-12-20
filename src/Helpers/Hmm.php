<?php

namespace Kykurniawan\Hmm\Helpers;

use Exception;
use Kykurniawan\Hmm\Hmm as HmmHmm;

class Hmm
{
    /**
     * @return \Kykurniawan\Hmm\Hmm
     */
    public static function instance()
    {
        $hmm = &HmmHmm::getInstance();

        if (is_null($hmm)) {
            throw new Exception("The application has not been instantiated.");
        }

        return $hmm;
    }
}
