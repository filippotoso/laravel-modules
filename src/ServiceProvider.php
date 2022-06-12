<?php

namespace FilippoToso\LaravelModules;

use DirectoryIterator;
use FilippoToso\LaravelModules\Facades\Module as ModuleFacade;
use FilippoToso\LaravelModules\Support\Module;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Illuminate\Support\Str;
use ReflectionClass;

abstract class ServiceProvider extends BaseServiceProvider
{
    /**
     * Root folder of the current module
     *
     * @var string|null
     */
    protected $directory;

    /**
     * Name of the configuration file in /config/modules
     *
     * @var string|null
     */
    protected $config;

    /**
     * Slug of the module
     *
     * @var string|null
     */
    protected $slug;

    /**
     * Name of the views
     *
     * @var string|null
     */
    protected $viewsName;

    /**
     * Name of the translations prefix
     *
     * @var string|null
     */
    protected $translationPrefix;

    /**
     * Array of commands to be registered
     *
     * @var array|null
     */
    protected $commands;

    /**
     * Current module namespace
     *
     * @var string
     */
    protected $namespace;

    /**
     * Config prefix/folder
     */
    protected const CONFIG_PREFIX = 'modules';

    /**
     * View name prefix/folder
     */
    protected const VIEW_PREFIX = 'modules-';

    /**
     * Create a new service provider instance.
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     * @return void
     */
    public function __construct($app)
    {
        parent::__construct($app);

        $reflection = new ReflectionClass($this);

        $this->namespace = $reflection->getNamespaceName();

        $name = Arr::last(explode('\\', $this->namespace));

        $this->slug = Str::slug($name);

        $this->config = $this->config ?? $this->slug;
        $this->viewsName = $this->viewsName ?? static::VIEW_PREFIX . $this->slug;
        $this->translationPrefix = $this->translationPrefix ?? $this->slug;
        $this->directory = $this->directory ?? dirname(dirname($reflection->getFileName()));
        $this->commands = $this->commands ?? $this->listCommands($this->directory, $this->namespace);
    }

    /**
     * Load routes form the /routes folder
     *
     * @return void
     */
    protected function loadRoutes()
    {
        $files = [
            $this->directory . '/resources/routes/api.php',
            $this->directory . '/resources/routes/console.php',
            $this->directory . '/resources/routes/web.php',
        ];

        foreach ($files as $file) {
            if (!is_readable($file)) {
                continue;
            }

            $this->loadRoutesFrom($file);
        }
    }

    /**
     * Load migrations from the /migrations
     *
     * @return void
     */
    protected function loadMigrations()
    {
        if (!is_dir($folder = $this->directory . '/resources/migrations')) {
            return;
        }

        $this->loadMigrationsFrom($folder);
    }

    /**
     * Load and publish translations
     *
     * @return void
     */
    protected function loadAndPublishTranslations()
    {
        if (!is_dir($folder = $this->directory . '/resources/lang')) {
            return;
        }

        $this->loadTranslationsFrom($folder, $this->translationPrefix);

        $this->publishes([
            $folder => $this->app->langPath('modules/' . $this->translationPrefix),
        ], $this->slug . '-translations');
    }

    /**
     * Load and publish views from the /views folder
     *
     * @return void
     */
    protected function loadAndPublishViews()
    {
        if (!is_dir($folder = $this->directory . '/resources/views')) {
            return;
        }

        $this->loadViewsFrom($folder, $this->viewsName);

        $this->publishes([
            $folder => resource_path('views/vendor/' . $this->viewsName),
        ], $this->slug . '-views');
    }

    /**
     * Load console commands
     *
     * @return void
     */
    protected function loadCommands()
    {
        if ($this->app->runningInConsole() && !empty($this->commands)) {
            $this->commands($this->commands);
        }
    }

    /**
     * list console commands
     *
     * @param string $directory
     * @param string $namespace
     * @return void
     */
    protected function listCommands($directory, $namespace)
    {
        $commands = [];

        $directory = $directory . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Commands';

        $directories = is_array($directory) ? $directory : [$directory];

        foreach ($directories as $directory) {

            if (!is_dir($directory)) {
                continue;
            }

            $classes = static::files($directory, function ($file) use ($directory, $namespace) {
                $pathname = $file->getPathname();
                $path = dirname($pathname) . DIRECTORY_SEPARATOR . basename($pathname, '.' . pathinfo($pathname, PATHINFO_EXTENSION));
                $class = $namespace . '\\Commands\\' . substr($path, strlen($directory) + 1);
                $class = str_replace('/', '\\', $class);
                return $class;
            });

            foreach ($classes as $class) {
                if (is_subclass_of($class, \Illuminate\Console\Command::class)) {
                    $commands[] = $class;
                }
            }
        }

        return $commands;
    }

    protected static function files($directory, $mapperCallback = null)
    {
        $results = [];

        foreach (new DirectoryIterator($directory) as $file) {
            if ($file->isDot()) {
                continue;
            }

            if ($file->isDir()) {
                $results = array_merge($results, static::files($file->getPathname(), $mapperCallback));
            } else {
                $results[] = is_callable($mapperCallback) ? $mapperCallback($file) : $file->getPathname();
            }
        }

        return $results;
    }

    /**
     * Publish configuration
     *
     * @return void
     */
    protected function publishConfiguration()
    {
        if (!file_exists($file = $this->directory . '/resources/config/' . $this->config . '.php')) {
            return;
        }

        $this->publishes([
            $file => config_path('/' . static::CONFIG_PREFIX . '/' . $this->config . '.php'),
        ], $this->slug . '-config');
    }

    /**
     * Bootstrap any package services.
     *
     * @return void
     */
    public function boot()
    {
        $this->extendsBlade();

        $this->publishConfiguration();

        if ($this->isModuleEnabled()) {
            $this->loadRoutes();
            $this->loadCommands();
            $this->loadMigrations();
            $this->loadAndPublishViews();
            $this->loadAndPublishTranslations();

            $this->bootModule();
        }
    }

    protected function extendsBlade()
    {
        Blade::directive('module', function ($expression) {
            return "<?php \FilippoToso\LaravelModules\Facades\Module::loopViews($expression, get_defined_vars(), function() use (\$__env) { echo \$__env->make(func_get_arg(0), \Illuminate\Support\Arr::except(func_get_arg(1), ['__data', '__path']))->render(); }); ?>";
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfiguration();

        $this->registerFacades();

        if ($this->isModuleEnabled()) {
            $this->registerModule();
        }
    }

    /**
     * Merge configuration
     *
     * @return void
     */
    protected function mergeConfiguration()
    {
        if (!is_readable($file = $this->directory . '/resources/config/' . $this->config . '.php')) {
            return;
        }

        $this->mergeConfigFrom($file, static::CONFIG_PREFIX . '.' . $this->config);
    }

    /**
     * Register module facades
     *
     * @return void
     */
    protected function registerFacades()
    {
        App::bind(ModuleFacade::class, function () {
            return new Module;
        });
    }

    /**
     * Checks if the module is enabled in the configuration
     *
     * @return boolean
     */
    protected function isModuleEnabled()
    {
        return config('modules.' . $this->config . '.enabled', false);
    }

    /**
     * Register module
     *
     * @return void
     */
    abstract protected function registerModule();

    /**
     * Boot module
     *
     * @return void
     */
    abstract protected function bootModule();
}
