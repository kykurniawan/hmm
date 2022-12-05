<?php

namespace Kykurniawan\Hmm\Exceptions;

use Exception;

class PageNotFoundException extends Exception
{
    public function __construct($message = 'Page Not Found')
    {
        parent::__construct($message, 404);
    }
}