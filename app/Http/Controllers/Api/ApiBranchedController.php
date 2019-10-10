<?php
namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;

use Input;
use DB;
use File;
use Session;

class ApiBranchedController extends Controller
{
    //! RULE
    //1. Semua Tabel Branched, wajib memiliki company_id dan branch_id
    public static $branched_tables = [
        "product",
        "product_assigned_station",
        "product_category",
        "product_station",
        "product_variant",
        "product_stock_adjustment",
        "product_stock_adjustment_category",
        "table_group",
        "table_management",
        "unit",
        "users",

        "bank_account",

        "customer",

        "news",
        "news_audience",
        
        "order",
        "order_detail",
        "order_payment",
        "cash",

        "adjustment",
        "adjustment_detail",


        "employee",
    ];

    public static function isBranched($table_name){
        $exists = in_array($table_name, ApiBranchedController::$branched_tables);
        return $exists;
    }

    public static function needBranchedAuth($table_name){
        if(ApiBranchedController::isBranched($table_name)){
            if(Session::get("current_user")==null){
                return true;
            }
        }
        return false;
    }

    public static function useBranchedQuery(&$query,$table_name){
        //if table_name is exists in Branched
        if(ApiBranchedController::isBranched($table_name)){
            $user = Session::get("current_user");
            $query = $query->where([
                "company_id" => $user["company_id"],
                "branch_id" => $user["branch"]["branch_id"],
            ]);
        }
    }

    public static function useBranchedPostData(&$postdata,$table_name){
        //if table_name is exists in Branched
        if(ApiBranchedController::isBranched($table_name)){
            $user = Session::get("current_user");

            $postdata["company_id"] = $user["company_id"];
            $postdata["branch_id"] = $user["branch"]["branch_id"];
        }
    }
}
