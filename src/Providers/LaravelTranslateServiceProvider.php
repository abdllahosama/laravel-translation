<?php

namespace Abdallah\LaravelTranslate\Providers;

use Abdallah\LaravelTranslate\Commands\TranslationHandlerCommand;
use Illuminate\Support\ServiceProvider;

class LaravelTranslateServiceProvider extends ServiceProvider
{

    public function boot()
    {

        if ($this->app->runningInConsole()) {
            $this->commands([
                TranslationHandlerCommand::class
            ]);
        }
    }


    public function register()
    {
    }
}
