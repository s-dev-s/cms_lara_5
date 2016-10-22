<?php namespace Vis\Builder\Facades;

use Illuminate\Support\Facades\Facade;

class Jarboe extends Facade
{

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'jarboe';
    } // end getFacadeAccessor
}
