<?php

namespace Pyrite357\LaravelBake\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class BakeCommand extends Command {

    protected $signature = 'cake:bake 
                            {table_name}
                            {--overwrite : Overwrite existing files w/o prompting}';
    protected $description = 'Bake a new model (+CRUD pages) with Laravel-Bake by Pyrite357';

    protected function renderStub(string $stubPath, array $vars): string {
        $content = file_get_contents($stubPath);
        return str_replace(array_keys($vars), array_values($vars), $content);
    }

    public function does_table_exist($schema, $table) {
        $exists = DB::selectOne(
            'SELECT 1 FROM information_schema.tables WHERE table_schema = :schema AND table_name = :table',
            ['schema' => $schema, 'table' => $table]
        );
        return $exists;
    }

    public function backup_existing_file($file_path) {
        $backup_path = $file_path . '.' . now()->format('Ymd_His') . '.backup.php';
        File::copy($file_path, $backup_path);
        $this->info("Backup created: {$backup_path}");
    }

    public function stringInFile($filePath, $searchString) {
        if (!file_exists($filePath) || !is_readable($filePath)) {
            return false;
        }
        $contents = file_get_contents($filePath);
        if ($contents === false) {
            return false;
        }
        return strpos($contents, $searchString) !== false;
    }

    public function handle() {

        // Ensure first argument is schema_name.table_name
        $input = $this->argument('table_name'); // e.g. 'myschema.mytable'
        if (!str_contains($input, '.')) {
            // If no dot, check if table in public schema, else tell them to provide it
            if (!$this->does_table_exist('public', $input)) {
                $this->error("Invalid format. Expected 'schema.table'. For example: public.users or closeio.contacts");
                return Command::FAILURE;
            } else {
                $input = 'public.'.$input;
            }
        }
        [$schema, $table] = explode('.', strtolower($input), 2);

        // Ensure table exists
        if (!$this->does_table_exist($schema, $table)) {
            $this->error("Table '$schema.$table' does not exist.");
            return Command::FAILURE;
        }

        // Get column names and types
        $columns = DB::select(
            'SELECT column_name, data_type
             FROM information_schema.columns
             WHERE table_schema = :schema AND table_name = :table
             ORDER BY ordinal_position',
            ['schema' => $schema, 'table' => $table]
        );

        // Table headers for views
        $table_headers = '';
        $table_rows = '';
        $fillable = [];
        $route_resource = Str::snake(Str::plural($table));
        foreach ($columns AS $c) {
            $col = $c->column_name;
            if (!in_array($col, ['id','created_at','updated_at','created_by','updated_by','created','modified','user_created','user_modified'])) {
                $fillable[] = "'$col'";
            }
            $table_headers .= "\t\t\t\t\t<th><a href=\"{{ route('$route_resource.index', ['sort' => '$col', 'direction' => \$sort === '$col' && \$direction === 'asc' ? 'desc' : 'asc']) }}\">".ucfirst($col)."</a></th>\n";
            $table_rows .= "\t\t\t\t\t\t<td>{{ ";
            if (substr($c->data_type,0,4) == 'time') {
                $table_rows .= "\\Carbon\\Carbon::parse(\$item->$col)->format('n/j/y, g:i A')";
            } else if ($c->data_type == 'date') {
                $table_rows .= "\\Carbon\\Carbon::parse(\$item->$col)->format('n/j/y')";
            } else {
                $table_rows .= "\$item->$col";
            }
            $table_rows .= " }}</td>\n";
        }
        $fillable = implode(',', $fillable);
        $table_rows .= <<<EOT
                        <td>
                            <a href="{{ route('{$route_resource}.show', \$item) }}" class="btn btn-sm btn-info">View</a>
                            <a href="{{ route('{$route_resource}.edit', \$item) }}" class="btn btn-sm btn-warning">Edit</a>
                            <form action="{{ route('{$route_resource}.destroy', \$item) }}" method="POST" style="display:inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                            </form>
                        </td>
EOT;

        // Table name in various forms
        $name = Str::studly($table); // PascalCase for class names
        $modelName = Str::singular($name);
        $replacements = [
            '{{ modelSingular }}' => $modelName,                                // e.g., 'Post'
            '{{ modelClass }}' => $modelName,                                // e.g., 'Post'
            '{{ modelPlural }}' => Str::plural($modelName),                    // 'Posts'
            '{{ modelVariable }}' => Str::camel($modelName),                   // 'post'
            '{{ modelVariablePlural }}' => Str::camel(Str::plural($modelName)),// 'posts'
            '{{ modelName }}' => $name,
            '{{ modelNameLowerCase }}' => strtolower(Str::singular($modelName)),
            '{{ table }}' => Str::snake(Str::plural($modelName)),              // 'posts'
            '{{ routeName }}' => Str::snake(Str::plural($modelName)),          // 'posts'
            '{{ routePrefix }}' => Str::snake(Str::plural($modelName)),          // 'posts'
            '{{ viewFolder }}' => Str::snake(Str::plural($modelName)),         // 'posts'
            '{{ title }}' => Str::headline(Str::plural($modelName)),           // 'Posts'
            '{{ title2 }}' => Str::headline(Str::singular($modelName)),           // 'Engineering Bom'
            '{{ controllerClass }}' => Str::singular($modelName).'Controller',                // 'PostsController'
            '{{ softDeletes }}' => '',
            '{{ fillable }}' => $fillable,
            '{{ relations }}' => '',
            '{{ table_headers }}' => $table_headers,
            '{{ table_rows }}' => $table_rows,
            '{{ defaultSortColumn }}' => 'id' // TODO: use primary key if exists or first column

        ];
        $tableName = Str::snake($table); // snake_case for URLs
        $this->info("Generating CRUD for: $name");

        if ($this->option('overwrite')) {
            // --overwrite activated
            $this->info('overwrite mode activated for views');
            //
            // TODO: do stuff here
        }

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
            if (! $this->option('overwrite')) {
                if (! $this->confirm("File '{$modelPath}' exists. Overwrite? (Backup will be created if yes)", false)) {
                    // Selected no
                    $domodel = false;
                    $this->info("Skipping ".$modelPath);
                } else {
                    // Create a backup before overwriting
                    $this->backup_existing_file($modelPath);
                }
            } else {
                // --overwrite (without prompting) was selected
                // Create a backup before overwriting
                $this->backup_existing_file($modelPath);
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
        $target_controller = app_path('Http/Controllers/'.$model.'Controller.php');
        file_put_contents($target_controller, $code_controller);
        $this->info("Controller created: $target_controller");

        // 3. Append route to routes/web.php
        if (!$this->stringInFile(base_path('routes/web.php'), "Route::resource('$table'")) {
            $route = "Route::resource('$table', App\\Http\\Controllers\\{$model}Controller::class); // [Auto-Generated CRUD for $name]\n";
            file_put_contents(base_path('routes/web.php'), $route, FILE_APPEND);
            $this->info("Resource route created in routes/web.php for $table");
        }


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

        // All done
        $this->info("\nCRUD for scaffolding for $input complete!\n");
        $url = route(Str::snake(Str::plural($modelName)).'.index');  // Works if APP_URL is set and config:cache is too
        $this->info("You may open ${url} in your browser now!");
        return Command::SUCCESS;
    }
}

