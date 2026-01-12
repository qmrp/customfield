<?php

namespace Qmrp\CustomField\Facades;

use Illuminate\Support\Facades\Facade;

class CustomField extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'customFieldService';
    }
}
