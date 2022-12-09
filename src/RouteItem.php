<?php

namespace Kykurniawan\Hmm;

use Closure;

class RouteItem
{
    public string $method, $path;
    public Closure|array|string $handler;
    public array $beforeFunctions;

    public function __construct(string $method, string $path, Closure|array|string $handler, $beforeFunctions = [])
    {
        $this->method = $method;
        $this->path = $path;
        $this->handler = $handler;
        $this->beforeFunctions = $beforeFunctions;
    }
}
