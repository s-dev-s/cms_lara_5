<?php

return array(
    'db' => array(
        'table' => 'roles',
        'order' => array(
            'id' => 'ASC',
        ),
        'pagination' => array(
            'per_page' => 20,
            'uri' => '/admin/handle/groups',
        ),
    ),

    'options' => array(
        'caption' => "Группы пользователей",
        'ident' => 'groups-container',
        'form_ident' => 'groups-form',
        'table_ident' => 'groups-table',
        'action_url' => '/admin/handle/groups',
        'not_found'  => 'NOT FOUND',
        'handler'    => 'Vis\Builder\Helpers\GroupsHandler',
        'model' => 'Group',
    ),

    'position' => array(
        'tabs' => array(
            'Общая'     => array(
                'id',
                'slug',
                'name',
            ),
            'Права доступа' => array(
                'permissions',
            ),
        )
    ),

    'fields' => array(
        'id' => array(
            'caption' => '#',
            'type' => 'readonly',
            'class' => 'col-id',
            'width' => '1%',
            'hide' => true,
            'is_sorting' => false,
        ),
        'slug' => array(
            'caption' => "Имя",
            'type' => 'text',
            'filter' => 'text',
            'is_sorting' => true,
        ),
        'name' => array(
            'caption' => "Название",
            'type' => 'text',
            'filter' => 'text',
            'is_sorting' => false,
        ),
        'permissions' => array(
            'caption' => "Доступы",
            'type' => 'text',
            'filter' => 'text',
            'is_sorting' => false,
            'hide_list' => true,
            'permissions' => array(
                'admin.access' => 'Доступ в админку',
                'новости' => array(
                    'admin.access_views' => 'Просмотр',
                    'admin.access_edit' => 'Редактирование',
                    'admin.access_delete' => 'Удаление',
                )
            )
        ),

    ),

    'actions' => array(
        'search' => array(
            'caption' => "Поиск",
        ),
        'insert' => array(
            'caption' => "Добавить",
        ),
        'update' => array(
            'caption' => 'Редактировать',
            'check' => function() {
                return true;
            }
        ),
        'delete' => array(
            'caption' => "Удалить",
        ),
    ),
);