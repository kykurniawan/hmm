<?php

namespace Kykurniawan\Hmm;

class Controller
{
    /**
     * @var \Kykurniawan\Hmm\Hmm
     */
    private Hmm $hmm;

    public function init(Hmm $hmm)
    {
        $this->hmm = $hmm;
    }

    /**
     * @return \Kykurniawan\Hmm\Hmm
     */
    public function hmm(): Hmm
    {
        return $this->hmm;
    }
}
