<?php namespace Vis\Builder\Handlers;

use Vis\Builder\JarboeController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Cache;

class QueryHandler
{

    protected $controller;

    protected $db;
    protected $dbOptions;

    public function __construct(JarboeController $controller)
    {
        $this->controller = $controller;

        $definition = $controller->getDefinition();

        if (isset($definition['cache']['tags'])) {
            $this->cache = $definition['cache']['tags'];
        } else {
            $this->cache = "";
        }

        $this->dbOptions = $definition['db'];
    } // end __construct

    protected function getOptionDB($ident)
    {
        return $this->dbOptions[$ident];
    } // end getOptionDB

    protected function hasOptionDB($ident)
    {
        return isset($this->dbOptions[$ident]);
    } // end hasOptionDB

    public function getRows($isPagination = true, $isUserFilters = true, $betweenWhere = array(), $isSelectAll = false)
    {
        $this->db = DB::table($this->dbOptions['table']);
        $this->prepareSelectValues();

        if ($isSelectAll) {
            $this->db->addSelect($this->getOptionDB('table') .'.*');
        }
      //  exit();
        $this->prepareFilterValues();

        if ($isUserFilters) {
            $this->onSearchFilterQuery();
        }

        $this->dofilter();

        $definitionName = $this->controller->getOption('def_name');
        $sessionPath = 'table_builder.'.$definitionName.'.order';
        $order = Session::get($sessionPath, array());

        if ($order && $isUserFilters) {
            $this->db->orderBy($this->getOptionDB('table') .'.'. $order['field'], $order['direction']);
        } elseif ($this->hasOptionDB('order')) {
            $order = $this->getOptionDB('order');

            foreach ($order as $field => $direction) {
                $this->db->orderBy($this->getOptionDB('table') .'.'. $field, $direction);
            }
        }

        // FIXME:
        if ($betweenWhere) {
            $betweenField  = $betweenWhere['field'];
            $betweenValues = $betweenWhere['values'];

            $this->db->whereBetween($betweenField, $betweenValues);
        }

        if ($this->hasOptionDB('pagination') && $isPagination) {
            $pagination = $this->getOptionDB('pagination');
            $perPage = $this->getPerPageAmount($pagination['per_page']);
            $paginator = $this->db->paginate($perPage);
            /*if ($this->cache && is_array($this->cache)) {
                $paginator = Cache::tags($this->cache)->rememberForever($this->fileDefinition.$this->id,  function() use ($perPage) {
                    return $this->db->paginate($perPage);
                });

            } else {
                $paginator = $this->db->paginate($perPage);
            }*/

           // $paginator->setBaseUrl($pagination['uri']);

            return $paginator;
        }

        return $this->db->get();
    } // end getRows

    private function dofilter()
    {
        if (Input::has("filter")) {
            $filters = Input::get("filter");

            foreach ($filters as $nameField => $valueField) {
                if ($valueField) {
                    $this->db->where($nameField, $valueField);
                }
            }
        }
    }

    private function getPerPageAmount($info)
    {
        if (!is_array($info)) {
            return $info;
        }

        $definitionName = $this->controller->getOption('def_name');
        $sessionPath = 'table_builder.'.$definitionName.'.per_page';
        $perPage = Session::get($sessionPath);
        if (!$perPage) {
            $keys = array_keys($info);
            $perPage = $keys[0];
        }

        return $perPage;
    } // end getPerPageAmount

    protected function prepareFilterValues()
    {
        $definition = $this->controller->getDefinition();
        $filters = isset($definition['filters']) ? $definition['filters'] : array();
        if (is_callable($filters)) {
            $filters($this->db);
            return;
        }

        foreach ($filters as $name => $field) {
            $this->db->where($name, $field['sign'], $field['value']);
        }
    } // end prepareFilterValues

    protected function doPrependFilterValues(&$values)
    {
        $definition = $this->controller->getDefinition();
        $filters = isset($definition['filters']) ? $definition['filters'] : array();
        if (is_callable($filters)) {
            return;
        }

        foreach ($filters as $name => $field) {
            $values[$name] = $field['value'];
        }
    } // end doPrependFilterValues

