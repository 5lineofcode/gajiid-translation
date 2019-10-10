<?php

namespace App\Http\Controllers\api;
use App\Http\Controllers\Controller;

use DB;
use Input;
use Hash;
use Session;
use Mail;

class TableGroupController extends Controller
{
    // public function OnTable(){
    //     $result = DB::table("table_group")
    //         ->orderBy("table_order")
    //         ->paginate(10);
    //     return $result;
    // }

    public static function GenerateTableManagement($company_id,$branch_id){
        $items = DB::table("table_group")
            ->where("company_id",$company_id)
            ->where("branch_id",$branch_id)
            ->orderBy("table_order")
            ->get();

        $table_number = 1;
        foreach($items as $item){
            $table_group_id = $item->table_group_id;
            $name = $item->name;
            $table_order = $item->table_order;
            $table_count = $item->table_count;

            $table_management_items = [];
            for($i=0;$i<$table_count;$i++){
                $table_management_items[] = [
                    "company_id" => $company_id,
                    "branch_id" => $branch_id,
                    "table_group_id" => $table_group_id,
                    "table_number" => $table_number,
                    "status" => "AVAILABLE",
                ];
                $table_number++;
            }

            DB::table("table_management")
                ->insert($table_management_items);

        }
        
    }

    public function generateTableLayout(){
        TableGroupController::GenerateCurrentUserTableManagement();
        

        return [
            "error" => false,
            "message" => "Generate Table Layout Success"
        ];
    }

    public static function GenerateCurrentUserTableManagement(){
        $company_id = Session::get("current_user")["company_id"];
        $branch_id = Session::get("current_user")["branch"]["branch_id"];

        $items = DB::table("table_group")
            ->where("company_id",$company_id)
            ->where("branch_id",$branch_id)
            ->orderBy("table_order")
            ->get();

        $table_number = 1;

        DB::table("table_management")
            ->where("company_id",$company_id)
            ->where("branch_id",$branch_id)
            ->delete();

        foreach($items as $item){
            $table_group_id = $item->table_group_id;
            $name = $item->name;
            $table_order = $item->table_order;
            $table_count = $item->table_count;

            $table_management_items = [];
            for($i=0;$i<$table_count;$i++){
                $table_management_items[] = [
                    "company_id" => $company_id,
                    "branch_id" => $branch_id,
                    "table_group_id" => $table_group_id,
                    "table_number" => $table_number,
                    "status" => "AVAILABLE",
                ];
                $table_number++;
            }

            DB::table("table_management")
                ->insert($table_management_items);

        }
        
    }
}
