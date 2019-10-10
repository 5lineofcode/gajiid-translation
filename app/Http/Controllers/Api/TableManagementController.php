<?php
namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\ApiResponseController;
use App\Http\Controllers\Api\ApiBranchedController;

use Input;
use Session;
use DB;
use File;

class TableManagementController extends Controller
{
    public function UseTable()
    {
        $company_id = Session::get("current_user")["company_id"];
        $branch_id = Session::get("current_user")["branch"]["branch_id"];

        $table_id = Input::get("table_id");
        $order_id = Input::get("order_id");

        //Check if Table is Used
        $table = DB::table("table_management")
                    ->where([
                        "company_id" => $company_id,
                        "branch_id" => $branch_id,
                        "table_id" => $table_id,
                    ])->first();
        
        if(count($table)==0){
            response()->json([
                "error" => true,
                "message" => "Invalid Table ID > " . $table_id,
            ])->send();
            exit;
        }

        if($table->status=="USED" && $table->order_id != $order_id){
            response()->json([
                "error" => true,
                "message" => "This Table is Used by Another Customer",
            ])->send();
            exit;
        }

        DB::beginTransaction();

        //Update OLD Table
        // DB::table("table_management")
        //         ->where("order_id",$order_id)
        //         ->update([
        //             "order_id" => "",
        //             "status" => "AVAILABLE"
        //         ]);


        $query = DB::table("table_management");
        $query = $query->where([
            "company_id" => $company_id,
            "branch_id" => $branch_id,
            "table_id" => $table_id,
        ]);  

        if($table->status=="AVAILABLE"){
            $is_success = $query->update([
                "status" =>"USED",
                "order_id" => $order_id,
            ]);
        }
        else{
            $is_success = $query->update([
                "status" => "AVAILABLE",
                "order_id" => "",
            ]);
        }
        

        DB::commit();

        return ApiResponseController::updateResponse($is_success);
    }

    public function ClearTableOrder(){
        $company_id = Session::get("current_user")["company_id"];
        $branch_id = Session::get("current_user")["branch"]["branch_id"];

        DB::table("table_management")
            ->where([
                "company_id" => $company_id,
                "branch_id" => $branch_id,
            ])->update([
                "order_id" => null,
                "status" => "AVAILABLE",
            ]);
            
        return response()->json([
            "error" => false,
            "message" => "Clear All Table Order Done!",
        ]);
    }

    public static function ClearTableByOrderId(){
        $company_id = Session::get("current_user")["company_id"];
        $branch_id = Session::get("current_user")["branch"]["branch_id"];
        $order_id = Input::get("order_id");


        DB::table("table_management")
            ->where([
                "company_id" => $company_id,
                "branch_id" => $branch_id,
                "order_id" => intval($order_id),
            ])->update([
                "order_id" => null,
                "status" => "AVAILABLE",
            ]);
    }
}
