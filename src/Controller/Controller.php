<?php

namespace Sparkframe\Controller;

use Sparkframe\Model\Model;

abstract class Controller
{
    // een controller moet een model hebben
//    protected Model $model;
//
//    public function __construct(Model $model)
//    {
//        $this->model = $model;
//    }

//todo: zorg dat de controller de request bevat
    public function __construct()
    {
        //todo: een controller heeft toegang nodig tot de request.
        // Dus de request moet al bestaan in de globals.
    }
}
