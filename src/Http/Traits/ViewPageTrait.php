<?php namespace Vis\Builder\Helpers\Traits;

use Vis\Builder\ViewPage;
use Illuminate\Support\Facades\DB;

trait ViewPageTrait
{
    public function setView()
    {
        ViewPage::create(array(
           "model" => get_class($this),
           "id_record" => $this->id
        ));
    }
}
