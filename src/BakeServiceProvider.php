<?php

namespace Pyrite357\Bake;

use Illuminate\Support\ServiceProvider;

class BakeServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Register commands
        $this->commands([
            \Pyrite357\Bake\Commands\BakeCommand::class,
        ]);
    }

    public function boot()
    {
        //
    }
}

