<?php namespace Vis\Builder\Helpers\Traits;

use Cartalyst\Sentry\Facades\Laravel\Sentry;
use Illuminate\Support\Facades\View;

trait QuickEditTrait
{
    public function editor($field)
    {
        $admin = Sentry::findGroupByName('admin');

        if (Sentry::check() && Sentry::getUser()->inGroup($admin)) {
            $pageEditor = $this;
            $fieldEdit = "editor_init_".get_class($pageEditor)."_".$field."_".$pageEditor->id;

            return View::make('builder::partials.editor_init', compact("pageEditor", "field", "fieldEdit"));
        } else {
            return $this->$field;
        }
    }
}
