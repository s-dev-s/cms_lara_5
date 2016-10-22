<?php

namespace Vis\Builder\Fields;

class WysiwygField extends AbstractField
{

    public function isEditable()
    {
        return true;
    } // end isEditable

    public function getListValue($row)
    {
        if ($this->hasCustomHandlerMethod('onGetListValue')) {
            $res = $this->handler->onGetListValue($this, $row);
            if ($res) {
                return $res;
            }
        }
        
        return mb_substr(strip_tags($this->getValue($row)), 0, 300) . '...';
    } // end getListValue
    
    public function getEditInput($row = array())
    {
        if ($this->hasCustomHandlerMethod('onGetEditInput')) {
            $res = $this->handler->onGetEditInput($this, $row);
            if ($res) {
                return $res;
            }
        }

        //$wysiwyg = $this->getAttribute('wysiwyg', 'summernote');

        $input = \View::make('admin::tb.input_wysiwyg_redactor');
        $input->value = $this->getValue($row);
        $input->name  = $this->getFieldName();
        $input->toolbar = $this->getAttribute('toolbar');
        $input->comment = $this->getAttribute('comment');
        $input->inlineStyles = $this->getAttribute('inlineStyles');
        $input->options = $this->getAttribute('options');

        $action = $this->definition['options']['action_url'];
        if (isset($this->definition['options']['action_url_tree'])) {
            $action = $this->definition['options']['action_url_tree'];
        }
        $input->action = $action;

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
        
        //$wysiwyg = $this->getAttribute('wysiwyg', 'summernote');
        
        $input = \View::make('admin::tb.tab_input_wysiwyg_redactor');
        $input->value = $this->getValue($row);
        $input->name  = $this->getFieldName();
        $input->toolbar = $this->getAttribute('toolbar');
        $input->tabs = $this->getPreparedTabs($row);
        $input->caption = $this->getAttribute('caption');
        $input->inlineStyles = $this->getAttribute('inlineStyles');
        $input->options = $this->getAttribute('options');
        
        $action = $this->definition['options']['action_url'];
        if (isset($this->definition['options']['action_url_tree'])) {
            $action = $this->definition['options']['action_url_tree'];
        }
        $input->action = $action;
        // HACK: for tabs right behaviour in edit-create modals
        $input->pre = $row ? 'e' : 'c';
        
        
        return $input->render();
    } // end getTabbedEditInput

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
}
