<?php
session_start();
if (!isset($_SESSION["id"])){
    header('Location: close.php');
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">

<title>SQL</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.0.0/css/bootstrap.css'>
<link rel='stylesheet' href='css/index.css?v2'>
<link rel='stylesheet' href='css/loader.css?v2'>
<link rel="stylesheet" href="js/select2/select2.css">
		<link rel="stylesheet" href="js/select2/select2-bootstrap.css">

</head>
<body translate="no">
    <div class="wrap">
        <div class="container">
            <form class="cool-b4-form" id="dataForm" method="POST" action="generate.php" onsubmit="">
                <h2 class="text-center pt-4">Nuevo endpoint</h2>
                <div class="form-row">
                    <div class="col-md-12">
                        <div class="form-group">
                      
                           <select class="form-control" id="module" name="module">
                              
                           </select>
                           
                            <span class="input-highlight"></span>
                        </div>
                        <div class="form-group">
                            <label for="name">Nombre de la consulta (solo letras sin espacios). Opcional. Si se envía vacío, la herramienta colocará un nombre por defecto</label>
                            <input type="text" class="form-control" name="name" id="name" placeholder="" onfocus="Index.focusElement(this)" onblur="Index.blurElement(this)">

                            <span class="input-highlight"></span>
                        </div>

                        <div class="form-group">
                      
                            <select class="form-control" id="query_type" name="query_type">
                                <option value="">Selecciona tipo de consulta</option>
                                <option value="UNIQ">Base de datos única</option>
                                <option value="MULTIPLE">Múltiple base de datos</option>
                            </select>
                            
                             <span class="input-highlight"></span>
                         </div>

                         <div class="form-group" id="UNIQ" style="display: none;">
                            <label for="db" style="font-weight: bold;">Al ser una consulta a una única base de datos, no se aceptan expresiones basededatos.tabla. Selecciona el nombre de la base de datos para ser enviada como parámetro.</label>
                            
                            <select class="form-control" id="db" name="db">
                                <option value="">Selecciona una BD</option>
                                
                            </select>
                         
                         </div>

                         <div class="form-group" id="MULTIPLE" style="display: none;">
                            <label style="font-weight: bold;">Al ser una consulta multi-base de datos, se debe tener expresiones basededatos.tabla</label>
                            
                         </div>
              
                        <div class="form-group">
                            <label for="sql">Inserta la consulta reemplazando los filtros por el carácter ? sin usar comillas simples '' o dobles ""</label>
                            <textarea name="sql" id="sql" cols="5" rows="10" style="resize: vertical;" class="form-control" placeholder="" onfocus="Index.focusElement(this)" onblur="Index.blurElement(this)"></textarea>
                            
                            <span class="input-highlight"></span>
                        </div>
                    </div>
                  
                </div>
                <div class="col-md-10 mx-auto mt-3">
                    <input type="hidden" id="data" name="data" value="">
                    <button type="button" class="btn btn-lg btn-primary btn-block" onclick="Index.validateForm()">Ejecutar</button>
                    <button type="button" class="btn btn-lg btn-danger btn-block" onclick="window.location.href = 'main.php'">Volver</button>
                </div>
            </form>
        </div>
    </div>
    <div id="loader"><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" style="margin: auto; background: rgb(255, 255, 255); display: block; shape-rendering: auto;" width="200px" height="200px" viewBox="0 0 100 100" preserveAspectRatio="xMidYMid">
        <circle cx="50" cy="50" fill="none" stroke="#ea4c88" stroke-width="10" r="35" stroke-dasharray="164.93361431346415 56.97787143782138" transform="rotate(266.786 50 50)">
        <animateTransform attributeName="transform" type="rotate" repeatCount="indefinite" dur="1s" values="0 50 50;360 50 50" keyTimes="0;1"></animateTransform>
        </circle>
        </svg></div>
<script src='https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js'></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/5.4.0/bootbox.min.js"></script>
<script src="https://unpkg.com/node-sql-parser/umd/index.umd.js"></script>
<script src="js/select2/select2.js"></script>
<script src="js/index.js?v1"></script>
<script id="rendered-js">
(function ($) {
  "use strict"; // Start of use strict

  // Detect when form-control inputs are not empty
  $(".cool-b4-form .form-control").on("input", function () {
    if ($(this).val()) {
      $(this).addClass("hasValue");
    } else {
      $(this).removeClass("hasValue");
    }
  });
})(jQuery); // End of use strict
//# sourceURL=pen.js
    </script>
</body>
</html>
