<?php

namespace Vis\Builder\Handlers;

use Vis\Builder\JarboeController;

abstract class CustomHandler
{

    protected $controller;


    public function __construct(JarboeController $controller)
    {
        $this->controller = $controller;
    } // end __construct

    protected function getOption($ident)
    {
        return $this->controller->getOption($ident);
    } // end getOption

    public function handle()
    {
    } // end handle

    public function onGetValue($formField, array &$row, &$postfix)
    {
    } // end onGetValue
    
    public function onGetExportValue($formField, $type, array &$row, &$postfix)
    {
    } // end onGetExportValue

    public function onGetEditInput($formField, array &$row)
    {
    } // end onGetEditInput
    
    public function onGetListValue($formField, array &$row)
    {
    } // end onGetListValue
    
    public function onSelectField($formField, &$db)
    {
    } // end onSelectField

    public function onPrepareSearchFilters(array &$filters)
    {
    } // end onPrepareSearchFilters
    
    public function onSearchFilter(&$db, $name, $value)
    {
    } // end onSearchFilter

    public function onUpdateRowResponse(array &$response)
    {
    } // end onUpdateRowResponse

    public function onInsertRowResponse(array &$response)
    {
    } // end onInsertRowResponse

    public function onDeleteRowResponse(array &$response)
    {
    } // end onDeleteRowResponse
    
    public function handleDeleteRow($id)
    {
        /*
        return array(
            'id'     => $id,
            'status' => true|false
        );
        */
    } // end handleDeleteRow
    
    public function handleInsertRow($values)
    {
        /*
        return array(
            'id' => $idInsertedRow,
            'values' => $values
        );
        */
    } // end handleInsertRow
    
    public function handleUpdateRow($values)
    {
        /*
        return array(
            'id' => $idUpdatedRow,
            'values' => $values
        );
        */
    } // end handleUpdateRow
    
    public function onUpdateFastRowResponse(array &$response)
    {
    } // end onUpdateFastRowResponse
    
    public function onInsertRowData(array &$data)
    {
    } // end onInsertRowData
    
    public function onUpdateRowData(array &$data, $row)
    {
    } // end onUpdateRowData
    
    public function onSearchCustomFilter($formField, &$db, $value)
    {
    } // end onSearchCustomFilter
    
    public function onGetCustomValue($formField, array &$row, &$postfix)
    {
    } // end onGetCustomValue
    
    public function onGetCustomEditInput($formField, array &$row)
    {
    } // end onGetCustomEditInput
        
    public function onGetCustomListValue($formField, array &$row)
    {
    } // end onGetCustomListValue
    
    public function onSelectCustomValue(&$db)
    {
    } // end onSelectCustomValue
    
    public function onFileUpload($file)
    {
        /*
        $data = array(
            'status'     => true|false,
            'link'       => absolute path,
            'short_link' => relative path,
        );
        return Response::json($data);
        */
    } // end onFileUpload
    
    public function onPhotoUpload($formField, $file)
    {
        /*
        $data = array(
            'status'     => true|false,
            'link'       => absolute path,
            'short_link' => relative path,
            'delimiter'  => ','
        );
        return Response::json($data);
        */
    } // end onPhotoUpload
    
    public function onPhotoUploadFromWysiwyg($file)
    {
        /*
        $data = array(
            'status' => true|false,
            'link'   => absolute path
        );
        return Response::json($data);
        */
    } // end onPhotoUploadFromWysiwyg
    
    
    public function onInsertButtonFetch($def)
    {
    } // end onInsertButtonFetch
    
    public function onUpdateButtonFetch($def)
    {
    } // end onUpdateButtonFetch
    
    public function onDeleteButtonFetch($def)
    {
    } // end onDeleteButtonFetch
}
