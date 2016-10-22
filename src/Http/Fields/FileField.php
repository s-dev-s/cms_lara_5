<?php

namespace Vis\Builder\Fields;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;

class FileField extends AbstractField
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
        if ($this->hasCustomHandlerMethod('onGetEditInput')) {
            $res = $this->handler->onGetEditInput($this, $row);
            if ($res) {
                return $res;
            }
        }

        $type = $this->getAttribute('type');

        $valueJson = $this->getValue($row);
        if ($valueJson && $this->isJson($valueJson)) {
            $filesArray = json_decode($valueJson);
        }

        $input = View::make('admin::tb.input_'. $type);
        $input->value = $this->getValue($row);
        $input->name  = $this->getFieldName();
        $input->rows  = $this->getAttribute('rows');

        $input->is_multiple  = $this->getAttribute('is_multiple');
        $input->mask  = $this->getAttribute('mask');
        $input->placeholder = $this->getAttribute('placeholder');
        $input->accept = $this->getAttribute('accept');
        $input->comment = $this->getAttribute('comment');

        if (isset($filesArray)) {
            $input->source  = $filesArray;
        }

        return $input->render();
    } // end getEditInput

    private function isJson($string)
    {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }

    public function getListValue($row)
    {
        if ($this->hasCustomHandlerMethod('onGetListValue')) {
            $res = $this->handler->onGetListValue($this, $row);
            if ($res) {
                return $res;
            }
        }
        
        if (!$this->getValue($row)) {
            return '';
        }
        
        // FIXME: move to template
        $src = URL::to($this->getValue($row));
        $html = '<a href="'. $src .'" target="_blank">'. $src .'</a>';
        
        return $html;
    } // end getListValue
}
