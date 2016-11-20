<?php namespace Vis\Builder\Fields;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;

abstract class AbstractField
{
    protected $fieldName;
    protected $attributes;
    protected $options;
    protected $definition;
    protected $handler;

    public function __construct($fieldName, $attributes, $options, $definition, $handler)
    {
        //echo $fieldName."<br>";
        $this->attributes = $this->_prepareAttributes($attributes);
        $this->options    = $options;
        $this->definition = $definition;
        $this->fieldName  = $fieldName;
        
        $this->handler = &$handler;
    } // end __construct
    
    public function isPattern()
    {
        return false;
    } // end isPattern

    public function getFieldName()
    {
        return $this->fieldName;
    } // end getFieldName

    private function _prepareAttributes($attributes)
    {
        // TODO:
        $attributes['fast-edit'] = isset($attributes['fast-edit']) && $attributes['fast-edit'];
        $attributes['filter'] = isset($attributes['filter']) ? $attributes['filter'] : false;
        $attributes['hide'] = isset($attributes['hide']) ? $attributes['hide'] : false;
        $attributes['is_null'] = isset($attributes['is_null']) ? $attributes['is_null'] : false;

        return $attributes;
    } // end _prepareAttributes

    protected function getOption($ident)
    {
        return $this->options[$ident];
    } // end getOption

    public function getAttribute($ident, $default = false)
    {
        return isset($this->attributes[$ident]) ? $this->attributes[$ident] : $default;
    } // end getAttribute
    
    public function getRequiredAttribute($ident)
    {
        if (!array_key_exists($ident, $this->attributes)) {
            throw new \RuntimeException('Image storage field requires ['. $ident .'] attribute');
        }
        
        return $this->attributes[$ident];
    } // end getAttribute
    
    public function isHidden()
    {
        return $this->getAttribute('hide');
    } // end isHidden

    public function getValue($row, $postfix = '')
    {
        if ($this->hasCustomHandlerMethod('onGetValue')) {
            $res = $this->handler->onGetValue($this, $row, $postfix);
            if ($res) {
                return $res;
            }
        }
        
        $fieldName = $this->getFieldName() . $postfix;
        // postfix used for getting values for form - tabs loop
        // so there is no need to force appending postfix
        if ($this->getAttribute('tabs') && !$postfix) {
            $tabs = $this->getAttribute('tabs');
            $fieldName = $fieldName . $tabs[0]['postfix'];
        }

        $value = isset($row[$fieldName]) ? $row[$fieldName] : '';
        
        return $value;
    } // end getValue
    
    public function getExportValue($type, $row, $postfix = '')
    {
        if ($this->hasCustomHandlerMethod('onGetExportValue')) {
            $res = $this->handler->onGetExportValue($this, $type, $row, $postfix);
            if ($res) {
                return $res;
            }
        }
        
        $value = $this->getValue($row, $postfix);
        // cuz double quotes is escaping by more double quotes in csv
        $escapedValue = preg_replace('~"~', '""', $value);
        
        return $escapedValue;
    } // end getExportValue

    public function getListValue($row)
    {
        if ($this->hasCustomHandlerMethod('onGetListValue')) {
            $res = $this->handler->onGetListValue($this, $row);
            if ($res) {
                return $res;
            }
        }
        
        return $this->getValue($row);
    } // end getListValue

    public function getReplaceStr($row)
    {
        if ($this->getAttribute('result_show')) {
            $arrParam = explode("%", $this->getAttribute('result_show'));

            foreach ($arrParam as $k => $val) {
                if (isset($row[$val])) {
                    $arrParam[$k] = $row[$val];
                }
            }

            return implode("", $arrParam);
        }
    } // end getListValue

    public function getEditInput($row = array())
    {
        if ($this->hasCustomHandlerMethod('onGetEditInput')) {
            $res = $this->handler->onGetEditInput($this, $row);
            if ($res) {
                return $res;
            }
        }

        $type = $this->getAttribute('type');

        $input = View::make('admin::tb.input_'. $type);
        $input->value = $this->getValue($row);
        $input->name  = $this->getFieldName();
        $input->rows  = $this->getAttribute('rows');
        $input->mask  = $this->getAttribute('mask');
        $input->placeholder = $this->getAttribute('placeholder');
        $input->comment = $this->getAttribute('comment');

        return $input->render();
    } // end getEditInput
    
    public function getTabbedEditInput($row = array())
    {
        if ($this->hasCustomHandlerMethod('onGetTabbedEditInput')) {
            $res = $this->handler->onGetTabbedEditInput($this, $row);
            if ($res) {
                return $res;
            }
        }
        
        $type = $this->getAttribute('type');

        $input = View::make('admin::tb.tab_input_'. $type);
        $input->value = $this->getValue($row);
        $input->name  = $this->getFieldName();
        $input->rows  = $this->getAttribute('rows');
        $input->mask  = $this->getAttribute('mask');
        $input->custom_type  = $this->getAttribute('custom_type');
        $input->placeholder = $this->getAttribute('placeholder');
        $input->caption = $this->getAttribute('caption');
        $input->tabs = $this->getPreparedTabs($row);
        // HACK: for tabs right behaviour in edit-create modals
        $input->pre = $row ? 'e' : 'c';
        $input->comment = $this->getAttribute('comment');
        
        return $input->render();
    } // end getTabbedEditInput
    
