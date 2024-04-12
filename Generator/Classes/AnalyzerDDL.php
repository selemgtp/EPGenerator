<?php
declare (strict_types = 1);
namespace Generator\Classes;

class AnalyzerDDL{

    public static function drop(string $queryType){
        $arrayFieldsRequest = array();
       
        $arrayFieldsResponse = array(
            "message" => "Eliminación realizada con éxito."
        );
        if ($queryType == "UNIQ"){

            array_push(
                $arrayFieldsRequest,
                array(
                    "columnName"=>"table", "name" => "table", "operator"=>"=", "parameter"=>"table:value","type" => "string", "comment" => "Nombre de la tabla a eliminar"
                )
            );
            
        }else{

            array_push(
                $arrayFieldsRequest,
                array(
                    "columnName"=>"database", "name" => "database", "operator"=>"=", "parameter"=>"database:value","type" => "string", "comment" => "Nombre de la base de datos donde se encuentra la tabla"
                )
            );

            array_push(
                $arrayFieldsRequest,
                array(
                    "columnName"=>"table", "name" => "table", "operator"=>"=", "parameter"=>"table:value","type" => "string", "comment" => "Nombre de la tabla a eliminar"
                )
            );
        }
        return array(
            "ok" => [
                "request" => $arrayFieldsRequest,
                "response" => $arrayFieldsResponse
            ]
        );
    }
}

?>