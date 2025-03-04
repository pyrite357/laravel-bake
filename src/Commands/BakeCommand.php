<?php

namespace Pyrite357\LaravelBake\Commands;

use Illuminate\Console\Command;

class BakeCommand extends Command
{
    protected $signature = 'cake:bake {name}';
    protected $description = 'Bake a new model (+CRUD pages) with Laravel-Bake by Pyrite357';

    public function handle()
    {
        $name = $this->argument('name');
        $this->call('cake:bake', ['name' => $name, '--migration' => true, '--factory' => true]);
        $this->info("Model $name created with migration and factory!");
    }
}

