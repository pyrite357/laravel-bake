<?php

namespace Pyrite357\LaravelBake\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;


class ListCommand extends Command {

    protected $signature = 'cake:list';
    protected $description = 'List all tables in all schemas in the database';

    public function handle() {

        $tables = DB::selectAll('SELECT schema, table FROM information_schema.tables');
        print_r($tables);
        return Command::SUCCESS;
    }
}

