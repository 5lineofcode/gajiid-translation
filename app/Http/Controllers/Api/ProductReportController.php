<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;

use DB;
use Input;
use Session;
use Carbon\Carbon;
use Ramsey\Uuid\Codec\OrderedTimeCodec;

class ProductReportController extends Controller
{
    public function getProductReport(){
        $company_id = Session::get("current_user")["company_id"];
        $branch_id = Session::get("current_user")["branch"]["branch_id"];
        $date_from = Input::get("date_from") ?? Carbon::today();
        $date_to = Input::get("date_to") ?? Carbon::today();
        $period = Input::get("periode");
        
        $products = DB::table("product")
                    ->where("branch_id",$branch_id)
                    ->get();

        foreach($products as $product){
            $product_id = $product->product_id;

            
            //GET WAITER SALES TOTAL
            $waiter_sales_total_plus = DB::table("order_detail")
                ->join("order","order.order_id","=","order_detail.order_id")
                ->whereBetween("order_detail.order_date", [$date_from, $date_to])
                ->where("order_detail.qty",">",0)
                ->where([
                    "order_detail.product_id" => $product_id,
                    "order.payment_status" => "Paid",
                ])->sum("order_detail.total");

            
            $waiter_sales_total_minus = DB::table("order_detail")
                ->join("order","order.order_id","=","order_detail.order_id")
                ->whereBetween("order_detail.order_date", [$date_from, $date_to])
                ->where("order_detail.qty","<",0)
                ->where([
                    "order_detail.product_id" => $product_id,
                    "order.payment_status" => "Paid",
                ])->sum("order_detail.total");

            $user_sales_count = DB::table("order_detail")
                ->join("order","order.order_id","=","order_detail.order_id")
                ->whereBetween("order_detail.order_date", [$date_from, $date_to])
                ->where("order_detail.qty","<",0)
                ->where([
                    "order_detail.product_id" => $product_id,
                    "order.payment_status" => "Paid",
                ])->sum("order_detail.qty");
                
            $product->waiter_order_total = $waiter_sales_total_plus ?? 0;
            $product->waiter_void_total = $waiter_sales_total_minus ?? 0;
            $product->waiter_final_order_total = ($waiter_sales_total_plus - $waiter_sales_total_minus) ?? 0;
            $product->sales_count = $user_sales_count;
        }

        
        $product_report_by_sales_total = [];
        $product_report_by_sales_count = [];
        $products = collect($products)->sortBy('waiter_final_order_total')->reverse()->toArray();

        foreach($products as $product){
            $product_report_by_sales_total[] = [
                "col1" => "",
                "col2" => $product->product_name,
                "col3" => "",
                "col4" => $product->waiter_final_order_total,
            ];
        }

        $products = collect($products)->sortBy('sales_count')->reverse()->toArray();
        foreach($products as $product){
            $product_report_by_sales_count[] = [
                "col1" => "",
                "col2" => $product->product_name,
                "col3" => "",
                "col4" => $product->sales_count,
            ];
        }

        return  [
            "data" => [
                    "report_name" => "Periode",
                    "period" => $period,
                    "report_items"=> [
                        [
                            "header"=> "by Sales Total",
                            "table_name" => "Product",
                            "table_qty" => null,
                            "table_amount" => "Sales Total",
                            "items"=> $product_report_by_sales_total,
                        ],
                        [
                            "header"=> "by Sales Item Count",
                            "table_name" => "Product",
                            "table_qty" => null,
                            "table_amount" => "Sales Count",
                            "items"=> $product_report_by_sales_count,
                        ],
                    ],
            ],
        ];
    }
}
