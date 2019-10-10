<?php
namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\ApiResponseController;
use App\Http\Controllers\Api\ApiBranchedController;

use Input;
use Session;
use DB;
use File;

class BlankController extends Controller
{
    public function getItem(){
        $items = DB::table("product")->get();
        return $items;
    }
}
