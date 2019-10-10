<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;

use DB;
use Input;
use Carbon\Carbon;

class StockOpnameController extends Controller
{
    public function save()
    {
        $items = json_decode(Input::get("items"));

        DB::beginTransaction();

        foreach ($items as $item) {
            $id = intval($item->id);
            $qty = intval($item->qty);

            DB::update("update ingredients set stock = $qty where id = $id");
        }

        DB::commit();

        return json_encode([
            "is_success" => true,
            "message" => "Date is Updated!",
        ]);
    }
}
