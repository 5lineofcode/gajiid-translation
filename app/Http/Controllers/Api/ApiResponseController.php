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

    public static function branchedNeedAuthResponse(){
        return [
            "error" => true,
            "error_code" => "BRANCHED_ENDPOINT",
            "message" => "This endpoint need you to login first!"
        ];
    }

    public static function updateEndpointVersion($endpoint){
        $new_version = uniqid();
        $company_id = Session::get("current_user")["company_id"];
        $branch_id = Session::get("current_user")["branch"]["branch_id"];
        
        //TODO: Implement Branched
        DB::delete("delete from  endpoint_version where endpoint='$endpoint'");
        DB::table("endpoint_version")
            ->insert([
                "endpoint" => $endpoint,
                "version" => $new_version,
                "branch_id" => $branch_id,
                "company_id" => $company_id,
            ]);
    }
}
