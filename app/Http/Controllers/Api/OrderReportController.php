<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;

use DB;
use Input;
use Hash;
use Session;
use Carbon;

// ! Session::get("current_user"); !
// array:8 [
//     "name" => "Owner Demo"
//     "email" => "admin@gmail.com"
//     "password" => "$2y$10$M.XwtPkL7Nj424kLyZJ/8efRV94G8XZ79osMFSyNzwUmPT5ZFPmcO"
//     "photo" => "http://localhost/sajiweb/public/uploads/1/2019-05/rsz_5b0aa9af7ee6a0c42f0c1f4283ca38fc.png"
//     "mobile" => "082146727409"
//     "privileges" => array:2 [
//       "id_cms_privileges" => 1
//       "name" => "Owner"
//     ]
//     "company_id" => 1
//     "branch" => array:3 [
//       "branch_id" => 1
//       "branch_name" => "Restaurant Admin"
//       "photo" => "http://localhost/sajiweb/public/uploads/1/2019-05/image_20190515_1630046869442476451516688.jpg"
//     ]
//   ]

class OrderReportController extends Controller
{

    public function getOrderReport(){
        $company_id = Session::get("current_user")["company_id"];
        $branch_id = Session::get("current_user")["branch"]["branch_id"];

        $total_sales = DB::table("order_detail")
                            ->join("order","order.order_id","=","order_detail.order_id")
                            ->select(DB::raw('SUM(order_detail.total) as total_sales'))
                            ->whereDate("order.order_date", Carbon::today())
                            ->where([
                                "order.company_id" => $company_id,
                                "order.branch_id" => $branch_id,
                                "order.payment_status" => "Paid",
                            ])->first()->total_sales;

        $total_order = DB::table("order")
                            ->whereDate("order_date", Carbon::today())
                            ->where([
                                "company_id" => $company_id,
                                "branch_id" => $branch_id,
                                "payment_status" => "Paid",
                            ])->count();
        
        $tax = DB::table("order")
                ->whereDate("order.order_date", Carbon::today())
                ->where([
                    "order.company_id" => $company_id,
                    "order.branch_id" => $branch_id,
                    "order.payment_status" => "Paid",
                ])->sum("tax_value");

        $serviceCharge = DB::table("order")
                ->whereDate("order.order_date", Carbon::today())
                ->where([
                    "order.company_id" => $company_id,
                    "order.branch_id" => $branch_id,
                    "order.payment_status" => "Paid",
                ])->sum("service_charge_value");

        //temporary
        $voucher = 0;
        $discount = 0;
        
        $grandTotal = $total_sales+$tax+$serviceCharge+$voucher+$discount; 

        return [
            "data" => [
                "total_sales" => $total_sales,
                "total_order" => $total_order,
                "grand_total" => $grandTotal,
            ],
        ];
    }

