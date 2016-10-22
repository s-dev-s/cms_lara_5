<?php namespace Vis\Builder\Helpers;

use Vis\Builder\Handlers\CustomHandler;
use Illuminate\Support\Facades\View;
use Cartalyst\Sentinel\Laravel\Facades\Activation;
use Cartalyst\Sentinel\Laravel\Facades\Sentinel;
use Illuminate\Support\Facades\DB;

class UsersHandler extends CustomHandler
{
    /*
     * show field in list users
     */
    public function onGetListValue($formField, array &$row)
    {
        if ($formField->getFieldName() == "activated") {
            $activation = DB::table("activations")->where("user_id", $row['id'])->where("completed", 1)->count();

            if ($activation) {
                return View::make('admin::tb.input_checkbox_list')->with('is_checked', 1);
            } else {
                return View::make('admin::tb.input_checkbox_list')->with('is_checked', 0);
            }
        }
    }

    /*
     * not select in db
     */
    public function onAddSelectField($formField, $row)
    {
        if ($formField->getFieldName() == "activated") {
            return true;
        }
    }

    public function onGetValue($formField, array &$row, &$postfix)
    {
        if ($formField->getFieldName() == "activated") {
            if (!isset($row['id'])) {
                return "0";
            }
            $activation = DB::table("activations")->where("user_id", $row['id'])->where("completed", 1)->count();

            if ($activation == 0) {
                return "0";
            } else {
                return "1";
            }
        }
    }

    /*
     * update record
     */
    public function onUpdateRowData(array &$value, $row)
    {
        if (isset($row['id'])) {
            if ($value['activated'] == 0) {
                $user = Sentinel::findById($row['id']);
                Activation::remove($user);
            } else {
                $user = Sentinel::findById($row['id']);
                Activation::remove($user);
                $activation = Activation::create($user);
                Activation::complete($user, $activation->code);
            }

            if ($value['password'] && $value['password'] != "password") {
                Sentinel::update($user, array('password' => $value['password']));
            }
            
            unset($value['activated']);
            unset($value['password']);
        }
    }

    /*
     * insert user
     */
    public function onInsertRowData(array &$value)
    {
        $user = Sentinel::register($value);
        if ($value['activated'] != 0) {
            $activation = Activation::create($user);
            Activation::complete($user, $activation->code);
        }

        return $user->id;
    }
}
