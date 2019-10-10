<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;

use DB;
use Input;
use Session;
use Carbon\Carbon;
use Ramsey\Uuid\Codec\OrderedTimeCodec;

class WaiterReportController extends Controller
{
    public function getMostSalesWaiter()
    {
        $orderDetail = DB::select("
            select *,(select count(qty) from order_detail od where od.product_id = p.product_id) as sales 
            from product p ORDER by sales DESC
        ");
        return $orderDetail;
    }

    public function getPerformanceReport(){
        $company_id = Session::get("current_user")["company_id"];
        $branch_id = Session::get("current_user")["branch"]["branch_id"];
        $date_from = Input::get("date_from") ?? Carbon::today();
        $date_to = Input::get("date_to") ?? Carbon::today();
        $period = Input::get("periode");
        
        $users = DB::table("users")
                    ->where("branch_id",$branch_id)
                    ->get();

        foreach($users as $user){
            $user_id = $user->id;

            $cashier_sales_total = DB::table("order")
                ->whereBetween("order.order_date", [$date_from, $date_to])
                ->where([
                    "order.payment_status" => "Paid",
                    "order.user_id" => $user_id,
                ])->sum("order.grand_total");

            $user->cashier_sales_total = $cashier_sales_total ?? 0;

            //GET WAITER SALES TOTAL
            $waiter_sales_total_plus = DB::table("order_detail")
                ->join("order","order.order_id","=","order_detail.order_id")
                ->whereBetween("order_detail.order_date", [$date_from, $date_to])
                ->where("order_detail.qty",">",0)
                ->where([
                    "order_detail.user_id" => $user_id,
                    "order.payment_status" => "Paid",
                ])->sum("order_detail.total");

            
            $waiter_sales_total_minus = DB::table("order_detail")
                ->join("order","order.order_id","=","order_detail.order_id")
                ->whereBetween("order_detail.order_date", [$date_from, $date_to])
                ->where("order_detail.qty","<",0)
                ->where([
                    "order_detail.user_id" => $user_id,
                    "order.payment_status" => "Paid",
                ])->sum("order_detail.total");

            $user_sales_count = DB::table("order_detail")
                ->join("order","order.order_id","=","order_detail.order_id")
                ->whereBetween("order_detail.order_date", [$date_from, $date_to])
                ->where("order_detail.qty","<",0)
                ->where([
                    "order_detail.user_id" => $user_id,
                    "order.payment_status" => "Paid",
                ])->sum("order_detail.qty");


                
            $user->waiter_order_total = $waiter_sales_total_plus ?? 0;
            $user->waiter_void_total = $waiter_sales_total_minus ?? 0;
            $user->waiter_final_order_total = ($waiter_sales_total_plus - $waiter_sales_total_minus) ?? 0;

            $user->sales_count = $user_sales_count;
          
        }

        
        $performance_report_by_sales_total = [];
        $performance_report_by_sales_count = [];

        $users = collect($users)->sortBy('waiter_final_order_total')->reverse()->toArray();
        foreach($users as $user){
            $performance_report_by_sales_total[] = [
                "col1" => "",
                "col2" => $user->name,
                "col3" => "",
                "col4" => $user->waiter_final_order_total,
            ];
        }

        $users = collect($users)->sortBy('sales_count')->reverse()->toArray();
        foreach($users as $user){
            $performance_report_by_sales_count[] = [
                "col1" => "",
                "col2" => $user->name,
                "col3" => "",
                "col4" => $user->sales_count,
            ];
        }

        return  [
            "data" => [
                    "report_name" => "Periode Report",
                    "period" => $period,
                    "report_items"=> [
                        [
                            "header"=> "Performance by Total Sales",
                            "table_name" => "Username",
                            "table_qty" => null,
                            "table_amount" => "Sales Total",
                            "items"=> $performance_report_by_sales_total,
                        ],
                        [
                            "header"=> "Performance by Sales Count",
                            "table_name" => "Username",
                            "table_qty" => null,
                            "table_amount" => "Sales Count",
                            "items"=> $performance_report_by_sales_count,
                        ],
                    ],
            ],
        ];
    }
}
