<?php
namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;

use Input;
use DB;
use Storage;

class ExtremeController extends Controller
{
    public function getFormClassName($table_name)
    {
        $table_name = str_replace("_", " ", $table_name);
        $table_name = str_replace(" ", "", $table_name);
        $className = ucwords($table_name) . "FormPage";
        return $className;
    }

    public function getClassName($table_name)
    {
        $table_name = str_replace("_", " ", $table_name);
        $table_name = str_replace(" ", "", $table_name);
        $className = ucwords($table_name);
        return $className;
    }

    public function getVariableName($table_name)
    {
        $table_name = str_replace("_", " ", $table_name);
        $className = ucwords($table_name);
        $className = str_replace(" ", "", $className);
        $className = lcfirst($className);
        return $className;
    }

    public function getTitle($table_name)
    {
        $table_name = str_replace("_", " ", $table_name);
        $className = ucwords($table_name);
        return $className;
    }

    public function getCodeList($table_name, $primaryKey, $display_field_candidate, $definition, $title, $version = 1)
    {
        $className = $this->getFormClassName($table_name);
        $photo_candidate = null;
        $subtitle_candidate = null;

        for ($i = 0; $i < count($definition); $i++) {
            $field = $definition[$i]["field"];

            if (strpos($field, 'photo') !== false || strpos($field, 'image') !== false) {
                if ($photo_candidate == null) {
                    $photo_candidate = $field;
                }
            }

            if (strpos($field, 'price') !== false || strpos($field, 'stock') !== false || strpos($field, 'number') !== false) {
                if ($subtitle_candidate == null) {
                    $subtitle_candidate = $field;
                }
            }
        }

        //clean
        if ($photo_candidate == "" || $photo_candidate == null) {
            $photo_candidate = "null";
        } else {
            $photo_candidate = "\"$photo_candidate\"";
        }

        if ($display_field_candidate == "" || $display_field_candidate == null) {
            $display_field_candidate = "null";
        } else {
            $display_field_candidate = "\"$display_field_candidate\"";
        }


        if ($subtitle_candidate == "" || $subtitle_candidate == null) {
            $subtitle_candidate = "null";
        } else {
            $subtitle_candidate = "\"$subtitle_candidate\"";
        }

        //fix 
        if ($display_field_candidate == "null") {
            $display_field_candidate == $subtitle_candidate;
        }

        if ($version == 1) {
            return "Page.show(
                context,
                ExList(
                  title: \"$title\",
                  formPageTemplate: $className(),
                  apiDefinition: ApiDefinition(
                    endpoint: \"$table_name\",
                    primaryKey: \"$primaryKey\",
                    leadingPhotoIndex: $photo_candidate,
                    titleIndex: $display_field_candidate,
                    subtitleIndex: $subtitle_candidate,
                  ),
                ),
              );";
        } else if ($version == 2) {
            return "
                {
                    'text': '$title',
                    'icon': Icons.receipt,
                    'color': Colors.orange,
                    'formPageTemplate': $className(),
                    'apiDefinition': ApiDefinition(
                        endpoint: \"$table_name\",
                        primaryKey: \"$primaryKey\",
                        leadingPhotoIndex: $photo_candidate,
                        titleIndex: $display_field_candidate,
                        subtitleIndex: $subtitle_candidate,
                    ),
                },
            ";
        }
    }

    public function getInputType($type)
    {
        switch ($type) { }
        return "textfield";
    }

    public function getLabel($field)
    {
        $field = str_replace("_", " ", $field);
        $className = ucwords($field);
        return $className;
    }

    public function getInputCode($field, $type)
    {
        $label = $this->getLabel($field);

        switch ($type) {
            case "datetime":
                return "
                    ExDatePicker(
                        id: '$field',
                        label: '$label',
                        icon: Icons.calendar_today,
                        context: context,
                        value: Input.getValueOnEdit('$field', isEdit),
                    ),
                ";
                break;

            case "text":
                return "
                    ExTextArea(
                        id: '$field',
                        label: '$label',
                        context: context,
                        value: Input.getValueOnEdit('$field', isEdit),
                    ),
                ";
                break;
        }

        //Spesific Field Type
        if (strpos($field, 'photo') !== false || strpos($field, 'image') !== false || strpos($field, 'logo') !== false) {
            return "
                ExImageUpload(
                    id: '$field',
                    value: Input.getValueOnEdit('$field', isEdit),
                ),
            ";
        }

        //Spesific Field Type
        if (strpos($field, 'phone') !== false || strpos($field, 'mobile') !== false) {
            return "
                ExTextField(
                    id: '$field',
                    label: '$label',
                    icon: FontAwesomeIcons.keyboard,
                    keyboardType: TextInputType.phone,
                    useBorder: true,
                    useIcon: true,
                    value: Input.getValueOnEdit('$field', isEdit),
                ),
            ";
        }

        if (strpos($field, 'email') !== false) {
            return "
                ExTextField(
                    id: '$field',
                    label: '$label',
                    icon: FontAwesomeIcons.keyboard,
                    keyboardType: TextInputType.emailAddress,
                    useBorder: true,
                    useIcon: true,
                    value: Input.getValueOnEdit('$field', isEdit),
                ),
            ";
        }

        if (strpos($type, 'int') !== false || strpos($type, 'double') !== false) {
            return "
                ExTextField(
                    id: '$field',
                    label: '$label',
                    icon: FontAwesomeIcons.keyboard,
                    keyboardType: TextInputType.number,
                    useBorder: true,
                    useIcon: true,
                    value: Input.getValueOnEdit('$field', isEdit),
                ),
            ";
        }

        return "
            ExTextField(
                id: '$field',
                label: '$label',
                icon: FontAwesomeIcons.keyboard,
                keyboardType: TextInputType.text,
                useBorder: true,
                useIcon: true,
                value: Input.getValueOnEdit('$field', isEdit),
            ),
        ";
    }


    public function getVariableFields($field)
    {
        $variableName = $this->getVariableName($field);
        return "var $variableName = Input.get('$field');";
    }

    public function getPostFields($field)
    {
        $variableName = $this->getVariableName($field);
        return "'$field': $variableName,";
    }

    public function getGeneratedFormDefinition($table_name, $primaryKey, $definition)
    {
        $title = $this->getTitle($table_name);
        $className = $this->getFormClassName($table_name);
        $flutterName = $this->getVariableName($table_name);
        $stateClassName = "_" . $this->getFormClassName($table_name) . "State";

        $input_code_string = "";
        $input_variable_fields = "";
        $input_post_fields = "";

        foreach ($definition as $def) {

            $field = $def["field"];
            $type = $def["type"];
            $key = $def["key"];
            $display_field_candidate = $def["display_field_candidate"];
            $input_type = $def["input_type"];
            $input_code = $def["input_code"];

            if ($field == $primaryKey) {
                continue;
            }

            $post_field = $this->getPostFields($field);
            $variable_field = $this->getVariableFields($field);

            $input_code_string .= $input_code;
            $input_variable_fields .= $variable_field;
            $input_post_fields .= $post_field;

            // $input_code_string += $input_code;
            // die();
        }

        return "
        import 'package:flutter/material.dart';
        import 'package:sajiapp/core.dart';
        
        class $className extends StatefulWidget {
          @override
          $stateClassName createState() => $stateClassName();
        }
        
        class $stateClassName extends State< $className > {
          bool isEdit = false;
          bool isLoading = false;
          int selectedId;
        
          onValidation() {}
        
          createData() async {
            $input_variable_fields
        
            var postResponse = await srv.$flutterName.create({
              $input_post_fields
            });
        
            if (postResponse.isSuccess == true) {
                Alert.showSuccess(
                    context: context,
                    title: null,
                    message: postResponse.message,
                );
            } else {
                Alert.showError(
                    context: context,
                    title: null,
                    message: postResponse.message,
                );
            }
          }
        
          updateData() async {
            $input_variable_fields
        
            var postResponse = await srv.$flutterName.update({
              'id': selectedId.toString(),
              $input_post_fields
            });
        
            if (postResponse.isSuccess == true) {
                Alert.showSuccess(
                    context: context,
                    title: null,
                    message: postResponse.message,
                );
            } else {
                Alert.showError(
                    context: context,
                    title: null,
                    message: postResponse.message,
                );
            }
          }
        
          loadData() async {
            Map<String, dynamic> response =
                await srv.$flutterName.get(selectedId);
        
            response.forEach((key, value) {
              Input.set(key, value);
            });
        
            setState(() {
              isLoading = false;
            });
          }
        
          @override
          void initState() {
            if(Input.get('selectedId')!=null){
              selectedId = Input.get('selectedId');
            }
        
            if (selectedId != null) {
              isEdit = true;
              isLoading = true;
              loadData();
            }
            super.initState();
          }

        
          @override
          Widget build(BuildContext context) {
            var form = [
              $input_code_string
            ];
        
            return Scaffold(
              appBar: Saji.getAppBar(
                title: (isEdit ? 'Edit ' : 'Add ') + '$title',
                context: context,
              ),
              bottomNavigationBar: FormSaveButton(
                onPressed: () {
                  isEdit ? updateData() : createData();
                },
              ),
              body: isLoading
                  ? Loading.show()
                  : SingleChildScrollView(
                      child: Container(
                        padding: EdgeInsets.all(8.0),
                        child: Column(
                          children: form,
                        ),
                      ),
                    ),
            );
          }
        }
        
        ";
    }

    public function createControllerIfNotExists($table_name){
        //Create PHP Controller if NOT EXISTS
        $controllerFileName = $this->getClassName($table_name) . "Controller.php";
        $controllerName = $this->getClassName($table_name) . "Controller";

        if($table_name=="branch"){
            Storage::put($controllerFileName, 'Controller Content');
        }
    }

    public function onIndex()
    {
        $result = [];

        $tables = DB::select("show tables");
        foreach ($tables as $key => $table) {
            reset($table);
            $first = current($table);
            $table_name = $first;

            if($table_name == "company.old") continue;

            if (strpos($table_name, 'cms_') === false || $table_name == "users") {
                $columns = DB::select("SHOW COLUMNS FROM `$table_name`");
                // echo json_encode($columns);
                // echo "<hr/>";

                $definition = [];
                $primaryKey = "";
                $display_field_candidate = "";

                foreach ($columns as $column) {
                    $field = $column->Field;
                    $type = $column->Type;
                    $key = $column->Key;

                    if ($field == "company_id") {
                        continue;
                    }

                    if ($field == "branch_id") {
                        continue;
                    }

                    if ($key == "PRI") {
                        $primaryKey = $field;
                    }

                    if (strpos($field, 'name') !== false) {
                        $display_field_candidate = $field;
                    }

                    $input_type = $this->getInputType($type);
                    $input_code = $this->getInputCode($field, $type);

                    $definition[] = [
                        "field" => $field,
                        "type" => $type,
                        "key" => $key,
                        "display_field_candidate" => $display_field_candidate != "" ? true : false,
                        "input_type" => $input_type,
                        "input_code" => $input_code,

                    ];
                }

                $title = $this->getTitle($table_name);
                $result[] = [
                    "table_name" => $table_name,
                    "definition" => $definition,
                    "exlist" => $this->getCodeList($table_name, $primaryKey, $display_field_candidate, $definition, $title, 1),
                    "form" => $this->getGeneratedFormDefinition($table_name, $primaryKey, $definition),
                    "menuItem" => $this->getCodeList($table_name, $primaryKey, $display_field_candidate, $definition, $title, 2),
                    "primaryKey" => $primaryKey,
                    "display_field_candidate" => $display_field_candidate,
                    "className" => $this->getFormClassName($table_name),
                    "title" => $title,
                ];


                $this->createControllerIfNotExists($table_name);
            }
        }

        return $result;
    }

    public function OnGetApiList(){

        DB::table("cms_flutter_method")->delete();

        $tables = DB::select("show tables");
        foreach ($tables as $key => $table) {
            reset($table);
            $first = current($table);
            $table_name = $first;

            if (strpos($table_name, 'cms_') === false || $table_name == "users") {
                echo $table_name . "<br/>";
            }

            $endpoint = str_replace("_"," ",$table_name);
            $endpoint = ucwords($endpoint);
            $endpoint = str_replace(" ","", $endpoint);
            $endpoint = lcfirst($endpoint);

            DB::table("cms_flutter_method")
                ->insert([
                    "endpoint" => $endpoint,
                    "table_name" => $table_name,
                    "method" => "-",
                    "type" => "table"
                ]);
        }

        $files = scandir(public_path() . "/../app/Http/Controllers/Api");

        foreach($files as $file){

            if($file=="." || $file=="..") continue;

            $file = str_replace(".php","",$file);
            echo $file . "<br/>";


            $class = "App\Http\\Controllers\\Api\\$file";
            $methods = get_class_methods($class);

            foreach($methods as $method){
                if($method=="middleware"){
                    break;
                }
                
                $className = basename($class);
                $className = str_replace("Controller","",$className);
                $className = lcfirst($className);

                DB::table("cms_flutter_method")
                    ->insert([
                        "endpoint" => $className,
                        "table_name" => "-",
                        "method" => $method,
                        "type" => "custom"
                    ]);
                    
                echo $method . "<br/>";
            }
            echo "<hr/>";
            
        }

        return [
            "error" => false,
            "message" => "ApiList Generated! Data has been saved to cms_flutter_method!",
        ];
    }
}
