<?php

namespace Kykurniawan\Hmm;

class Request
{
    private string $method;
    private array $params;
    private array $queries;
    private Hmm $app;

    public function __construct(Hmm $app)
    {
        $this->app = $app;
        $this->method = strtoupper($_SERVER['REQUEST_METHOD']);
        $this->queries = $_GET;
    }

    public function setParams(array $params)
    {
        $this->params = $params;
    }

    public function method()
    {
        return $this->method;
    }

    public function app(): Hmm
    {
        return $this->app;
    }

    public function params($key = null)
    {
        if ($key) {
            if (isset($this->params[$key])) {
                return $this->params[$key];
            }
            return null;
        }

        return $this->params;
    }

    public function queries($key = null)
    {
        if ($key) {
            if (isset($this->queries[$key])) {
                return $this->queries[$key];
            }
            return null;
        }

        return $this->queries;
    }
}
