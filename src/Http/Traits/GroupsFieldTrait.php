<?php namespace Vis\Builder\Helpers\Traits;

use App\Modules\Settings\Models\Setting;
use Illuminate\Support\Facades\Config;

trait GroupsFieldTrait
{
    public function getArrayGroup($nameField)
    {
        if (isset($this->$nameField)) {
            $valueArray = json_decode($this->$nameField);
            $section = [];
            if (is_object($valueArray)) {
                foreach ($valueArray as $nameVal => $val) {
                    foreach ($val as $k => $res) {
                        $section[$k][$nameVal] = $res;
                    }
                }

                return $section;
            } else {
                return "Поле неправильного  формата";
            }
        } else {
            return "Нет такого поля";
        }
    } // end getArrayGroup
}
