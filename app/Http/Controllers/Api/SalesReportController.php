<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;

use DB;
use Input;
use Session;
use Carbon\Carbon;
use Ramsey\Uuid\Codec\OrderedTimeCodec;

class SalesReportController extends Controller
{
    public function getMostSalesProduct()
    {
        $orderDetail = DB::select("
            select *,(select sum(qty) from order_detail od where od.product_id = p.product_id) as sales 
            from product p ORDER by sales DESC
        ");
        return $orderDetail;
    }

    public function getTotalSales()
    {
        $query = DB::table("order")
            ->select(DB::raw('SUM(order_detail.total) as sales_total'))
            ->join("order_detail", "order_detail.order_id", "=", "order.order_id")
            ->get();

        $sales_total = $query[0]->sales_total;

        $query = DB::table("order")
            ->select(DB::raw('SUM(order_detail.total) as sales_today'))
            ->join("order_detail", "order_detail.order_id", "=", "order.order_id")
            ->whereDate('order.order_date', Carbon::today())
            ->get();

        $sales_today = $query[0]->sales_today;

        $query = DB::table("order")
            ->select(DB::raw('SUM(order_detail.total) as sales_void'))
            ->join("order_detail", "order_detail.order_id", "=", "order.order_id")
            ->whereDate('order.order_date', Carbon::today())
            ->where("order.is_void", 1)
            ->get();

        $sales_void = $query[0]->sales_void;

        return [
            "sales_total" => $sales_total ?? 0,
            "sales_today" => $sales_today ?? 0,
            "sales_void" => $sales_void ?? 0,
        ];
    }

    public function GetUser($user_id)
    {
        $user = DB::table("users")
            ->where("id", $user_id)
            ->first();
        return $user;
    }

    public function GetMinMaxOrderDate($user_id)
    {
        $minmax = DB::table("order")
            ->where("user_id", $user_id)
            ->where("user_id", $user_id)
            ->first();
        return $minmax;
    }

    public function getSalesReport()
    {
        $branch_id = Input::get("branch_id");
        $date_now = Input::get("date_now");

        $query = "
        SELECT 
            DISTINCT user_id, 
            MIN(order_date) as first_trx, 
            MAX(order_date) as last_trx 
        FROM 
            `order` 
        WHERE 
            branch_id = $branch_id AND 
            payment_status = 'Paid' AND
            LEFT(order_date,10) = '$date_now'
        GROUP BY 
            user_id";
        // dd($query);

        $users = DB::select($query);

        // dd($users);
        foreach ($users as $user) {
            $user_id = $user->user_id;

            $user->user_name = $this->GetUser($user_id)->name;
            $order_details = DB::select("
            SELECT 
                SUM(customer_count) as customer_count, 
                SUM(tax_value) as tax_value, 
                SUM(service_charge_value) as service_charge_value, 
                SUM(total) as subtotal, 
                SUM(grand_total) as grand_total,
                COUNT(*) as order_total
            FROM `order` 
            WHERE user_id = $user_id");


            $order_items = DB::select("
            SELECT 
                p.product_id,
                pc.category_name,
                p.product_name,
                SUM(qty) as qty, 
                SUM(total) as total 
            FROM 
                order_detail od INNER JOIN 
                product p ON od.product_id = p.product_id INNER JOIN
                product_category pc ON p.category_id = pc.category_id
            WHERE 
                order_id IN 
                    (SELECT order_id FROM `order` WHERE user_id = $user_id AND payment_status = 'Paid') 
            GROUP BY 
                p.product_id, pc.category_name, p.product_name
            ORDER BY
                pc.category_name, p.product_name");

            // $order_items = DB::select("
            // SELECT 
            //     (SELECT product_name FROM product WHERE product_id = od.product_id) as product_name, 
            //     SUM(qty) as qty, 
            //     SUM(total) as total 
            // FROM 
            //     order_detail od 
            // WHERE 
            //     order_id IN 
            //         (SELECT order_id FROM `order` WHERE user_id = $user_id AND order_status <> 'Draft') 
            // GROUP BY 
            //     product_id");

            $payment_details = DB::select("
            SELECT payment_method, COUNT(*) total, SUM(payment_amount) amount 
            FROM order_payment_history 
            WHERE user_id = $user_id 
            GROUP BY payment_method");

            $query = "
            SELECT 
                pc.category_name,
                SUM(qty) as qty, 
                SUM(total) as total 
            FROM 
                order_detail od INNER JOIN 
                product p ON od.product_id = p.product_id INNER JOIN
                product_category pc ON p.category_id = pc.category_id
            WHERE 
                order_id IN 
                    (SELECT order_id FROM `order` WHERE user_id = $user_id AND payment_status = 'Paid') 
            GROUP BY 
                pc.category_name
            ORDER BY
                pc.category_name";
            
            // dd($query);
            $category_summary = DB::select($query);

            $user->order_details = $order_details;
            $user->order_items = $order_items;
            $user->category_summary = $category_summary;
            $user->payment_details = $payment_details;
        }

        $order_params = DB::select("
        SELECT 
            tax_name, tax_percent, service_charge_percent 
        FROM branch 
        WHERE 
            branch_id = $branch_id");

        return [
            "params" => $order_params,
            "data" => $users,
        ];
    }
}
