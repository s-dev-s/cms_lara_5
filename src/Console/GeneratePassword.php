<?php namespace Vis\Builder;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Cartalyst\Sentinel\Laravel\Facades\Sentinel;

class GeneratePassword extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'admin:generatePassword';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate password for admin';


    /**
     * Create a new command instance.
     *
     * @return void
     */

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        $this->replacePassword();
    }

    /*
 * replace password for admin
 */
    public function replacePassword()
    {
        $leters = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','P','Q','R','S','T','U','V','W','X','Y','Z','1','2','3','4','5','6','7','8','9','0');
        for ($i_zn = 0; $i_zn < 10; $i_zn++) {
            $arrLett[] = $leters[rand(0, count($leters)-1)];
        }
        $newPass = implode("", $arrLett);

        $userAdmin = Sentinel::findById(1);
        Sentinel::update($userAdmin, array('password' => $newPass));

        $this->info('Access in cms: ');
        $this->info('Login: admin@vis-design.com');
        $this->info('Password: '.$newPass);
    }
}
