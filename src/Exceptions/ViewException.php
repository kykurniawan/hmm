<?php

namespace Kykurniawan\Hmm\Exceptions;

use Exception;

class ViewException extends Exception
{
    public function __construct($message = 'View Exception')
    {
        parent::__construct($message);
    }

    public static function forInvalidCellMethod(string $class, string $method)
    {
        return new static('Invalid cell method');
    }

    public static function forMissingCellParameters(string $class, string $method)
    {
        return new static('Missing cell parameters');
    }

    public static function forInvalidCellParameter(string $key)
    {
        return new static('Invalid cell parameter');
    }

    public static function forNoCellClass()
    {
        return new static('No cell class');
    }

    public static function forInvalidCellClass(string $class = null)
    {
        return new static('Invalid cell class');
    }

    public static function forTagSyntaxError(string $output)
    {
        return new static('Tag syntax error');
    }

    public static function forInvalidFile(string $path)
    {
        return new static('Invalid file: ' . $path);
    }

    public static function forInvalidArgumentException(string $message)
    {
        return new static('Invalid argument');
    }
}
