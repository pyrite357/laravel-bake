<?php

namespace Pyrite357\Bake\Commands;

use Illuminate\Console\Command;

class BakeCommand extends Command
{
    protected $signature = 'bake:model {name}';
    protected $description = 'Generate a new model with migration and factory';

    public function handle()
    {
        $name = $this->argument('name');

        $this->call('make:model', ['name' => $name, '--migration' => true, '--factory' => true]);

        $this->info("Model $name created with migration and factory!");
    }
}

