<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\TableGroupController;
use Illuminate\Support\Facades\Redirect;


use DB;
use Input;
use Hash;
use Session;
use Mail;
use Carbon;

class AuthController extends Controller
{
    public function login()
    {
        $email = Input::get("email");
        $password = Input::get("password");

        $user = DB::table("users")
            ->where("email", $email)
            ->first();

        // //developer bypass password
        // if ($password != "101") {
        //     if (!Hash::check($password, $user->password)) {
        //         return [
        //             "error" => true,
        //             "message" => "Invalid Email or Password"
        //         ];
        //     }
        // }

        // dd($user);

        if (!Hash::check($password, $user->password)) {
            return [
                "error" => true,
                "message" => "Invalid Email or Password"
            ];
        }

        if ($user == null) {
            return [
                "error" => true,
                "message" => "This account is not registered, please Register" . $user->email
            ];
        }

        if ($user->status == 0) {
            return [
                "error" => true,
                "message" => "This account is not activated, please Verify On " . $user->email
            ];
        }

        $userData = $this->getUserDataByUser($user);

        return [
            "error" => false,
            "data" => $userData,
            "message" => "Login Success"
        ];
    }

    public function getUserDataByUser($user)
    {
        $privilege = DB::table("cms_privileges")
            ->where("id", $user->id_cms_privileges)
            ->first();

        $branch = DB::table("branch")
            ->where("branch_id", $user->branch_id)
            ->first();

        $version = DB::table("app_parameters")
            ->where("param_key", "Version")
            ->first()->param_value;

        $userData = [
            "id" => $user->id,
            "name" => $user->name,
            "email" => $user->email,
            "password" => $user->password,
            "photo" => $user->photo,
            "mobile" => $user->mobile,
            "user_level" => $user->user_level,
            "privileges" => [
                "id_cms_privileges" => $user->id_cms_privileges,
                "name" => $privilege->name,
            ],
            "company_id" => $user->company_id,
            "alert_change_password" => $user->alert_change_password,
            "branch" =>  json_decode(json_encode($branch), true),
            "version" =>  $version,
            "server_date" => Carbon::now()->toDateString(),
        ];

        Session::put("current_user", $userData);
        return $userData;
    }

    public function updateToken()
    {
        $email = Input::get("email");
        $fcm_token = Input::get("fcm_token");

        $is_success = DB::table("users")
            ->where("email", $email)
            ->update([
                "fcm_token" =>  $fcm_token
            ]);

        return [
            "error" => false,
            "message" => "Token Update Success"
        ];
    }

    public function completeWizard()
    {
        $branch_id = Session::get("current_user")["branch"]["branch_id"];

        $is_success = DB::table("branch")
            ->where("branch_id", $branch_id)
            ->update([
                "is_complete_wizard" =>  1
            ]);

        return [
            "error" => false,
            "message" => "Outlet Wizard Complete"
        ];
    }

    public function getUserData()
    {
        if (Session::get('current_user') == null) {
            return response()->json([
                "error" => "NOT_LOGGED_IN",
                "message" => "Please, login first!",
            ]);
        }

        return response()->json([
            "users" => Session::get("current_user")
        ]);
    }

    public function logout()
    {
        Session::flush();
        $id = Input::get("id");

        $is_success = DB::table("users")
            ->where("id", $id)
            ->update([
                "fcm_token" =>  ""
            ]);

        // return [
        //     "error" => false,
        //     "message" => "Toke Update Success"
        // ];
        return response()->json([
            "message" => "Logout Success"
        ]);
    }

    public function changePassword()
    {
        $id = Input::get("id");
        $password = Input::get("password");
        // dd($id);

        $is_success = DB::table("users")
            ->where("id", $id)
            ->update([
                "password" =>  Hash::make($password),
                "alert_change_password" =>  1
            ]);

        return response()->json([
            "message" => "Change Password Success"
        ]);
    }

