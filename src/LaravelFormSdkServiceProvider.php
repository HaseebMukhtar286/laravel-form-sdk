<?php

namespace haseebmukhtar286\LaravelFormSdk;

use Illuminate\Support\ServiceProvider;


use Illuminate\Support\Facades\Route;
use haseebmukhtar286\LaravelFormSdk\Controllers\FormSubmissionController;
use haseebmukhtar286\LaravelFormSdk\Controllers\SchemaController;
use haseebmukhtar286\LaravelFormSdk\Controllers\ExcelGenerateController;
use haseebmukhtar286\LaravelFormSdk\Controllers\FormSchemaController;
use haseebmukhtar286\LaravelFormSdk\Controllers\ImageUploadController;
use haseebmukhtar286\LaravelFormSdk\Controllers\PdfController;
use haseebmukhtar286\LaravelFormSdk\Controllers\ReportController;

class LaravelFormSdkServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        // $this->addDynamicMiddlewareToKernel();
        Route::get('api/form/all-forms', [SchemaController::class, 'listingBySecretKeyAll']);
        Route::get('api/form/all-forms-list', [FormSchemaController::class, 'index']);
        Route::post('api/image-upload/{id}', [ImageUploadController::class, 'imageUpload']);
        
        Route::prefix('api/formSchema')->group(function () {
            Route::post('create', [FormSchemaController::class, 'store']);
        });

        // Route::prefix('api')->middleware('your.dynamic.middleware')->group(function () {
        //     Route::get('/submition-data', [ReportController::class, 'submitionData']);
        // });

        Route::prefix('api')->group(function () {
            Route::get('/submition-data', [ReportController::class, 'submitionData']);
        });

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
                Route::get('/all', [FormSubmissionController::class, 'all']);
                Route::post('/approved', [FormSubmissionController::class, 'approve']);
                Route::post('/rejected', [FormSubmissionController::class, 'reject']);
                // Route::get('moc-dashboard', [FormSubmissionController::class, 'dashboard']);
            });

            // for schema controller
            Route::prefix('form')->group(function () {
                Route::get('/', [SchemaController::class, 'listingBySecretKey']);
                Route::post('create', [SchemaController::class, 'createForm']);
                Route::post('update-form', [SchemaController::class, 'updateForm']);
                Route::get('show/{id}', [SchemaController::class, 'showFormById']);
                Route::post('update', [SchemaController::class, 'updateFormById']);
                Route::delete('delete/{id}', [SchemaController::class, 'deleteFormById']);


                Route::get('/builder/{id}', [SchemaController::class, 'getBuilder']);
                Route::post('change-status', [SchemaController::class, 'changeStatus']);
                // Route::post('/fill/{id}', [SchemaController::class, 'fillForm']);

                // Route::put('/submission/update/{id}', [SchemaController::class, 'updateSubmissionForm']);
                // Route::get('/submissions/{id}', [SchemaController::class, 'getAllSubmissionForm']);
                // Route::get('/submission/{id}', [SchemaController::class, 'getSubmissionShow']);
                // Route::delete('/submission/delete/{id}', [SchemaController::class, 'deleteSubmission']);
                // Route::get('/edit-builder/{id}', [SchemaController::class, 'getEditBuilderUrl']);
            });

            Route::get('excelgenerate/{id}', [ExcelGenerateController::class, 'excelGenerate']);
            Route::get('extractPdfData/{id}', [PdfController::class, 'pdfGenerate']);
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

        // Register dynamic middleware
        // $this->registerDynamicMiddleware();
    }

    // private function registerDynamicMiddleware()
    // {
    //     $middlewareFilePath = app_path('Http/Middleware/YourDynamicMiddleware.php');

    //     if (!class_exists(\App\Http\Middleware\YourDynamicMiddleware::class) && file_exists($middlewareFilePath)) {
    //         require_once $middlewareFilePath;
    //     }

    //     if (class_exists(\App\Http\Middleware\YourDynamicMiddleware::class)) {
    //         // Register the middleware under a custom name
    //         app('router')->aliasMiddleware('your.dynamic.middleware', \App\Http\Middleware\YourDynamicMiddleware::class);
    //     }
    // }

    // private function addDynamicMiddlewareToKernel()
    // {
    //     if (!class_exists(\App\Http\Middleware\YourDynamicMiddleware::class)) {
    //         // If it doesn't exist, dynamically create it
    //         file_put_contents(app_path('Http/Middleware/YourDynamicMiddleware.php'), '<?php

    //             namespace App\Http\Middleware;

    //             use Closure;

    //             class YourDynamicMiddleware
    //             {
    //                 public function handle($request, Closure $next)
    //                 {
    //                     if ($request->api_key != "LjRcW9cKRdUsmMD0FNJrJTmbxi") {
    //                     return response()->json(["message" => "Unauthenticated."], 401);
    //                 }
    //                     // Middleware logic here
    //                     return $next($request);
    //                 }
    //             }
    //             ');
    //         $this->app[Kernel::class]->pushMiddleware(\App\Http\Middleware\YourDynamicMiddleware::class);
    //     }
    // }
}
