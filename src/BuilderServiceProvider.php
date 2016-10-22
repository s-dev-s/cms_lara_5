<?php namespace Vis\Builder;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\Finder\Finder;

class BuilderServiceProvider extends ServiceProvider
{

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $router = $this->app['router'];
        $router->middleware('auth.admin', \Vis\Builder\Authenticate::class);
        $router->middleware('auth.user', \Vis\Builder\AuthenticateFrontend::class);

        require __DIR__ . '/../vendor/autoload.php';
        require __DIR__ . '/Http/helpers.php';
        require __DIR__ . '/Http/view_composers.php';


        $this->setupRoutes($this->app->router);

        $this->loadViewsFrom(realpath(__DIR__ . '/resources/views'), 'admin');

        $this->publishes([
            __DIR__
            . '/published/assets' => public_path('packages/vis/builder'),
            __DIR__ . '/config' => config_path('builder/')
        ], 'builder');

        $this->publishes([
            __DIR__
            . '/published/assets' => public_path('packages/vis/builder')
        ], 'public');
    }

    /**
     * Define the routes for the application.
     *
     * @param  \Illuminate\Routing\Router $router
     *
     * @return void
     */
    public function setupRoutes(Router $router)
    {
        if (!$this->app->routesAreCached()) {
            require __DIR__ . '/Http/route_frontend.php';
            require __DIR__ . '/Http/route_translation.php';
            require __DIR__ . '/Http/route_settings.php';
            require __DIR__ . '/Http/routers.php';
        }
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app['jarboe'] = $this->app->share(function ($app) {
            return new Jarboe();
        });

        $this->registerCommands();
    }

    private function registerCommands()
    {
        $this->app->singleton('command.admin.install', function ($app) {
            return new InstallCommand();
        });

        $this->app->singleton('command.admin.generatePassword', function ($app) {
            return new GeneratePassword();
        });


        $this->commands('command.admin.install');
        $this->commands('command.admin.generatePassword');
    }

    public function provides()
    {
        return [
            'command.admin.install',
            'command.admin.generatePassword'
        ];
    }
}
