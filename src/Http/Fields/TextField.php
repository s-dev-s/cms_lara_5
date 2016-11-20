<?php namespace Vis\Builder\Fields;

use Illuminate\Support\Facades\View;

class TextField extends AbstractField
{

    public function isEditable()
    {
        return true;
    } // end isEditable

    public function onSearchFilter(&$db, $value)
    {
        $table = $this->definition['db']['table'];
        $tabs = $this->getAttribute('tabs');
        if ($tabs) {
            $field = $table .'.'. $this->getFieldName();
            $db->where(function ($query) use ($field, $value, $tabs) {
                foreach ($tabs as $tab) {
                    $query->orWhere($field . $tab['postfix'], 'LIKE', '%'.$value.'%');
                }
            });
        } else {
            $db->where($table .'.'. $this->getFieldName(), 'LIKE', '%'.$value.'%');
        }
    } // end onSearchFilter
    
    public function getSubActions()
    {
        $def = $this->getAttribute('subactions');
        if (!$def) {
            return '';
        }
        
        $subactions = array();
        foreach ($def as $options) {
            $class = '\\Yaro\\Jarboe\\Fields\\Subactions\\'. ucfirst($options['type']);
            $subactions[] = new $class($options);
        }
        
        return View::make('admin::tb.subactions', compact('subactions'))->render();
    } // end getSubActions

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
        $input->custom_type  = $this->getAttribute('custom_type');
        $input->multi = $this->getAttribute('multi');

        if ($input->multi) {
            $input->name = $input->name . "[]";
            $input->value = json_decode($input->value);
        }

        $input->placeholder = $this->getAttribute('placeholder');
        $input->is_password = $this->getAttribute('is_password');
        $input->comment = $this->getAttribute('comment');
        $input->only_numeric = $this->getAttribute('only_numeric');
        $input->readonly_for_edit = $this->getAttribute('readonly_for_edit');

        return $input->render();
    } // end getEditInput

    public function getListValue($row)
    {
        if ($this->getAttribute('fast_edit')) {
            $html = "<p>".parent::getListValue($row)."</p>";
            return $html;
        }

        if ($this->getAttribute('multi')) {

           $arrayValue = json_decode($this->getValue($row));

           if (is_array($arrayValue)) {
              
               return implode("<br>", $arrayValue);
           }

        }

        return $this->getValue($row);;
    } // end getListValue
}
