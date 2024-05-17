<?php
session_start();
if (!isset($_SESSION["id"])){
    header('Location: close.php');
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="robots" content="noindex, nofollow">

    <title>Listado de herramientas</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="//maxcdn.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css">
    <style type="text/css">
        </style>
    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js"></script>
   
</head>
<body>
    <div class="container">
    <h1 style="margin: auto; text-align: center;">Listado de herramientas</h1>
    <br />
    <div class="row">
        <div class="col-sm-4 py-2">
            <div class="card h-100 text-white bg-success">
                <div class="card-body">
                    <h3 class="card-title">Generador de endpoints</h3>
                    <p class="card-text">herramienta para generar endpoints rest a partir de una consulta SQL.</p>
                    <a href="generator.php" class="btn btn-outline-light">Ir a herramienta</a>
                </div>
            </div>
        </div>
        <!--<div class="col-sm-4 py-2">
            <div class="card h-100 text-white bg-primary">
                <div class="card-body">
                    <h3 class="card-title">Crud´s API</h3>
                    <p class="card-text">Documentación de las API CRUD.</p>
                    <a href="http://ms.gtp.bz/crud/docs/" target="_blank" class="btn btn-outline-light">Ir a documentación</a>
                </div>
            </div>
        </div>-->
        <div class="col-sm-4 py-2">
            <div class="card text-white bg-danger">
                <div class="card-body">
                    <a href="close.php" class="btn btn-outline-light">Salir</a>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
