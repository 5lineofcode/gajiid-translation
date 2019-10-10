<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\ApiResponseController;
use App\Http\Controllers\Api\ApiBranchedController;
use App\Http\Controllers\Api\AuthController;

use Input;
use DB;
use File;
use Session;

class NewsController extends Controller
{

    public function OnTable($params)
    {
        $sort_field = $params["sort_field"] ?? $params["primary_key"];
        $sort_order = $params["sort_order"] ?? "asc";
        $page_count = $params["page_count"] ?? 10;

        $query = DB::table("news");
        if (count($params["where"]) > 0) {
            $query = $query->where($params["where"]);
        }

        ApiBranchedController::useBranchedQuery($query, "news");

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
            //Example 1 To Many RelationShip
            $news_audience = DB::table("news_audience")->where("news_id", $item->news_id)->get();
            $item->audience = $news_audience;

            return $item;
        });

        return $paginator;
    }

    public function prepare_selected_audience(&$postdata)
    {
        $this->selected_audience = $postdata["selected_audience"];
        unset($postdata["selected_audience"]);
    }

    public function OnCreate($params)
    {
        $postdata = Input::all();
        $this->prepare_selected_audience($postdata);

        $query = DB::table("news");
        if (count($params["where"]) > 0) {
            $query = $query->where($params["where"]);
        }

        ApiBranchedController::useBranchedPostData($postdata, $params["table_name"]);

        DB::beginTransaction();
        $is_success = $query->insert($postdata);

        $news_id = DB::getPdo()->lastInsertId();
        $this->SaveNewsAudience($news_id);
        DB::commit();

        return ApiResponseController::createResponse($is_success);
    }

    public function OnEdit($params)
    {
        $postdata = Input::except("news_id");
        $news_id = Input::get("news_id");
        // dd($news_id);
        $this->prepare_selected_audience($postdata);

        $query = DB::table("news");
        $query = $query->where("news_id", $news_id);
        if (count($params["where"]) > 0) {
            $query = $query->where($params["where"]);
        }

        ApiBranchedController::useBranchedPostData($postdata, $params["table_name"]);
        $is_success = $query->update($postdata);
        $this->SaveNewsAudience($news_id);

        return ApiResponseController::updateResponse($is_success);
    }

    public function SaveNewsAudience($news_id)
    {
        $company_id = Session::get("current_user")["company_id"];
        $branch_id = Session::get("current_user")["branch"]["branch_id"];

        $selected_audience = $this->selected_audience;

        DB::table("news_audience")
            ->where("company_id", $company_id)
            ->where("branch_id", $branch_id)
            ->where("news_id", $news_id)
            ->delete();

        foreach ($selected_audience as $audience) {
            $id_cms_privileges = $audience["id"];

            DB::table("news_audience")
                ->insert([
                    "company_id" => $company_id,
                    "branch_id" => $branch_id,
                    "news_id" => $news_id,
                    "id_cms_privileges" => $id_cms_privileges,
                ]);
        }
    }

    public function OnGetSingleData($params)
    {
        $query = DB::table("news");
        if (count($params["where"]) > 0) {
            $query = $query->where($params["where"]);
        }
        $query = $query->where("news_id", $params["id"]);

        ApiBranchedController::useBranchedQuery($query, "news");

        $news = $query->first();

        //Example 1 To Many RelationShip
        $news_audience = DB::table("news_audience")
                            ->where("news_id", $news->news_id)
                            ->get();

        $news->audience = $news_audience;

        return response()->json($news);
    }

    public function testing()
    {
        $branch_id = Session::get("current_user")["branch"]["branch_id"];

        $is_success = DB::table("branch")
            ->where("branch_id", $branch_id)
            ->update([
                "is_complete_wizard" =>  1
            ]);

        return [
            "error" => false,
            "message" => "Outlet Wizard Complete"
        ];
    }
}
