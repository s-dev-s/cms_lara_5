<?php

Route::pattern('id', '[0-9]+');
Route::pattern('slug', '[a-z0-9-]+');

include 'view_composers.php';

Route::group(
    ['prefix' => LaravelLocalization::setLocale()],
    function () {

        Route::get('/articles/{slug}-{id}', [
            'as' => 'articles_article',
            'uses' => 'ArticlesController@showPage'
        ]);

        Route::get('/product/{slug}-{id}', [
            'as' => 'product',
            'uses' => 'ProductController@showPage'
        ]);
    }
);
