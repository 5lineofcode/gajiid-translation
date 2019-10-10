<?php
use Illuminate\Support\Facades\Input;
use Carbon\Carbon;
use Illuminate\Http\Request;

Route::get('/', function () {
    return DB::table("translation")->paginate(10);
});

Route::group(["prefix" => "api"], function () {

    //! {table_name} = endpoint dari flutter
    Route::any("/custom/{table_name}/{method_name}", function (Request $request,$table_name, $method_name) {
        $controller = new \App\Http\Controllers\Api\ApiController();
        return $controller->hook_custom($request, $table_name,$method_name);
    });

    //! {table_name} = endpoint dari flutter
    //! memanggil OnTable di "{table_name}Controller"
    Route::any('table/{table_name}', function (Request $request, $table_name) {
        $controller = new \App\Http\Controllers\Api\ApiController();
        return $controller->hook_table($request, $table_name);
    });
    
    //! {table_name} = endpoint dari flutter
    //! memanggil [OnGetAll] di "{table_name}Controller"
    Route::any('get-all/{table_name}', function ($table_name, Request $request) {
        $controller = new \App\Http\Controllers\Api\ApiController();
        return $controller->hook_get_all($request, $table_name);
    });
    
    //OPSI 1
    Route::any('delete/{table_name}/{id}', function ($table_name, $id) {
        $controller = new \App\Http\Controllers\Api\ApiController();
        return $controller->hook_delete($request, $table_name, $id);
    });

    //OPSI 2
    Route::any('delete/{table_name}', function ($table_name) {
        $controller = new \App\Http\Controllers\Api\ApiController();
        return $controller->hook_delete($request, $table_name);
    });

    Route::any('get/{table_name}', function ($table_name) {
        return json_encode([
            "is_error" => true,
            "message" => "Router not found, did you mean \\Api\get\\$table_name\\\$id ?"
        ]);
    });
    
    //! {table_name} = endpoint dari flutter
    //! memanggil [OnGetSingleData] di "{table_name}Controller"
    Route::any('get/{table_name}/{id}', function ($table_name, $id) {
        $controller = new \App\Http\Controllers\Api\ApiController();
        return $controller->hook_get_single_data($request, $table_name, $id);
    });
    
    //! {table_name} = endpoint dari flutter
    //! memanggil [OnCreate] di "{table_name}Controller"
    Route::any('create/{table_name}', function ($table_name) {
        $controller = new \App\Http\Controllers\Api\ApiController();
        return $controller->hook_create($request, $table_name);
    });
    
    //! {table_name} = endpoint dari flutter
    //! memanggil [OnEdit] di "{table_name}Controller"
    Route::any('update/{table_name}', function ($table_name) {
        $controller = new \App\Http\Controllers\Api\ApiController();
        return $controller->hook_update($request, $table_name);
    });

    Route::any("/upload", function () {
        $controller = new \App\Http\Controllers\Api\ApiController();
        return $controller->hook_upload($request, $table_name);
    });
});