    protected function prepareSelectValues()
    {
        $this->db->select($this->getOptionDB('table') .'.id');
        $def = $this->controller->getDefinition();
        if (isset($def['options']['is_sortable']) && $def['options']['is_sortable']) {
            if (!Schema::hasColumn($this->getOptionDB('table'), "priority")) {
                Schema::table(
                    $this->getOptionDB('table'),
                    function ($table) {
                        $table->integer("priority");
                    }
                );
            }

            $this->db->addSelect($this->getOptionDB('table') .'.priority');
        }

        $fields = $this->controller->getFields();
      
        foreach ($fields as $name => $field) {
            $field->onSelectValue($this->db);
        }
    } // end prepareSelectValues

    public function getRow($id)
    {
        $this->db = DB::table($this->getOptionDB('table'));

        $this->prepareSelectValues();

        $this->db->where($this->getOptionDB('table').'.id', $id);

        return $this->db->first();
    } // end getRow

    public function getTableAllowedIds()
    {
        if (!Session::has($this->getOptionDB('table') . "_exist")) {
            if (!Schema::hasTable($this->getOptionDB('table'))) {
                Schema::create($this->getOptionDB('table'), function ($table) {
                    $table->increments('id');
                });
            }
        }
        $this->db = DB::table($this->getOptionDB('table'));
        $this->prepareFilterValues();
        $ids = $this->db->lists('id');

        Session::push($this->getOptionDB('table') . "_exist", 'created');

        return $ids;
    } // end getTableAllowedIds

    protected function onSearchFilterQuery()
    {
        $definitionName = $this->controller->getOption('def_name');
        $sessionPath = 'table_builder.'.$definitionName.'.filters';

        $filters = Session::get($sessionPath, array());
        foreach ($filters as $name => $value) {
            if ($this->controller->hasCustomHandlerMethod('onSearchFilter')) {
                $res = $this->controller->getCustomHandler()->onSearchFilter($this->db, $name, $value);
                if ($res) {
                    continue;
                }
            }

            $this->controller->getField($name)->onSearchFilter($this->db, $value);
        }
    } // end onSearchFilterQuery

    public function updateRow($values)
    {
        $this->clearCache();

        if (!$this->controller->actions->isAllowed('update')) {
            throw new \RuntimeException('Update action is not permitted');
        }

        if ($this->controller->hasCustomHandlerMethod('handleUpdateRow')) {
            $res = $this->controller->getCustomHandler()->handleUpdateRow($values);
            if ($res) {
                return $res;
            }
        }
        
        $updateData = $this->_getRowQueryValues($values);
        $def = $this->controller->getDefinition();

        $model = $def['options']['model'];
        $this->_checkFields($updateData);

        if ($this->controller->hasCustomHandlerMethod('onUpdateRowData')) {
            $this->controller->getCustomHandler()->onUpdateRowData($updateData, $values);
        }
        $this->doValidate($updateData);

        //$this->doPrependFilterValues($updateData);

        $modelObj = $model::find($values['id']);

        if (method_exists($modelObj, "setFillable")) {
            $modelObj->setFillable(array_keys($updateData));
        }

        foreach ($updateData as $fild => $data) {
            if (is_array($data)) {
                $updateDataRes[$fild] = json_encode($data);
            } else {
                $updateDataRes[$fild] = $data;
            }
        }

        if (isset($updateDataRes['slug']) && $updateDataRes['slug'] == "/") {
            unset($updateDataRes['slug']);
        }
        //exit(print_arr($updateDataRes));
        $modelObj->update($updateDataRes);

      /*  $modelObj2 = $model::find($values['id']);
        $modelObj2->update($updateDataRes);*/

      //  Event::fire("table.updated", array($this->dbOptions['table'], $values['id']));

        // FIXME: patterns
        foreach ($this->controller->getPatterns() as $pattern) {
            $pattern->update($values, $values['id']);
        }

        $fields = $this->controller->getFields();
        foreach ($fields as $field) {
            if (preg_match('~^many2many~', $field->getFieldName())) {
                $this->onManyToManyValues($field->getFieldName(), $values, $values['id']);
            }
        }
        $res = array(
            'id'     => $values['id'],
            'values' => $updateData
        );
        if ($this->controller->hasCustomHandlerMethod('onUpdateRowResponse')) {
            $this->controller->getCustomHandler()->onUpdateRowResponse($res);
        }

        return $res;
    } // end updateRow

