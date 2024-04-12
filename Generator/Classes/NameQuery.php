<?php
declare (strict_types = 1);
namespace Generator\Classes;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Connection;

class NameQuery
{

    private $conn = null;

    private $tableName = "endpoints_sql_tool";

    public function __construct(array $database){
        $this->conn = DriverManager::getConnection([
            'dbname' => $database["database"],
            'user' => $database["username"],
            'password' => $database["password"],
            'host' => $database["host"],
            'driver' => $database["driver"],
            'charset'=> $database["charset"]
        ]);
        //$this->createTable();
    }
    public function searchQuery(string $query){
        /*$sql = "DELETE FROM ".$this->tableName." ";
        $statement = $this->conn->prepare($sql);
        $statement->bindValue(":query", $query);
        $statement->execute();
        echo "Elimando";
exit;*/
        $sql = "SELECT * FROM ".$this->tableName." WHERE query = :query";
        $statement = $this->conn->prepare($sql);
        $statement->bindValue(":query", $query);
        $statement->execute();

        return $statement->fetchAll();
    }

    public function searchName(string $name){
        $sql = "SELECT * FROM ".$this->tableName." WHERE name = :name";
        $statement = $this->conn->prepare($sql);
        $statement->bindValue(":name", $name);
        $statement->execute();

        return $statement->fetchAll();
    }

