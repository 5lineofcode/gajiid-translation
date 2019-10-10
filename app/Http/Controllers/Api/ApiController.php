<?php
namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\ApiResponseController;
use App\Http\Controllers\Api\ApiHelperController;

use Input;
use DB;
use File;

class ApiController extends Controller
{
    public function hook_get_all($request, $table_name){
        $override_method = "OnGetAll";
        $primaryKey = ApiHelperController::getPrimaryKey($table_name);
        
        //! Filter
        $where = ApiHelperController::getWhere();
        
        //! Sort & Limit
        $count = DB::table($table_name)->count();
        $sort_field = Input::get("sort_field") ?? $primaryKey;
        $sort_order = Input::get("sort_order") ?? "asc";
        $limit = Input::get("limit") ?? $count;

        if($count==0) {
            $limit = 1;
        }

        $controllerMethod = ApiHelperController::callMethodIfExists($table_name, $override_method, [
            "where" => $where,
            "primary_key" => $primaryKey,
            "sort_field" => $sort_field,
            "sort_order" => $sort_order,
            "page_count" => $page_count,
            "table_name" => $table_name,
        ]);
        if ($controllerMethod) {
            return $controllerMethod;
        }

        //! Clean INPUT
        Input::offsetUnset("sort_order");
        Input::offsetUnset("limit");
        
        $query = DB::table($table_name)
                    ->where($where);

        $query = $query->orderBy($sort_field, $sort_order);
        $query = $query->limit($limit);
        $results = $query->paginate($limit);

        if ($results == null) {
            $results = [
                "no_data" => true,
                "message" =>  "No Data"
            ];
        }

        ApiResponseController::updateEndpointVersion($table_name);
        return response()->json($results);
    }

    public function hook_table($request,$table_name){
        $override_method = "OnTable";
        $primaryKey = ApiHelperController::getPrimaryKey($table_name);
        
        //! Filter
        $where = ApiHelperController::getWhere();

        //! Sort & Limit
        $count = DB::table($table_name)->count();
        $sort_field = Input::get("sort_field") ?? $primaryKey;
        $sort_order = Input::get("sort_order") ?? "asc";
        $limit = Input::get("limit") ?? $count;

        $page_count = Input::get("page_count") ?? 0;
        $controllerMethod = ApiHelperController::callMethodIfExists($table_name, $override_method, [
            "where" => $where,
            "primary_key" => $primaryKey,
            "sort_field" => $sort_field,
            "sort_order" => $sort_order,
            "page_count" => $page_count,
            "table_name" => $table_name,
        ]);
        if ($controllerMethod) {
            return $controllerMethod;
        }

        $query = DB::table($table_name)
            ->where($where)
            ->orderBy($sort_field, $sort_order);
    
        $query = $page_count == 0 ? $query->paginate() : $query->paginate($page_count);
        return response()->json($query);
    }

    public function hook_custom($request,$table_name,$method){
        $override_method = $method;
        $primaryKey = null;
        
        $controllerMethod = ApiHelperController::callCustomMethod($table_name, $override_method, []);

        return $controllerMethod;
    }

    public function hook_delete($request,$table_name,$id = null){
        $override_method = "OnDelete";
        $primaryKey = ApiHelperController::getPrimaryKey($table_name);
        
        //! Filter
        $where = ApiHelperController::getWhere();
        if($id==null){
            $id = Input::get("id");
        }

        $controllerMethod = ApiHelperController::callMethodIfExists($table_name, $override_method, [
            "where" => $where,
            "primary_key" => $primaryKey,
            "sort_field" => $sort_field,
            "sort_order" => $sort_order,
            "table_name" => $table_name,
            "id" => $id,
        ]);
        
        if ($controllerMethod) {
            ApiResponseController::updateEndpointVersion($table_name);
            return $controllerMethod;
        }

        $query = DB::table($table_name)
                   ->where($primaryKey, $id);

        $is_success = $query->delete();
        
        ApiResponseController::updateEndpointVersion($table_name);
        return response()->json(ApiResponseController::deleteResponse($is_success));
    }

    public function hook_get_single_data($request,$table_name,$id) {
        $override_method = "OnGetSingleData";
        $primaryKey = ApiHelperController::getPrimaryKey($table_name);
        
        //! Filter
        $where = ApiHelperController::getWhere();

        $controllerMethod = ApiHelperController::callMethodIfExists($table_name, $override_method, [
            "where" => $where,
            "primary_key" => $primaryKey,
            "sort_field" => $sort_field,
            "sort_order" => $sort_order,
            "table_name" => $table_name,
            "id" => $id,
        ]);
        
        if ($controllerMethod) {
            return $controllerMethod;
        }

        $query = DB::table($table_name)
            ->where($primaryKey, $id);

        $result = $query->first();

        if(count($result)==0){
            return response()->json([
                "error" => true,
                "message" => "NO_DATA",
            ]);
        }
        return response()->json($result);
    }

    public function hook_create($request,$table_name){
        $override_method = "OnCreate";
        $primaryKey = ApiHelperController::getPrimaryKey($table_name);
        
        //! Filter
        $where = ApiHelperController::getWhere();

        //! Create Data
        $postdata = Input::all();

        $controllerMethod = ApiHelperController::callMethodIfExists($table_name, $override_method, [
            "where" => $where,
            "primary_key" => $primaryKey,
            "sort_field" => $sort_field,
            "sort_order" => $sort_order,
            "table_name" => $table_name,
        ]);
        
        if ($controllerMethod) {
            ApiResponseController::updateEndpointVersion($table_name);
            return $controllerMethod;
        }

        $query = DB::table($table_name);
        $is_success = $query->insert($postdata);

        ApiResponseController::updateEndpointVersion($table_name);
        return response()->json(ApiResponseController::createResponse($is_success));
    }

    public function hook_update($request,$table_name){
        $override_method = "OnEdit";
        $primaryKey = ApiHelperController::getPrimaryKey($table_name);
        
        //! Filter
        $where = ApiHelperController::getWhere();

        //! Update DATA
        $postdata = Input::all();
        $id = $postdata["id"];
        unset($postdata["id"]);

        $controllerMethod = ApiHelperController::callMethodIfExists($table_name, $override_method, [
            "where" => $where,
            "primary_key" => $primaryKey,
            "sort_field" => $sort_field,
            "sort_order" => $sort_order,
            "table_name" => $table_name,
            "id" => $id,
        ]);
        
        if ($controllerMethod) {
            ApiResponseController::updateEndpointVersion($table_name);
            return $controllerMethod;
        }

        $is_success = DB::table($table_name)
            ->where($where)
            ->where($primaryKey, $id)
            ->update($postdata);

        ApiResponseController::updateEndpointVersion($table_name);
        return response()->json(ApiResponseController::updateResponse($is_success));
    }

    public function hook_upload($request,$table_name){
        $image = Input::get("base64_image");
        $image = str_replace('data:image/png;base64,', '', $image);
        $image = str_replace(' ', '+', $image);
        $imageName = str_random(10) . '.' . 'png';
        $saveFileName = 'uploads/mobile/' . $imageName;
        $fullpath = storage_path() . '/app/' . $saveFileName;
        \File::put($fullpath, base64_decode($image));

        return response()->json([
            "is_success" => true,
            "image_url" => $saveFileName
        ]);
    }
}
