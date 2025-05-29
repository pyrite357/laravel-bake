<?php

namespace Pyrite357\LaravelBake\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;


class BakeCommand extends Command {

    protected $signature = 'cake:bake {table_name}';
    protected $description = 'Bake a new model (+CRUD pages) with Laravel-Bake by Pyrite357';

    protected function renderStub(string $stubPath, array $vars): string {
        $content = file_get_contents($stubPath);
        return str_replace(array_keys($vars), array_values($vars), $content);
    }


    public function handle() {

        // Ensure first argument is schema_name.table_name
        $input = $this->argument('table_name'); // e.g. 'myschema.mytable'
        if (!str_contains($input, '.')) {
            $this->error("Invalid format. Expected 'schema.table'. For example: public.users or closeio.contacts");
            return Command::FAILURE;
        }
        [$schema, $table] = explode('.', strtolower($input), 2);
        //$this->info($schema);
        //$this->info($table);

        // Ensure table exists
        $exists = DB::selectOne(
            'SELECT 1 FROM information_schema.tables WHERE table_schema = :schema AND table_name = :table',
            ['schema' => $schema, 'table' => $table]
        );
        if (!$exists) {
            $this->error("Table '$schema.$table' does not exist.");
            return Command::FAILURE;
        }

        // Table name in various forms
        $name = Str::studly($table); // PascalCase for class names
        $modelName = $name;
        $replacements = [
            '{{ modelSingular }}' => $modelName,                                // e.g., 'Post'
            '{{ modelPlural }}' => Str::plural($modelName),                    // 'Posts'
            '{{ modelVariable }}' => Str::camel($modelName),                   // 'post'
            '{{ modelVariablePlural }}' => Str::camel(Str::plural($modelName)),// 'posts'
            '{{ table }}' => Str::snake(Str::plural($modelName)),              // 'posts'
            '{{ routeName }}' => Str::snake(Str::plural($modelName)),          // 'posts'
            '{{ viewFolder }}' => Str::snake(Str::plural($modelName)),         // 'posts'
            '{{ title }}' => Str::headline(Str::plural($modelName)),           // 'Posts'
        ];
        $tableName = Str::snake($table); // snake_case for URLs
        $this->info("Generating CRUD for: $name");

        // Get column names and types
        $columns = DB::select(
            'SELECT column_name, data_type
             FROM information_schema.columns
             WHERE table_schema = :schema AND table_name = :table
             ORDER BY ordinal_position',
            ['schema' => $schema, 'table' => $table]
        );
        $this->info("Columns in $schema.$table:");
        foreach ($columns as $col) {
            $this->line(" - {$col->column_name} ({$col->data_type})");
        }

        // 1. Create Model with migration and factory
        $this->call('make:model', [
            'name' => $name,
            '--migration' => false,
            '--factory' => false,
        ]);
        $this->info("\nModel created: app/Models/$name.php\n");

        // 2. Create Controller
        $this->call('make:controller', [
            'name' => "{$name}Controller",
            '--resource' => true,
            '--model' => $name, // binds the model
        ]);

        // 3. Append route to routes/web.php
        $route = "\n// [Auto-Generated CRUD for $name]\n";
        $route .= "Route::resource('$table', App\\Http\\Controllers\\{$name}Controller::class);\n";
        file_put_contents(base_path('routes/web.php'), $route, FILE_APPEND);

        // Generate Model
        //$this->call('make:model', ['name' => $name, '--migration' => false, '--factory' => false]);

        // Generate Controller
        //$this->call('make:controller', ['name'=>"{$name}Controller"]);
        //$this->info("Controller created: app/Http/Controllers/{$name}Controller.php");

        $stubPath = __DIR__ . '/../stubs/form.stub.blade.php';
        $this->info("stubPath is $stubPath");

        // Generate Views
        $viewPath = resource_path("views/{$tableName}/form.blade.php");
        if (!file_exists(dirname($viewPath))) {
            mkdir(dirname($viewPath), 0755, true);
        }
        $formView = $this->renderStub($stubPath, $replacements);
        file_put_contents($viewPath, $formView);
        $this->info("View created: resources/views/{$tableName}/form.blade.php");

        // Define Routes
        /*
        $route_top = "use App\Http\Controllers\\" . "{$name}Controller;";
        $route_bot = "Route::resource('$tableName', {$name}Controller::class);";
        $this->info("Add the following 2 lines to routes/web.php");
        $this->info('');
        $this->info($route_top);
        $this->info($route_bot);
        $this->info('');
        */

        /*
        $routeDefinition = <<<EOT
Route::get('/$tableName', [\App\Http\Controllers\\{$name}Controller::class, 'index'])->name('$tableName.index');
EOT;

        file_put_contents(base_path('routes/web.php'), PHP_EOL . $routeDefinition, FILE_APPEND);
        $this->info("Routes added to routes/web.php");
        */
        $this->info("\n\n");
        $this->info("CRUD for scaffolding for $input complete!");
        return Command::SUCCESS;
    }
}