    protected function getPreparedTabs($row)
    {
        $tabs = $this->getAttribute('tabs');
        $required = array(
            'placeholder',
            'postfix'
        );
        foreach ($tabs as &$tab) {
            foreach ($required as $option) {
                if (!isset($tab[$option])) {
                    $tab[$option] = '';
                }
            }
            
            $tab['value'] = $this->getValue($row, $tab['postfix']);
        }
        
        return $tabs;
    } // end getPreparedTabs

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
        $input->value = $filter;

        return $input->render();
    } // end getFilterInput

    protected function hasCustomHandlerMethod($methodName)
    {
        return $this->handler && is_callable(array($this->handler, $methodName));
    } // end hasCustomHandlerMethod

    public function prepareQueryValue($value)
    {
        if (!$value) {
            if ($this->getAttribute('is_null')) {
                return null;
            }
        }

        return $value;
    } // end prepareQueryValue

    public function onSelectValue(&$db)
    {
        if ($this->hasCustomHandlerMethod('onAddSelectField')) {
            $res = $this->handler->onAddSelectField($this, $db);
            if ($res) {
                return $res;
            }
        }
        
        $tabs = $this->getAttribute('tabs');

        $table_name = $this->definition['db']['table'];
        $field_name = $this->getFieldName();

        if ($tabs) {
            foreach ($tabs as $tab) {
                $name = $table_name .'.'. $this->getFieldName() . $tab['postfix'];

                $this->doCreateField($table_name, $this->getFieldName() . $tab['postfix']);
                $db->addSelect($name);
            }
        } else {
            //echo $field_name."<br>";
            $this->doCreateField($table_name, $field_name);
            $db->addSelect($table_name .'.'. $field_name);
        }
    } // end onSelectValue

    //autocreate fields in db
    protected function doCreateField($table_name, $field_name)
    {
        $field_bd = $this->getAttribute('field');
        $data = Session::all();

        if (!Session::has($table_name.'.'.$field_name)) {
            if ($field_bd && !Schema::hasColumn($table_name, $field_name)) {
                Session::push($table_name . '.' . $field_name, 'created');

                @list($field, $param) = explode("|", $field_bd);

                Schema::table(
                    $table_name,
                    function ($table) use ($field_name, $field, $param) {
                        $field_add = $table->$field($field_name);
                        if ($param) {
                            $field_add->length($param);
                        }
                    }
                );
            } else {
                Session::push($table_name . '.' . $field_name, 'created');
            }
        }
    }
    
    public function isReadonly()
    {
        return false;
    } // end isReadonly
    
    public function getClientsideValidatorRules()
    {
        $validation = $this->getAttribute('validation');
        if (!isset($validation['client'])) {
            return;
        }
        $validation = $validation['client'];
        
        $rules = isset($validation['rules']) ? $validation['rules'] : array();
        $name  = $this->getFieldName();
        $tabs  = $this->getAttribute('tabs');
        
        $data = compact('rules', 'name', 'tabs');

        return View::make('admin::tb.validator_rules', $data)->render();
    } // end getClientsideValidatorRules
    
    public function getClientsideValidatorMessages()
    {
        $validation = $this->getAttribute('validation');
        if (!isset($validation['client'])) {
            return;
        }
        $validation = $validation['client'];
        
        $messages = isset($validation['messages']) ? $validation['messages'] : array();
        $name     = $this->getFieldName();
        $tabs     = $this->getAttribute('tabs');
        
        $data = compact('messages', 'name', 'tabs');

        return View::make('admin::tb.validator_messages', $data)->render();
    } // end getClientsideValidatorMessages
    
    public function doValidate($value)
    {
        $validation = $this->getAttribute('validation');
        if (!isset($validation['server'])) {
            return;
        }
        
        $rules = $validation['server']['rules'];
        $messages = isset($validation['server']['messages']) ? $validation['server']['messages'] : array();
        $name = $this->getFieldName();

        if (isset($validation['server']['ignore_this_id']) && $validation['server']['ignore_this_id']) {
            $rules .= ",".Input::get("id");
        }
       
        $validator = Validator::make(
            array(
                $name => $value,
            ),
            array(
                $name => $rules,
            ),
            $messages
        );
        
        if ($validator->fails()) {
            $errors = implode('|', $validator->messages()->all());
            throw new \Exception($errors);
        }
    } // end doValidate
    
    public function getSubActions()
    {
        return '';
    } // end getSubActions
    
    public function getLabelClass()
    {
        return 'input';
    } // end getLabelClass

    public function isEditable()
    {
        return true;
    } // end isEditable

    public function getRowColor($row)
    {
        return '';
    } // end getRowColor

    abstract public function onSearchFilter(&$db, $value);
}
