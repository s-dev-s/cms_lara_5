<?php namespace Vis\Builder\Handlers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Input;

class ButtonsHandler
{
    protected $def;
    protected $controller;

    public function __construct(array $exportDefinition, &$controller)
    {
        $this->def = $exportDefinition;
        $this->controller = $controller;
    } // end __construct

    public function fetch()
    {
        $def = $this->def;
        $buttonsHtml = "";
        if (count($def)) {
            foreach ($def as $button) {
                if (!$button || !$button['check']()) {
                    $buttonsHtml .= '';
                } else {
                    $buttonsHtml .= View::make('admin::tb.button', compact('button'));
                }
            }
        }

        return $buttonsHtml;
    }
}
