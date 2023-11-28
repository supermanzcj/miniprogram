<?php

namespace Superzc\Miniprogram\Facades;

use Illuminate\Support\Facades\Facade;

class Miniprogram extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'miniprogram';
    }
}