    public function sendResetPassMail($password)
    {
        $to_name = Input::get("email");
        $to_email = Input::get("email");

        $data = array(
            "email" => $to_email,
            "password" => $password,
            "email_sha1" => Hash::make($to_email),
            "password_sha1" => Hash::make($password),
        );

        $password = "nih password";

        //emails.mail = folder : emails, blade : mail
        //emails.reset = folder : emails, blade : reset
        Mail::send('emails.resetpass', $data, function ($message) use ($to_name, $to_email) {
            $message->to($to_email, $to_name)
                ->subject('Saji, Reset Password');
            $message->from('notifikasi-saji@afe.co.id', 'Saji');
        });
    }

    public function sendTestEmail()
    {
        $to_name = Input::get("email");
        $to_email = Input::get("email");

        $data = array(
            "email" => $to_email,
            "email_sha1" => Hash::make($to_email),
        );

        Mail::send('emails.test', $data, function ($message) use ($to_name, $to_email) {
            $message->to($to_email, $to_name)
                ->subject('Saji, Test Email');
            $message->from('notifikasi-saji@afe.co.id', 'Saji');
        });

        return [
            "error" => false,
            "message" => "Send test email success"
        ];
    }



    public function checkEmailExists($email)
    {
        $user = DB::table("users")
            ->where("email", $email)
            ->first();

        if (count($user) <= 0) {
            response()->json([
                "error" => true,
                "error_code" => "Email not found",
                "message" => "No email found in database",
            ])->send();

            exit;
        }
    }

    public function forgotPassword()
    {
        $email = Input::get("email");
        $password = mt_rand(100000, 999999);

        $this->checkEmailExists($email);
        // dd($id);

        $is_success = DB::table("users")
            ->where("email", $email)
            ->update([
                "password" =>  Hash::make($password)
            ]);

        $this->sendResetPassMail($password);

        return response()->json([
            "error" => false,
            "message" => "Reset Password Success"
        ]);
    }

    public function isEmailExists($email)
    {
        $user = DB::table("users")
            ->where("email", $email)
            ->first();

        if (count($user) > 0) {

            if ($user->status == 1) {
                response()->json([
                    "error" => true,
                    "error_code" => "EMAIL_IS_EXISTS",
                    "message" => "This email has been used, please use another email",
                ])->send();
            } else {
                response()->json([
                    "error" => true,
                    "error_code" => "EMAIL_USED_AND_NOT_VERIFIED",
                    "message" => "This email has been used, but not verified. Please verify it on your email!",
                ])->send();
            }
            exit;
        }
    }

    public function getRandomUserPhoto()
    {
        $folder = opendir(storage_path("app/uploads/default-photo/users"));
        $images = [];
        $i = 0;
        while (false != ($file = readdir($folder))) {
            if ($file != "." && $file != "..") {
                $images[$i] = $file;
                $i++;
            }
        }

        $random_img = rand(0, count($images) - 1);
        return "uploads/default-photo/users/" . $images[$random_img];
    }

    public function getRandomBranchPhoto()
    {
        $folder = opendir(storage_path("app/uploads/default-photo/branch"));
        $images = [];
        $i = 0;
        while (false != ($file = readdir($folder))) {
            if ($file != "." && $file != "..") {
                $images[$i] = $file;
                $i++;
            }
        }

        $random_img = rand(0, count($images) - 1);
        return "uploads/default-photo/branch/" . $images[$random_img];
    }


    public function registerUserAndGetUserId($password, $verification_code)
    {
        $name = Input::Get("name");
        $email = Input::get("email");
        $mobile = Input::get("mobile");

        $this->isEmailExists($email);

        DB::table("users")
            ->insert([
                "name" => $name,
                "email" => $email,
                "password" => Hash::make($password),
                "id_cms_privileges" => 7,
                "user_level" => "Owner",
                "status" => 0,
                "alert_change_password" => 0,
                "mobile" => $mobile,
                "verification_code" => $verification_code,
                "photo" => $this->getRandomUserPhoto(),
            ]);

        return DB::getPdo()->lastInsertId();
    }

