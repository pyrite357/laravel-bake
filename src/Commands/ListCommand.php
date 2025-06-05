<?php

namespace Pyrite357\LaravelBake\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;


class ListCommand extends Command {

    protected $signature = 'cake:list';
    protected $description = 'List all tables in all schemas in the database';

    public function handle() {
        $dbname = config('database.connections.' . config('database.default') . '.database');
        $sql = "SELECT table_schema || '.' || table_name AS table FROM information_schema.tables WHERE table_schema NOT IN ('pg_catalog', 'information_schema') ORDER BY table_schema, table_name";
        $tables = DB::select($sql);
        $this->info('');
        $this->info('Listing all tables in '.$dbname);
        $this->info('');
        foreach ($tables AS $t) {
            $this->info($t->table);
        }
        $this->info('');
        return Command::SUCCESS;
    }
}

