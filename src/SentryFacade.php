<?php
namespace EETechMedia\Sentry;

use Illuminate\Support\Facades\Facade;

class SentryFacade extends Facade
{
    /**
     * Get facade accessor
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'sentry';
    }
}