    public function getSummaryReport(){
        $company_id = Session::get("current_user")["company_id"];
        $branch_id = Session::get("current_user")["branch"]["branch_id"];
        $date_from = Input::get("date_from");
        $date_to = Input::get("date_to");
        $period = Input::get("periode") ?? "No Period Selected"; 
        
        $tax = DB::table("order")
                ->whereBetween("order.order_date", [$date_from, $date_to])
                ->where([
                    "order.company_id" => $company_id,
                    "order.branch_id" => $branch_id,
                    "order.payment_status" => "Paid",
                ])->sum("tax_value");

        $serviceCharge = DB::table("order")
                ->whereBetween("order.order_date", [$date_from, $date_to])
                ->where([
                    "order.company_id" => $company_id,
                    "order.branch_id" => $branch_id,
                    "order.payment_status" => "Paid",
                ])->sum("service_charge_value");

        //temporary
        $voucher = 0;
        $discount = 0;

        $total_sales = DB::table("order_detail")
                            ->join("order","order.order_id","=","order_detail.order_id")
                            ->select(DB::raw('SUM(order_detail.total) as total_sales'))
                            ->whereBetween("order.order_date", [$date_from, $date_to])
                            ->where([
                                "order.company_id" => $company_id,
                                "order.branch_id" => $branch_id,
                                "order.payment_status" => "Paid",
                            ])->first()->total_sales;

        $total_order = DB::table("order")
                            ->whereBetween("order.order_date", [$date_from, $date_to])
                            ->where([
                                "company_id" => $company_id,
                                "branch_id" => $branch_id,
                                "payment_status" => "Paid",
                            ])->count();

        $total_new = DB::table("order_detail")
                        ->join("order", "order.order_id", "=", "order_detail.order_id")
                        ->select(DB::raw('SUM(order_detail.qty) as total_new'))
                        ->whereBetween("order.order_date", [$date_from, $date_to])
                        ->where([
                            "order.company_id" => $company_id,
                            "order.branch_id" => $branch_id,
                            "order_detail.status" => "New Order",
                        ])->first()->total_new;
        
        $total_add = DB::table("order_detail")
                        ->join("order", "order.order_id", "=", "order_detail.order_id")
                        ->select(DB::raw('SUM(order_detail.qty) as total_add'))
                        ->whereBetween("order.order_date", [$date_from, $date_to])
                        ->where([
                            "order.company_id" => $company_id,
                            "order.branch_id" => $branch_id,
                            "order_detail.status" => "Add Order",
                        ])->first()->total_add;

        $total_void = DB::table("order_detail")
                        ->join("order", "order.order_id", "=", "order_detail.order_id")
                        ->select(DB::raw('SUM(order_detail.qty) as total_void'))
                        ->whereBetween("order.order_date", [$date_from, $date_to])
                        ->where([
                            "order.company_id" => $company_id,
                            "order.branch_id" => $branch_id,
                            "order_detail.status" => "Void Order",
                        ])->first()->total_void;

        $total_product = ($total_new+$total_add)-$total_void;

        $total_sales_dineIn = DB::table("order_detail")
                            ->join("order","order.order_id","=","order_detail.order_id")
                            ->select(DB::raw('SUM(order_detail.total) as total_sales_dineIn'))
                            ->whereBetween("order.order_date", [$date_from, $date_to])
                            ->where([
                                "order.company_id" => $company_id,
                                "order.branch_id" => $branch_id,
                                "order.order_type" => "Dine In",
                                "order.payment_status" => "Paid",
                            ])->first()->total_sales_dineIn;

        $total_dine_in = DB::table("order")
                            ->whereBetween("order.order_date", [$date_from, $date_to])
                            ->where([
                                "company_id" => $company_id,
                                "branch_id" => $branch_id,
                                "order_type" => "Dine In",
                                "payment_status" => "Paid",
                            ])->count();
        
        $total_sales_takeAway = DB::table("order_detail")
                                ->join("order","order.order_id","=","order_detail.order_id")
                                ->select(DB::raw('SUM(order_detail.total) as total_sales_takeAway'))
                                ->whereBetween("order.order_date", [$date_from, $date_to])
                                ->where([
                                    "order.company_id" => $company_id,
                                    "order.branch_id" => $branch_id,
                                    "order.order_type" => "Take Away",
                                    "order.payment_status" => "Paid",
                                ])->first()->total_sales_takeAway;

        $total_take_away = DB::table("order")
                            ->whereBetween("order.order_date", [$date_from, $date_to])
                            ->where([
                                "company_id" => $company_id,
                                "branch_id" => $branch_id,
                                "order_type" => "Take Away",
                                "payment_status" => "Paid",
                            ])->count();

        $total_sales_cash = DB::table("order_payment_history")
                                ->join("order","order.order_id","=","order_payment_history.order_id")
                                ->select(DB::raw('SUM(order.total) as total_sales_cash'))
                                ->whereBetween("order.order_date", [$date_from, $date_to])
                                ->where([
                                    "order.company_id" => $company_id,
                                    "order.branch_id" => $branch_id,
                                    "order_payment_history.payment_method" => "Cash",
                                    "order.payment_status" => "Paid",
                                ])->first()->total_sales_cash;

        $total_cash = DB::table("order_payment_history")
                            ->join("order","order.order_id","=","order_payment_history.order_id")
                            ->whereBetween("order.order_date", [$date_from, $date_to])
                            ->where([
                                "order.company_id" => $company_id,
                                "order.branch_id" => $branch_id,
                                "order.payment_status" => "Paid",
                                "order_payment_history.payment_method" => "Cash", 
                            ])->count();

        $total_sales_nonCash = DB::table("order_payment_history")
                                ->join("order","order.order_id","=","order_payment_history.order_id")
                                ->select(DB::raw('SUM(order.total) as total_sales_nonCash'))
                                ->whereBetween("order.order_date", [$date_from, $date_to])
                                ->where([
                                    "order.company_id" => $company_id,
                                    "order.branch_id" => $branch_id,
                                    "order.payment_status" => "Paid",
                                ])
                                ->where("order_payment_history.payment_method","<>","Cash")
                                ->first()->total_sales_nonCash;

        $total_non_cash = DB::table("order_payment_history")
                            ->join("order","order.order_id","=","order_payment_history.order_id")
                            ->whereBetween("order.order_date", [$date_from, $date_to])
                            ->where("order_payment_history.payment_method","<>","Cash")
                            ->where([
                                "order.company_id" => $company_id,
                                "order.branch_id" => $branch_id,
                                "order.payment_status" => "Paid",
                            ])->count();

        $grandTotal = $total_sales+$tax+$serviceCharge+$voucher+$discount; 

        return [
            "data" => [
                    "report_name" => "Period",
                    "period" => $period,
                    "report_items"=> [
                        [
                            "header"=> "Sales Total",
                            "table_name" => null,
                            "table_qty" => null,
                            "table_amount" => null,
                            "items"=> [
                                [
                                    "col1"=> "",
                                    "col2"=> "Grand Total",
                                    "col3"=> "",
                                    "col4"=> $grandTotal,
                                ],
                            ],
                        ],
                        [
                            "header"=> "Sales Detail",
                            "table_name" => "Payment Summary",
                            "table_qty" => "Qty",
                            "table_amount" => "Amount",
                            "items"=> [
                                [
                                    "col1"=> "",
                                    "col2"=> "Subtotal",
                                    "col3"=> "",
                                    "col4"=> $total_sales ?? 0,
                                ],
                                [
                                    "col1"=> "",
                                    "col2"=> "Tax",
                                    "col3"=> "",
                                    "col4"=> $tax,
                                ],
                                [
                                    "col1"=> "",
                                    "col2"=> "Service Charge",
                                    "col3"=> "",
                                    "col4"=> $serviceCharge,
                                ],
                                [
                                    "col1"=> "",
                                    "col2"=> "Voucher",
                                    "col3"=> "",
                                    "col4"=> $voucher,
                                ],
                                [
                                    "col1"=> "",
                                    "col2"=> "Discount",
                                    "col3"=> "",
                                    "col4"=> $discount,
                                ],
                            ],
                        ],
                        [
                            "header"=> "Order Summary",
                            "table_name" => "Order Summary",
                            "table_qty" => "Qty",
                            "table_amount" => "Amount",
                            "items"=> [
                                [
                                    "col1"=> $total_order ?? 0,
                                    "col2"=> "Total Order",
                                    "col3"=> "",
                                    "col4"=> $total_sales ?? 0,
                                ],
                            ],
                        ],
                        [
                            "header"=> "Total Orders per Order Type",
                            "table_name" => "Order Type",
                            "table_qty" => "Qty",
                            "table_amount" => "Amount",
                            "items"=> [
                                [
                                    "col1"=> $total_dine_in ?? 0,
                                    "col2"=> "Dine In",
                                    "col3"=> "",
                                    "col4"=> $total_sales_dineIn ?? 0,
                                ],
                                [
                                    "col1"=> $total_take_away ?? 0,
                                    "col2"=> "Take Away",
                                    "col3"=> "",
                                    "col4"=> $total_sales_takeAway ?? 0,
                                ],
                            ],
                        ],
                        [
                            "header"=> "Total Orders per Payment Type",
                            "table_name" => "Payment Type",
                            "table_qty" => "Qty",
                            "table_amount" => "Amount",
                            "items"=> [
                                [
                                    "col1"=> $total_cash ?? 0,
                                    "col2"=> "Cash",
                                    "col3"=> "",
                                    "col4"=> $total_sales_cash ?? 0,
                                ],
                                [
                                    "col1"=> $total_non_cash ?? 0,
                                    "col2"=> "Non Cash",
                                    "col3"=> "",
                                    "col4"=> $total_sales_nonCash ?? 0,
                                ],
                            ],
                        ],
                    ],
            ],
        ];
    }

