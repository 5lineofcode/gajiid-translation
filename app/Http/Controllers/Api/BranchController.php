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

class BranchController extends Controller
{

    public function OnTable($params)
    {
        $company_id = Session::get("current_user")["company_id"];
        $branch_id = Session::get("current_user")["branch"]["branch_id"];

        $sort_field = $params["sort_field"] ?? $params["primary_key"];
        $sort_order = $params["sort_order"] ?? "asc";
        $page_count = $params["page_count"] ?? 10;
       
        $query = DB::table("branch"); 
        if(count($params["where"])>0){
            $query = $query->where($params["where"]);
        }
        $query = $query->where("company_id",$company_id);
        $query = $query->orderBy($sort_field,$sort_order);

        if($page_count == null){
            $page_count = 0;
        }

        if ($page_count == 0) {
            $paginator = $query->paginate();
        } else {
            $paginator = $query->paginate($page_count);
        }

        return $paginator;
    }

    public function OnCreate($params)
    {
        $company_id = Session::get("current_user")["company_id"];
        // $branch_id = Session::get("current_user")["branch"]["branch_id"];
        $user_id = Session::get("current_user")["id"];

        $authController = new AuthController();

        $postdata = Input::all();
        $postdata["company_id"] = $company_id;
        $postdata["owner_id"] = $user_id;

        DB::beginTransaction();
        
        
        $branch_id = $authController->registerBranch($user_id,$company_id);

        //Update Branch
        if($postdata["photo"]==null){
            $postdata["photo"] = $authController->getRandomUserPhoto();
        }

        $is_success = DB::table("branch")
            ->where("branch_id",$branch_id)
            ->update($postdata);
        
        DB::commit();

        return ApiResponseController::createResponse($is_success);
    }

    public function OnEdit($params)
    {
        $authController = new AuthController();
        $postdata = Input::except("id");

        $query = DB::table("branch");
        $query = $query->where("branch_id", $params["id"]);  
        if(count($params["where"])>0){
            $query = $query->where($params["where"]);
        }
        ApiBranchedController::useBranchedPostData($postdata,$params["table_name"]);

        //Update Branch
        if($postdata["photo"]==null){
            $postdata["photo"] = $authController->getRandomUserPhoto();
        }
        
        $is_success = $query->update($postdata);

        return ApiResponseController::updateResponse($is_success);
    }
}
