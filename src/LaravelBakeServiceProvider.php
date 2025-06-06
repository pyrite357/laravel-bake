<?php

namespace Pyrite357\LaravelBake;

use Illuminate\Support\ServiceProvider;

class LaravelBakeServiceProvider extends ServiceProvider {

    public function register() {
        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                \Pyrite357\LaravelBake\Commands\BakeCommand::class,
                \Pyrite357\LaravelBake\Commands\ListCommand::class
            ]);
        }
    }

}

