<?php

namespace haseebmukhtar286\LaravelFormSdk;

use Illuminate\Support\Facades\Facade;

/**
 * @see \haseebmukhtar286\LaravelFormSdk\Skeleton\SkeletonClass
 */
class LaravelFormSdkFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'laravel-form-sdk';
    }
}