    public function registerCompanyAndGetCompanyId($user_id)
    {
        DB::table("company")
            ->insert([
                "owner_id" => $user_id,
                "is_active" => 1,
            ]);

        return DB::getPdo()->lastInsertId();
    }

    public function updateUserCompanyAndBranchId($user_id, $company_id, $branch_id)
    {
        DB::table("users")
            ->where("id", $user_id)
            ->update([
                "company_id" => $company_id,
                "branch_id" => $branch_id,
            ]);

        $user = DB::table("users")
            ->where("id", $user_id)
            ->first();

        return $user;
    }

    public function createDefaultBranch($company_id, $user_id)
    {
        // $branch_name = explode(" ",Input::get("name"))[0] . "'s Outlet";
        $branch_name = Input::get("name") . "'s Outlet";

        DB::table("branch")
            ->insert([
                "branch_name" => $branch_name,
                "photo" => $this->getRandomBranchPhoto(),
                "company_id" => $company_id,
                "owner_id" => $user_id,
                "mobile" => Input::get("mobile"),
                "email" => Input::get("email"),
                "address" => "-",
                "tax_name" => "Ppn",
                "tax_percent" => 0,
                "service_charge_percent" => 0,
                "membership_level" => "Trial",
                "expired_date" => Carbon::now()->addDays(30),
                "gopay_qr_code" => "",
                "ovo_qr_code" => "",
                "dana_qr_code" => "",
                "is_complete_wizard" => 0,
            ]);

        $branch_id = DB::getPdo()->lastInsertId();
        $this->branch_id = $branch_id;

        $this->createDefaultData($company_id, $branch_id);
        $this->updateUserCompanyAndBranchId($user_id, $company_id, $branch_id);

        $branch_users = $this->createDefaultBranchUser($company_id, $branch_id);

        return $branch_users;
    }

    public function createUser($company_id, $branch_id, $name, $email, $password, $id_cms_privileges, $assigned_station_id, $user_level)
    {
        DB::table("users")
            ->insert([
                "company_id" => $company_id,
                "branch_id" => $branch_id,
                "name" => $name,
                "email" => $email,
                "password" => $password,
                "id_cms_privileges" => $id_cms_privileges,
                "user_level" => $user_level,
                "station_id" => $assigned_station_id,
                "status" => 0,
                "alert_change_password" => 0,
                "mobile" => "-",
                "photo" => $this->getRandomUserPhoto(),
            ]);
    }

    public function createDefaultBranchUser($company_id, $branch_id)
    {
        $pass_arr = [];
        $pass_arr[] = mt_rand(100000, 999999);
        $pass_arr[] = mt_rand(100000, 999999);
        $pass_arr[] = mt_rand(100000, 999999);
        $pass_arr[] = mt_rand(100000, 999999);
        $pass_arr[] = mt_rand(100000, 999999);

        $foods = DB::table("product_station")
            ->where("branch_id", $branch_id)
            ->where("name", "Foods")
            ->first();

        $drinks = DB::table("product_station")
            ->where("branch_id", $branch_id)
            ->where("name", "Beverages")
            ->first();

        $user_arr = [];

        $user_arr[] = [
            $company_id,
            $branch_id,
            "Manager",
            "manager." . $branch_id,
            Hash::make($pass_arr[0]),
            $pass_arr[0],
            2,
            $foods->id,
            "Manager",
        ];

        $user_arr[] = [
            $company_id,
            $branch_id,
            "Waiter",
            "waiter." . $branch_id,
            Hash::make($pass_arr[1]),
            $pass_arr[1],
            3,
            $foods->id,
            "Waiter",
        ];

        $user_arr[] = [
            $company_id,
            $branch_id,
            "Chef",
            "chef." . $branch_id,
            Hash::make($pass_arr[2]),
            $pass_arr[2],
            4,
            $foods->id,
            "Kitchen",
        ];

        $user_arr[] = [
            $company_id,
            $branch_id,
            "Barista",
            "barista." . $branch_id,
            Hash::make($pass_arr[3]),
            $pass_arr[3],
            4,
            $drinks->id,
            "Kitchen",
        ];

        $user_arr[] = [
            $company_id,
            $branch_id,
            "Cashier",
            "cashier." . $branch_id,
            Hash::make($pass_arr[4]),
            $pass_arr[4],
            5,
            $foods->id,
            "Cashier",
        ];

        for ($i = 0; $i < count($user_arr); $i++) {
            $user = $user_arr[$i];
            $this->createUser(
                $user[0],
                $user[1],
                $user[2],
                $user[3],
                $user[4],
                $user[6],
                $user[7],
                $user[8]
            );
        }

        return $user_arr;
    }

