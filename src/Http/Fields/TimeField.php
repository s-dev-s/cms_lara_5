<?php

namespace Vis\Builder\Fields;

use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Session;

class TimeField extends AbstractField
{

    public function onSearchFilter(&$db, $value)
    {
        $table = $this->definition['db']['table'];
        if ($this->getAttribute('is_range')) {
            if (!isset($value['from']) && !isset($value['to'])) {
                return;
            }

            $valueFrom = isset($value['from']) ? $value['from'] : '00:00';
            $valueTo = isset($value['to']) ? $value['from'] : '23:59';
            $db->whereBetween(
                $table .'.'. $this->getFieldName(),
                [$valueFrom, $valueTo]
            );
        } else {
            $db->where(
                $table .'.'. $this->getFieldName(),
                $value
            );
        }
    } // end onSearchFilter

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

        return $this->getValue($row);
    } // end getListValue


    public function getEditInput($row = array())
    {
        if ($this->hasCustomHandlerMethod('onGetEditInput')) {
            $res = $this->handler->onGetEditInput($this, $row);
            if ($res) {
                return $res;
            }
        }

        $value = $this->getValue($row);
        $value = $value ? $value : '';

        $input = View::make('admin::tb.input_time');
        $input->value  = $value;
        $input->name   = $this->getFieldName();
        $input->prefix = $row ? 'e-' : 'c-';
        $input->comment = $this->getAttribute('comment');

        return $input->render();
    } // end getEditInput

    public function getFilterInput()
    {
        if (!$this->getAttribute('filter')) {
            return '';
        }

        if ($this->getAttribute('is_range')) {
            return $this->getFilterRangeInput();
        }

        $definitionName = $this->getOption('def_name');
        $sessionPath = 'table_builder.'.$definitionName.'.filters.'.$this->getFieldName();
        $filter = Session::get($sessionPath, '');

        $input = View::make('admin::tb.filter_time');
        $input->name = $this->getFieldName();
        $input->value = $filter;

        return $input->render();
    } // end getFilterInput

    private function getFilterRangeInput()
    {
        $definitionName = $this->getOption('def_name');
        $sessionPath = 'table_builder.'.$definitionName.'.filters.'.$this->getFieldName();
        $filter = Session::get($sessionPath, array());

        $input = View::make('admin::tb.filter_time_range');
        $input->name = $this->getFieldName();
        $input->valueFrom = isset($filter['from']) ? $filter['from'] : false;
        $input->valueTo = isset($filter['to']) ? $filter['to'] : false;
        $input->months = $this->getAttribute('months');

        return $input->render();
    } // end getFilterRangeInput
}
