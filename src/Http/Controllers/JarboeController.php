<?php namespace Vis\Builder;

use Illuminate\Support\Facades\Session;
use Vis\Builder\Handlers\ViewHandler;
use Vis\Builder\Handlers\RequestHandler;
use Vis\Builder\Handlers\QueryHandler;
use Vis\Builder\Handlers\ActionsHandler;
use Vis\Builder\Handlers\ExportHandler;
use Vis\Builder\Handlers\ImportHandler;
use Vis\Builder\Handlers\ButtonsHandler;
use Vis\Builder\Handlers\CustomClosureHandler;

class JarboeController
{
    protected $currentID = false;

    protected $options;
    protected $definition;

    protected $handler;
    protected $callbacks;
    protected $fields;
    protected $groupFields;
    protected $patterns = array();

    public $view;
    public $request;
    public $query;
    public $actions;
    public $export;
    public $import;
    public $imageStorage;
    public $fileStorage;

    protected $allowedIds;

    public function __construct($options)
    {
        $this->options = $options; //$this->getPreparedOptions($options);
        $this->definition = $this->getTableDefinition($this->getOption('def_name'));
        $this->doPrepareDefinition();

        $this->handler = $this->createCustomHandlerInstance();
        if (isset($this->definition['callbacks'])) {
            $this->callbacks = new CustomClosureHandler($this->definition['callbacks'], $this);
        }
        $this->fields  = $this->loadFields();
        $this->groupFields  = $this->loadGroupFields();

        $this->actions = new ActionsHandler($this->definition['actions'], $this);

        $this->export  = new ExportHandler($this->definition['export'], $this);
        $this->import  = new ImportHandler($this->definition['import'], $this);
        if (isset($this->definition['buttons'])) {
            $this->buttons  = new ButtonsHandler($this->definition['buttons'], $this);
        }
        $this->query   = new QueryHandler($this);

        $this->allowedIds = $this->query->getTableAllowedIds();

        $this->view    = new ViewHandler($this);
        $this->request = new RequestHandler($this);

        $this->currentID = \Input::get('id');
    } // end __construct

    public function getCurrentID()
    {
        return $this->currentID;
    } // end getCurrentID

    private function doPrepareDefinition()
    {
        if (!isset($this->definition['export'])) {
            $this->definition['export'] = array();
        }
        if (!isset($this->definition['import'])) {
            $this->definition['import'] = array();
        }

        if (!isset($this->definition['actions'])) {
            $this->definition['actions'] = array();
        }

        if (!isset($this->definition['db']['pagination']['uri'])) {
            $this->definition['db']['pagination']['uri'] = $this->options['url'];
        }
    } // end doPrepareDefinition

    public function handle()
    {
        if ($this->hasCustomHandlerMethod('handle')) {
            $res = $this->getCustomHandler()->handle();
            if ($res) {
                return $res;
            }
        }

        return $this->request->handle();
    } // end handle

    public function isAllowedID($id)
    {
        return in_array($id, $this->allowedIds);
    } // end isAllowedID

    protected function getPreparedOptions($opt)
    {
        $options = $opt;
        $options['def_path'] = app_path(). $opt['def_path'];

        return $options;
    } // end getPreparedOptions

    protected function createCustomHandlerInstance()
    {
        if (isset($this->definition['options']['handler'])) {
            $handler = '\\'. $this->definition['options']['handler'];
            return new $handler($this);
        }

        return false;
    } // end createCustomHandlerInstance

    public function hasCustomHandlerMethod($methodName)
    {
        return $this->getCustomHandler() && is_callable(array($this->getCustomHandler(), $methodName));
    } // end hasCustomHandlerMethod

    public function isSetDefinitionCallback($methodName)
    {
        //
    } // end isSetDefinitionCallback

    public function getCustomHandler()
    {
        return $this->handler ? : $this->callbacks;
    } // end getCustomHandler

    public function getField($ident)
    {
        if (isset($this->fields[$ident])) {
            return $this->fields[$ident];
        } elseif (isset($this->patterns[$ident])) {
            return $this->patterns[$ident];
        } elseif (isset($this->groupFields[$ident])) {
            return $this->groupFields[$ident];
        }

        throw new \RuntimeException("Field [{$ident}] does not exist for current scheme.");
    } // end getField

    public function getFields()
    {
        return $this->fields;
    } // end getFields

    public function getOption($ident)
    {
        if (isset($this->options[$ident])) {
            return $this->options[$ident];
        }

        throw new \RuntimeException("Undefined option [{$ident}].");
    } // end getOption

    public function getAdditionalOptions()
    {
        if (isset($this->options['additional'])) {
            return $this->options['additional'];
        }

        return array();
    } // end getAdditionalOptions

    public function getDefinition()
    {
        return $this->definition;
    } // end getDefinition

    protected function loadFields()
    {
        $definition = $this->getDefinition();

        $fields = array();
        foreach ($definition['fields'] as $name => $info) {
            if ($this->isPatternField($name)) {
                $this->patterns[$name] = $this->createPatternInstance($name, $info);
            } else {
                $fields[$name] = $this->createFieldInstance($name, $info);
            }
        }

        return $fields;
    } // end loadFields

    protected function loadGroupFields()
    {
        $definition = $this->getDefinition();
        $fields = array();
        foreach ($definition['fields'] as $name => $info) {
            if ($info['type'] == "group" && count($info['filds'])) {
                foreach ($info['filds'] as $nameGroup => $infoGroup) {
                    $fields[$nameGroup] =  $this->createFieldInstance($nameGroup, $infoGroup);
                }
            }
        }

        return $fields;
    }

    public function getPatterns()
    {
        return $this->patterns;
    } // end getPatterns

    public function isPatternField($name)
    {
        return preg_match('~^pattern\.~', $name);
    } // end isPatternField

    protected function createPatternInstance($name, $info)
    {
        return new Fields\PatternField(
            $name,
            $info,
            $this->options,
            $this->getDefinition(),
            $this->getCustomHandler()
        );
    } // end createPatternInstance

    protected function createFieldInstance($name, $info)
    {
        $className = 'Vis\\Builder\\Fields\\'. ucfirst(camel_case($info['type'])) ."Field";

        return new $className(
            $name,
            $info,
            $this->options,
            $this->getDefinition(),
            $this->getCustomHandler()
        );
    } // end createFieldInstance

    protected function getTableDefinition($table)
    {

        $table = preg_replace('~\.~', '/', $table);
        $path = config_path() .'/builder/tb-definitions/'. $table .'.php';

        if (!file_exists($path)) {
            throw new \RuntimeException("Definition \n[{$path}]\n does not exist.");
        }

        $options = $this->getAdditionalOptions();
        $definition = require($path);
        if (!$definition) {
            throw new \RuntimeException("Empty definition?");
        }

        $definition['is_searchable'] = $this->_isSearchable($definition);
        $definition['options']['admin_uri'] = \Config::get('builder.admin.uri');

        return $definition;
    } // end getTableDefinition

    private function _isSearchable($definition)
    {
        $isSearchable = false;

        foreach ($definition['fields'] as $field) {
            if (isset($field['filter'])) {
                $isSearchable = true;
                break;
            }
        }

        return $isSearchable;
    } // end _isSearchable
}
