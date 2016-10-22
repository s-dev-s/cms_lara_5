<?php

return array(

    'model' => 'Tree',
    'cache' => array(
        'tags' => array('tree'),
    ),
    'templates' => array(
        'contacts' => array(
            'action' => 'ContactsController@showPage',
            'node_definition' => 'contacts',
            'check' => function() {
                return true;
            },
            'title' => 'Контакты'
        ),
        'news' => array(
            'action' => 'NewsController@showPages',
            'node_definition' => 'node',
            'check' => function() {
                return true;
            },
            'title' => 'Новости'
        ),

        'about' => array(
            'action' => 'AboutController@showPage',
            'node_definition' => 'node',
            'check' => function() {
                return true;
            },
            'title' => "О нас"
        ),

        'article' => array(
            'action' => 'ArticleController@showPage',
            'node_definition' => 'node',
            'check' => function() {
                return true;
            },
            'title' => "Статья"
        ),

        'main' => array(
            'action' => 'HomeController@showPage',
            'node_definition' => 'node',
            'check' => function() {
                return true;
            },
            'title' => "Главная"
        ),

    ),

    'default' => array(
        'type' => 'node',
        'action' => 'HomeController@showPage',
        'node_definition' => 'node',
    ),

    'actions' => array(

        'update' => array(
            'caption' => 'Редактировать',
            'check' => function() {
                return true;
            }
        ),
        'preview' => array(
            'caption' => 'Предпросмотр',
            'check' => function() {
                return true;
            }
        ),
        'clone' => array(
            'caption' => 'Клонировать',
            'check' => function() {
                return true;
            }
        ),

        'revisions' => array(
            'caption' => 'Версии',
            'check' => function() {
                return true;
            }
        ),
        'delete' => array(
            'caption' => 'Удалить',
            'check' => function() {
                return true;
            }
        ),
    ),

);
