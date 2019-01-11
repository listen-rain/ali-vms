<?php

namespace Listen\AliVms\Facades;

use Illuminate\Support\Facades\Facade;

class AliVms extends Facade
{
    public static function getFacadeAccessor()
    {
        return 'alivms';
    }
}
