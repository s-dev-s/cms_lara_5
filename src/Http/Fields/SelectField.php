<?php

namespace Vis\Builder\Fields;

use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Session;

class SelectField extends AbstractField
{

    public function isEditable()
    {
        return true;
    } // end isEditable

    public function onSearchFilter(&$db, $value)
    {
        $table = $this->definition['db']['table'];
        $db->where($table .'.'. $this->getFieldName(), '=', $value);
    } // end onSearchFilter

    public function getFilterInput()
    {
        if (!$this->getAttribute('filter')) {
            return '';
        }

        $definitionName = $this->getOption('def_name');
        $sessionPath = 'table_builder.'.$definitionName.'.filters.'.$this->getFieldName();
        $filter = Session::get($sessionPath, '');

        $table = View::make('admin::tb.filter_select');
        $table->filter = $filter;
        $table->name  = $this->getFieldName();
        $table->options = $this->getAttribute('options');

        return $table->render();
    } // end getFilterInput

    public function getEditInput($row = array())
    {
        if ($this->hasCustomHandlerMethod('onGetEditInput')) {
            $res = $this->handler->onGetEditInput($this, $row);
            if ($res) {
                return $res;
            }
        }

        $table = View::make('admin::tb.input_select');
        $table->selected = $this->getValue($row);
        $table->name  = $this->getFieldName();
        $options = $this->getAttribute('options');
        if (is_callable($options)) {
            $table->options = $options();
        } else {
            $table->options = $this->getAttribute('options');
        }

        $table->action = $this->getAttribute('action');
        $table->readonly_for_edit = $this->getAttribute('readonly_for_edit');

        return $table->render();
    } // end getEditInput

    public function getListValue($row)
    {
        if ($this->hasCustomHandlerMethod('onGetListValue')) {
            $res = $this->handler->onGetListValue($this, $row);
            if ($res) {
                return $res;
            }
        }

        $val = $this->getValue($row);
        $optionsRes = $this->getAttribute('options');

        if (is_callable($optionsRes)) {
            $options = $optionsRes();
        } else {
            $options = $optionsRes;
        }


        if (isset($options[$val])) {
            return $options[$val];
        } else {
            return $val;
        }
    } // end getListValue

    public function getRowColor($row)
    {
        $colors = $this->getAttribute('colors');
        if ($colors) {
            return isset($colors[$this->getValue($row)]) ? $colors[$this->getValue($row)] : '';
        }
    }
}
