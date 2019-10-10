<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\ApiResponseController;
use App\Http\Controllers\Api\ApiBranchedController;
use App\Http\Controllers\Api\ApiHelperController;
use App\Http\Controllers\Api\TableManagementController;

use DB;
use Input;
use Session;
use Carbon\Carbon;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function GetPaymentHistory($order_id)
    {
        $payment_history = DB::table("order_payment_history")
            ->where("order_id", order_id)
            ->get();
        return $payment_history;
    }

    public function GetProduct($product_id)
    {
        $product = DB::table("product")
            ->where("product_id", $product_id)
            ->first();
        return $product;
    }

    public function GetUser($user_id)
    {
        $user = DB::table("users")
            ->where("id", $user_id)
            ->first();
        return $user;
    }

    public function GetBranch($branch_id)
    {
        $branch = DB::table("branch")
            ->where("branch_id", $branch_id)
            ->first();
        return $branch;
    }

    public function GetProductCategory($category_id)
    {
        $category = DB::table("product_category")
            ->where("category_id", $category_id)->first();
        return $category;
    }

    public function GetProductStation($product_id)
    {
        $product_stations = DB::table("product_assigned_station")
            ->where("product_id", $product_id)->get();
        return $product_stations;
    }

    public function GetOrderItems($order_id)
    {
        $order_detail_items = DB::table("order_detail")
            ->where("order_id", $order_id)
            ->get();

        foreach ($order_detail_items as $order_detail_item) {
            $product_id = $order_detail_item->product_id;
            
            array_push($this->productIdList,$product_id);

            $product = $this->GetProduct($product_id);

            //!Get Product Category
            $product->category = $this->GetProductCategory($product->category_id);

            //!Get Product Assigned Station
            $product->stations = $this->GetProductStation($product_id);

            //!Remove Unused Column
            unset($order_detail_item->product_id);

            $order_detail_item->product = $product;
        }
        return $order_detail_items;
    }

    public function GetOrderItemsSummary($order_id)
    {
        $order_detail_items = DB::table("order_detail")
            ->select(DB::raw('order_id, product_id, price, sum(qty) as qty, sum(total) as subtotal'))
            ->where("order_id", $order_id)
            ->groupBy('order_id', 'price', 'product_id')
            ->get();

        foreach ($order_detail_items as $order_detail_item) {
            $product_id = $order_detail_item->product_id;

            $product = $this->GetProduct($product_id);

            //!Get Product Category
            // $product->category = $this->GetProductCategory($product->category_id);

            //!Get Product Assigned Station
            // $product->stations = $this->GetProductStation($product_id);

            //!Remove Unused Column
            unset($order_detail_item->product_id);

            $order_detail_item->product = $product;
        }
        return $order_detail_items;
    }


    public function GetQtyFromOrderItems($product_id){
        $order_items = $this->order_items;
        
        foreach($order_items as $order_item){
            
            if($order_item->product->product_id == $product_id){
                return $order_item->qty;
                break;
            }
            
        }
        
        echo "Invalid Qty for Product ID : $product_id, CONTACT DEVELOPER!";
        exit;
    }
    
    //! Assigned Product ID in station
    public function GetProductItemInStation($station_id, $order_id)
    {
        $product_assigned_stations = DB::table("product_assigned_station")
            ->where("station_id", $station_id)
            ->get();

        // return $product_assigned_stations;
        foreach ($product_assigned_stations as $product_assigned_station) {
            $product_id = $product_assigned_station->product_id;
            
            $product_assigned_station->order_items = $this->GetPrintKitchenData($product_id, $order_id);
            $product_assigned_station->product_details = $this->GetProduct($product_id);
            
            // return $order_items;
            // if($order_items->order_index == $this->max_order_index){
            //     $product = $this->GetProduct($product_id);
            //     $product->qty = $this->GetQtyFromOrderItems($product_id);
                
            //     $arr[] = $product;
            // }
           
        }
        return $product_assigned_stations;
    }

    public function GetOrderItemsAllStation($order_id)
    {
        $strQuery = "SELECT * FROM order_detail WHERE order_id = $order_id AND order_index = (select MAX(order_index) as order_index from order_detail where order_id = $order_id AND 'status' <> 'Void Order')";
        $orders = DB::select($strQuery);
        
        foreach ($orders as $order) {
            $product = $this->GetProduct($order->product_id);

            $order->product = $product;
        }
        // return $strQuery;
        return $orders;
    }

    //! Station Name
    public function GetOrderItemsByStation($order_items)
    {
        $query = DB::table("product_station");
        ApiBranchedController::useBranchedQuery($query, "product_station");
        $product_stations = $query->get();

        foreach ($order_items as $order_item) { 
            $order_id = $order_item->order_id;

            foreach ($product_stations as $product_station) {
                $product_station->items = $this->GetProductItemInStation($product_station->id, $order_id);   
            }
        }

        return $product_stations;
    }

    public function OnTable($params)
    {
        $company_id = Session::get("current_user")["company_id"];
        $branch_id = Session::get("current_user")["branch"]["branch_id"];

        $where = $params["where"];
        $sort_field = $params["sort_field"] ?? $params["primary_key"];
        $sort_order = $params["sort_order"] ?? "asc";
        $page_count = $params["page_count"] ?? 10;

        $query = DB::table("order");
        $query = $query->where("total", "<>", null);

        if (count($params["where"]) > 0) {
            $query = $query->where($params["where"]);
        }
        

        ApiBranchedController::useBranchedQuery($query, "order");
        $query = $query->orderBy($sort_field, $sort_order);

        if ($page_count == null) {
            $page_count = 0;
        }

        if ($page_count == 0) {
            $paginator = $query->paginate();
        } else {
            $paginator = $query->paginate($page_count);
        }

        
        
        
        $this->productIdList = [];
        
        $paginator->getCollection()->transform(function ($item) {

            $item->payment_history = $this->GetPaymentHistory($item->order_id);

            $item->branch = $this->GetBranch($item->branch_id);
            $item->user = $this->GetUser($item->user_id);

            $item->order_items = $this->GetOrderItems($item->order_id);
            
                                               
           
           $order_id = $item->order_id;
                                               
           //! Get Max Order Index
           $this->max_order_index = DB::select("select MAX(order_index) as order_index from order_detail where order_id = $order_id AND 'status' <> 'Void Order'")[0]->order_index;
           
            //! variable ini nantinya akan digunakan untuk mendapatkan qty produk
            $this->order_items = $item->order_items;
                                               
            $item->order_items_summary = $this->GetOrderItemsSummary($item->order_id);
            $item->order_items_all_station = $this->GetOrderItemsAllStation($item->order_id);
            $item->order_items_by_station = $this->GetOrderItemsByStation($item->order_items);

            return $item;
        });

        return $paginator;
    }

    public function OnCreate($params)
    {
        $company_id = Session::get("current_user")["company_id"];
        $branch_id = Session::get("current_user")["branch"]["branch_id"];
        $date_now = Input::get("date_now");

        $postdata = [
            "company_id" => $company_id,
            "branch_id" => $branch_id,
            "customer_name" => "",
            "memo" => null,
            // "order_date" => Carbon::parse(now())->addHour(7),
            "order_date" => $date_now,
            "order_status" => "Draft",
        ];

        $query = DB::table("order");

        $is_success = $query->insert($postdata);

        return ApiResponseController::createResponse($is_success);
    }


    public function getQueueNumber()
    {
        $company_id = Session::get("current_user")["company_id"];
        $branch_id = Session::get("current_user")["branch"]["branch_id"];

        $orderCount = DB::table("order")
            ->where('order_date', '>=', Carbon::today())
            ->where("order_status", "<>", "Draft")
            ->where("company_id", $company_id)
            ->where("branch_id", $branch_id)
            ->count();

        return $orderCount + 1;
    }

    public function getTakeAwayNumber()
    {
        $company_id = Session::get("current_user")["company_id"];
        $branch_id = Session::get("current_user")["branch"]["branch_id"];

        $orderCount = DB::table("order")
            ->where('order_date', '>=', Carbon::today())
            ->where("order_status", "<>", "Draft")
            ->where("order_type", "Take Away")
            ->where("company_id", $company_id)
            ->where("branch_id", $branch_id)
            ->count();

        return $orderCount + 1;
    }

    public function sendNotificationToAll($message_title, $message_text)
    {
        $users = DB::table("users")
            ->where("company_id", $company_id)
            ->where("branch_id", $branch_id)
            ->get();

        foreach ($users as $user) {
            $fcm_token = $user->fcm_token;

            if (strlen($fcm_token) > 0) {
                $status =  \App\Library\FirebaseHelper::sendNotification($message_title, $message_text, $fcm_token);

                if ($status == 1) {
                    DB::table("firebase_notification_logs")
                        ->insert([
                            "firebase_notification_id" => $id,
                            "date" => now(),
                            "message" => "SUCCESS - " . $user->id . " > " . $user->email
                        ]);
                } else if ($status == 0) {
                    DB::table("firebase_notification_logs")
                        ->insert([
                            "firebase_notification_id" => $id,
                            "date" => now(),
                            "message" => "ERROR - Invalid FCM Token @id -> " . $user->id
                        ]);
                }
            }
        }
    }

    public function sendNotificationToKitchen($message_title, $message_text)
    {
        $company_id = Session::get("current_user")["company_id"];
        $branch_id = Session::get("current_user")["branch"]["branch_id"];

        $users = DB::table("users")
            ->where("company_id", $company_id)
            ->where("branch_id", $branch_id)
            ->where("id_cms_privileges", "4")
            ->get();

        foreach ($users as $user) {
            $fcm_token = $user->fcm_token;

            if (strlen($fcm_token) > 0) {
                $status =  \App\Library\FirebaseHelper::sendNotification($message_title, $message_text, $fcm_token);

                if ($status == 1) {
                    DB::table("firebase_notification_logs")
                        ->insert([
                            "firebase_notification_id" => $id,
                            "date" => now(),
                            "message" => "SUCCESS - " . $user->id . " > " . $user->email
                        ]);
                } else if ($status == 0) {
                    DB::table("firebase_notification_logs")
                        ->insert([
                            "firebase_notification_id" => $id,
                            "date" => now(),
                            "message" => "ERROR - Invalid FCM Token @id -> " . $user->id
                        ]);
                }
            }
        }
    }

    public function sendNotificationToWaiter($message_title, $message_text)
    {
        $company_id = Session::get("current_user")["company_id"];
        $branch_id = Session::get("current_user")["branch"]["branch_id"];

        $users = DB::table("users")
            ->where("company_id", $company_id)
            ->where("branch_id", $branch_id)
            ->where("id_cms_privileges", "3")
            ->get();

        foreach ($users as $user) {
            $fcm_token = $user->fcm_token;

            if (strlen($fcm_token) > 0) {
                $status =  \App\Library\FirebaseHelper::sendNotification($message_title, $message_text, $fcm_token);

                if ($status == 1) {
                    DB::table("firebase_notification_logs")
                        ->insert([
                            "firebase_notification_id" => $id,
                            "date" => now(),
                            "message" => "SUCCESS - " . $user->id . " > " . $user->email
                        ]);
                } else if ($status == 0) {
                    DB::table("firebase_notification_logs")
                        ->insert([
                            "firebase_notification_id" => $id,
                            "date" => now(),
                            "message" => "ERROR - Invalid FCM Token @id -> " . $user->id
                        ]);
                }
            }
        }
    }

    public function sendNotificationTimeUp($message_title, $message_text)
    {
        $company_id = Session::get("current_user")["company_id"];
        $branch_id = Session::get("current_user")["branch"]["branch_id"];

        $users = DB::table("users")
            ->where("company_id", $company_id)
            ->where("branch_id", $branch_id)
            ->where("id_cms_privileges", ["3", "4"])
            ->get();

        foreach ($users as $user) {
            $fcm_token = $user->fcm_token;

            if (strlen($fcm_token) > 0) {
                $status =  \App\Library\FirebaseHelper::sendNotification($message_title, $message_text, $fcm_token);

                if ($status == 1) {
                    DB::table("firebase_notification_logs")
                        ->insert([
                            "firebase_notification_id" => $id,
                            "date" => now(),
                            "message" => "SUCCESS - " . $user->id . " > " . $user->email
                        ]);
                } else if ($status == 0) {
                    DB::table("firebase_notification_logs")
                        ->insert([
                            "firebase_notification_id" => $id,
                            "date" => now(),
                            "message" => "ERROR - Invalid FCM Token @id -> " . $user->id
                        ]);
                }
            }
        }
    }

    public function create()
    {
        $company_id = Session::get("current_user")["company_id"];
        $branch_id = Session::get("current_user")["branch"]["branch_id"];
        $user_id = Session::get("current_user")["id"];
        $use_stock_management = Session::get("current_user")["branch"]["use_stock_management"];

        $items = json_decode(Input::get("items"));
        $order_type = Input::get("order_type");
        $customer_name = Input::get("customer_name");
        $order_id = Input::get("order_id");
        $order_type = Input::get("order_type");
        $order_table = Input::get("order_table");
        $pos_mode = Input::get("pos_mode");
        $customer_count = Input::get("customer_count");

        DB::beginTransaction();

        $grand_total = 0;
        $sub_total = 0;

        $orderIndex = DB::select("select COALESCE(order_index, 0) as order_index from order_detail where order_id = $order_id")[0]->order_index + 1;


        $errorMessage = "";
        foreach ($items as $item) {
            $product_id = intval($item->product_id);
            $qty = intval($item->qty);
            $price = intval($item->price);

            $total = intval($qty) * intval($price);
            $grand_total += $total;

            $this->sub_total += $total;

            $memo = $item->memo;


            //TODO: Check if User use Stock Management
            if($use_stock_management==1){
                //TODO: Check if Stock Available
                $product = DB::table("product")
                    ->where("product_id",$product_id)
                    ->first();

                if($product->stock - $qty < 0){
                    $errorMessage .= $product->product_name . " \nCurrentStock: " . $product->stock . "\n\n";
                }
                else {
                    DB::table("product")
                        ->where("product_id",$product_id)
                        ->update([
                            "stock" => DB::raw("stock - $qty"),
                        ]);
                }
            }

            DB::table("order_detail")
                ->insert([
                    "company_id" => $company_id,
                    "branch_id" => $branch_id,
                    "user_id" => $user_id,
                    "order_id" => $order_id,
                    "product_id" => $product_id,
                    "qty" => $qty,
                    "price" => $price,
                    "total" => $total,
                    "memo" => $memo,
                    "status" => $pos_mode,
                    "progress_status" => "Pending",
                    "order_index" => $orderIndex,
                    "order_date" => Carbon::parse(now())->addHour(7),
                ]);
        }

        if($errorMessage!=""){
            DB::rollBack();

            $errorMessage =  "Not Enough Stock:\n---\n" . $errorMessage;
            return [
                "error" => true,
                "message" => $errorMessage,
            ];
        }

        //Get Tax & Service Percent From Branch
        $branch = DB::table("branch")->where("branch_id", $branch_id)->first();
        $tax_name = $branch->tax_name;
        $tax_percent = $branch->tax_percent;
        $service_charge_percent = $branch->service_charge_percent;

        $sub_total = DB::select("select sum(qty*price) as total from order_detail where order_id = $order_id")[0]->total;
        $tax_value = ($tax_percent * $sub_total) / 100;
        $service_charge_value  = ($service_charge_percent * $sub_total) / 100;

        $calculated_tax_total = $tax_value + $service_charge_value;
        $grand_total = $sub_total + $calculated_tax_total;

        //add postbranched here
        //TODO: Create PO Number
        $postdata = [
            "company_id" => $company_id,
            "branch_id" => $branch_id,
            "user_id" => $user_id,
            "customer_name" => $customer_name,
            "memo" => null,
            "order_status" => "New Order",
            "order_table" => $order_table,
            "order_type" => $order_type,
            "payment_status" => "Unpaid",
            "tax_name" => $tax_name,
            "tax_percent" => $tax_percent,
            "tax_value" => $tax_value,
            "service_charge_percent" => $service_charge_percent,
            "service_charge_value" => $service_charge_value,
            "total" => $sub_total,
            "grand_total" => $grand_total,
            "customer_count" => $customer_count,
        ];

        //Lock Table Management


        $order_id = intval($order_id);



        if ($order_type == "Dine In") {
            DB::table("table_management")
                ->where("company_id", $company_id)
                ->where("branch_id", $branch_id)
                ->whereIn("table_number", explode(",", $order_table))
                ->update([
                    "order_id" => $order_id,
                    "status" => "USED"
                ]);
        }

        if ($pos_mode == "New Order") {
            $queue_number = $this->getQueueNumber();
            $queue_number = 1000 + $queue_number; 
            $queue_number = Str::substr($queue_number, 1, 3);
            
            $po_number = Carbon::now()->format('ymd');
            $po_number = $po_number."-".$queue_number;
    
            $postdata["queue_number"] = $queue_number;
            $postdata["takeaway_number"] = $this->getTakeAwayNumber();
            $postdata["po_number"] = $po_number;
        }

        DB::table("order")
            ->where("order_id", $order_id)
            ->update($postdata);



        $this->sendNotificationToKitchen("New Order", "Queue Number " . $postdata["order_id"]);

        $postdata["order_id"] = $order_id;
        \App\Library\FirebaseHelper::saveData("order", $postdata);


        DB::commit();

        return response()->json([
            "error" => false,
            "message" => "Create Order Success",
        ]);
    }
    //api/custom/order/cobaNotif

    public function cobaNotif()
    {
        $this->sendNotificationToKitchen("New Order", "Tes!");
        return [
            "error" => false,
            "message" => "Mantap Bos!",
        ];
    }

    public function notifToWaiter()
    {
        $this->sendNotificationToWaiter("Order Ready", "an Order Ready to Serve!");
        return [
            "error" => false,
            "message" => "Mantap Bos!",
        ];
    }

    public function notifTimeUp()
    {
        $this->sendNotificationTimeUp("Incomplete Order", "Some order are taking too long process time!");
        return [
            "error" => false,
            "message" => "Mantap Bos!",
        ];
    }

    public function processPayment()
    {
        $company_id = Session::get("current_user")["company_id"];
        $branch_id = Session::get("current_user")["branch"]["branch_id"];
        $user_id = Session::get("current_user")["id"];

        $payment_method = Input::get("payment_method");
        $payment_reference_code = Input::get("payment_reference_code");
        $payment_amount = Input::get("payment_amount");
        $payment_datetime = Input::get("payment_datetime");
        $change = Input::get("change");

        $order_id = Input::get("order_id");

        DB::beginTransaction();
        DB::table("order")
            ->where("order_id", $order_id)
            ->update([
                // "order_status" => "UnPaid",
                "payment_status" => "Paid",
            ]);

        //TODO: Implement Payment Number
        $is_success = DB::table("order_payment_history")
            ->insert([
                "company_id" => $company_id,
                "branch_id" => $branch_id,
                "user_id" => $user_id,
                "order_id" => $order_id,
                "payment_number" => uniqid(),
                "payment_method" => $payment_method,
                "payment_reference_code" => $payment_reference_code,
                "payment_amount" => $payment_amount,
                "change" => $change,
                "payment_datetime" => $payment_datetime,
                // "payment_datetime" => Carbon::parse(now())->addHour(7),

            ]);

        //Release Table 
        TableManagementController::ClearTableByOrderId();

        DB::commit();

        return [
            "error" => !boolVal($is_success),
            "message" => "Create Order Payment Success",
        ];
    }


    public function GetPrintKitchenData($product_id, $order_id)
    {
        $strQuery = "SELECT * FROM order_detail WHERE COALESCE(order_index,0) <> 0 AND product_id = $product_id AND order_id = $order_id AND order_index = (SELECT MAX(order_index) FROM order_detail WHERE product_id = $product_id AND order_id = $order_id AND status <> 'Void Order')";
        $query = DB::select($strQuery);

        // return $strQuery;
        return $query;
        // return [
        //     "error" => "false",
        //     "data" => $query,
        // ];
    }


    public function OnGetSingleData($params)
    {        
        $query = DB::table("order");
        if (count($params["where"]) > 0) {
            $query = $query->where($params["where"]);
        }
        $query = $query->where("order_id", $params["id"]);
        ApiBranchedController::useBranchedQuery($query, "order");

        $item = $query->first();
        // dd($item);

        $branch = DB::table("branch")->where("branch_id", $item->branch_id)->first();
        $item->branch = [
            "branch_id" => $branch->branch_id,
            "branch_name" => $branch->branch_name,
        ];


        unset($item->branch_id);

        return response()->json($item);
    }


    public function updateProgressStatus()
    {
        $order_detail_id = Input::get("order_detail_id");
        $progress_status = Input::get("progress_status");

        $is_success = DB::table("order_detail")
            ->where("order_detail_id", $order_detail_id)
            ->update([
                "progress_status" => $progress_status,
            ]);

        return [
            "error" => boolval($is_success),
            "message" => "Success",
        ];
    }
}
