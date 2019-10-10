<?php
namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\ApiResponseController;
use App\Http\Controllers\Api\ApiBranchedController;

use Input;
use Session;
use DB;
use File;

class TranslatorVersionController extends Controller
{
    public function getVersion()
    {
        return [
            "version_number" => DB::table("translator_version")->get()[0]->version_number
        ];
    }

    public function updateVersion()
    {
        $current_version = DB::table("translator_version")->get()[0]->version_number;

        DB::table("translator_version")->update([
            "version_number" => $current_version + 1
        ]);

        return [
            "message" => "Done"
        ];
    }
}