    public function createDefaultData($company_id, $branch_id)
    {
        DB::table("product_category")
            ->insert([
                [
                    "company_id" => $company_id,
                    "branch_id" => $branch_id,
                    "category_name" => "Main Course"
                ],
                [
                    "company_id" => $company_id,
                    "branch_id" => $branch_id,
                    "category_name" => "Beverages"
                ],
                [
                    "company_id" => $company_id,
                    "branch_id" => $branch_id,
                    "category_name" => "Snack"
                ],
                [
                    "company_id" => $company_id,
                    "branch_id" => $branch_id,
                    "category_name" => "Appetizers"
                ],
                [
                    "company_id" => $company_id,
                    "branch_id" => $branch_id,
                    "category_name" => "Dessert"
                ],
            ]);

        DB::table("product_station")
            ->insert([
                [
                    "company_id" => $company_id,
                    "branch_id" => $branch_id,
                    "name" => "Foods"
                ],
                [
                    "company_id" => $company_id,
                    "branch_id" => $branch_id,
                    "name" => "Beverages"
                ],
            ]);

        // DB::table("table_group")
        //     ->insert([
        //         [
        //             "company_id" => $company_id,
        //             "branch_id" => $branch_id,
        //             "name" => "Lantai 1",
        //             "table_order" => 1,
        //             "table_count" => 10,
        //         ],
        //         [
        //             "company_id" => $company_id,
        //             "branch_id" => $branch_id,
        //             "name" => "Lantai 1 - Smoking Area",
        //             "table_order" => 1,
        //             "table_count" => 10,
        //         ],
        //         [
        //             "company_id" => $company_id,
        //             "branch_id" => $branch_id,
        //             "name" => "Lantai 2",
        //             "table_order" => 1,
        //             "table_count" => 10,
        //         ],
        //     ]);

        TableGroupController::GenerateTableManagement($company_id, $branch_id);

        // DB::table("unit")
        //     ->insert([
        //         [
        //             "company_id" => $company_id,
        //             "branch_id" => $branch_id,
        //             "unit_name" => "PCS",
        //         ],
        //         [
        //             "company_id" => $company_id,
        //             "branch_id" => $branch_id,
        //             "unit_name" => "BUAH",
        //         ],
        //         [
        //             "company_id" => $company_id,
        //             "branch_id" => $branch_id,
        //             "unit_name" => "KG",
        //         ],
        //     ]);

        DB::table("product_stock_adjustment_category")
            ->insert([
                [
                    "company_id" => $company_id,
                    "branch_id" => $branch_id,
                    "name" => "Rusak",
                    "adjustment_type" => "Minus",
                ],
                [
                    "company_id" => $company_id,
                    "branch_id" => $branch_id,
                    "name" => "Hilang",
                    "adjustment_type" => "Minus",
                ],
                [
                    "company_id" => $company_id,
                    "branch_id" => $branch_id,
                    "name" => "Expired",
                    "adjustment_type" => "Minus",
                ],
                [
                    "company_id" => $company_id,
                    "branch_id" => $branch_id,
                    "name" => "Kelebihan Stock",
                    "adjustment_type" => "Add",
                ],
                [
                    "company_id" => $company_id,
                    "branch_id" => $branch_id,
                    "name" => "Retur ke Supplier",
                    "adjustment_type" => "Minus",
                ],
                [
                    "company_id" => $company_id,
                    "branch_id" => $branch_id,
                    "name" => "Penggunaan Pribadi",
                    "adjustment_type" => "Minus",
                ],
            ]);
    }

