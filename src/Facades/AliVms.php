<?php
/**
 * Created by PhpStorm.
 * User: <zhufengwei@100tal.com>
 * Date: 2019/1/4
 * Time: 16:52
 */

namespace Listen\AliVms\Facades;

use Illuminate\Support\Facades\Facade;

class AliVms extends Facade
{
    public function getFacadeAccessor()
    {
        return 'alivms';
    }
}
