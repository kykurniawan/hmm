<?php

namespace Kykurniawan\Hmm;

class Response
{
    private Hmm $hmm;
    public int $code = 200;
    public mixed $content = '';
    public array $headers = [];

    public function __construct(Hmm $hmm)
    {
        $this->hmm = $hmm;
    }

    public function hmm(): Hmm
    {
        return $this->hmm;
    }

    public function code(int $code)
    {
        $this->code = $code;

        return $this;
    }

    public function header(string $header)
    {
        array_push($this->headers, $header);

        return $this;
    }

    public function view($viewPath, $data = [])
    {
        $view = new View($this->hmm()->config(Hmm::CONF_VIEW_PATH));
        $view->setData($data);
        $this->content = $view->render(str_replace('.', '/', $viewPath));

        return $this;
    }

    public function content($content = '')
    {
        $this->content = $content;

        return $this;
    }

    public function redirect(string $to, bool $permanent = false)
    {
        if ($permanent) {
            http_response_code(301);
        } else {
            http_response_code(302);
        }
        header('Location: ' . $to);
        exit;
    }
}
