<?php

namespace Vis\Builder\Fields;

use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\DB;

class ManyToManyField extends AbstractField
{

    public function isEditable()
    {
        return true;
    } // end isEditable

    public function onSearchFilter(&$db, $value)
    {
        // TODO:
    } // end onSearchFilter

    public function onPrepareRowValues($values, $id)
    {
        // we get comma separated values in string if select2 ajax search
        $values = is_array($values) ? $values : explode(',', $values);

        $delete = DB::table($this->getAttribute('mtm_table'))
            ->where($this->getAttribute('mtm_key_field'), $id);

        if ($this->getAttribute('mtm_external_model')) {
            $delete = $delete->where($this->getAttribute('mtm_external_model'), $this->definition['options']['model']);
        }

        $delete->delete();

        $data = array();
        if ($this->getAttribute('show_type') == 'extra') {
            foreach ($values as $info) {
                $temp = array(
                    $this->getAttribute('mtm_key_field')          => $id,
                    $this->getAttribute('mtm_external_key_field') => $info['id'],
                );

                $extraFields = $this->getAttribute('extra_fields', array());
                foreach ($extraFields as $fieldName => $fieldInfo) {
                    $temp[$fieldName] = $info[$fieldName];
                }

                $data[] = $temp;
            }
        } else {
            $values = array_filter($values);
            // HACK: in checkbox we have id as key of element, in select - as value
            $isInValueElement = ($this->getAttribute('show_type', 'checkbox') == 'select2' || $this->getAttribute('show_type', 'checkbox') == 'select3' || $this->getAttribute('show_type', 'checkbox') == 'select_tree');
            foreach ($values as $key => $val) {
                $externalID = $isInValueElement ? $val : $key;
                $data[$key] = array(
                    $this->getAttribute('mtm_key_field')          => $id,
                    $this->getAttribute('mtm_external_key_field') => $externalID
                );

                if ($this->getAttribute('mtm_external_model')) {
                    $data[$key][$this->getAttribute('mtm_external_model')] = $this->definition['options']['model'];
                }
            }
        }

        if ($data) {
            DB::table($this->getAttribute('mtm_table'))->insert($data);
        }
    } // end onPrepareRowValues

    public function onSelectValue(&$db)
    {
        // HACK: we dont need this method to be called for many2many field
    } // end onSelectValue

    public function getValue($row, $postfix = '')
    {
        if ($this->hasCustomHandlerMethod('onGetValue')) {
            $res = $this->handler->onGetValue($this, $row, $postfix);
            if ($res) {
                return $res;
            }
        }

        $assocTable = $this->getAttribute('mtm_table');
        $extTable = $this->getAttribute('mtm_external_table');
        $assocKeyField = $this->getAttribute('mtm_key_field');
        $assocExtKeyField = $this->getAttribute('mtm_external_key_field');
        $extKeyField = $this->getAttribute('mtm_external_foreign_key_field');
        $extValueField = $this->getAttribute('mtm_external_value_field');
        $value = DB::table($assocTable)
            ->join($extTable, $assocTable . '.' . $assocExtKeyField, '=', $extTable . '.' . $extKeyField)
        ->where($assocTable . '.' . $assocKeyField, $row['id'])->value($extValueField);
        
        return $value;
    } // end getValue

    public function getListValue($row)
    {
        if ($this->hasCustomHandlerMethod('onGetListValue')) {
            $res = $this->handler->onGetListValue($this, $row);
            if ($res) {
                return $res;
            }
        }

        return implode(', ', $this->getRelatedExternalFieldOptions($row));
    } // end getListValue

    public function getEditInput($row = array())
    {
        if ($this->hasCustomHandlerMethod('onGetEditInput')) {
            $res = $this->handler->onGetEditInput($this, $row);
            if ($res) {
                return $res;
            }
        }

        $showType = $this->getAttribute('show_type', 'checkbox');
        // FIXME:
        if ($showType == 'extra') {
            return $this->getEditInputWithExtra($row);
        }
        if ($showType == 'select2' && $this->getAttribute('select2_search')) {
            return $this->getEditInputSelectWithAjaxSearch($row);
        }

        $input = View::make('admin::tb.input_many2many_'. $showType);
        $input->selected = array();
        if ($row) {
            $input->selected = $this->getRelatedExternalFieldOptions($row);
        }

        $input->link    = $this->getAttribute('with_link');
        $input->name    = $this->getFieldName();
        $input->divide  = $this->getAttribute('divide_columns', 2);

        if ($showType == "select_tree") {
            $input->options = $this->getFieldOptionForTree();
        } else {
            $input->options = $this->doDivideOnParts(
                $this->getAllExternalFieldOptions(),
                $this->getAttribute('divide_columns', 2)
            );
        }

        return $input->render();
    } // end getEditInput

    private function getEditInputSelectWithAjaxSearch($row)
    {
        $input = View::make('admin::tb.input_many2many_select2_search');

        $data = array();
        if ($row) {
            $selected = $this->getRelatedExternalFieldOptions($row);
            foreach ($selected as $id => $title) {
                $data[] = array(
                    'id'   => $id,
                    'name' => $title,
                );
            }
        }
        $input->selected = json_encode($data);

        $input->postfix  = $row ? '_e' : '_c';
        $input->link    = $this->getAttribute('with_link');
        $input->name    = $this->getFieldName();
        $input->search  = $this->getAttribute('select2_search');

        return $input->render();
    } // end getEditInputSelectWithAjaxSearch

