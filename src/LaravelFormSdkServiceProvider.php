<?php

namespace haseebmukhtar286\LaravelFormSdk;

use Illuminate\Support\ServiceProvider;


use Illuminate\Support\Facades\Route;
use haseebmukhtar286\LaravelFormSdk\Controllers\FormSubmissionController;
use haseebmukhtar286\LaravelFormSdk\Controllers\SchemaController;
use haseebmukhtar286\LaravelFormSdk\Controllers\ExcelGenerateController;

class LaravelFormSdkServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        Route::prefix('/api')->middleware(['auth:api'])->group(function () {
            // Route::get('/outlaw-form', [FormController::class, 'index']);
            // Route::post('/outlaw-form', [FormController::class, 'store']);
            // Route::get('/outlaw-form/{id}', [FormController::class, 'show']);
            // Route::put('/outlaw-form/{id}', [FormController::class, 'update']);
            // Route::delete('/outlaw-form/{id}', [FormController::class, 'destroy']);
            Route::prefix('FormSubmission')->group(function () {
                Route::get('/', [FormSubmissionController::class, 'index']);
                Route::post('create', [FormSubmissionController::class, 'store']);
                Route::get('show/{id}', [FormSubmissionController::class, 'show']);
                Route::put('update/{id}', [FormSubmissionController::class, 'update']);
                Route::delete('delete/{id}', [FormSubmissionController::class, 'destroy']);
                // Route::get('moc-dashboard', [FormSubmissionController::class, 'dashboard']);
            });

            Route::prefix('form')->group(function () {
                Route::get('/', [SchemaController::class, 'listingBySecretKey']);
                Route::post('create', [SchemaController::class, 'createForm']);
                Route::get('show/{id}', [SchemaController::class, 'showFormById']);
                Route::post('update', [SchemaController::class, 'updateFormById']);
                Route::delete('delete/{id}', [SchemaController::class, 'deleteFormById']);

                Route::get('/all-forms', [SchemaController::class, 'listingBySecretKeyAll']);
                Route::get('/builder', [SchemaController::class, 'getBuilder']);
                // Route::post('/fill/{id}', [SchemaController::class, 'fillForm']);

                // Route::put('/submission/update/{id}', [SchemaController::class, 'updateSubmissionForm']);
                // Route::get('/submissions/{id}', [SchemaController::class, 'getAllSubmissionForm']);
                // Route::get('/submission/{id}', [SchemaController::class, 'getSubmissionShow']);
                // Route::delete('/submission/delete/{id}', [SchemaController::class, 'deleteSubmission']);
                // Route::get('/edit-builder/{id}', [SchemaController::class, 'getEditBuilderUrl']);
            });

            Route::get('excelgenerate/{id}', [ExcelGenerateController::class, 'excelGenerate']);
        });
        /*
         * Optional methods to load your package assets
         */
        // $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'laravel-form-sdk');
        // $this->loadViewsFrom(__DIR__.'/../resources/views', 'laravel-form-sdk');
        // $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        // $this->loadRoutesFrom(__DIR__.'/routes.php');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/config.php' => config_path('laravel-form-sdk.php'),
            ], 'config');

            // Publishing the views.
            /*$this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/laravel-form-sdk'),
            ], 'views');*/

            // Publishing assets.
            /*$this->publishes([
                __DIR__.'/../resources/assets' => public_path('vendor/laravel-form-sdk'),
            ], 'assets');*/

            // Publishing the translation files.
            /*$this->publishes([
                __DIR__.'/../resources/lang' => resource_path('lang/vendor/laravel-form-sdk'),
            ], 'lang');*/

            // Registering package commands.
            // $this->commands([]);
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__ . '/../config/config.php', 'laravel-form-sdk');

        // Register the main class to use with the facade
        $this->app->singleton('laravel-form-sdk', function () {
            return new LaravelFormSdk;
        });
    }
}
