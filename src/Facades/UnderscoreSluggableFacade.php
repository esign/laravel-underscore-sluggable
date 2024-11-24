<?php

namespace Esign\UnderscoreSluggable\Facades;

use Illuminate\Support\Facades\Facade;

class UnderscoreSluggableFacade extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'underscore-sluggable';
    }
}
