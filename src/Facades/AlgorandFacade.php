<?php


namespace Rootsoft\Algorand\Facades;

use Illuminate\Support\Facades\Facade;
use Rootsoft\Algorand\Algorand;

class AlgorandFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return Algorand::class;
    }
}
