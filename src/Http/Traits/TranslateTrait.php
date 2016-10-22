<?php namespace Vis\Builder\Helpers\Traits;

use Illuminate\Support\Facades\App;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

trait TranslateTrait
{
    public function t($ident)
    {
        $ident = $this->t_fild($ident);

        return $this->$ident;
    }

    public function t_fild($ident)
    {
        $lang = LaravelLocalization::setLocale();
        if ($lang) {
            $ident = $ident."_".$lang;
        }

        return $ident;
    }
}
