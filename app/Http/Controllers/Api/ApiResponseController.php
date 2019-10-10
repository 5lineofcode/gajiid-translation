<?php
namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;

use Input;
use DB;
use File;
use Session;

class ApiResponseController extends Controller
{
    public static function createResponse($is_success){
        return [
            "error" => !boolVal($is_success),
            "id" => DB::getPdo()->lastInsertId(),
            "message" => "Create Data Success"
        ];
    }

    public static function updateResponse($is_success){
        return [
            "error" => !boolVal($is_success),
            "message" => "Update Data Success"
        ];
    }

    public static function deleteResponse($is_success){
        return [
            "error" => !boolVal($is_success),
            "message" => "Delete Data Success"
        ];
    }
    
}
