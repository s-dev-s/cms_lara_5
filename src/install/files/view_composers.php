<?php

View::composer(array('partials.header'), function ($view) {

    $menu = Cache::tags(array('tree'))->rememberForever('menuProduct', function () {
        $menu = Tree::isMenu()->get();

        return $menu;
    });

    $view->with('menu', $menu);
});

View::composer(array('partials.footer'), function ($view) {

    $menu = Cache::tags(array('tree'))->rememberForever('menuProduct', function () {
        $menu = Tree::isMenu()->get();

        return $menu;
    });
    
    $view->with('menu', $menu);
});


View::composer('partials.breadcrumbs', function ($view) {

    if (!isset($view->getData()['page'])) {
        return "Не передан параметр";
    }
    $page = $view->getData()['page'];

    //if node
    if (get_class($page) == "Tree" || get_class($page) == "NewsTree") {
        $breadcrumbs = new Breadcrumbs($page);
    } else {
        $node = $page->getNode();
        $breadcrumbs = new Breadcrumbs($node);
        $breadcrumbs->add($page->getUrl(), $page->title);
    }

    $view->with('breadcrumbs', $breadcrumbs);
});
