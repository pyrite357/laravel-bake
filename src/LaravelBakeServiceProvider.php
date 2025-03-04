<?php

namespace Pyrite357\LaravelBake;

use Illuminate\Support\ServiceProvider;

class LaravelBakeServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Register commands
        $this->commands([
            \Pyrite357\LaravelBake\Commands\BakeCommand::class,
        ]);
    }

    public function boot()
    {
        //
    }
}

