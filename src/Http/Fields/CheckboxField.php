<?php

namespace Vis\Builder\Fields;

use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Session;

class CheckboxField extends AbstractField
{

    public function isEditable()
    {
        return true;
    } // end isEditable

    public function prepareQueryValue($value)
    {
        if (!$value) {
            if ($this->getAttribute('is_null')) {
                return null;
            }
        }

        return $value ? '1' : '0';
    } // end prepareQueryValue
    
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

        $table = View::make('admin::tb.filter_checkbox');
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

        $table = View::make('admin::tb.input_checkbox');
        $table->value = $this->getValue($row);
        $table->name  = $this->getFieldName();
        $table->caption = $this->getAttribute('caption');

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
        
        return View::make('admin::tb.input_checkbox_list')->with('is_checked', $this->getValue($row));
    } // end getListValue
    
        
    public function getValue($row, $postfix = '')
    {
        if ($this->hasCustomHandlerMethod('onGetValue')) {
            $res = $this->handler->onGetValue($this, $row, $postfix);

            if ($res != false) {
                return $res;
            }
        }

        $value = (
            (isset($row[$this->getFieldName()]) && $row[$this->getFieldName()]) ||
            (!isset($row[$this->getFieldName()]) && $this->getAttribute('not_checked_default') != true)

                )
                    ? '1' : '0';

        return $value;
    } // end getValue
}
