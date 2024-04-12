<?php
declare(strict_types=1);
namespace Generator\Classes;
use Generator\Classes\Utils\Normalize;
class SwaggerAnnotation{

    private $method;
    private $queryName;
    private $module;
    private $statusCode; 
    private $responseShema;


    public function validateRequstFields(array $fields){

        $arrayFields = $fields;
        $arrayNamesFields = array();
        $arrayRequest = array();
        for ($i = 0; $i < count($arrayFields); $i++){

            $changeName = false;

            if (in_array($arrayFields[$i]["columnName"], $arrayNamesFields)){

                $arrayFields[$i]["columnName"] = $arrayFields[$i]["columnName"].".".($i+1);
                $arrayFields[$i]["name"] = $arrayFields[$i]["name"].".".($i+1);
                array_push($arrayNamesFields, $arrayFields[$i]["columnName"]);
                $changeName = true;
            }else{
                array_push($arrayNamesFields, $arrayFields[$i]["columnName"]);
            }


            switch($arrayFields[$i]["operator"]){

                case "BETWEEN":

                    if (count($arrayFields[$i]["parameter"]) == 1){
                        if ($changeName){
                            $arrayFields[$i]["parameter"] = array($arrayFields[$i]["columnName"].".left:value");
                        }
                        array_push($arrayRequest, array($arrayFields[$i]["columnName"]=>$arrayFields[$i]["type"]));
                    }else{
                        if ($changeName){
                            $arrayFields[$i]["parameter"] = array(
                                array($arrayFields[$i]["columnName"].".left:value"),
                                array($arrayFields[$i]["columnName"].".right:value"),
                            );
                        }
                        array_push($arrayRequest, array($arrayFields[$i]["columnName"]=>array($arrayFields[$i]["type"])));
                    }
                break;

                case "IN":
                    if ($changeName){
                        $arrayFields[$i]["parameter"] = array("[".$arrayFields[$i]["columnName"].".in:value]");
                    }
                    array_push($arrayRequest, array($arrayFields[$i]["columnName"]=>array($arrayFields[$i]["type"])));

                break;

                case "LIKE":
                    if ($changeName){
                        $arrayFields[$i]["parameter"] = array($arrayFields[$i]["columnName"].".like_expr:value");
                    }
                    array_push($arrayRequest, array($arrayFields[$i]["columnName"]=>"LIKE EXPR"));


                break;
                case "NOT LIKE":
                    if ($changeName){
                        $arrayFields[$i]["parameter"] = array($arrayFields[$i]["columnName"].".not_like_expr:value");
                    }
                    array_push($arrayRequest, array($arrayFields[$i]["columnName"]=>"NOT LIKE EXPR"));

                break;

                default:
                    if ($changeName){
                        $arrayFields[$i]["parameter"] = array($arrayFields[$i]["columnName"].".value");
                    }
                    array_push($arrayRequest, array($arrayFields[$i]["columnName"]=>$arrayFields[$i]["type"]));


            }
            
        }

        $arrayRequestFinal = array();

        for($i = 0; $i < count($arrayRequest); $i++){
            foreach($arrayRequest[$i] as $key=>$value){
                $arrayRequestFinal[$key]= $value;
            }
        }
        //print_r($arrayFields);
        return array("fields"=>$arrayFields, "request"=>$arrayRequestFinal);

    }

    public function config($method, $queryName, $module, $onlyMessagePost = ''){
        $this->module = $module;
        $this->queryName = $queryName;
        $this->method = $method;

        switch($this->method){
            case "post":
                $this->statusCode = 201;
                if ($onlyMessagePost == ''){
                    $this->responseShema = "ResponseInsert";
                }else{
                    $this->responseShema = "ResponseMessage";
                }
            break;

            case "get":
                $this->statusCode = 200;
                $this->responseShema = Normalize::normalizeUCWords($this->module).Normalize::normalizeUCWords($this->queryName)."Response";
            break;

            case "delete":
                $this->statusCode = 200;
                $this->responseShema = "ResponseMessage";
            break;

            case "put":
                $this->statusCode = 201;
                $this->responseShema = "ResponseMessage";
            break;
        }

    }

    public function getPathDoc(){
        return "/docs/#/".Normalize::normalizeUCWords($this->module)."/".strtolower($this->method)."_".strtolower(Normalize::normalizeUCWords($this->module))."_".strtolower(Normalize::normalizeUCWords($this->queryName));
    }   

