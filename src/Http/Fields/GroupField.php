<?php
namespace Vis\Builder\Fields;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;

class GroupField extends AbstractField
{
    public function isEditable()
    {
        return true;
    } // end isEditable

    public function onSearchFilter(&$db, $value)
    {
        $db->where($this->getFieldName(), 'LIKE', '%'.$value.'%');
    } // end onSearchFilter

    public function getEditInput($row = array())
    {

        $type = $this->getAttribute('type');
        $valueJson = $this->getValue($row);
        $valueArray = [];
        if ($valueJson && $this->isJson($valueJson)) {
            $valueArray = json_decode($valueJson);
        }
        $filds = $this->getAttribute('filds');
        $section = [];
        if (count($valueArray)) {
            foreach ($valueArray as $nameVal => $val) {
                foreach ($val as $k => $res) {
                    $section[$k][$nameVal] = $res;
                }
            }
            
            foreach ($section as $k => $param) {
                foreach ($param as $nameParam => $valParam) {
                    if (isset($filds[$nameParam]['type'])) {
                        $nameClass = "Vis\\Builder\\Fields\\".ucfirst($filds[$nameParam]['type'])."Field";

                        $resultObjectFild =  new $nameClass($nameParam, $filds[$nameParam] , $this->options, $this->definition, $this->handler);
                        $sectionResult[$k][$nameParam] = $filds[$nameParam];

                        if (isset($filds[$nameParam]['tabs'])) {
                            $sectionResult[$k][$nameParam]['html'] = $resultObjectFild->getTabbedEditInput($param);
                        } else {
                            $sectionResult[$k][$nameParam]['html'] = $resultObjectFild->getEditInput(array($nameParam => $valParam));
                        }
                    }
                }
            }
        } else {
            foreach ($filds as $name => $fild) {
                $nameClass = "Vis\\Builder\\Fields\\".ucfirst($fild['type'])."Field";

                $resultObjectFild =  new $nameClass($name, $fild , $this->options, $this->definition, $this->handler);
                $sectionResult[0][$name] = $fild;
                if (isset($fild['tabs'])) {
                    $sectionResult[0][$name]['html'] = $resultObjectFild->getTabbedEditInput();
                } else {
                    $sectionResult[0][$name]['html'] = $resultObjectFild->getEditInput();
                }
            }
        }

        $input = View::make('admin::tb.input_'. $type);
        $input->value = $valueArray;
        $input->name  = $this->getFieldName();
        $input->rows  = $sectionResult;
        $input->hide_add = $this->getAttribute('hide_add');

        return $input->render();
    } // end getEditInput

    private function isJson($string)
    {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }
}
