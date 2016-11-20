<?php

namespace Vis\Builder\Fields;

use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\DB;

class ForeignField extends AbstractField
{
    private $treeMy;
    private $treeOptions;
    private $recursiveOnlyLastLevel = false;
    private $selectOption;

    public function isEditable()
    {
        return true;
    } // end isEditable


    public function getExportValue($type, $row, $postfix = '')
    {
        $value = $this->getValue($row, $postfix);
        // FIXME:
        if ($value == '<i class="fa fa-minus"></i>') {
            $value = '';
        }

        // cuz double quotes is escaping by more double quotes in csv
        $escapedValue = preg_replace('~"~', '""', $value);

        return $escapedValue;
    } // end getExportValue

    public function getFilterInput()
    {
        if (!$this->getAttribute('filter')) {
            return '';
        }

        $definitionName = $this->getOption('def_name');
        $sessionPath = 'table_builder.'.$definitionName.'.filters.'.$this->getFieldName();
        $filter = Session::get($sessionPath, '');

        $type = $this->getAttribute('filter');

        $input = View::make('admin::tb.filter_'. $type);
        $input->name = $this->getFieldName();
        $input->selected = $filter;
        $input->recursive = $this->getAttribute('recursive');

        $input->value = $filter;
        if ($input->recursive) {
            $this->treeMy = $this->getCategory($this->getAttribute('recursiveIdCatalog'));
            $this->recursiveOnlyLastLevel = $this->getAttribute('recursiveOnlyLastLevel');
            $this->selectOption = $input->selected;
            $this->printCategories($this->getAttribute('recursiveIdCatalog'), 0);

            $input->options = $this->treeOptions;
        } else {
            $input->options  = $this->getForeignKeyOptions();
        }


        // $input->options = $this->getForeignKeyOptions();

        return $input->render();
    } // end getFilterInput

    public function onSearchFilter(&$db, $value)
    {
        $foreignTable = $this->getAttribute('foreign_table');
        if ($this->getAttribute('alias')) {
            $foreignTable = $this->getAttribute('alias');
        }
        $foreignValueField = $foreignTable .'.'. $this->getAttribute('foreign_value_field');

        // FIXME:
        if ($this->getAttribute('filter') == 'foreign') {
            $foreignValueField = $foreignTable .'.'. $this->getAttribute('foreign_key_field');
            $db->where($foreignValueField, $value);
            return;
        }

        $db->where($foreignValueField, 'LIKE', '%'.$value.'%');
    } // end onSearchFilter

    public function onSelectValue(&$db)
    {
        if ($this->hasCustomHandlerMethod('onAddSelectField')) {
            $res = $this->handler->onAddSelectField($this, $db);
            if ($res) {
                return $res;
            }
        }

        $internalSelect = $this->definition['db']['table'] .'.'. $this->getFieldName();

        $db->addSelect($internalSelect);

        $foreignTable = $this->getAttribute('foreign_table');
        $foreignTableName = $foreignTable;
        if ($this->getAttribute('alias')) {
            $foreignTableName .= ' as '. $this->getAttribute('alias');
            $foreignTable = $this->getAttribute('alias');
        }
        $foreignKeyField = $foreignTable .'.'. $this->getAttribute('foreign_key_field');

        $join = $this->getAttribute('is_null') ? 'leftJoin' : 'join';
        $db->$join(
            $foreignTableName,
            $foreignKeyField, '=', $internalSelect
        );

        if ($this->getAttribute('is_select_all')) {
            $db->addSelect($foreignTable .'.*');
        } else {
            $fieldAlias = ' as '. $foreignTable.'_'.$this->getAttribute('foreign_value_field');
            $db->addSelect($foreignTable .'.'. $this->getAttribute('foreign_value_field') . $fieldAlias);
        }
    } // end onSelectValue

    public function getValueId($row)
    {
        $fieldName = $this->getFieldName();

        $value = isset($row[$fieldName]) ? $row[$fieldName] : '';

        return $value;
    }

    public function getNewValueId($input='')
    {
        $new_id = null;
        if (strlen($input)) {
            $foreignTable = $this->getAttribute('foreign_table');
            $foreignKey = $this->getAttribute('foreign_key_field');
            $foreignValueField = $this->getAttribute('foreign_value_field');
            $first = DB::table($foreignTable)->where($foreignValueField, '=', $input)->first();
            if (!$first) {
                $new_id = DB::table($foreignTable)->insertGetId([$foreignValueField => $input]);
            }
        }
        return $new_id;
    }