    public function constructShemaResponseSelect(array $fields){

        $schemaTemplate = $this->responseShemaSelect($fields,Normalize::normalizeUCWords($this->module).Normalize::normalizeUCWords($this->queryName).'ResponseTemplate', 'Respuesta de query '.$this->queryName.' tipo select');
        
        $shema = '
        /**
         * @OA\Schema(
         * schema="'.Normalize::normalizeUCWords($this->module).Normalize::normalizeUCWords($this->queryName).'Response",
         *         type="array",
         *         @OA\Items(
        *                      @OA\Schema(ref="#/components/schemas/'.Normalize::normalizeUCWords($this->module).Normalize::normalizeUCWords($this->queryName).'ResponseTemplate"),
        *           
         *                      
         *                     example={';
         
         foreach($fields as $field){
              

                $shema .= '
                *                           "'.$field["name"].'": "'.$this->getDataTypeSwagger($field["type"]).'",
                ';
        }
         
         
         $shema .= '*                     }
         *
         *         )
         * )
         */
        ';


        return $schemaTemplate.'
        
        '.$shema;
    }

    public function responseShemaSelect(array $fields, string $name = '', string $description = ''){

        $nameSchema = ($name == '') ? Normalize::normalizeUCWords($this->module).Normalize::normalizeUCWords($this->queryName).'Request' : $name;
        $this->descriptionSchema = ($description == '') ? 'Cuerpo de la petición para la consulta '.$this->queryName : $description;

        $exampleString = '
        * example = {
        ';
        $shema = 
        '
        /**
         * @OA\Schema(
         * schema="'.$nameSchema.'",
         * description="'.$this->descriptionSchema.'",';
         
         foreach($fields as $column){

               
            $shema .= '
            * @OA\Property(
                *  property="'.$column["name"].'",
                *  type="'.$this->getDataTypeSwagger($column["type"]).'",
                *  description="'.$column["comment"].'"
            * ),
                ';

            $exampleString .= '
            * "'.$column["name"].'": "'.$this->getDataTypeSwagger($column["type"]).'",
            ';

        }

         $exampleString .= '
         *}
         ';

         $shema .= $exampleString;

         $shema .='
         * )
         */
        ';
        return $shema;
    
    }

    public function requestShema(array $fields, string $name = '', string $description = ''){

        $nameSchema = ($name == '') ? Normalize::normalizeUCWords($this->module).Normalize::normalizeUCWords($this->queryName).'Request' : $name;
        $this->descriptionSchema = ($description == '') ? 'Cuerpo de la petición para la consulta '.$this->queryName : $description;

        $exampleString = '
        * example = {
        ';
        $shema = 
        '
        /**
         * @OA\Schema(
         * schema="'.$nameSchema.'",
         * description="'.$this->descriptionSchema.'",';
         
         foreach($fields as $column){

                switch($column["operator"]){

                    case "BETWEEN":

                        if (count($column["parameter"]) == 1){
                            $shema .= '
                            * @OA\Property(
                                *  property="'.$column["name"].'",
                                *  type="'.$this->getDataTypeSwagger($column["type"]).'",
                                *  description="'.$column["comment"].'",
                            * ),
                            ';
    
                            $exampleString .= '
                            * "'.$column["name"].'": "'.$this->getDataTypeSwagger($column["type"]).'",
                            ';
                        }else{
                            $shema .= '
                            * @OA\Property(
                                *  property="'.$column["name"].'",
                                *  type="array",
                                *  description="'.$column["comment"].'. Array de valores tipo '.$column["type"].'",
                                *       @OA\Items(
                                *           type="'.$this->getDataTypeSwagger($column["type"]).'"
                                *       )
                                * ),
                            ';
                            $exampleString .= '
                            * "'.$column["name"].'": {'.sprintf('"%s"',implode('","',  array($column["type"], $column["type"]))).'},
                            ';
                        }

                    break;

                    case "IN":
                        $shema .= '
                        * @OA\Property(
                            *  property="'.$column["name"].'",
                            *  type="array",
                            *  description="'.$column["comment"].'. Array de valores de tipo '.$column["type"].'",
                            *       @OA\Items(
                            *           type="'.$this->getDataTypeSwagger($column["type"]).'"
                            *       )
                            * ),
                        ';
                            
                        $exampleString .= '
                            * "'.$column["name"].'": {'.sprintf('"%s"',implode('","',  array($column["type"], $column["type"]))).'},
                            ';



                    break;

                    case "LIKE":
                        $shema .= '
                        * @OA\Property(
                            *  property="'.$column["name"].'",
                            *  type="'.$this->getDataTypeSwagger($column["type"]).'",
                            *  description="'.$column["comment"].'"
                        * ),
                        ';

                        $exampleString .= '
                        * "'.$column["name"].'": "like_expr:%'.$this->getDataTypeSwagger($column["type"]).'%",
                        ';
                    break;

                    case "NOT LIKE":

                    $shema .= '
                    * @OA\Property(
                        *  property="'.$column["name"].'",
                        *  type="'.$this->getDataTypeSwagger($column["type"]).'",
                        *  description="'.$column["comment"].'"
                    * ),
                        ';

                    $exampleString .= '
                    * "'.$column["name"].'": "not_like_expr:%'.$this->getDataTypeSwagger($column["type"]).'%",
                    ';

                    break;

                    default:

                    $shema .= '
                    * @OA\Property(
                        *  property="'.$column["name"].'",
                        *  type="'.$this->getDataTypeSwagger($column["type"]).'",
                        *  description="'.$column["comment"].'"
                    * ),
                        ';

                    $exampleString .= '
                    * "'.$column["name"].'": "'.$this->getDataTypeSwagger($column["type"]).'",
                    ';

                }
                


        }

         $exampleString .= '
         *}
         ';

         $shema .= $exampleString;

         $shema .='
         * )
         */
        ';
        return $shema;
    
    }

    public function annotationRequestBody(){
        $annotation = '
            *   @OA\RequestBody(
            *         @OA\MediaType(
            *             mediaType="application/json",
            *             @OA\Schema(ref="#/components/schemas/'.Normalize::normalizeUCWords($this->module).Normalize::normalizeUCWords($this->queryName).'Request"),
            *         )
            *     ),
                *   @OA\Response(
                *     response='.$this->statusCode.',
                *     description="Respuesta de ejecución correcta",
                *     @OA\MediaType(
                *         mediaType="application/json",
                *         @OA\Schema(ref="#/components/schemas/'.$this->responseShema.'")
                *     )
                *   ),
        
        ';

        return $annotation;
    }

    public function annotationsMethodGeneric($authenticationType, $parameter){
        $annotation = '
        /**
         * @OA\\'.Normalize::normalizeUCWords($this->method).'(
              *   path="/'.strtolower($this->module).'/'.strtolower(Normalize::normalizeUCWords($this->queryName)).'",
              *   tags={"'.Normalize::normalizeUCWords($this->module).'"},
              *   summary="Endpoint tipo '.$this->method.' para la consulta con nombre '.$this->queryName.'",
              *   security={{"'.$authenticationType.'": {}}},
            '.$parameter.'
            '.$this->annotationRequestBody().'
              *   @OA\Response(
              *     response=500,
              *     description="an ""unexpected"" error",
              *     @OA\MediaType(
              *         mediaType="application/json",
              *         @OA\Schema(ref="#/components/schemas/serverError")
              *     )
              *   ),
              * @OA\Response(
              *     response=401,
              *     description="Invalid authentication",
              *     @OA\MediaType(
              *         mediaType="application/json",
              *         @OA\Schema(ref="#/components/schemas/Unauthorized")
              *     )
              *   )
              * )
              */
        ';
        return $annotation;
    }

    public function getDataTypeSwagger(string $typeDatabase) : string{
        /*
        Swagger Types:
        string
        number
        integer
        boolean
        array
        object*/
        $type = strtolower($typeDatabase);
        
        if (strpos($type, "int") !== false){
            return 'integer';
        }else if (strpos($type, "float") !== false or strpos($type, "double") !== false or strpos($type, "decimal") !== false){
            return 'number';
        }else if (strpos($type, "char") !== false or strpos($type, "text") !== false or strpos($type, "binary") !== false or strpos($type, "blob") !== false or strpos($type, "date") !== false or strpos($type, "time") !== false){
            return 'string';
        }else if (strpos($type, "json") !== false){
            return 'object';
        }else if (strpos($type, "array") != false){
            return 'array';
        }else{
            return 'string';
        }

    }

}
?>