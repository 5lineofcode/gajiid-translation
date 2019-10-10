<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;

use DB;

class IngredientsController extends Controller
{
    public function save_photo(&$postdata)
    {
        if ($postdata["photo"] == null) {
            $name = strtolower($postdata["product_name"]);
            $name = str_replace(" ", "-", $name);

            $filename = "uploads/default-photo/en/$name.png";

            //check en
            if (File::exists(storage_path("app/$filename"))) {
                $postdata["photo"] = $filename;
            }

            //check id
            $filename = "uploads/default-photo/id/$name.png";

            //check en
            if (File::exists(storage_path("app/$filename"))) {
                $postdata["photo"] = $filename;
            }
        }
    }

    public function onIndex()
    {
        $result = DB::table("ingredients")
            ->select("ingredients.*")
            ->addSelect("unit.unit_name")
            ->join("unit", "ingredients.unit_id", "=", "unit.id")
            ->paginate(10);
        return $result;
    }

    public function onGetAll()
    {
        $result = DB::table("ingredients")
            ->select("ingredients.*")
            ->addSelect("unit.unit_name")
            ->join("unit", "ingredients.unit_id", "=", "unit.id")
            ->paginate(10);
        return $result;
    }

    public function onGetSingleData($id)
    {
        $result = DB::table("ingredients")
            ->select("ingredients.*")
            ->addSelect("unit.unit_name")
            ->join("unit", "ingredients.unit_id", "=", "unit.id")
            ->where("ingredients.id", $id)
            ->first();

        return $result;
    }

    public function onCreate()
    {
        $postdata = Input::all();
        $this->save_photo($postdata);

        $is_success = DB::table("ingredients")
            ->insert($postdata);

        return [
            "is_success" => $is_success,
            "message" => "Create Data Success"
        ];
    }

    public function onEdit()
    {
        $postdata = Input::all();
        $this->save_photo($postdata);

        $is_success = DB::table("ingredients")
            ->where("id", $postdata["id"])
            ->update($postdata);

        return [
            "is_success" => $is_success,
            "message" => "Update Data Success"
        ];
    }
}
