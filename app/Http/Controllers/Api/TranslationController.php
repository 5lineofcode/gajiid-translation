<?php
namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\ApiResponseController;
use App\Http\Controllers\Api\ApiBranchedController;
use Google\Cloud\Translate\TranslateClient;

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

    public function clearTranslation(){
        DB::table("translation")->delete();
        return [
            "message" => "Done",
        ];
    }


    public function getTranslate($word,$language_code){
        $translate = new TranslateClient([
            // 'key' => 'your_key'
            'keyFile' => json_decode(file_get_contents(base_path('google-services.json')), true)
        ]);
    
        // Translate text from english to french.
        $result = $translate->translate($word, [
            'source' => 'id',
            'target' => $language_code
        ]);
    
        return $result['text'];
    }

    public function registerUndefinedWord(){
        $word = Input::get("word");

        $current = DB::table("translation")
                        ->where("indonesia",$word)
                        ->get();

        if(count($current)>0){
            foreach($current as $c){
                return response()->json($c);
            }
        }

        $item = [
            "string_code" => uniqid(),
            "indonesia" => $word,
            "english" => $this->getTranslate($word,"en"),
            "german" => $this->getTranslate($word,"de"),
            "china" => $this->getTranslate($word,"zh-CN"),
            "japan" => $this->getTranslate($word,"ja"),
            "korea" => $this->getTranslate($word,"ko"),
            "rusia" =>  $this->getTranslate($word,"ru"),
        ];

        DB::table("translation")->insert($item);
        return $item;
    }
}


