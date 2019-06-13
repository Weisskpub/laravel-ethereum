<?php

namespace Weisskpub\Ethereum\Facades;

use Illuminate\Support\Facades\Facade;

class Ethereumd extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'ethereumd';
    }
}