    private function getEditInputWithExtra($row)
    {
        $input = View::make('admin::tb.input_many2many_extra');

        $input->selected = array();
        if ($row) {
            $input->selected = $this->getRelatedExternalFieldOptions($row, true);
        }

        $input->postfix = $row ? '_e' : '_c';
        $input->name    = $this->getFieldName();
        $input->options = $this->getAllExternalFieldOptions(true);
        $input->extra   = $this->getAttribute('extra_fields');

        return $input->render();
    } // end getEditInputWithExtra

    private function doDivideOnParts($array, $segmentCount)
    {
        $dataCount = count($array);
        if ($dataCount === 0) {
            // HACK: when there is no many2many options
            return array(array());
        }

        $segmentLimit = ceil($dataCount / $segmentCount);
        $outputArray  = array_chunk($array, $segmentLimit, true);

        return $outputArray;
    } // end doDivideOnParts

    protected function getRelatedExternalFieldOptions($row, $isGetAll = false)
    {
        $keyField = $this->getAttribute('mtm_table') .'.'. $this->getAttribute('mtm_external_key_field');
        $valueField = $this->getAttribute('mtm_external_table') .'.'. $this->getAttribute('mtm_external_value_field');
        $externalTable = $this->getAttribute('mtm_external_table');
        $externalForeignKey = $externalTable .'.'. $this->getAttribute('mtm_external_foreign_key_field');

        $options = DB::table($this->getAttribute('mtm_table'));
        $options->select($keyField, $valueField);
        if ($isGetAll) {
            $options->addSelect($this->getAttribute('mtm_table') .'.*');
        }

        $options->join($externalTable, $keyField, '=', $externalForeignKey);

        $additionalWheres = $this->getAttribute('additional_where');
        if ($additionalWheres) {
            foreach ($additionalWheres as $key => $opt) {
                $options->where($key, $opt['sign'], $opt['value']);
            }
        }

        $options->where($this->getAttribute('mtm_key_field'), $row['id']);

        if ($this->getAttribute('mtm_external_model')) {
            $options->where($this->getAttribute('mtm_external_model'), $this->definition['options']['model']);
        }

        $externalOrder = $this->getAttribute('mtm_external_order');
        if ($externalOrder) {
            if (is_callable($externalOrder)) {
                $externalOrder($options);
            } else {
                foreach ($externalOrder as $key => $opt) {
                    $options->orderBy($key, $opt);
                }
            }
        }
        if ($this->getAttribute('show_type') == "select3") {
            $res = $options->orderBy($this->getAttribute('mtm_table').".id", "asc")->get();
        } else {
            $res = $options->get();
        }

        $options = array();
        foreach ($res as $opt) {
            $id = $opt[$this->getAttribute('mtm_external_key_field')];
            $value = $opt[$this->getAttribute('mtm_external_value_field')];

            if ($isGetAll) {
                $options[$id] = array(
                    'value' => $value,
                    'info'  => $opt
                );
            } else {
                $options[$id] = $value;
            }
        }

        return $options;
    } // end getRelatedExternalFieldOptions

    protected function getAllExternalFieldOptions($isGetAll = false)
    {
        $valueField = $this->getAttribute('mtm_external_table') .'.'. $this->getAttribute('mtm_external_value_field');
        $externalTable = $this->getAttribute('mtm_external_table');
        $externalForeignKey = $externalTable .'.'. $this->getAttribute('mtm_external_foreign_key_field');

        $options = DB::table($externalTable);
        if (!$isGetAll) {
            $options->select($externalForeignKey, $valueField);
        }

        $additionalWheres = $this->getAttribute('additional_where');
        if ($additionalWheres) {
            foreach ($additionalWheres as $key => $opt) {
                $options->where($key, $opt['sign'], $opt['value']);
            }
        }

        $res = $options->get();
        $options = array();
        foreach ($res as $opt) {
            $id = $opt[$this->getAttribute('mtm_external_foreign_key_field')];
            $value = $opt[$this->getAttribute('mtm_external_value_field')];

            if ($isGetAll) {
                $options[$id] = array(
                    'value' => $value,
                    'info'  => $opt
                );
            } else {
                $options[$id] = $value;
            }
        }

        return $options;
    } // end getAllExternalFieldOptions

    public function getFieldOptionForTree()
    {
        $params = $this->getAttribute('type_tree_params');
        $model = $params['model'];
        $options = $model::where("parent_id", $params['start_id_folder']);

        $additionalWheres = $this->getAttribute('additional_where');
        if ($additionalWheres) {
            foreach ($additionalWheres as $key => $opt) {
                $options->where($key, $opt['sign'], $opt['value']);
            }
        }
        $res = $options->orderBy("parent_id")->get();
    
        return $res;
    }

    public function getAjaxSearchResult($query, $limit, $page)
    {
        // TODO: create handler callback & lambda callback
        $results = DB::table($this->getAttribute('mtm_external_table'))
            ->select('id', $this->getAttribute('mtm_external_value_field'))
            ->where($this->getAttribute('mtm_external_value_field'), 'LIKE', '%'. $query .'%')
            ->take($limit)
            ->skip(($limit * $page) - $limit);
        // ->get();

        $additionalWheres = $this->getAttribute('additional_where');
        if ($additionalWheres) {
            foreach ($additionalWheres as $key => $opt) {
                $results->where($key, $opt['sign'], $opt['value']);
            }
        }

        $results = $results->get();


        $results = $results ? : array();

        $res = array();
        foreach ($results as $result) {
            $res[] = array(
                'id'   => $result['id'],
                'name' => $result[$this->getAttribute('mtm_external_value_field')],
            );
        }

        return array(
            'results' => $res,
            'more'    => $res && !empty($res),
        );
    } // end getAjaxSearchResult
}
