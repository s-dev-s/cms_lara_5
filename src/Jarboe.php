<?php namespace Vis\Builder;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Vis\Builder\Helpers\URLify;
use Yandex\Translate\Exception;
use Yandex\Translate\Translator;

class Jarboe
{
    protected $controller;
    protected $default;

    protected function onInit($options)
    {
        $this->controller = new JarboeController($options);
    } // end onInit

    protected function onFinish()
    {
        Config::set('view.pagination', $this->default['pagination']);
        Config::set('database.fetch', $this->default['fetch']);
    } // end onFinish

    public function table($options)
    {
        $this->onInit($options);
        $result = $this->controller->handle();

        return $result;
    } // end table

    public function checkNavigationPermissions()
    {
        $menu = new NavigationMenu();
        $menu->checkPermissions();
    } // end checkNavigationPermissions

    public function urlify($string)
    {
        return URLify::filter($string);
    } // end urlify

    public function tree(
        $model = 'Vis\Builder\Tree',
        $options = array(),
        $nameTree = "tree"
    ) {
        $controller = new TreeCatalogController($model, $options, $nameTree);

        return $controller;
    } // end tree
}