    public function getValue($row, $postfix = '')
    {
        if ($this->hasCustomHandlerMethod('onGetValue')) {
            $res = $this->handler->onGetValue($this, $row, $postfix);
            if ($res) {
                return $res;
            }
        }

        $foreignTableName = $this->getAttribute('foreign_table');
        if ($this->getAttribute('alias')) {
            $foreignTableName = $this->getAttribute('alias');
        }
        $fieldName = $foreignTableName .'_'. $this->getAttribute('foreign_value_field');

        $value = isset($row[$fieldName]) ? $row[$fieldName] : '';

        if (!$value && $this->getAttribute('is_null')) {
            // FIXME:
            $value = $this->getAttribute('null_caption', '<i class="fa fa-minus"></i>');
        }

        return $value;
    } // end getValue

    public function getEditInput($row = array())
    {
        if ($this->hasCustomHandlerMethod('onGetEditInput')) {
            $res = $this->handler->onGetEditInput($this, $row);
            if ($res) {
                return $res;
            }
        }

        if ($this->getAttribute('is_readonly')) {
            return $this->getValue($row);
        }

        $input = View::make('admin::tb.input_foreign');
        $input->selected = $this->getValueId($row);
        $input->name     = $this->getFieldName();
        $input->is_null  = $this->getAttribute('is_null');
        $input->null_caption = $this->getAttribute('null_caption');
        $input->recursive = $this->getAttribute('recursive');
        $input->allow_foreign_add = $this->getAttribute('foreign_allow_add');

        if ($input->recursive) {
            $this->treeMy = $this->getCategory($this->getAttribute('recursiveIdCatalog'));
            $this->recursiveOnlyLastLevel = $this->getAttribute('recursiveOnlyLastLevel');
            $this->selectOption = $input->selected;
            $this->printCategories($this->getAttribute('recursiveIdCatalog'), 0);

            $input->options = $this->treeOptions;
        } else {
            $input->options  = $this->getForeignKeyOptions();
        }
        $input->readonly_for_edit = $this->getAttribute('readonly_for_edit');

        return $input->render();
    } // end getEditInput

    private function getCategory($id)
    {
        $node = \Tree::find($id);
        $children = $node->descendants()->get(array("id", "title", "parent_id"))->toArray();
        $result = array();
        foreach ($children as $row) {
            $result[$row["parent_id"]][] = $row;
        }
        return $result;
    }

    private function printCategories($parent_id, $level)
    {
        //Делаем переменную $category_arr видимой в функции
        if (isset($this->treeMy[$parent_id])) { //Если категория с таким parent_id существует
            foreach ($this->treeMy[$parent_id] as $value) { //Обходим
                if (isset($this->treeMy[$value["id"]]) && $this->recursiveOnlyLastLevel) {
                    $disable = "disabled";
                } else {
                    $disable = "";
                }

                $selectOption = $this->selectOption == $value["id"] ? "selected" : "";
                $paddingLeft = "";
                for ($i=0; $i<$level; $i++) {
                    $paddingLeft .= "--";
                }

                $this->treeOptions[] = "<option $selectOption $disable value ='" . $value["id"] . "'>". $paddingLeft . $value["title"] . "</option>";
                $level = $level + 1;
                $this->printCategories($value["id"], $level);
                $level = $level - 1;
            }
        }
    }

    protected function getForeignKeyOptions()
    {
        $db = DB::table($this->getAttribute('foreign_table'))
            ->select($this->getAttribute('foreign_value_field'))
            ->addSelect($this->getAttribute('foreign_key_field'));


        $additionalWheres = $this->getAttribute('additional_where');
        if ($additionalWheres) {
            foreach ($additionalWheres as $key => $opt) {
                if (trim($opt['sign']) == "in") {
                    $db->whereIn($key, $opt['value']);
                } elseif (trim($opt['sign']) == "not in") {
                    $db->whereNotIn($key, $opt['value']);
                } else {
                    $db->where($key, $opt['sign'], $opt['value']);
                }
            }
        }

        $orderBy = $this->getAttribute('orderBy');
        if ($orderBy && is_array($orderBy)) {
            foreach ($orderBy as $order) {
                if (isset($order['field']) && isset($order['type'])) {
                    $db->orderBy($order['field'], $order['type']);
                }
            }
        }

        $res = $db->get();

        $options = array();
        $foreignKey = $this->getAttribute('foreign_key_field');
        $foreignValue = $this->getAttribute('foreign_value_field');
        foreach ($res as $val) {
            $options[$val[$foreignKey]] = $val[$foreignValue];
        }

        return $options;
    } // end getForeignKeyOptions
}
