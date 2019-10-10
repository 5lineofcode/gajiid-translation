<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
 
use DB;
use Input;
use Carbon\Carbon;

class StockAdjustmentController extends Controller
{
    public function create()
    {
        $items = json_decode(Input::get("items"));
        $adjustment_category_id = Input::get("adjustment_category_id");

        $product_stock_adjustment_category = DB::table("product_stock_adjustment_category")
            ->where("adjustment_category_id", $adjustment_category_id)
            ->first();


        DB::beginTransaction();

        DB::table("adjustment")
            ->insert([
                "company_id" => null,
                "branch_id" => null,
                "adjustment_category_id" => $adjustment_category_id,
                "created_at" => Carbon::now(),
            ]);

        $adjustment_id = DB::getPdo()->lastInsertId();

        foreach ($items as $item) {
            $id = intval($item->id);
            $qty = intval($item->qty);

            if ($product_stock_adjustment_category->adjustment_category_id == "Add") {
                DB::update("update ingredients set stock = stock + $qty where id = $id");
            } else {
                DB::update("update ingredients set stock = stock - $qty where id = $id");
            }

            DB::table("adjustment_detail")
                ->insert([
                    "company_id" => null,
                    "branch_id" => null,
                    "adjustment_id" => $adjustment_id,
                    "ingredient_id" => $id,
                    "qty" => $qty,
                ]);
        }


        DB::commit();

        return json_encode([
            "is_success" => true,
            "message" => "Stock Adjustment Success!",
        ]);
    }
}
