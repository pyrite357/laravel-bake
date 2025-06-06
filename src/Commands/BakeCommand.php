<?php

namespace Pyrite357\LaravelBake\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

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
            '{{ modelClass }}' => $modelName,                                // e.g., 'Post'
            '{{ modelPlural }}' => Str::plural($modelName),                    // 'Posts'
            '{{ modelVariable }}' => Str::camel($modelName),                   // 'post'
            '{{ modelVariablePlural }}' => Str::camel(Str::plural($modelName)),// 'posts'
            '{{ table }}' => Str::snake(Str::plural($modelName)),              // 'posts'
            '{{ routeName }}' => Str::snake(Str::plural($modelName)),          // 'posts'
            '{{ routePrefix }}' => Str::snake(Str::plural($modelName)),          // 'posts'
            '{{ viewFolder }}' => Str::snake(Str::plural($modelName)),         // 'posts'
            '{{ title }}' => Str::headline(Str::plural($modelName)),           // 'Posts'
            '{{ controllerClass }}' => $modelName.'Controller',                // 'PostsController'
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
        $model = Str::singular($modelName);
        $modelPath = app_path("Models/" . $model . ".php");
        $modelExists = File::exists($modelPath);
        $domodel = true;
        if ($modelExists) {
            if (! $this->confirm("File '{$modelPath}' exists. Overwrite? (Backup will be created if yes)", false)) {
                // Selected no
                $domodel = false;
                $this->info("Skipping ".$modelPath);
            } else {
                // Create a backup before overwriting
                $backupPath = $modelPath . '.' . now()->format('Ymd_His') . '.bak';
                File::copy($modelPath, $backupPath);
                $this->info("Backup created: {$backupPath}");
            }
        }
        if ($domodel) {
            $stub_model = file_get_contents(base_path('vendor/pyrite357/laravel-bake/stubs/models/model.stub'));
            $code_model = $this->renderStub(base_path('vendor/pyrite357/laravel-bake/stubs/models/model.stub'), $replacements);
            file_put_contents($modelPath, $code_model);
            $this->info("Model created: $modelPath");
        }

        // 2. Create Controller
        $stub_controller = file_get_contents(base_path('vendor/pyrite357/laravel-bake/stubs/controllers/controller.stub'));
        $code_controller = $this->renderStub(base_path('vendor/pyrite357/laravel-bake/stubs/controllers/controller.stub'), $replacements);
        $target_controller = app_path('Http/Controllers/'.$name.'Controller.php');
        file_put_contents($target_controller, $code_controller);
        $this->info("Controller created: $target_controller");

        // 3. Append route to routes/web.php
        $route = "\n// [Auto-Generated CRUD for $name]\n";
        $route .= "Route::resource('$table', App\\Http\\Controllers\\{$name}Controller::class);\n";
        file_put_contents(base_path('routes/web.php'), $route, FILE_APPEND);


        // 4. Generate Views
        $stubPath = __DIR__ . '/../../stubs/views/';
        $viewPath = resource_path("views/{$tableName}/");
        if (!file_exists($viewPath)) {
            mkdir($viewPath, 0755, true);
        }
        $views = ['index','create','edit','show','form'];
        foreach ($views AS $view) {
            $viewContent = $this->renderStub("{$stubPath}{$view}.stub", $replacements);
            file_put_contents("{$viewPath}{$view}.blade.php", $viewContent);
            $this->info("View created: resources/views/{$tableName}/{$view}.blade.php");
        }
        //$formView = $this->renderStub($stubPath.'form.stub', $replacements);
        //file_put_contents($viewPath.'form.blade.php', $formView);
        //$this->info("View created: resources/views/{$tableName}/form.blade.php");

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
        $this->info("\n\n");
        $url = route(Str::snake(Str::plural($modelName)).'.index');  // Works if APP_URL is set

        $this->info("You may open ${url} in your browser now!");

        return Command::SUCCESS;
    }
}

