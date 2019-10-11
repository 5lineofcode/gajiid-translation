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

    public function updateTranslation()
    {
        $items = Input::get("items");

        DB::beginTransaction();
        DB::table("translation")->delete();

        DB::table("translation")->insert($items);

        DB::commit();

        return [
            "message" => "Done",
        ];
    }

    public function emptyTranslation(){
        DB::table("translation")->delete();
        return [
            "message" => "Done",
        ];
    }
}


