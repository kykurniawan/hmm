<?php

namespace Kykurniawan\Hmm;

class Response
{
    private Hmm $app;

    public function __construct(Hmm $app)
    {
        $this->app = $app;
    }

    public function app(): Hmm
    {
        return $this->app;
    }

    public function view($viewPath, $data = [])
    {
        $view = new View($this->app()->config('view_path'));

        $view->setData($data);

        return $view->render(str_replace('.', '/', $viewPath));
    }
}