    public function createTable(){
        $sql = "CREATE TABLE IF NOT EXISTS `{$this->tableName}` (
            `name` VARCHAR(200) NOT NULL COMMENT 'Nombre de la consulta', 
            `query` TEXT NOT NULL COMMENT 'Query o sentencia SQL', 
            `endpoint` TEXT NOT NULL COMMENT 'Url del endpoint para ser consumido', 
            `method` VARCHAR(6) NOT NULL COMMENT 'Método usado para consumir el endpoint: get, post, put, delete', 
            request_body TEXT NOT NULL COMMENT 'Cuerpo que se debe enviar en la petición al consumir el endpoint', 
            `response_body` TEXT NOT NULL COMMENT 'Respuesta obtenida al consumir el endpoint exitosamente', 
            `doc` TEXT NOT NULL COMMENT 'Link de la documentación Swagger', 
            created DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha creación del endpoint', 
            PRIMARY KEY (`name`)
        )";
        //$sql = "CREATE TABLE IF NOT EXISTS demo ( `name` VARCHAR(200) NOT NULL );";
        $statement = $this->conn->prepare($sql);
        $statement->execute();
    }

    public function deleteTable(){
        $sql = "DROP TABLE endpoints_sql_tool;";
        $statement = $this->conn->prepare($sql);
        $statement->execute();
    }

    public function saveQuery(array $values){
        $this->conn->insert($this->tableName, $values);
        //$conn->insert('endpoints_sql_tool', array('username' => 'jwage'));
        // INSERT INTO user (username) VALUES (?) (jwage)
    }

    public function getNameQuery(array $sqlData, string $method, string $module, string $db){
        
        if ($module == ''){
            $module = $sqlData["arrayTables"][0]["db"];
            if ($module == null){
                if ($db != ''){
                    $module = $db;
                }else{
                    $module = 'Generic';
                }
            }
        }

        $name = $module."_".$method;

            switch($method){
                case "get":

                    $name = $this->getQueryNameSelect($sqlData, $db, $name);
    
                break;
    
                case "post":                   
                    $name = $this->getQueryNameInsert($sqlData, $db, $name);
                break;

                case "put":
                    $name = $this->getQueryNameUpdate($sqlData, $db, $name);
                break;

                case "delete":
                    $name = $this->getQueryNameDelete($sqlData, $db, $name);
                break;
            }

            return $name;
      
    }

    public function getQueryNameSelect(array $sqlData, string $db, $name){
        //$name = $name;
        $dbSelect = ($db == "") ? $sqlData["arrayTables"][0]["db"] : $db;
        $name .= "_".$dbSelect."_".$sqlData["arrayTables"][0]["table"];

        $name = $this->processSelect($sqlData["arraySelect"], $sqlData["arrayColumns"], $name, $db);

        return $name;

    }

    public function getQueryNameDelete(array $sqlData, string $db, $name){
        $name = $name;
        $dbSelect = ($db == "") ? $sqlData["arrayTables"][0]["db"] : $db;
        $name .= "_".$dbSelect."_".$sqlData["arrayTables"][0]["table"]."_filterby";
        $newName = false;
        for ($i = 0; $i <= count($sqlData["arrayColumns"]); $i++){
               
            $name .= "_".(($sqlData["arrayColumns"][$i]["table"] == null) ? '': $sqlData["arrayColumns"][$i]["table"]."_").$sqlData["arrayColumns"][$i]["column"];
            $searchName = $this->searchName($name);

            if (count($searchName) == 0){
                $i = count($sqlData["arrayColumns"]);
                $newName = true;
            }

        }

        return ($newName == false) ? $name."_".uniqid() : $name;

    }

    public function getQueryNameUpdate(array $sqlData, string $db, $name){
        $name = $name;
        $dbSelect = ($db == "") ? $sqlData["arrayTables"][0]["db"] : $db;
        $name .= "_".$dbSelect."_".$sqlData["arrayTables"][0]["table"]."_set";
        $newName = false;


        $arraySet = array();
        $arrayWhere = array();

        for ($i = 0; $i < count($sqlData["dataSql"]["set"]); $i++){
           array_push($arraySet, array(
               "table"=>($sqlData["dataSql"]["set"][$i]["table"] == '')? null : $sqlData["dataSql"]["set"][$i]["table"],
               "column"=>$sqlData["dataSql"]["set"][$i]["column"]
           ));
        }
       
        for ($j = 0; $j < count($sqlData["arrayColumns"]); $j++){
            
            $columnFilter = array();
            for ($k = 0; $k < count($arraySet); $k++){
                if ($arraySet[$k]['table'] == $sqlData["arrayColumns"][$j]["table"] 
                and $arraySet[$k]["column"] == $sqlData["arrayColumns"][$j]["column"]){
                    $columnFilter = $sqlData["arrayColumns"][$j];
                }
                $k = count($arraySet);
            }
      
            
            if (count($columnFilter) == 0){
                array_push($arrayWhere, $sqlData["arrayColumns"][$j]);
            }
        }

        $limitArray = 1;
        $selectCounter = 0;
        $filterCounter = 0;

        while($newName == false){
            
            $nameTemp = $name;

            for ($i = 0; $i <= $selectCounter; $i++){
                if (isset($arraySet[$i])){
                    $nameTemp .= "_".(($arraySet[$i]["table"] == null) ? '' : $arraySet[$i]["table"]."_").$arraySet[$i]["column"];
                }
            }

            $nameTemp .= "_filterby";

            for ($i = 0; $i <= $filterCounter; $i++){
                if (isset($arrayWhere[$i])){
                    $nameTemp .= "_".(($arrayWhere[$i]["table"] == null) ? '' : $arrayWhere[$i]["table"]."_").$arrayWhere[$i]["column"];
                }
            }

            $searchName = $this->searchName($nameTemp);

            if (count($searchName) == 0){
                $newName = true;
                $name = $nameTemp;
            }else{

                if ($selectCounter >= $limitArray and $filterCounter >= $limitArray and $newName == false){
                    $nameTemp .= "_".uniqid();
                    $newName = true;
                }

                if ($selectCounter < $limitArray){
                    $selectCounter++;
                }

                if ($selectCounter == $limitArray){
                    if ($filterCounter < $limitArray){
                        $filterCounter++;
                    }
                }

                
                /*if ($selectCounter < $limitArray and $filterCounter == $limitArray){
                    $selectCounter++;
                }

                if ($selectCounter == $limitArray and $filterCounter == $limitArray){
                    $filterCounter++;
                }

                if ($selectCounter == $limitArray and $filterCounter == $limitArray and $newName == false){
                    $nameTemp .= "_".uniqid();
                    $name = $nameTemp;
                    $newName = true;
                }*/
            }


        }




        return $name;

    }


    public function getQueryNameInsert(array $sqlData, string $db, $name){
        $name = $name;
        $dbSelect = ($db == "") ? $sqlData["arrayTables"][0]["db"] : $db;
        $name .= "_".$dbSelect."_".$sqlData["arrayTables"][0]["table"];
        $newName = false;

        $searchName = $this->searchName($name);

        if (count($searchName) == 0){
            $newName = true;
        }

        if (isset($sqlData["arrayTables"][1]) and $newName == false){
            $name .= "_from_".(($sqlData["arrayTables"][1]["db"] != null) ? $sqlData["arrayTables"][1]["db"]."_" : "").$sqlData["arrayTables"][1]["table"]; 
            $searchName = $this->searchName($name);
            if (count($searchName) == 0){
                $newName = true;
            }
        }

        return ($newName == false) ? $name."_".uniqid() : $name;

    }

    public function processSelect(array $select, array $filters, string $name, string $db, $limitArray = 1){


        $newName = false;
        $selectCounter = 0;
        $filterCounter = 0;
      
        while($newName == false){
            $nameTemp = $name;
            for ($i = 0; $i <= $selectCounter; $i++){
                if (isset($select[$i])){
                    $nameTemp .= "_".(($select[$i]["table"] == null) ? '' : $select[$i]["table"]."_").$select[$i]["column"];
                }
            }

            $nameTemp .= "_filterby";

            for ($i = 0; $i <= $filterCounter; $i++){
                if (isset($filters[$i])){
                    $nameTemp .= "_".(($filters[$i]["table"] == null) ? '' : $filters[$i]["table"]."_").$filters[$i]["column"];
                }
            }
            
            $searchName = $this->searchName($nameTemp);

            if (count($searchName) == 0){
                $newName = true;
            }else{
                
                //$newName = true;
             
                /*if ($selectCounter < $limitArray and $filterCounter == $limitArray){
                    $selectCounter++;
                }

                if ($selectCounter == $limitArray and $filterCounter == $limitArray){
                    $filterCounter++;
                }*/
                if ($selectCounter < $limitArray){
                    $selectCounter++;
                }

                if ($selectCounter == $limitArray){
                    if ($filterCounter < $limitArray){
                        $filterCounter++;
                    }
                }

                if ($selectCounter >= $limitArray and $filterCounter >= $limitArray and $newName == false){
                    $nameTemp .= "_".uniqid();
                    $newName = true;
                }
            }

        }

        return $nameTemp;

    } 

}