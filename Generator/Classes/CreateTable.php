<?php
declare (strict_types = 1);
namespace Generator\Classes;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Connection;

class CreateTable{
    public static function validate(array $databases, string $table, string $type, $databaseSelect = array())
	{
      
       $conn = null;
    
       $tableName = '';

       if(count($databaseSelect) == 0){

            $tableInfo = explode(".",$table);

            $tableName = $tableInfo[1];

            $db = $tableInfo[0];
            $indexDBInfo = (int) array_search($db, array_column($databases, 'database'));

            $conn = DriverManager::getConnection([
                'dbname' => $databases[$indexDBInfo]["database"],
                'user' => $databases[$indexDBInfo]["username"],
                'password' => $databases[$indexDBInfo]["password"],
                'host' => $databases[$indexDBInfo]["host"],
                'driver' => $databases[$indexDBInfo]["driver"],
                'charset'=> $databases[$indexDBInfo]["charset"]
            ]);

       }else{
            $conn = DriverManager::getConnection([
                'dbname' => $databaseSelect["database"],
                'user' => $databaseSelect["username"],
                'password' => $databaseSelect["password"],
                'host' => $databaseSelect["host"],
                'driver' => $databaseSelect["driver"],
                'charset'=> $databaseSelect["charset"]
            ]);
            $tableName = $table;
       }

       $schemaManager = $conn->getSchemaManager();

       $table = $schemaManager->listTableDetails($tableName);

       if (count($table->getColumns()) == 0) {
            if ($type == "INTO"){
                return array(
					"error" => "La tabla " . $tableName. " no existe en la base de datos. Para usar el select into la tabla destino debe existir."
				);
            }else{
                return array("ok"=>1);
            }
       }else{
            if ($type == "INTO"){
                return array("ok"=>1);
            }else{
                return array(
                    "error" => "La tabla " . $tableName. " ya existe en la base de datos. Para usar el create table la tabla no debe existir."
                );
            }
       }

    }
}

?>