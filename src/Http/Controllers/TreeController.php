<?php namespace Vis\Builder;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\App;

class TreeController extends Controller
{
    protected $node;

    public function init($node, $method)
    {
        if (!$node->active(App::getLocale()) && !Input::has('show')) {
            App::abort(404);
        }
        $this->node = $node;

        return $this->$method();
    }
}