    public function sendVerificationLink($branch_users, $password, $verification_code)
    {
        $to_name = Input::get("name");
        $to_email = Input::get("email");

        $data = array(
            "branch_users" => $branch_users,
            "name" => $to_name,
            "email" => $to_email,
            "password" => $password,
            "email_sha1" => Hash::make($to_email),
            "password_sha1" => Hash::make($password),
            "url" => url("api/custom/auth/verify?verification_code=$verification_code")
        );

        Mail::send('emails.mail', $data, function ($message) use ($to_name, $to_email) {
            $message->to($to_email, $to_name)
                ->subject('Saji Registration, Need Verification');
            $message->from('notifikasi-saji@afe.co.id', 'Saji');
        });
    }

    public function register()
    {
        DB::beginTransaction();

        $verification_code = Hash::make(mt_rand(1000, 9999));
        $password = mt_rand(100000, 999999);

        $user_id = $this->registerUserAndGetUserId($password, $verification_code);
        $company_id = $this->registerCompanyAndGetCompanyId($user_id);
        $branch_users = $this->createDefaultBranch($company_id, $user_id);

        $this->sendVerificationLink($branch_users, $password, $verification_code);

        DB::commit();

        return [
            "error" => false,
            "message" => "Registration of new account Success"
        ];
    }

    public function registerBranch($user_id, $company_id)
    {
        DB::beginTransaction();

        $verification_code = Hash::make(mt_rand(1000, 9999));

        $branch_users = $this->createDefaultBranch($company_id, $user_id);

        // $this->sendVerificationLink($branch_users, $password, $verification_code );

        DB::commit();
        return $this->branch_id;
    }

    public function verify()
    {
        $verification_code = Input::get("verification_code");

        $user = DB::table("users")
            ->where("verification_code", $verification_code)
            ->whereNotNull("verification_code")
            ->first();

        if ($user->status == 1) {
            // return [
            //     "error" => false,
            //     "message" => "This Account has been activated, You Can Login Now!",
            // ];
            return Redirect::away("http://aosdev.xyz/saji/landing/?page_id=10352");
        }

        if ($user) {

            DB::table("users")
                ->where("verification_code", $verification_code)
                ->whereNotNull("verification_code")
                ->update([
                    "status" => 1
                    // "verification_code" => null
                ]);

            DB::table("users")
                ->where("company_id", $user->company_id)
                ->update([
                    "status" => 1
                    // "verification_code" => null
                ]);

            return Redirect::away("http://aosdev.xyz/saji/landing/?page_id=10352");
        }

        return [
            "error" => true,
            "message" => "Invalid Verification Code, Can't be activated!"
        ];
    }

    public function checkStatus()
    {
        $email = Input::get("email");

        $user = DB::table("users")
            ->where("email", $email)
            ->first();

        if ($user != null) {
            return [
                "email" => $email,
                "is_active" => boolVal($user->status),
            ];
        }

        return [
            "error" => true,
            "message" => "User Not Found!",
        ];
    }

    public function checkVersion()
    {
        $version = DB::table("app_parameters")
            ->where("param_key", "Version")
            ->first()->param_value;

        return [
            "error" => true,
            "message" => $version,
        ];
    }

    public function checkParameters()
    {
        $version = DB::table("app_parameters")
            ->get();

        return [
            "error" => true,
            "message" => $version,
        ];
    }
}
