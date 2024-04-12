<?php
declare (strict_types = 1);
namespace Generator\Classes;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Connection;

class Analyzer
{

	public function validate(array $databases, array $sqlData, array $databaseSelect = array())
	{
      
       $conn = null;

        //buscar que las tablas existan en la bd
		$arrayFieldsRequest = array();
		$arrayFieldsResponse = array();
		$resultArray = array();
		$arrayTablesQuery = Array();

		for ($i = 0; $i < count($sqlData["arrayTables"]); $i++) {

            if(count($databaseSelect) == 0){

                $db = $sqlData["arrayTables"][$i]["db"];
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

            }

            $schemaManager = $conn->getSchemaManager();

            $table = $schemaManager->listTableDetails($sqlData["arrayTables"][$i]["table"]);

            /*$tables = $schemaManager->listTables();

            foreach ($tables as $table) {*/
                //echo $table->getName() . " columns:\n\n";
                //echo $table->getName() . "\n\n";
                /*foreach ($table->getColumns() as $column) {
                    echo ' - ' . $column->getName() . "\n";
                }*/
            //}


            
            //exit;
            
			if (count($table->getColumns()) == 0) {
				$resultArray = array(
					"error" => "La tabla " . $sqlData["arrayTables"][$i]["table"] . " no existe en la base de datos. "
				);
				$i = count($sqlData["arrayTables"]);
			} else {
				array_push(
					$arrayTablesQuery,
					array(
						"as" => $sqlData["arrayTables"][$i]["as"],
						"name" => $sqlData["arrayTables"][$i]["table"],
						"dbal_table" => $table
					)
				);
            }
            


        }


    



        //validar que las columnas existan en la tabla correspondiente y que no existen ambiguedades. Este for es para columnas de tipo preparadas "?"

		if (count($resultArray) == 0 ) {
			for ($i = 0; $i < count($sqlData["arrayColumns"]); $i++) {
				$columnData = $sqlData["arrayColumns"][$i];
				$column = $columnData["column"];
				$columnName = $columnData["columnName"];
				$columnTable = $columnData["table"];

				if ($columnTable == null) {
					$counterFound = 0;
					$columnDBALData = NULL;
					for ($j = 0; $j < count($arrayTablesQuery); $j++) {
						foreach (($arrayTablesQuery[$j]["dbal_table"])->getColumns() as $columnDBAL) {
							if ($columnDBAL->getName() == $column) {
								$columnDBALData = $columnDBAL;
								$counterFound++;
							}
						}
					}

					if ($counterFound == 0) {
						$resultArray = array(
							"error" => "La columna " . $column . " no tiene pertenece a ninguna tabla."
						);
						$i = count($sqlData["arrayColumns"]);
					} else if ($counterFound > 1) {
						$resultArray = array(
							"error" => "La columna " . $column . " presenta ambiguedad. Número de referencias: " . $counterFound . " columnas."
						);
						$i = count($sqlData["arrayColumns"]);
					} else {
						if ($sqlData["arrayColumns"][$i]["operator"] != "") {
							array_push(
								$arrayFieldsRequest,
								array("columnName"=>$sqlData["arrayColumns"][$i]["columnName"],"name" => $columnName, "operator"=>$sqlData["arrayColumns"][$i]["operator"],"parameter"=>$sqlData["arrayColumns"][$i]["parameter"], "type" => $columnDBALData->getType()->getName(), "comment" => $columnDBALData->getComment())
							);
						}
					}
				} else {

                    //index por nombre de tabla
					$indexName = (int)array_search($sqlData["arrayColumns"][$i]["table"], array_column($arrayTablesQuery, 'name'));
                    //index por alias
					$indexAlias = (int)array_search($sqlData["arrayColumns"][$i]["table"], array_column($arrayTablesQuery, 'as'));

					if ($indexName == -1 and $indexAlias == -1) {
						$resultArray = array(
							"error" => "La columna " . $columnName . " pertenece a una tabla que no tiene referencia en el query."
						);
						$i = count($sqlData["arrayColumns"]);
					} else {
						$tableDBAL = NULL;
						if ($indexName != -1) {
							$tableDBAL = $arrayTablesQuery[$indexName]["dbal_table"];
						} else {
							$tableDBAL = $arrayTablesQuery[$indexAlias]["dbal_table"];
                        }
                        
                        
                        $columnDBALData = NULL;
                        
						foreach ($tableDBAL->getColumns() as $columnDBAL) {
							if ($columnDBAL->getName() == $column) {
								$columnDBALData = $columnDBAL;
								break;
							}
						}

						if ($columnDBALData == NULL) {
							$resultArray = array(
								"error" => "La columna " . $columnName . " pertenece a una tabla que no tiene referencia en el query."
							);
							$i = count($sqlData["arrayColumns"]);
						} else {
							if ($sqlData["arrayColumns"][$i]["operator"] != "") {
								array_push(
									$arrayFieldsRequest,
									array(
										"columnName"=>$sqlData["arrayColumns"][$i]["columnName"], "name" => $columnName, "operator"=>$sqlData["arrayColumns"][$i]["operator"], "parameter"=>$sqlData["arrayColumns"][$i]["parameter"],"type" => $columnDBALData->getType()->getName(), "comment" => $columnDBALData->getComment()
									)
								);
							}
						}
					}
				}
			}
        }
        
     
        

		if (count($resultArray) == 0) {
			switch ($sqlData["method"]) {
                case "post":
                    if ($sqlData["tableCreate"] == ''){
                        $arrayFieldsResponse = array(
                            "id" => 0,
                            "message" => "Registro creado con éxito"
                        );
                    }else{
                        $arrayFieldsResponse = array(
                            "message" => "Eliminación realizada con éxito."
                        );
                    }
					break;

				case "delete":
					$arrayFieldsResponse = array(
						"message" => "Eliminación realizada con éxito."
					);
					break;

                case "update":
                    case "put":
					$arrayFieldsResponse = array(
						"message" => "Actualización realizada con éxito."
					);
					break;

                case "get":
                    

                    for ($i = 0; $i < count($sqlData["arraySelect"]); $i++) {
                        $columnData = $sqlData["arraySelect"][$i];
                        $column = $columnData["column"];
                        $columnTable = $columnData["table"];

                        $columnName = ($columnTable == null) ? $column : $columnTable.".".$column;
                        
        
                        if ($columnTable == null) {
                            $counterFound = 0;
                            $columnDBALData = NULL;
                            for ($j = 0; $j < count($arrayTablesQuery); $j++) {
                                foreach (($arrayTablesQuery[$j]["dbal_table"])->getColumns() as $columnDBAL) {
                                    if ($columnDBAL->getName() == $column) {
                                        $columnDBALData = $columnDBAL;
                                        $counterFound++;
                                    }
                                }
                            }
        
                            if ($counterFound == 0) {
                                $resultArray = array(
                                    "error" => "La columna " . $column . " no tiene pertenece a ninguna tabla."
                                );
                                $i = count($sqlData["arrayColumns"]);
                            } else if ($counterFound > 1) {
                                $resultArray = array(
                                    "error" => "La columna " . $column . " presenta ambiguedad. Número de referencias: " . $counterFound . " columnas."
                                );
                                $i = count($sqlData["arrayColumns"]);
                            }
                        } else {
        
                            //index por nombre de tabla
                            $indexName = (int)array_search($sqlData["arraySelect"][$i]["table"], array_column($arrayTablesQuery, 'name'));
                            //index por alias
                            $indexAlias = (int)array_search($sqlData["arraySelect"][$i]["table"], array_column($arrayTablesQuery, 'as'));
        
                            if ($indexName == -1 and $indexAlias == -1) {
                                $resultArray = array(
                                    "error" => "La columna " . $columnName . " pertenece a una tabla que no tiene referencia en el query."
                                );
                                $i = count($sqlData["arraySelect"]);
                            } else {
                                $tableDBAL = NULL;
                                if ($indexName != -1) {
                                    $tableDBAL = $arrayTablesQuery[$indexName]["dbal_table"];
                                } else {
                                    $tableDBAL = $arrayTablesQuery[$indexAlias]["dbal_table"];
                                }
                                $columnDBALData = NULL;
                                foreach ($tableDBAL->getColumns() as $columnDBAL) {
                                    if ($columnDBAL->getName() == $column) {
                                        $columnDBALData = $columnDBAL;
                                        break;
                                    }
                                }
        
                                if ($columnDBALData == NULL) {
                                    $resultArray = array(
                                        "error" => "La columna " . $columnName . " pertenece a una tabla que no tiene referencia en el query."
                                    );
                                    $i = count($sqlData["arraySelect"]);
                                }
                            }
                        }
                    }


                    if (count($resultArray) == 0){
                        if ($sqlData["dataSql"]["columns"] == "*") {
                            for ($i = 0; $i < count($arrayTablesQuery); $i++) {
                                foreach ( ($arrayTablesQuery[$i]["dbal_table"])->getColumns() as $column) {

                                    $nameTable = ($arrayTablesQuery[$i]["as"] == null) ? ($arrayTablesQuery[$i]["dbal_table"])->getName() : $arrayTablesQuery[$i]["as"];
                                    array_push(
                                        $arrayFieldsResponse,
                                        array(
                                            "name" =>  $nameTable. "." . $column->getName(), "type" => $column->getType()->getName(), "comment" => $column->getComment()
                                        )
                                    );
                                }
                            }
                        } else {
                           
                            for ($i = 0; $i < count($sqlData["dataSql"]["columns"]); $i++) {
                                $columnName = "";

                                if ($sqlData["dataSql"]["columns"][$i]["expr"]["type"] == "number") {
                                    array_push(
                                        $arrayFieldsResponse,
                                        array("name" => $sqlData["dataSql"]["columns"][$i]["expr"]["value"], "type" => "integer", "comment" => "Valor como columna")
                                    );
                                } else if ($sqlData["dataSql"]["columns"][$i]["expr"]["type"] == "string") {
                                    array_push(
                                        $arrayFieldsResponse,
                                        array("name" => $sqlData["dataSql"]["columns"][$i]["expr"]["value"], "type" => "string", "comment" => "Valor como columna")
                                    );
                                } else if ($sqlData["dataSql"]["columns"][$i]["expr"]["type"] == "column_ref") {
                                    $columnTable = $sqlData["dataSql"]["columns"][$i]["expr"]["table"];

                                    $column = $sqlData["dataSql"]["columns"][$i]["expr"]["column"];

                                    $columnName = ($sqlData["dataSql"]["columns"][$i]["as"] != null) ? $sqlData["dataSql"]["columns"][$i]["as"] : '';

                                    if ($columnTable == null) {
                                        if ($columnName == '') {
                                            $columnName = $column;
                                        }

                                        $counterFound = 0;
                                        $columnDBALData = NULL;
                                        for ($j = 0; $j < count($arrayTablesQuery); $j++) {
                                            foreach (($arrayTablesQuery[$j]["dbal_table"])->getColumns() as $columnDBAL) {
                                                if ($columnDBAL->getName() == $column) {
                                                    $columnDBALData = $columnDBAL;
                                                    $counterFound++;
                                                }
                                            }
                                        }

                                        if ($counterFound == 0) {
                                            $resultArray = array(
                                                "error" => "La columna " . $column . " del select no pertenece a ninguna tabla."
                                            );
                                            $i = count($sqlData["dataSql"]["columns"]);
                                        } else if ($counterFound > 1) {
                                            $resultArray = array(
                                                "error" => "La columna " . $column . " presenta ambiguedad. Número de referencias: " . $counterFound . " columnas."
                                            );
                                            $i = count($sqlData["dataSql"]["columns"]);
                                        } else {
                                            
                                                array_push(
                                                    $arrayFieldsResponse,
                                                    array("name" => $columnName, "type" => $columnDBALData->getType()->getName(), "comment" => $columnDBALData->getComment())
                                                );
                                            
                                        }
                                    } else {
                                        if ($columnName == '') {
                                            $columnName = $columnTable . "." . $column;
                                        }
                                                //index por nombre de tabla
                                        $indexName = (int)array_search($sqlData["dataSql"]["columns"][$i]["expr"]["table"], array_column($arrayTablesQuery, 'name'));
                                                //index por alias
                                        $indexAlias = (int)array_search($sqlData["dataSql"]["columns"][$i]["expr"]["table"], array_column($arrayTablesQuery, 'as'));

                                        if ($indexName == -1 and $indexAlias == -1) {
                                            $resultArray = array(
                                                "error" => "La columna " . $columnName . " pertenece a una tabla que no tiene referencia en el query."
                                            );
                                            $i = count($sqlData["dataSql"]["columns"]);
                                        } else {
                                            $tableDBAL = NULL;
                                            if ($indexName != -1) {
                                                $tableDBAL = $arrayTablesQuery[$indexName]["dbal_table"];
                                            } else {
                                                $tableDBAL = $arrayTablesQuery[$indexAlias]["dbal_table"];
                                            }
                                            $columnDBALData = NULL;
                                            foreach ($tableDBAL->getColumns() as $columnDBAL) {
                                                if ($columnDBAL->getName() == $column) {
                                                    $columnDBALData = $columnDBAL;
                                                    break;
                                                }
                                            }

                                            if ($columnDBALData == NULL) {
                                                $resultArray = array(
                                                    "error" => "La columna " . $columnName . " pertenece a una tabla que no tiene referencia en el query."
                                                );
                                                $i = count($sqlData["dataSql"]["columns"]);
                                            } else {
                                              
                                                    array_push(
                                                        $arrayFieldsResponse,
                                                        array("name" => $columnName, "type" => $columnDBALData->getType()->getName(), "comment" => $columnDBALData->getComment())
                                                    );
                                                
                                            }
                                        }
                                    }
                                } else if ($sqlData["dataSql"]["columns"][$i]["expr"]["type"] == "aggr_func") {
                                    $columnName = ($sqlData["dataSql"]["columns"][$i]["as"] != null) ? $sqlData["dataSql"]["columns"][$i]["as"] : '';
                                    if ($columnName == '') $columnName = $sqlData["dataSql"]["columns"][$i]["expr"]["name"];
                                    array_push(
                                        $arrayFieldsResponse,
                                        array(
                                            "name" => $columnName, "type" => "string", "comment" => "Función de agregación tipo " . $sqlData["dataSql"]["columns"][$i]["expr"]["name"]
                                        )
                                    );
                                }
                            }
                        }
                    
                    }

					break;
			}
		}
     
		if (count($resultArray) == 0) {
			return array(
				"ok" => [
					"request" => $arrayFieldsRequest,
					"response" => $arrayFieldsResponse
				]
			);
		} else {
			return $resultArray;
		}
	}
}
?>