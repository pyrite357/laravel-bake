<?php

namespace Pyrite357\LaravelBake\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;


class ListCommand extends Command {

    protected $signature = 'cake:list {--s|--schema= : List tables/views in a specific schema only}';
    protected $description = 'List all tables/views in all schemas in the database (ignores system schemas like pg_catalog)';

    public function handle() {
        $dbname = config('database.connections.' . config('database.default') . '.database');
        $schema = $this->option('schema');
        $s = 'all schemas';
        if ($schema) {
            $sql = "SELECT table_schema || '.' || table_name AS table FROM information_schema.tables WHERE table_schema = '$schema' ORDER BY table_schema, table_name";
            $s = $schema;
        } else {
            $this->warn('No schema specified');
            $sql = "SELECT table_schema || '.' || table_name AS table FROM information_schema.tables WHERE table_schema NOT IN ('pg_catalog', 'information_schema') ORDER BY table_schema, table_name";
        }

        $tables = DB::select($sql);
        $this->info('');
        $this->info('Listing all tables/views in '.$s.' in '.$dbname);
        $this->info('');
        foreach ($tables AS $t) {
            $this->info($t->table);
        }
        $this->info('');
        return Command::SUCCESS;
    }
}

