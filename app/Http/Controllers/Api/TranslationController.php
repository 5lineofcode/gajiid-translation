<?php
namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\ApiResponseController;
use App\Http\Controllers\Api\ApiBranchedController;

use Input;
use Session;
use DB;
use File;

class TranslationController extends Controller
{
    public function getTranslation()
    {
        return DB::table("translation")->get();
    }
}


