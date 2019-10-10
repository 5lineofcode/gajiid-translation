<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\ApiResponseController;
use App\Http\Controllers\Api\ApiBranchedController;

use Input;
use DB;
use File;
use Session;

class ProductController extends Controller
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
            } else {
                $postdata["photo"] = "uploads/default-photo/all/no_photo.jpg";
            }
        }
    }

    public function OnTable($params)
    {
        $sort_field = $params["sort_field"] ?? $params["primary_key"];
        $sort_order = $params["sort_order"] ?? "asc";
        $page_count = $params["page_count"] ?? 10;

        $query = DB::table("product");
        if (count($params["where"]) > 0) {
            $query = $query->where($params["where"]);
        }
        ApiBranchedController::useBranchedQuery($query, "product");

        $query = $query->orderBy($sort_field, $sort_order);

        if ($page_count == null) {
            $page_count = 0;
        }

        if ($page_count == 0) {
            $paginator = $query->paginate();
        } else {
            $paginator = $query->paginate($page_count);
        }

        $paginator->getCollection()->transform(function ($item) {
            //Example 1 To One RelationShip
            $product_category = DB::table("product_category")->where("category_id", $item->category_id)->first();
            $item->category = $product_category;

            //Example 1 To Many RelationShip
            $product_stations = DB::table("product_assigned_station")->where("product_id", $item->product_id)->get();
            $item->stations = $product_stations;

            return $item;
        });

        return $paginator;
    }

    public function OnGetAll($params)
    {
        $where = $params["where"];
        $sort_field = $params["sort_field"] ?? $params["primary_key"];
        $sort_order = $params["sort_order"] ?? "asc";

        $query = DB::table("product");
        if (count($params["where"]) > 0) {
            $query = $query->where($params["where"]);
        }
        ApiBranchedController::useBranchedQuery($query, "product");
        $query = $query->orderBy($sort_field, $sort_order);

        if ($page_count == null) {
            $page_count = 0;
        }

        $paginator = $query->paginate(100000);

        $paginator->getCollection()->transform(function ($item) {
            //Example 1 To One RelationShip
            $product_category = DB::table("product_category")->where("category_id", $item->category_id)->first();
            $item->category = $product_category;

            //Example 1 To Many RelationShip
            $product_stations = DB::table("product_assigned_station")->where("product_id", $item->product_id)->get();

            $station_detail = DB::table("product_station")->where("id", $product_stations->first()->station_id)->first();

            $item->category = $product_category;
            $item->stations = $product_stations;
            $item->station_detail = $station_detail;

            return $item;
        });

        return $paginator;
    }

    public function OnGetSingleData($params)
    {
        $query = DB::table("product");
        if (count($params["where"]) > 0) {
            $query = $query->where($params["where"]);
        }
        $query = $query->where("product_id", $params["id"]);

        ApiBranchedController::useBranchedQuery($query, "product");

        $product = $query->first();

        //Example 1 To One RelationShip
        $product_category = DB::table("product_category")
            ->where("category_id", $product->category_id)
            ->first();

        $product->category = $product_category;

        //Example 1 To Many RelationShip
        $product_stations = DB::table("product_assigned_station")
            ->where("product_id", $product->product_id)
            ->get();

        // $product_stations = DB::table("product_assigned_station")
        //     ->where("product_id", $product->product_id)
        //     ->first();

        // $station_name = DB::table("product_station")
        //     ->where("id", $product_stations->station_id)
        //     ->first()->name;

        // $product_stations->station_name = $station_name;

        $product->stations = $product_stations;

        // return response()->json($query->first());
        return response()->json($product);
    }


    public function SaveProductStations($product_id)
    {
        $company_id = Session::get("current_user")["company_id"];
        $branch_id = Session::get("current_user")["branch"]["branch_id"];

        $selected_stations = $this->selected_stations;

        DB::table("product_assigned_station")
            ->where("company_id", $company_id)
            ->where("branch_id", $branch_id)
            ->where("product_id", $product_id)
            ->delete();

        foreach ($selected_stations as $station) {
            $station_id = $station["id"];

            DB::table("product_assigned_station")
                ->insert([
                    "company_id" => $company_id,
                    "branch_id" => $branch_id,
                    "product_id" => $product_id,
                    "station_id" => $station_id,
                ]);
        }
    }

    public function prepare_selected_stations(&$postdata)
    {
        $this->selected_stations = $postdata["selected_stations"];
        unset($postdata["selected_stations"]);
    }

    public function OnCreate($params)
    {
        $postdata = Input::all();
        $this->save_photo($postdata);
        $this->prepare_selected_stations($postdata);

        $query = DB::table("product");
        if (count($params["where"]) > 0) {
            $query = $query->where($params["where"]);
        }

        ApiBranchedController::useBranchedPostData($postdata, $params["table_name"]);

        DB::beginTransaction();
        $is_success = $query->insert($postdata);

        $product_id = DB::getPdo()->lastInsertId();
        $this->SaveProductStations($product_id);
        DB::commit();

        return ApiResponseController::createResponse($is_success);
    }

    public function OnEdit($params)
    {
        $postdata = Input::except("id");
        $product_id = $params["id"];
        $this->save_photo($postdata);
        $this->prepare_selected_stations($postdata);

        $query = DB::table("product");
        $query = $query->where("product_id", $product_id);
        if (count($params["where"]) > 0) {
            $query = $query->where($params["where"]);
        }

        ApiBranchedController::useBranchedPostData($postdata, $params["table_name"]);
        $is_success = $query->update($postdata);
        $this->SaveProductStations($product_id);

        return ApiResponseController::updateResponse($is_success);
    }

    public function OnDelete($params)
    {
        $postdata = Input::except("id");

        $query = DB::table("product");
        $query = $query->where("product_id", $params["id"]);
        if (count($params["where"]) > 0) {
            $query = $query->where($params["where"]);
        }
        ApiBranchedController::useBranchedQuery($query, $params["table_name"]);
        $is_success = $query->delete();

        return ApiResponseController::deleteResponse($is_success);
    }

    public function UpdateDailyStock()
    {
        $company_id = Session::get("current_user")["company_id"];
        $branch_id = Session::get("current_user")["branch"]["branch_id"];

        $result = DB::table("product")
            ->where("branch_id", $branch_id)
            ->update([
                "stock" => DB::raw("stock = daily_stock")
            ]);

        return [
            "is_error" => boolval($result),
            "message" => "Daily Stock Update Success!",
        ];
    }

    public function stockOpname()
    {
        $items = Input::get("items");

        DB::beginTransaction();
        foreach($items as $item){
            $product_id = $item["product_id"];

            $stock = $item["qty"];

            DB::table("product")
                ->where("product_id",$product_id)
                ->update([
                    "stock" => $stock
                ]);

        }
        DB::commit();

        return [
            "is_error" => false,
            "message" => "Stock Opname Success ",
        ];
    }
}
