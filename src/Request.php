<?php

namespace Kykurniawan\Hmm;

class Request
{
    private string $method;
    private array $params;
    private array $queries;
    private Hmm $hmm;
    private $url = '';

    public function __construct(Hmm $hmm)
    {
        $this->hmm = $hmm;
        $this->method = strtoupper($_SERVER['REQUEST_METHOD']);
        $this->queries = $_GET;
        $this->url .= $_SERVER['REQUEST_URI'];
    }

    public function url()
    {
        return $this->url;
    }

    public function setParams(array $params)
    {
        $this->params = $params;
    }

    public function method()
    {
        return $this->method;
    }

    public function hmm(): Hmm
    {
        return $this->hmm;
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