    public function getSalesByProduct(){
        $branch_id = Input::get("branch_id");
        $date_from = Input::get("date_from") ?? Carbon::today();
        $date_to = Input::get("date_to") ?? Carbon::today();
        $period = Input::get("periode");
        
        // $getAll = Input::all();
        // dd($getAll);
        
        $query = "SELECT pc.category_name, p.product_name, od.price, SUM(od.qty) as qty, SUM(od.total) as total
                    FROM 
                        `order` o INNER JOIN
                        order_detail od ON o.order_id = od.order_id INNER JOIN
                        product p ON p.product_id = od.product_id INNER JOIN
                        product_category pc ON p.category_id = pc.category_id
                    WHERE
                        o.branch_id = $branch_id AND
                        o.order_status <> 'Draft' AND
                        o.payment_status = 'Paid' AND
                        o.order_date BETWEEN '$date_from' AND '$date_to'
                    GROUP BY
                        pc.category_name,
                        p.product_name, 
                        od.price";

        // $query = "SELECT pc.category_name, p.product_name, od.price, SUM(od.qty) as qty, SUM(od.total) as total
        //             FROM 
        //                 `order` o INNER JOIN
        //                 order_detail od ON o.order_id = od.order_id INNER JOIN
        //                 product p ON p.product_id = od.product_id INNER JOIN
        //                 product_category pc ON p.category_id = pc.category_id
        //             WHERE
        //                 o.branch_id = $branch_id AND
        //                 o.order_status <> 'Draft'
        //             GROUP BY
        //                 pc.category_name,
        //                 p.product_name, 
        //                 od.price";

        $product_report = DB::select($query);
            // ->where("is_counted_in_statistic",1)
            // ->where("branch_id",$branch_id)
            // ->get();

        return  [
            "data" => [
                    "report_name" => "Sales By Report",
                    "period" => $period,
                    "report_items"=> [
                        [
                            "header"=> "by Sales Total",
                            "table_name" => "Product Name",
                            "table_qty" => "Qty",
                            "table_amount" => "Sales Total",
                            "items"=> $product_report,
                        ],
                    ],
            ],
        ];
    }

