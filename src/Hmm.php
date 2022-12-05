<?php

namespace Kykurniawan\Hmm;

use Closure;
use Exception;
use Kykurniawan\Hmm\Exceptions\PageNotFoundException;

class Hmm
{
    const GET = 'GET';
    const POST = 'POST';
    const SUPPORTED_METHODS = [self::GET, self::POST];

    /**
     * @var string
     */
    protected $url;

    /**
     * @var \Kykurniawan\Hmm\RouteItem[]
     */
    protected $routeItems = [];

    /**
     * @var array
     */
    protected $configurations = [];

    /**
     * Construct the app
     * 
     * @param array $configurations
     */
    public function __construct(array $configurations)
    {
        $this->configurations = $configurations;
    }

    /**
     * @param string $method
     * @param string $path
     * @param Closure|array $closure
     * @param array $beforeFunction
     */
    public function route(string $method, string $path, Closure|array $closure, $beforeFunctions = [])
    {
        if (!in_array($method, self::SUPPORTED_METHODS)) {
            throw new Exception('Unsupported method: ' . $method);
        }

        $routeItem = new RouteItem($method, $path, $closure, $beforeFunctions);

        array_push($this->routeItems, $routeItem);

        return $this;
    }

    public function get(string $path, Closure|array $closure, $beforeFunctions = [])
    {
        $this->route(self::GET, $path, $closure, $beforeFunctions);

        return $this;
    }

    public function post(string $path, Closure|array $closure, $beforeFunctions = [])
    {
        $this->route(self::POST, $path, $closure, $beforeFunctions);

        return $this;
    }

    public function config(string $key, $default = null)
    {
        $current = $this->configurations;
        $p = strtok($key, '.');
        while ($p !== false) {
            if (!isset($current[$p])) {
                return $default;
            }
            $current = $current[$p];
            $p = strtok('.');
        }
        return $current;
    }

    public function run(Closure $errorHandler = null)
    {
        try {
            $currentPath = '/';
            if (isset($_SERVER['PATH_INFO'])) {
                $currentPath = $_SERVER['PATH_INFO'];
            }
            foreach ($this->routeItems as $route) {
                $currentPath = trim($currentPath, '/');
                $path = trim($route->path, '/');

                $explodedCurrentPath = explode('/', $currentPath);
                $explodedPath = explode('/', $path);

                if (sizeof($explodedCurrentPath) !== sizeof($explodedPath)) {
                    continue;
                }

                $params = [];
                $replacedPath = [];

                foreach ($explodedPath as $position => $it) {
                    if (preg_match('/^:.*/', $it, $match)) {
                        $param = trim($match[0], ':');
                        if ($explodedCurrentPath[$position] === '') {
                            continue;
                        }
                        $params[$param] = $explodedCurrentPath[$position];
                        $replacedPath[$position] = $explodedCurrentPath[$position];
                    } else {
                        $replacedPath[$position] = $it;
                    }
                }

                if (sizeof($params) === 0) {
                    if ($currentPath !== $path) {
                        continue;
                    }
                }

                if (implode('/', $replacedPath) !== $currentPath) {
                    continue;
                }

                $request = new Request($this);
                $request->setParams($params);
                $response = new Response($this);

                if ($request->method() !== $route->method) {
                    continue;
                }

                $beforeResult = $this->runBeforeFunction($route->beforeFunctions, $request, $response);

                if ($beforeResult instanceof Request) {
                    $result = '';
                    if (is_array($route->handler)) {
                        $controller = new $route->handler[0]($this);
                        if ($controller instanceof Controller == false) {
                            throw new Exception('Invalid controller class');
                        }
                        $controller->init($this);
                        $result = call_user_func([$controller, $route->handler[1]], $beforeResult, $response);
                    } else if (is_callable($route->handler)) {
                        $result = call_user_func($route->handler, $beforeResult, $response);
                    }

                    if (
                        is_string($result) ||
                        is_bool($result) ||
                        is_numeric($result) ||
                        is_long($result) ||
                        is_float($result) ||
                        is_int($result) ||
                        is_double($result)
                    ) {
                        print($result);
                    }
                    exit;
                } else {
                    if (
                        is_string($beforeResult) ||
                        is_bool($beforeResult) ||
                        is_numeric($beforeResult) ||
                        is_long($beforeResult) ||
                        is_float($beforeResult) ||
                        is_int($beforeResult) ||
                        is_double($beforeResult)
                    ) {
                        print($beforeResult);
                    }
                    exit;
                }
            }

            throw new PageNotFoundException();
        } catch (\Throwable $error) {
            if ($errorHandler) {
                $errorHandler($error);
            } else {
                $this->errorHandler($error);
            }
        }
    }

    private function errorHandler(\Throwable $error)
    {
        if ($error instanceof PageNotFoundException) {
            http_response_code($error->getCode());
            echo $error->getMessage();
            exit;
        }

        throw $error;
    }

    private function runBeforeFunction($beforeFunctions, $result, $response)
    {
        if (sizeof($beforeFunctions) === 0) {
            return $result;
        }

        $function = $beforeFunctions[0];
        array_shift($beforeFunctions);

        if ($result instanceof Request) {
            $result = $function($result, $response);
            $this->runBeforeFunction($beforeFunctions, $result, $response);
        }

        return $result;
    }
}
