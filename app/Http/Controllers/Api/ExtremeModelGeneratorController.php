<?php
namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\ApiResponseController;
use App\Http\Controllers\Api\ApiBranchedController;
use App\Http\Controllers\Api\ApiHelperController;

use Input;
use DB;
use File;
use Session;

class ExtremeModelGeneratorController extends Controller
{
    public function GetModel(){
        $arr = [];
        
        $arr[] = [
            "product" => DB::table("product")->limit(3)->get()
        ];

        $arr[] = [
            "product_category" => DB::table("product_category")->get()
        ];

        return $arr;
    }
}