    public function getProductBySales(){
        $company_id = Session::get("current_user")["company_id"];
        $branch_id = Session::get("current_user")["branch"]["branch_id"];



        $query = "SELECT *,
                    (SELECT sum(ord.total) 
                           FROM order_detail ord, `order` od
                           WHERE
                           DATE(od.order_date) = CURDATE() AND 
                           od.payment_status = 'Paid' AND
						   ord.product_id = p.product_id AND
                           od.order_id = ord.order_id AND
                           ord.total > 0)  as total,
                    (SELECT sum(ord.qty) 
                           FROM order_detail ord, `order` od
                           WHERE 
                           DATE(od.order_date) = CURDATE() AND
                           od.payment_status = 'Paid' AND
						   ord.product_id = p.product_id AND
                           od.order_id = ord.order_id AND
                           ord.qty > 0)  as qty      
                 FROM product p
                    WHERE 
                        p.company_id = $company_id AND
                        p.branch_id = $branch_id
                 ORDER BY qty desc LIMIT 3
								 ";

        $products = DB::select($query);
            // ->where("is_counted_in_statistic",1)
            // ->where("branch_id",$branch_id)
            // ->get();

        
        $sort_number = 1;
        foreach($products as $product){
            $product_id = $product->product_id;
            $total_sales = DB::select("select sum(ord.total) as total from order_detail ord, `order` od where od.payment_status = 'Paid' AND od.order_id = ord.order_id AND DATE(od.order_date) = CURDATE() AND  ord.product_id = $product_id")[0]->total;
            $quantity = DB::select("select sum(ord.qty) as qty from order_detail ord, `order` od where od.payment_status = 'Paid' AND od.order_id = ord.order_id AND DATE(od.order_date) = CURDATE() AND ord.product_id = $product_id")[0]->qty;
            $product->total = $product->total ?? 0;
            $product->total_sales = $total_sales ?? 0;
            $product->qty = $quantity ?? 0;
            $product->sort_number = $sort_number;
            $sort_number++;
        }
        return response()->json([
            "data" => $products
        ]);
    }

