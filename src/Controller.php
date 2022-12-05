<?php

namespace Kykurniawan\Hmm;

class Controller
{
    /**
     * @var \Kykurniawan\Hmm\Hmm
     */
    private Hmm $app;

    public function init(Hmm $app)
    {
        $this->app = $app;
    }

    /**
     * @return \Kykurniawan\Hmm\Hmm
     */
    public function app(): Hmm
    {
        return $this->app;
    }
}