    public function cloneRow($id)
    {
        $this->clearCache();
        $def = $this->controller->getDefinition();

        if ($this->controller->hasCustomHandlerMethod('handleCloneRow')) {
            $res = $this->controller->getCustomHandler()->handleCloneRow($id);
            if ($res) {
                return $res;
            }
        }

        $model = $def['options']['model'];

        $page = $this->db->where("id", $id)->select("*")->first();
        Event::fire("table.clone", array($this->dbOptions['table'], $id));
        $idClonePage = $page['id'];
        unset($page['id']);

        $this->db->insertGetId($page);

        $res = array(
            'id'     => $id,
            'status' => $page,
        );

        return $res;
    }

    public function deleteRow($id)
    {
        $this->clearCache();

        if (!$this->controller->actions->isAllowed('delete')) {
            throw new \RuntimeException('Delete action is not permitted');
        }

        if ($this->controller->hasCustomHandlerMethod('handleDeleteRow')) {
            $res = $this->controller->getCustomHandler()->handleDeleteRow($id);
            if ($res) {
                return $res;
            }
        }

        foreach ($this->controller->getPatterns() as $pattern) {
            $pattern->delete($id);
        }
        $res = $this->db->where('id', $id)->delete();

        Event::fire("table.delete", array($this->dbOptions['table'], $id));

        $res = array(
            'id'     => $id,
            'status' => $res
        );
        if ($this->controller->hasCustomHandlerMethod('onDeleteRowResponse')) {
            $this->controller->getCustomHandler()->onDeleteRowResponse($res);
        }

        return $res;
    } // end deleteRow

    public function fastSave($input)
    {
        $this->clearCache();

        $nameField = $input['name'];
        $valueField = $input['value'];

        $def = $this->controller->getDefinition();
        $model = $def['options']['model'];
        $modelObj = $model::find($input['id']);
        $modelObj->$nameField = $valueField;

        $modelObj->save();
    }

    public function insertRow($values)
    {
        $this->clearCache();

        if (!$this->controller->actions->isAllowed('insert')) {
            throw new \RuntimeException('Insert action is not permitted');
        }

        if ($this->controller->hasCustomHandlerMethod('handleInsertRow')) {
            $res = $this->controller->getCustomHandler()->handleInsertRow($values);
            if ($res) {
                return $res;
            }
        }

        $insertData = $this->_getRowQueryValues($values);
        $this->_checkFields($insertData);

        $this->doValidate($insertData);
        $id = false;
        if ($this->controller->hasCustomHandlerMethod('onInsertRowData')) {
            $id = $this->controller->getCustomHandler()->onInsertRowData($insertData);
        }

        if (!$id) {
            //$this->doPrependFilterValues($insertData);

            foreach ($insertData as $fild => $data) {
                if (is_array($data)) {
                    $insertDataRes[$fild] = json_encode($data);
                } else {
                    $insertDataRes[$fild] = $data;
                }
            }
            $def = $this->controller->getDefinition();
            $model = $def['options']['model'];

            $objectModel = new $model;
            foreach ($insertDataRes as $key => $value) {
                $objectModel->$key = (null !== $value) ? "$value" : null;
                ;
            }
            $objectModel->save();
            $id = $objectModel->id;
        }
        
        // FIXME: patterns
        foreach ($this->controller->getPatterns() as $pattern) {
            $pattern->insert($values, $id);
        }

        // FIXME:
        $fields = $this->controller->getFields();
        foreach ($fields as $field) {
            if (preg_match('~^many2many~', $field->getFieldName())) {
                $this->onManyToManyValues($field->getFieldName(), $values, $id);
            }
        }

        $res = array(
            'id' => $id,
            'values' => $insertData
        );
        if ($this->controller->hasCustomHandlerMethod('onInsertRowResponse')) {
            $this->controller->getCustomHandler()->onInsertRowResponse($res);
        }

        return $res;
    } // end insertRow

