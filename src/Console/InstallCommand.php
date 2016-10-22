<?php namespace Vis\Builder;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class InstallCommand extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'admin:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command for install base version cms';

    private $installPath;

    /**
     * Create a new command instance.
     *
     * @return void
     */

    public function __construct()
    {
        $this->installPath = __DIR__ . '/../install';
        ;
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        if ($this->confirm('Start install? [y|n]')) {
            $this->sqlDumpLoad();
            $this->createFolderMinifyCssJs();
            $this->loadFiles();
            $this->publishConfigs();
            $this->loadFilesAfterPublishConfigs();
            $this->deleteFiles();
            $this->finishInstall();
        }
    }

    /*
     * load table sql
     */
    private function sqlDumpLoad()
    {
        $dumpFiles = scandir($this->installPath . '/dump_sql_table/');
        foreach ($dumpFiles as $file) {
            if (preg_match('/\.(sql)/', $file)) {
                DB::unprepared(file_get_contents($this->installPath . '/dump_sql_table/' . $file));
                $this->info("Table created " . $file);
            }
        }
    }

    /*
     * create folder public/js/builds and public/css/builds
     */
    private function createFolderMinifyCssJs()
    {
        if (!is_dir(public_path() . '/css/builds')) {
            File::makeDirectory(public_path() . '/css/builds', 0777, true);
            $this->info('Folder /css/builds is created');
        }
        if (!is_dir(public_path() . '/js/builds')) {
            File::makeDirectory(public_path() . '/js/builds', 0777, true);
            $this->info('Folder /js/builds is created');
        }
    }

    /*
     * load and replace basic files
     */
    private function loadFiles()
    {
        copy($this->installPath . '/files/.htaccess', public_path() . '/.htaccess');
        $this->info('Replace htaccess - OK');

        copy($this->installPath . '/files/robots.txt', public_path() . '/robots.txt');
        $this->info('Replace robots.txt - OK');

        copy($this->installPath . '/files/app.php', config_path() . '/app.php');
        $this->info('Replace app.php - OK');

        if (!is_dir(app_path() . '/Models')) {
            File::makeDirectory(app_path() . '/Models', 0777, true);
            $this->info('Folder app/Models is created');
        }

        copy($this->installPath . '/files/cache.php', config_path() . '/cache.php');
        $this->info('Replace cache.php - OK');

        copy($this->installPath . '/files/database.php', config_path() . '/database.php');
        $this->info('Replace database.php - OK');


        copy($this->installPath . '/files/BaseModel.php', app_path() . '/Models/BaseModel.php');
        $this->info('Created app/Models/BaseModel.php - OK');

        copy($this->installPath . '/files/Tree.php', app_path() . '/Models/Tree.php');
        $this->info('Created app/Models/Tree.php - OK');

        copy($this->installPath . '/files/Article.php', app_path() . '/Models/Article.php');
        $this->info('Created app/Models/Article.php - OK');

        copy($this->installPath . '/files/News.php', app_path() . '/Models/News.php');
        $this->info('Created app/Models/News.php - OK');

        copy($this->installPath . '/files/User.php', app_path() . '/Models/User.php');
        $this->info('Created app/Models/User.php - OK');

        copy($this->installPath . '/files/Group.php', app_path() . '/Models/Group.php');
        $this->info('Created app/Models/Group.php - OK');


        copy($this->installPath . '/files/routes.php', app_path() . '/Http/routes.php');
        $this->info('Created app/Http/routes.php - OK');

        copy($this->installPath . '/files/composer.json', base_path() . '/composer.json');
        $this->info('Replace composer.json - OK');

        copy($this->installPath . '/files/HomeController.php', app_path() . '/Http/Controllers/HomeController.php');
        $this->info('Created app/Http/Controllers/HomeController.php- OK');

        copy($this->installPath . '/files/Breadcrumbs.php', app_path() . '/Models/Breadcrumbs.php');
        $this->info('Created app/Models/Breadcrumbs.php- OK');

        copy($this->installPath . '/files/view_composers.php', app_path() . '/Http/view_composers.php');
        $this->info('Created app/Http/view_composers.php- OK');

        copy($this->installPath . '/files/Handler.php', app_path() . '/Exceptions/Handler.php');
        $this->info('Created app/Exceptions/Handler.php - OK');


        if (!is_dir(base_path() . '/resources/views/layouts')) {
            File::makeDirectory(base_path() . '/resources/views/layouts', 0777, true);
            $this->info('Folder resources/views/layouts is created');
        }
        if (!is_dir(base_path() . '/resources/views/pages')) {
            File::makeDirectory(base_path() . '/resources/views/pages', 0777, true);
            $this->info('Folder resources/views/pages is created');
        }
        if (!is_dir(base_path() . '/resources/views/partials')) {
            File::makeDirectory(base_path() . '/resources/views/partials', 0777, true);
            $this->info('Folder resources/views/partials is created');
        }
        if (!is_dir(base_path() . '/resources/views/popups')) {
            File::makeDirectory(base_path() . '/resources/views/popups', 0777, true);
            $this->info('Folder resources/views/popups is created');
        }

        copy($this->installPath . '/files/default.blade.php', base_path() . '/resources/views/layouts/default.blade.php');
        $this->info('Created default.blade.php- OK');

        copy($this->installPath . '/files/index.blade.php', base_path() . '/resources/views/pages/index.blade.php');
        $this->info('Created index.blade.php- OK');

        exec("composer dump-autoload");
    }

    /*
     * published all configs file
     */
    private function publishConfigs()
    {
        $this->call('vendor:publish');
    }

    public function loadFilesAfterPublishConfigs()
    {
        copy($this->installPath . '/files/laravellocalization.php', config_path() . '/laravellocalization.php');
        $this->info('Replace laravellocalization.php - OK');

        copy($this->installPath . '/files/cartalyst.sentinel.php', config_path() . '/cartalyst.sentinel.php');
        $this->info('Replace cartalyst.sentinel.php - OK');

        copy($this->installPath . '/files/debugbar.php', config_path() . '/debugbar.php');
        $this->info('Replace debugbar.php - OK');

        copy($this->installPath . '/files/minify.config.php', config_path() . '/minify.config.php');
        $this->info('Replace minify.config.php - OK');
    }

    private function deleteFiles()
    {
        @unlink(app_path()."/User.php");
        File::deleteDirectory(base_path()."/resources/lang");
        File::deleteDirectory(base_path()."/resources/views/errors");
        File::deleteDirectory(base_path()."/resources/views/vendor");
        File::deleteDirectory(app_path()."/Http/Controllers/Auth");
        @unlink(base_path()."/resources/views/welcome.blade.php");
        @unlink(app_path()."/Http/Controllers/Controller.php");
    }

    /*
     * call cache:clear and other commands
     */
    private function finishInstall()
    {
        exec("composer dump-autoload");
        $this->info('composer dump-autoload completed');

        $this->call('cache:clear');
        $this->call('clear-compiled');
        $this->call('optimize');
    }
}
