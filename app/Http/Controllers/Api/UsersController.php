<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\ApiResponseController;
use App\Http\Controllers\Api\ApiBranchedController;

use Input;
use DB;
use File;
use Hash;
use Session;

class UsersController extends Controller
{
    public function save_photo(&$postdata)
    {
        if ($postdata["photo"] == null) {
            $name = strtolower($postdata["name"]);
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

    public function isEmailExists($id, $email)
    {
        if ($id == "0"){
            $user = DB::table("users")
            ->where("email", $email)
            ->first();
        } else {
            $user = DB::table("users")
            ->where("id", "<>", $id)
            ->where("email", $email)
            ->first();
        }

        if (count($user) > 0) {
            response()->json([
                "error" => true,
                "error_code" => "EMAIL_IS_EXISTS",
                "message" => "This user ID has been used, please use another user ID",
            ])->send();
            exit;
        }
    }

    public function checkMaxUserAllowed($branch_id)
    {
        $membership_level = DB::table("branch")
            ->where("branch_id", $branch_id)
            ->first()->membership_level;

        $max_user = DB::table("membership_plan")
            ->where("membership_freq", $membership_level)
            ->first()->user_total;

        if ($max_user != "Unlimited"){
            $user_count = DB::table("users")
            ->where("branch_id", $branch_id)
            ->where("id_cms_privileges", "<>", "1")
            ->where("status", "1")
            ->first();

            if (count($user_count) >= $max_user) {
                response()->json([
                    "error" => true,
                    "error_code" => "CANNOT ADD MORE USER",
                    "message" => "Cannot add more user for\n$membership_level membership plan",
                ])->send();
                exit;
            }
        }
    }

    public function OnTable($params)
    {
        $where = $params["where"];
        $sort_field = $params["sort_field"] ?? $params["primary_key"];
        $sort_order = $params["sort_order"] ?? "asc";
        $page_count = $params["page_count"] ?? 10;

        $query = DB::table("users")->where("id_cms_privileges", "<>", "1");
        $query = $query->where("id_cms_privileges","<>","6");
        $query = $query->where("id_cms_privileges","<>","7");

        ApiBranchedController::useBranchedQuery($query, "users");
        $query = $query->orderBy($sort_field, $sort_order);

        if ($page_count == null) {
            $page_count = 0;
        }

        $paginator = $query->paginate();

        $paginator->getCollection()->transform(function ($item) {
            //Example 1 To One RelationShip
            $name = DB::table("cms_privileges")->where("id", $item->id_cms_privileges)->first();
            $item->userlevel = $name;

            return $item;
        });

        return $paginator;
    }

    public function OnGetAll($params)
    {
        $where = $params["where"];
        $sort_field = $params["sort_field"] ?? $params["primary_key"];
        $sort_order = $params["sort_order"] ?? "asc";
        $page_count = $params["page_count"] ?? 10;

        $query = DB::table("users")->where("id_cms_privileges", "<>", "1");
        $query = $query->where("id_cms_privileges","<>","6");
        $query = $query->where("id_cms_privileges","<>","7");

        ApiBranchedController::useBranchedQuery($query, "users");
        $query = $query->orderBy($sort_field, $sort_order);

        if ($page_count == null) {
            $page_count = 0;
        }

        $paginator = $query->paginate();

        $paginator->getCollection()->transform(function ($item) {
            //Example 1 To One RelationShip
            $name = DB::table("cms_privileges")->where("id", $item->id_cms_privileges)->first();
            $item->userlevel = $name;

            return $item;
        });

        return $paginator;
    }

    public function OnCreate($params)
    {
        $this->isEmailExists("0", Input::get("email"));
        $this->checkMaxUserAllowed(Session::get("current_user")["branch"]["branch_id"]);

        $postdata = Input::except("new_password");
        $password = Input::get("new_password");
        $postdata["password"] = Hash::make($password);

        $this->save_photo($postdata);

        $query = DB::table("users");
        if (count($params["where"]) > 0) {
            $query = $query->where($params["where"]);
        }

        ApiBranchedController::useBranchedPostData($postdata, $params["table_name"]);

        DB::beginTransaction();
        $is_success = $query->insert($postdata);

        DB::commit();

        return ApiResponseController::createResponse($is_success);
    }

    public function OnEdit($params)
    {
        $this->isEmailExists(Input::get("id"), Input::get("email"));

        $postdata = Input::except("id", "new_password");
        $password = Input::get("new_password");
        $postdata["password"] = Hash::make($password);

        $this->save_photo($postdata);

        $query = DB::table("users");
        $query = $query->where("id", $params["id"]);
        if (count($params["where"]) > 0) {
            $query = $query->where($params["where"]);
        }

        ApiBranchedController::useBranchedPostData($postdata, $params["table_name"]);
        $is_success = $query->update($postdata);

        return ApiResponseController::updateResponse($is_success);
    }

    public function OnGetSingleData($params)
    {
        $query = DB::table("users");
        if (count($params["where"]) > 0) {
            $query = $query->where($params["where"]);
        }
        $query = $query->where("id", $params["id"]);

        ApiBranchedController::useBranchedQuery($query, "users");

        $user = $query->first();

        //Example 1 To One RelationShip
        $user_level = DB::table("cms_privileges")
            ->where("id", $user->id_cms_privileges)
            ->first();

        $user->user_level = $user_level->name;

        //Example 1 To One RelationShip
        $station_name = DB::table("product_station")
            ->where("id", $user->station_id)
            ->first();

        $user->station_name = $station_name->name;

        return response()->json($user);
    }
}
