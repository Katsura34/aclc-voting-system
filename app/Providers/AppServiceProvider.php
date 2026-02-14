<?php

namespace App\Providers;

use App\Support\ExtensionMimeTypeGuesser;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\Mime\MimeTypes;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register fallback MIME guesser when fileinfo extension is unavailable
        if (!extension_loaded('fileinfo')) {
            MimeTypes::getDefault()->registerGuesser(new ExtensionMimeTypeGuesser());
        }
    }
}
