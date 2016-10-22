<?php namespace App\Http\Controllers;

use Vis\Builder\TreeController;
use Illuminate\Support\Facades\Request;

class HomeController extends TreeController
{

    /*
     * show index page site
     */
    public function showPage()
    {

        $page = $this->node;

        return view('pages.index', compact("page"));
    }
}