    public function getProductCategoryBySales(){
        $company_id = Session::get("current_user")["company_id"];
        $branch_id = Session::get("current_user")["branch"]["branch_id"];

        $query = "SELECT *,
                    (SELECT sum(ord.total) 
                           FROM order_detail ord, `order` od, product p
                           WHERE
                           DATE(od.order_date) = CURDATE() AND 
                           od.payment_status = 'Paid' AND
						   ord.product_id = p.product_id AND
                           p.category_id = pc.category_id AND
                           od.order_id = ord.order_id AND
                           ord.total > 0)  as total,
                    (SELECT sum(ord.qty) 
                           FROM order_detail ord, `order` od, product p
                           WHERE 
                           DATE(od.order_date) = CURDATE() AND
                           od.payment_status = 'Paid' AND
						   ord.product_id = p.product_id AND
                           p.category_id = pc.category_id AND
                           od.order_id = ord.order_id AND
                           ord.qty > 0)  as qty
                 FROM product_category pc
                    WHERE 
                        pc.company_id = $company_id AND
                        pc.branch_id = $branch_id";

        $productCategories = DB::select($query);
            // ->where("is_counted_in_statistic",1)
            // ->where("branch_id",$branch_id)
            // ->get();

        
        $sort_number = 1;
        foreach($productCategories as $productCategory){
            $category_id = $productCategory->category_id;
            $quantity = DB::select("select sum(ord.qty) as qty from order_detail ord, `order` od, product p where od.payment_status = 'Paid' AND  ord.product_id = p.product_id AND od.order_id = ord.order_id AND DATE(od.order_date) = CURDATE() AND p.category_id = $category_id")[0]->qty;
            $total_sales = DB::select("select sum(ord.total) as total from order_detail ord, `order` od, product p where od.payment_status = 'Paid' AND  ord.product_id = p.product_id AND od.order_id = ord.order_id AND DATE(od.order_date) = CURDATE() AND p.category_id = $category_id")[0]->total;
            $productCategory->qty = $quantity ?? 0;
            $productCategory->total_sales = $total_sales ?? 0;
            $productCategory->sort_number = $sort_number;
            $sort_number++;
        }
        return response()->json([
            "data" => $productCategories
        ]);
    }


    public function getBranchSummary(){
        $company_id = Session::get("current_user")["company_id"];
        $branch_id = Session::get("current_user")["branch"]["branch_id"];

        $total_sales = DB::table("order_detail")
                                ->join("order","order.order_id","=","order_detail.order_id")
                                ->select(DB::raw('SUM(order_detail.total) as total_sales'))
                                ->where([
                                    "order.company_id" => $company_id,
                                    "order.branch_id" => $branch_id,
                                    "order.order_date" => Carbon::today(),
                                ])->first()->total_sales;

        $total_customer = DB::table("order")
                            ->where([
                                "company_id" => $company_id,
                                "branch_id" => $branch_id,
                                "order_date" => Carbon::today()
                            ])->count();

        return [
            "data" => [
                "total_sales" => $total_sales ?? 0,
                "total_customer" => $total_customer ?? 0,
            ],
        ];

    }

    public function getSalesByCategory(){
        $company_id = Session::get("current_user")["company_id"];
        $branch_id = Session::get("current_user")["branch"]["branch_id"];

        $total_sales = DB::table("order_detail")
                                ->join("order","order.order_id","=","order_detail.order_id")
                                ->select(DB::raw('SUM(total) as total_sales'))
                                ->where([
                                    "order.company_id" => $company_id,
                                    "order.branch_id" => $branch_id,
                                    "order.order_date" => Carbon::today(),
                                ])->first()->total_sales;

        $total_customer = DB::table("order")
                            ->where([
                                "company_id" => $company_id,
                                "branch_id" => $branch_id,
                                "order_date" => Carbon::today()
                            ])->count();

        return [
            "total_sales" => $total_sales ?? 0,
            "total_customer" => $total_customer ?? 0,
        ];

    }
}
