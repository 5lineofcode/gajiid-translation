<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;


use Input;
use DB;
use Carbon\Carbon;

class TransferRequestsController extends Controller
{
    public function updateStatus()
    {
        $id = Input::get("id");
        $status = Input::get("status");
        $company_id = Input::get("company_id");
        $branch_id = Input::get("branch_id");
        $days_add = Input::get("days_add");
        $membership_level = Input::get("membership_plan");
        $updated_date = Input::get("updated_date");

        DB::table("transfer_requests")
            ->where("id", $id)
            ->update([
                "status" =>  $status,
                "updated_date" => $updated_date
            ]);


        if ($days_add > 0) {
            $expired_date = DB::table("branch")
                ->where("branch_id", $branch_id)
                ->first()
                ->expired_date;

            if (Carbon::parse($expired_date) > Carbon::parse(now())) {
                $new_expired_date = Carbon::parse($expired_date)->addDays($days_add);
            } else {
                $new_expired_date = Carbon::parse(now())->addDays($days_add);
            }

            // dd($new_expired_date);

            DB::table("branch")
                ->where("branch_id", $branch_id)
                ->update([
                    "expired_date" =>  $new_expired_date,
                    "membership_level" =>  $membership_level
                ]);

            DB::table("transfer_requests")
                ->where("id", $id)
                ->update([
                    "extend_date" => $new_expired_date
                ]);
        }

        $this->sendNotificationToMembers($company_id, $branch_id, "Membership Payment Status", "Your membership request has been " . $status . "!");

        return response()->json([
            "message" => "Update Status Success"
        ]);
    }

    public function getTransferList()
    {
        $branch_id = Input::get("branch_id");
        $user_level = Input::get("user_level");


        if ($user_level == "AOS Finance") {
            $query = DB::table("transfer_requests")
                ->where("status", "Pending")
                ->get();
        } else {
            $query = DB::table("transfer_requests")
                ->where("branch_id", $branch_id)
                ->orderBy("id", desc)
                ->get();
        }

        foreach ($query as $request) {
            $name = DB::table("branch")->where("branch_id", $request->branch_id)->first();
            $request->branch_name = $name->branch_name;

            $plan = DB::table("membership_plan")
                ->where("plan_id", $request->plan_id)
                ->get();

            $request->plan = $plan;
        }

        return [
            "error" => false,
            "data" => $query,
        ];
    }

    public function getPendingTransfer()
    {
        $branch_id = Input::get("branch_id");

        $list = DB::table("transfer_requests")
            ->where("branch_id", $branch_id)
            ->where("status", "Pending")
            ->get();

        if (count($list) > 0) {
            response()->json([
                "error" => true,
                "error_code" => "REQUEST_DENIED",
                "message" => "You still have a pending request\nCannot add more request",
            ])->send();
            exit;
        } else {
            response()->json([
                "error" => false,
                "error_code" => "REQUEST_ALLOWED",
                "message" => "Clear! Allowed to add more request",
            ])->send();
            exit;
        }
    }

    public function sendNotificationToFinance()
    {
        $message_title = Input::get("title");
        $message_text = Input::get("text");

        $users = DB::table("users")
            ->where("id_cms_privileges", "999")
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

        return [
            "error" => false,
            "message" => "Notification To Finance Sent",
        ];
    }

    public function sendNotificationToMembers($company_id, $branch_id, $message_title, $message_text)
    {
        // $company_id = Input::get("company_id");
        // $branch_id = Input::get("branch_id");
        
        // $message_title = Input::get("title");
        // $message_text = Input::get("text");

        $users = DB::table("users")
            ->whereRaw("(company_id = $company_id AND id_cms_privileges = 1) OR (branch_id = $branch_id AND id_cms_privileges = 2)")
            ->get();

        // dd($users);

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

        return [
            "error" => false,
            "message" => "Notification To Members Sent",
        ];
    }
}
