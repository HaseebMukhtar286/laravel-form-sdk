<?php

namespace haseebmukhtar286\LaravelFormSdk;

use Illuminate\Support\Facades\Route;
use haseebmukhtar286\LaravelFormSdk\Controllers\FormController;

class OutlawSdkRoutes
{
    public static function routes($middleware = [])
    {
        return Route::middleware($middleware)->group(function () {
            Route::get('/outlaw-form', [FormController::class, 'index']);
            Route::post('/outlaw-form', [FormController::class, 'store']);
            Route::get('/outlaw-form/{id}', [FormController::class, 'show']);
            Route::put('/outlaw-form/{id}', [FormController::class, 'update']);
            Route::delete('/outlaw-form/{id}', [FormController::class, 'destroy']);
        });;
    }
}
