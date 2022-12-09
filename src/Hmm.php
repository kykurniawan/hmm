<?php

namespace Kykurniawan\Hmm;

use Closure;
use Exception;
use Kykurniawan\Hmm\Exceptions\PageNotFoundException;
use ReflectionClass;
use ReflectionFunction;
use ReflectionMethod;

class Hmm
{
    const METHOD_GET = 'GET';
    const METHOD_POST = 'POST';
    const SUPPORTED_METHODS = [self::METHOD_GET, self::METHOD_POST];
    const CONF_BASE_URL = 'base_url';
    const CONF_VIEW_PATH = 'view_path';
    const CONF_PUBLIC_PATH = 'public_path';

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
     * @param Closure|array|string $handler
     * @param array $beforeFunction
     */
    public function route(string $method, string $path, Closure|array|string $handler, $beforeFunctions = [])
    {
        if (!in_array($method, self::SUPPORTED_METHODS)) {
            throw new Exception('Unsupported method: ' . $method);
        }

        $routeItem = new RouteItem($method, $path, $handler, $beforeFunctions);

        array_push($this->routeItems, $routeItem);

        return $this;
    }

    public function get(string $path, Closure|array|string $handler, $beforeFunctions = [])
    {
        $this->route(self::METHOD_GET, $path, $handler, $beforeFunctions);

        return $this;
    }

    public function post(string $path, Closure|array|string $handler, $beforeFunctions = [])
    {
        $this->route(self::METHOD_POST, $path, $handler, $beforeFunctions);

        return $this;
    }

    /**
     * @return \Kykurniawan\Hmm\Request
     */
    public function request()
    {
        return $this->request;
    }

    /**
     * @return \Kykurniawan\Hmm\Response
     */
    public function response()
    {
        return $this->response;
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

                $result = $this->runBeforeFunction($route->beforeFunctions, $request, $response);

                switch (true) {
                    case $result instanceof Request:
                        if (is_array($route->handler)) {
                            $controller = new $route->handler[0];
                            if ($controller instanceof Controller == false) {
                                throw new Exception('Controller should extends the ' . Controller::class . ' class');
                            }
                            $controller->init($this);
                            $result = $this->runControllerMethod($controller, $route->handler[1], $result, $response);
                        } else if (is_string($route->handler) && class_exists($route->handler)) {
                            $invokableController = new $route->handler;
                            if ($invokableController instanceof Controller == false) {
                                throw new Exception('Controller should extends the ' . Controller::class . ' class');
                            }
                            $invokableController->init($this);
                            $result = $this->runInvokableController($invokableController, $result, $response);
                        } else if (is_callable($route->handler)) {
                            $result = $this->runClosure($route->handler, $result, $response);
                        }
                        $this->sendResponse($result);
                        break;
                    case $result instanceof Response:
                        $this->sendResponse($result);
                        break;
                    default:
                        $this->sendResponse($result);
                        break;
                }

                exit;
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
            $result = $this->runClosure($function, $result, $response);
            $this->runBeforeFunction($beforeFunctions, $result, $response);
        }

        return $result;
    }

    private function runClosure($closure, $request, $response)
    {

        $functionReflection = new ReflectionFunction($closure);
        $functionParameters = $functionReflection->getParameters();
        $arguments = $this->prepareRouteHandlerArguments($functionParameters, $request, $response);

        return $functionReflection->invoke(...$arguments);
    }

    private function runControllerMethod($controller, $method, $request, $response)
    {

        $methodReflection = new ReflectionMethod($controller, $method);
        $methodParameters = $methodReflection->getParameters();
        $arguments = $this->prepareRouteHandlerArguments($methodParameters, $request, $response);

        return $methodReflection->invoke($controller, ...$arguments);
    }

    private function runInvokableController($invokableController, $request, $response)
    {
        $invokableControllerReflection = new ReflectionClass($invokableController);
        if (!$invokableControllerReflection->hasMethod('__invoke')) {
            throw new Exception('Invokable class should have __invoke method');
        }
        $invokeMethod = $invokableControllerReflection->getMethod('__invoke');
        $invokeMethodParameters = $invokeMethod->getParameters();
        $arguments = $this->prepareRouteHandlerArguments($invokeMethodParameters, $request, $response);

        return $invokeMethod->invoke($invokableController, ...$arguments);
    }

    private function prepareRouteHandlerArguments($parameters, $request, $response)
    {
        $arguments = [];
        foreach ($parameters as $parameter) {
            if (is_null($parameter->getType())) {
                throw new Exception('Please add parameter type for ' . $parameter->getName());
            }
            $parameterType = $parameter->getType()->getName();
            if (!in_array($parameterType, [Request::class, Response::class])) {
                throw new Exception('Parameter type for ' . $parameter->getName() . ' should be ' . Request::class . ' or ' . Response::class . ', ' . $parameterType . ' given');
            }
            if ($parameterType == Request::class) {
                $arguments[$parameter->getName()] = $request;
            } else if ($parameterType == Response::class) {
                $arguments[$parameter->getName()] = $response;
            }
        }
        return $arguments;
    }

    private function sendResponse($result)
    {
        $code = 200;
        $content = $result;
        $headers = [];
        if ($result instanceof Response) {
            $code = $result->code;
            $content = $result->content;
            $headers = $result->headers;
        }

        if (
            is_string($content) ||
            is_bool($content) ||
            is_numeric($content) ||
            is_long($content) ||
            is_float($content) ||
            is_int($content) ||
            is_double($content)
        ) {
            http_response_code($code);
            foreach ($headers as $header) {
                header($header);
            }
            echo $content;
        } else if (is_array($content)) {
            $headers[] = 'Content-Type: application/json';

            http_response_code($code);
            foreach ($headers as $header) {
                header($header);
            }
            echo json_encode($content);
        } else {
            if (!is_null($content)) {
                var_dump($content);
            }
        }
    }
}
