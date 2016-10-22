<?php namespace Vis\Builder\Handlers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Cache;

class ActionsHandler
{
    
    protected $def;
    protected $controller;

    public function __construct(array $actionsDefinition, &$controller)
    {
        $this->def = $actionsDefinition;
        $this->controller = $controller;
    } // end __construct
    
    public function fetch($type, $row = array(), $buttonDefinition = array())
    {
        switch ($type) {
            case 'insert':
                return $this->onInsertButton();
                
            case 'update':
                return $this->onUpdateButton($row);

            case 'clone':
                return $this->onСloneButton($row);

            case 'revisions':
                return $this->onRevisionsButton($row);

            case 'preview':
                return $this->onPreviewButton($row);

            case 'delete':
                return $this->onDeleteButton($row);

            case 'views_statistic':
                return $this->onViewStatisticButton($row);
                
            case 'custom':
                return $this->onCustomButton($row, $buttonDefinition);
            
            default:
                throw new \RuntimeException('Not implemented row action');
        }
    } // end fetch
    
    private function onCustomButton($row, $button)
    {
        if (!$this->isAllowed('custom', $button)) {
            return '';
        }
        
        $action = View::make('admin::tb.action_custom');
        $action->row = $row;
        $action->def = $button;
        $action->definition = $this->controller->getDefinition();
        
        return $action;
    } // end onCustomButton
    
    private function onInsertButton()
    {
        if (!$this->isAllowed('insert')) {
            return '';
        }

        if ($this->controller->hasCustomHandlerMethod('onInsertButtonFetch')) {
            $res = $this->controller->getCustomHandler()->onInsertButtonFetch($this->def['insert']);
            if ($res) {
                return $res;
            }
        }
        
        $action = View::make('admin::tb.action_insert');
        $action->def = $this->def['insert'];
        $action->definition = $this->controller->getDefinition();
        
        return $action;
    } // end onInsertButton
    
    private function onUpdateButton($row)
    {
        if (!$this->isAllowed('update')) {
            return '';
        }
        
        if ($this->controller->hasCustomHandlerMethod('onUpdateButtonFetch')) {
            $res = $this->controller->getCustomHandler()->onUpdateButtonFetch($this->def['update']);
            if ($res) {
                return $res;
            }
        }
        
        $action = View::make('admin::tb.action_update');
        $action->row = $row;
        $action->def = $this->def['update'];
        $action->definition = $this->controller->getDefinition();
        
        return $action;
    } // end onUpdateButton

    private function onRevisionsButton($row)
    {
        if (!$this->isAllowed('revisions')) {
            return '';
        }

        $action = View::make('admin::tb.action_revisions');
        $action->row = $row;
        $action->def = $this->def['revisions'];
        $action->definition = $this->controller->getDefinition();

        return $action;
    }

    private function onСloneButton($row)
    {
        if (!$this->isAllowed('clone')) {
            return '';
        }

        if ($this->controller->hasCustomHandlerMethod('onCloneButtonFetch')) {
            $res = $this->controller->getCustomHandler()->onUpdateButtonFetch($this->def['clone']);
            if ($res) {
                return $res;
            }
        }

        $action = View::make('admin::tb.action_clone');
        $action->row = $row;
        $action->def = $this->def['clone'];
        $action->definition = $this->controller->getDefinition();

        return $action;
    }

    private function onViewStatisticButton($row)
    {
        if (!$this->isAllowed('views_statistic')) {
            return '';
        }

        $action = View::make('admin::tb.action_views_statistic');
        $action->row = $row;
        $action->def = $this->def['views_statistic'];
        $action->definition = $this->controller->getDefinition();

        return $action;
    }

    private function onPreviewButton($row)
    {
        if (!$this->isAllowed('preview') || !$this->controller->getDefinition()['options']['model']) {
            return '';
        }

        $action = View::make('admin::tb.action_preview');
        $action->row = $row;
        $action->def = $this->def['preview'];
        $action->definition = $this->controller->getDefinition();
        $model = $action->definition['options']['model'];
        $action->model = $model;
        $action->url = $model::find($row['id'])->getUrl();

        return $action;
    }

    
    private function onDeleteButton($row)
    {
        if (!$this->isAllowed('delete')) {
            return '';
        }
        
        if ($this->controller->hasCustomHandlerMethod('onDeleteButtonFetch')) {
            $res = $this->controller->getCustomHandler()->onDeleteButtonFetch($this->def['delete']);
            if ($res) {
                return $res;
            }
        }
        
        $action = View::make('admin::tb.action_delete');
        $action->row = $row;
        $action->def = $this->def['delete'];
        $action->definition = $this->controller->getDefinition();
        
        return $action;
    } // end onDeleteButton
    
    public function isAllowed($type, $buttonDefinition = array())
    {
        if (count($this->def) == 0) {
            return true;
        }

        $def = isset($this->def[$type]) ? $this->def[$type] : false;
        if ($buttonDefinition) {
            $def = $buttonDefinition;
        }
        
        if ($def) {
            if (array_key_exists('check', $def)) {
                return $def['check']();
            }
            
            return true;
        }
        
        return false;
    } // end isAllowed
}
