<?php

Route::group(['middleware' => ['web']], function () {
    Route::group(
        ['prefix' => 'admin', 'middleware' => 'auth.admin'],
        function () {

            Route::any(
                '/settings/settings_all',
                array(
                'as' => 'm.show_settings',
                'uses' => 'Vis\Builder\SettingsController@fetchIndex'
                )
            );

            if (Request::ajax()) {
                Route::post(
                    '/settings/create_pop',
                    array(
                    'as' => 'm.created_settings',
                    'uses' => 'Vis\Builder\SettingsController@fetchCreate'
                    )
                );
                        Route::post(
                            '/settings/add_record',
                            array(
                            'as' => 'm.add_settings',
                            'uses' => 'Vis\Builder\SettingsController@doSave'
                            )
                        );
                        Route::post(
                            '/settings/delete',
                            array(
                            'as' => 'm.del_settings',
                            'uses' => 'Vis\Builder\SettingsController@doDeleteSetting'
                            )
                        );
                        Route::post(
                            '/settings/edit_record',
                            array(
                            'as' => 'm.edit_settings',
                            'uses' => 'Vis\Builder\SettingsController@fetchEdit'
                            )
                        );
                        Route::post(
                            '/settings/del_select',
                            array(
                            'as' => 'm.del_select_setting',
                            'uses' => 'Vis\Builder\SettingsController@doDeleteSettingSelect'
                            )
                        );
            }
        }
    );
});
