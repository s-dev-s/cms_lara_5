<?php namespace Vis\Builder;

use Illuminate\Database\Eloquent\Model as Eloquent;

class SettingSelect extends Eloquent
{

    protected $table = 'setting_select';

    //Некоторые правила валидиции
    public static $rules = array(
        'value' => 'required',
        'id_setting' => 'required|numeric'
    );

    public $timestamps = false;

    public static function doDelete($id)
    {
        if (is_numeric($id)) {
            SettingSelect::find($id)->delete();
            Setting::reCacheSettings();
        }
    } //end doDelete
}