    private function onManyToManyValues($ident, $values, $id)
    {
        $field = $this->controller->getField($ident);
        $vals = isset($values[$ident]) ? $values[$ident] : array();
        $field->onPrepareRowValues($vals, $id);
    } // end onManyToManyValues

    private function doValidate($values)
    {
        $errors = array();
        $definition = $this->controller->getDefinition();

        $fields = $definition['fields'];
        foreach ($fields as $ident => $options) {
            try {
                $field = $this->controller->getField($ident);
                if ($field->isPattern()) {
                    continue;
                }
                
                $tabs = $field->getAttribute('tabs');
                if ($tabs) {
                    foreach ($tabs as $tab) {
                        $fieldName = $ident . $tab['postfix'];
                        $field->doValidate($values[$fieldName]);
                    }
                } else {
                    if (array_key_exists($ident, $values)) {
                        $field->doValidate($values[$ident]);
                    }
                }
            } catch (JarboePreValidationException $e) {
                $errors = array_merge($errors, explode('|', $e->getMessage()));
                continue;
            }
        }

        if ($errors) {
            $errors = implode('|', $errors);
            throw new JarboeValidationException($errors);
        }
    } // end doValidate

    private function _getRowQueryValues($values)
    {
        $values = $this->_unsetFutileFields($values);

        $definition = $this->controller->getDefinition();
        $fields = $definition['fields'];

        foreach ($fields as $ident => $options) {
            $field = $this->controller->getField($ident);

            if ($field->isPattern()) {
                continue;
            }
            
            $tabs = $field->getAttribute('tabs');
            if ($tabs) {
                foreach ($tabs as $tab) {
                    $fieldName = $ident . $tab['postfix'];
                    $values[$fieldName] = $field->prepareQueryValue($values[$fieldName]);
                }
            } else {
                if (isset($values[$ident])) {
                    $values[$ident] = $field->prepareQueryValue($values[$ident]);
                }
            }
        }

        return $values;
    } // end _getRowQueryValues

    private function _unsetFutileFields($values)
    {
        unset($values['id']);
        unset($values['query_type']);

        foreach ($values as $key => $val) {
            if (preg_match('~^many2many~', $key)) {
                unset($values[$key]);
            }
        }
        
        // patterns
        unset($values['pattern']);

        // for tree
        unset($values['node']);
        unset($values['__node']);

        return $values;
    } // end _unsetFutileFields

    private function _checkFields($values)
    {
        $definition = $this->controller->getDefinition();
        $fields = $definition['fields'];

        foreach ($fields as $ident => $options) {
            $field = $this->controller->getField($ident);

            if ($field->isPattern()) {
                continue;
            }
            
            $tabs = $field->getAttribute('tabs');

            if ($tabs) {
                foreach ($tabs as $tab) {
                    $this->_checkField($values, $ident, $field);
                }
            } else {
                if (isset($values[$ident])) {
                    $this->_checkField($values, $ident, $field);
                }
            }
        }
    } // end _checkFields

    private function _checkField($values, $ident, $field)
    {
        if (!$field->isEditable()) {
            throw new \RuntimeException("Field [{$ident}] is not editable");
        }
    } // end _checkField


    public function clearCache()
    {
        $definition = $this->controller->getDefinition();

        if (isset($definition['cache'])) {
            $cache = $definition['cache'];

            foreach ($cache as $key => $cacheDelete) {
                if ($key == "tags") {
                    foreach ($cacheDelete as $tag) {
                        Cache::tags($tag)->flush();
                    }
                }

                if ($key == "keys") {
                    foreach ($cacheDelete as $key) {
                        Cache::forget($key);
                    }
                }
            }
        }
    }
}
