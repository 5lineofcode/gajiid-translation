<?php
namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\ApiResponseController;
use App\Http\Controllers\Api\ApiBranchedController;

use Input;
use DB;
use File;

class ApiHelperController extends Controller
{

    public static function callCustomMethod($table_name,$method_name,$params){
        $table_name = str_replace("_", " ", $table_name);
        $table_name = ucwords($table_name);
        $table_name = str_replace(" ", "", $table_name);
        $controllerName = $table_name;
        $className = "\App\Http\Controllers\Api\\{$controllerName}Controller";
    
        if (class_exists($className)) {
            $controller = new $className();
    
            if (method_exists($className, $method_name)) {
                return $controller->$method_name($params);
            }
        }
        return null;
    }

    public static function callMethodIfExists($table_name, $method_name, $params)
    {
        $table_name = str_replace("_", " ", $table_name);
        $table_name = ucwords($table_name);
        $table_name = str_replace(" ", "", $table_name);
        $controllerName = $table_name;
        $className = "\App\Http\Controllers\Api\\{$controllerName}Controller";
    
        if (class_exists($className)) {
            $controller = new $className();
    
            if (method_exists($className, $method_name)) {
                return $controller->$method_name($params);
            }
        }
        return null;
    }
    
    public static function getOperatorAndValue($key,$value){
        $operator = "=";

        if(str_contains($value,"(%)")){
            $operator = "like";
            $value = str_replace("(%)","%",$value);
        }

        if(str_contains($value,"(>)")){
            $operator = ">";
            $value = str_replace("(>)","",$value);
            $value = intval($value);
        }

        if(str_contains($value,"(<)")){
            $operator = "<";
            $value = str_replace("(<)","",$value);
            $value = intval($value);
        }

        if(str_contains($value,"(>=)")){
            $operator = ">=";
            $value = str_replace("(>=)","",$value);
            $value = intval($value);
        }

        if(str_contains($value,"(<=)")){
            $operator = "<=";
            $value = str_replace("(<=)","",$value);
            $value = intval($value);
        }

        if(str_contains($value,"(<>)")){
            $operator = "<>";
            $value = str_replace("(<>)","",$value);
            $value = intval($value);
        }

        return [
            "operator" => $operator,
            "value" => $value,
        ];
    }

    public static function getWhere(){
        $input_all = Input::all();
        $where = [];
        foreach ($input_all as $key => $value) {
            if (starts_with($key, "f_")) {
                
                $opv = ApiHelperController::getOperatorAndValue($key,$value);
                $o_operator = $opv["operator"];
                $o_value = $opv["value"];

                $where[] = [
                    substr($key, 2), $o_operator, $o_value
                ];
                Input::offsetUnset($key);
            }
        }

    
        return $where;
    }

    public static function getPrimaryKey($table_name){
        $result = DB::select(DB::raw("SHOW KEYS FROM `{$table_name}` WHERE Key_name = 'PRIMARY'"));
        $primaryKey = $result[0]->Column_name;
        return $primaryKey;
    }
}
