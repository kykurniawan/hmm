<?php

namespace Kykurniawan\Hmm;

use Closure;
use Exception;

class RouteItem
{
    private string $method;
    private string $path;
    private Closure|array|string $handler;
    private array $beforeFunctions = [];
    private ?string $name = null;
    public array $paramList = [];

    public function __construct(string $method, string $path, Closure|array|string $handler)
    {
        $this->method = $method;
        $this->path = $path;
        $this->handler = $handler;

        $paramsList = [];
        $explodedPath = explode('/', trim($this->path, '/'));
        foreach ($explodedPath as $it) {
            if (preg_match('/^:.*/', $it, $match)) {
                if (in_array($match[0], $paramsList)) {
                    throw new Exception('Duplicate route parameter name: [' . $match[0] . '] in route path [' . $this->path . ']');
                }
                $paramsList[] = trim($match[0], ':');
            }
        }

        $this->paramList = $paramsList;
    }

    public function name(string $name)
    {
        $this->name = $name;

        return $this;
    }

    public function beforeFunctions(array $beforeFunctions)
    {
        $this->beforeFunctions = $beforeFunctions;

        return $this;
    }

    public function hasParameter()
    {
        return sizeof($this->paramList) > 0;
    }

    public function getMethod()
    {
        return $this->method;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function getRealPath(array $parameters = null)
    {

        if (!$this->hasParameter()) {
            return $this->path;
        } else {
            if (is_null($parameters)) {
                throw new Exception('Parameter value required for route ' . $this->path);
            }

            if (sizeof($parameters) !== sizeof($this->paramList)) {
                throw new Exception('Route ' . $this->path . ' is require ' . sizeof($this->paramList) . ' parameter');
            }

            $path = $this->path;
            foreach ($parameters as $parameter => $value) {
                if (!in_array($parameter, $this->paramList)) {
                    throw new Exception('Invalid parameter ' . $parameter . ' for route ' . $this->path);
                }

                $path = preg_replace('/:' . $parameter . '/', $value, $path);
            }

            return $path;
        }
    }

    public function getHandler()
    {
        return $this->handler;
    }

    public function getBeforeFunctions()
    {
        return $this->beforeFunctions;
    }

    public function getName()
    {
        return $this->name;
    }
}
