<?php

View::composer('admin::partials.navigation', function ($view) {
    $user = Sentinel::getUser();
    $menu = config('builder.admin.menu');

    $view->with('user', $user)->with("menu", $menu);
});

View::composer(array('admin::layouts.default', 'admin::partials.scripts'), function ($view) {

    $skin = Cookie::get('skin') ? : "smart-style-4";
    $thisLang = Cookie::get("lang_admin") ? : config("builder.translate_cms.lang_default");
    $customJs = config('builder.admin.custom_js');
   
    $view->with('skin', $skin)
        ->with("thisLang", $thisLang)
        ->with("customJs", $customJs);
});
