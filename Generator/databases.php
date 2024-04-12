<?php
session_start();
if (isset($_SESSION["id"])){
    $databases = require __DIR__.'/Database/databases.php';

    $arrayDatabases = array();

    for ($i = 0; $i < count($databases);$i++){
        array_push($arrayDatabases, array("name"=>$databases[$i]["database"]));
    }
    echo json_encode($arrayDatabases);
}else{
    header('Location: close.php');
}
?>