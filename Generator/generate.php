<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
use Doctrine\DBAL\DriverManager;
use Generator\Classes\Analyzer;
use Generator\Classes\AnalyzerDDL;
use Generator\Classes\MethodDDL;
use Generator\Classes\CreateTable;
use Generator\Classes\Method;
use Generator\Classes\Routes;
use Generator\Classes\SwaggerAnnotation;
use Generator\Classes\AuthenticationTypeApi as AuthenticationTypeApi;
use Generator\Classes\Api;
use Generator\Classes\ConfigureIndex;
use Generator\Classes\Middleware;
use Generator\Classes\Setting;
use Generator\Classes\Utils\Normalize;
use Generator\Classes\NameQuery;

$mensajeError = "";
$classButton = "success";
$serverProtocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') ? "https" : "http";

const FOLDER_CRUD_GENERATOR = 'Generator';
$basePath = '';
$domain = '';
if (isset($argc)) {
	if (isset($argv[1])){
        $domain = $argv[1];
    }
    if (isset($argv[2])){
        $basePath = $argv[2];
    }
}else{
    $domain = $_SERVER['HTTP_HOST'];

    $host_upper = strtoupper($domain);
    $path   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
    $partsPath = explode("/",$path);
    
    $partsPath = array_filter($partsPath, "strlen");
    $partsPath = array_values($partsPath);
    $indexEnd = 0;
    for ($i = 0; $i < count($partsPath); $i++){
        if ($partsPath[$i] == FOLDER_CRUD_GENERATOR){
            $indexEnd = $i;
            $i = count($partsPath);
        }
    }
    if ($indexEnd > 0){
        for ($j = 0; $j < $indexEnd; $j++){
            $basePath .= "/";
            $basePath .= $partsPath[$j];
        }
    }
}


$databases = require __DIR__.'/Database/databases.php';

require __DIR__ . '/../vendor/autoload.php';

$AUTHENTICATION = AuthenticationTypeApi::BASIC;

$ROOT_PATH = __DIR__."/../src/EndpointsApi";

$DEFAULT_NAMESPACE = "EndpointsApi\\";

$PATH_ROUTES = __DIR__."/../config/routes.php";

$module = Normalize::normalizeUCWords($_POST["module"]);


$queryName = (isset($_POST["name"]) and $_POST["name"] != '') ? $_POST["name"] : '';

$data = json_decode($_POST["data"], true);

//$rawSql = $_POST["sql"];
$rawSql = $data["sqlUser"];

$rawSql = str_replace('"','', $rawSql);
$rawSql = str_replace('".','', $rawSql);
$rawSql = str_replace('."','', $rawSql);
$rawSql = str_replace("'.",'', $rawSql);
$rawSql = str_replace(".'",'', $rawSql);

$rawSql = str_replace("'?'","?", $rawSql);
$rawSql = str_replace('"?"','?', $rawSql);

$rawSql = str_replace('"?','?', $rawSql);
$rawSql = str_replace('?"','?', $rawSql);

$rawSql = str_replace("'?","?", $rawSql);
$rawSql = str_replace("?'","?", $rawSql);

$arrayParameters = array();

$arrayColumsNames = array();

$queryType = $_POST["query_type"];

$db = isset($_POST["db"]) ? $_POST["db"] : '';

$arrayRequest = array();
$arrayResponse = array();
$endpoint = '';
$doc = '';
$method = '';

$sqlParameters = null;
$arrayParameters = array();

 //buscar query
 $queryNameFinal = new NameQuery([
    'driver' => 'pdo_mysql',
    "host" => "localhost",
    "database" => "maestra",
    "username" => "gtp-web-dbg01",
    "password" => "206Zd542czA4",
    'charset'   => 'utf8'
]);



$searchQuery = $queryNameFinal->searchQuery($rawSql);

if (count($searchQuery) > 0){
                
    $rawSql = $searchQuery[0]["query"];
    $queryName = $searchQuery[0]["name"];
    $arrayRequest["request"] = json_decode($searchQuery[0]["request_body"], true);

    $keysRequest = array_keys($arrayRequest["request"]);
    for ($i = 0; $i < count($keysRequest); $i++){
        array_push($arrayParameters, $keysRequest[$i]);
    } 
    $sqlParameters =  PdoDebugger::show($rawSql, $arrayParameters);

    $arrayResponse = json_decode($searchQuery[0]["response_body"], true);
    $endpoint = $searchQuery[0]["endpoint"];
    $doc = $searchQuery[0]["doc"];
    $method = $searchQuery[0]["method"];

}else{

    $method = $data["method"];

    if (isset($data["arrayTables"])){

        $validaBD = false;
        $databaseSelect = array();
        $arrayDatabases = array();

        if ($queryType == "UNIQ"){
        
            for ($i = 0; $i < count($databases);$i++){
                if ($databases[$i]["database"] == $db){
                    $validaBD = true;
                    $databaseSelect = $databases[$i];
                    $i = count($databases);
                }
            } 
        }else{
            $validaBD = true;
        
            for ($i = 0; $i < count($data["arrayTables"]); $i++){
                array_push($arrayDatabases, $data["arrayTables"][$i]["db"]);
            }

            for ($i = 0; $i < count($data["arrayTables"]);$i++){
                if (!in_array($data["arrayTables"][$i]["db"], $arrayDatabases)){
                    $validaBD = false;
                    $i = count($databases);
                }
            }

        }

        if ($validaBD){
            $specialPost = array("ok"=>1);
            if ($data["tableCreate"] != ''){
                $specialPost = CreateTable::validate($databases, $data["tableCreate"], $data["typeInsertTable"], $databaseSelect); 
            }

            $analyzer = new Analyzer();

            $arrayAnalyze = $analyzer->validate($databases, $data, $databaseSelect);

            if (isset($arrayAnalyze["error"])){
                $mensajeError = $arrayAnalyze["error"];
                $classButton = "danger";
            }else if (isset($specialPost["error"]) ){
                $mensajeError = $specialPost["error"];
                $classButton = "danger";
            }else{

                if ($queryName == ''){
                                
                    $queryName = $queryNameFinal->getNameQuery($data, $data["method"], $module, $db);
                }else{
                    $searchQueryName =  $queryNameFinal->searchName($queryName);
                    if (count($searchQueryName) > 0){
                        $queryName .=  uniqid();
                    } 
                }


                $swagger = new SwaggerAnnotation();

                $swagger->config($data["method"], $queryName, $module);

                $arrayRequest = $swagger->validateRequstFields($arrayAnalyze["ok"]["request"]);

                $requestShema = $swagger->requestShema($arrayRequest["fields"]);



                for ($i = 0; $i < count($arrayRequest["fields"]); $i++){

                    for ($j = 0; $j < count($arrayRequest["fields"][$i]["parameter"]); $j++){
                        array_push($arrayParameters,$arrayRequest["fields"][$i]["parameter"][$j]);
                    }

                }

                $sqlParameters =  PdoDebugger::show($rawSql, $arrayParameters);


                $responseShema = '';

                $arrayResponse = array();

                $doc = $serverProtocol . $domain . $basePath . $swagger->getPathDoc();

                $endpoint = $serverProtocol . $domain . $basePath . "/" . strtolower(Normalize::normalizeUCWords($module)) . "/" . strtolower(Normalize::normalizeUCWords($queryName));


                if ($data["method"] == "get"){
                    $responseShema = $swagger->constructShemaResponseSelect($arrayAnalyze["ok"]["response"]);

                    for ($i = 0; $i < count($arrayAnalyze["ok"]["response"]); $i++){
                        $arrayResponse[$arrayAnalyze["ok"]["response"][$i]["name"]] = $arrayAnalyze["ok"]["response"][$i]["type"];
                    }
                    $arrayResponse = array($arrayResponse);
                }else{
                    $arrayResponse = $arrayAnalyze["ok"]["response"];
                }

                $swaggerMethod = $swagger->annotationsMethodGeneric($AUTHENTICATION,'');

                $TotalSwaggerAnnotations = $requestShema.'

                '.$responseShema.'

                '.$swaggerMethod;

                $queryMethod = new Method();

                $queryMethod->pathConfigure($module, $queryName, $ROOT_PATH);
                $databaseName = (count($databaseSelect) == 0) ? $arrayDatabases[0]: $databaseSelect["database"];
                $queryMethod->setConfigMethod($DEFAULT_NAMESPACE, $rawSql, $data, $data["method"],$TotalSwaggerAnnotations, $databaseName);

                $queryMethod->generate();

                $formatCode = exec("php ". __DIR__."/Classes/Utils/phptidy.php suffix ".$queryMethod->getPathFile());
                //echo $formatCode;
                $routes = new Routes($PATH_ROUTES, $ROOT_PATH, $DEFAULT_NAMESPACE);

                $routes->generate();

                $formatCode = exec("php ". __DIR__."/Classes/Utils/phptidy.php suffix ".$PATH_ROUTES);


                
                //api

                $pathApi = __DIR__."/../src/api.php";
                Api::generate($pathApi, Middleware::shemaGlobal($AUTHENTICATION), $ROOT_PATH, $AUTHENTICATION, $domain, $basePath);
                $formatCode = exec("php ". __DIR__."/Utils/phptidy.php suffix ".$pathApi);


                //index
                $pathIndex = __DIR__."/../public_html/index.php";
                ConfigureIndex::generate($pathIndex, $basePath);

                $formatCode = exec("php ". __DIR__."/Utils/phptidy.php suffix ".$pathIndex);

                //settings

                $settings = new Setting();
                $settings->addDatabases($databases);
                $pathSettings = __DIR__."/../config/settings.php";
                $settings->generate($pathSettings);
                $formatCode = exec("php ". __DIR__."/Utils/phptidy.php suffix ".$pathSettings);

                //middleware
                $pathMiddleware = __DIR__."/../config/middleware.php";
                Middleware::generate($pathMiddleware, $AUTHENTICATION, $ROOT_PATH, $basePath);
                $formatCode = exec("php ". __DIR__."/Utils/phptidy.php suffix ".$pathMiddleware);

                $queryNameFinal->saveQuery(array(
                    "name"=>$queryName,
                    "query" => $rawSql,
                    "endpoint" => $endpoint,
                    "method" => $method,
                    "request_body" => json_encode($arrayRequest["request"]),
                    "response_body" => json_encode($arrayResponse),
                    "doc" => $doc
                ));


            //   echo SqlFormatter::format($sqlParameters);

            // echo json_encode($arrayAnalyze);

            

            }


        }else{
            $classButton = "danger";
            $mensajeError = "La base de datos no fue encontrada dentro del listado. Verifica y vuelve a intentarlo.";
        }

    }else{
        $validaBD = false;
        $databaseSelect = array();
        $arrayParameters = array();

        if ($queryType == "UNIQ"){
            
            for ($i = 0; $i < count($databases);$i++){
                if ($databases[$i]["database"] == $db){
                    $validaBD = true;
                    $databaseSelect = $databases[$i];
                    $i = count($databases);
                }
            } 
            $arrayParameters = array("table:value");

        }else{
            $validaBD = true;
            $arrayParameters = array("database.table:value");
        }

        if ($validaBD == true){
        
            $sqlParameters =  PdoDebugger::show($rawSql, $arrayParameters);


            if ($queryName == ''){
                $queryName = $queryNameFinal->getNameQuery($data, $data["method"], $module, $db);
            }else{
                $searchQueryName =  $queryNameFinal->searchName($queryName);
                if ($searchQuery != null and count($searchQueryName) > 0){
                    $queryName .=  uniqid();
                } 
            }

            $swagger = new SwaggerAnnotation();

            $swagger->config($data["method"], $queryName, $module, 'DROP');

            $analyzerArray = AnalyzerDDL::drop($queryType);


            

            $arrayRequest = $swagger->validateRequstFields($analyzerArray["ok"]["request"]);

        

            $requestShema = $swagger->requestShema($arrayRequest["fields"]);
            

            $responseShema = '';

            $arrayResponse = $analyzerArray["ok"]["response"];

            $swaggerMethod = $swagger->annotationsMethodGeneric($AUTHENTICATION,'');

            $TotalSwaggerAnnotations = $requestShema.'

            '.$responseShema.'

            '.$swaggerMethod;

            $queryMethod = new MethodDDL();

            $queryMethod->pathConfigure($module, $queryName, $ROOT_PATH);
            $databaseName = (count($databaseSelect) == 0) ? $databases[0]["database"]: $databaseSelect["database"];
            $queryMethod->setConfigMethod($DEFAULT_NAMESPACE, $rawSql, $queryType, $data["method"],$TotalSwaggerAnnotations, $databaseName);

            $queryMethod->generate();

            $formatCode = exec("php ". __DIR__."/Classes/Utils/phptidy.php suffix ".$queryMethod->getPathFile());
            //echo $formatCode;
            $routes = new Routes($PATH_ROUTES, $ROOT_PATH, $DEFAULT_NAMESPACE);

            $routes->generate();

            $formatCode = exec("php ". __DIR__."/Classes/Utils/phptidy.php suffix ".$PATH_ROUTES);


            
            //api

            $pathApi = __DIR__."/../src/api.php";
            Api::generate($pathApi, Middleware::shemaGlobal($AUTHENTICATION), $ROOT_PATH, $AUTHENTICATION, $domain, $basePath);
            $formatCode = exec("php ". __DIR__."/Utils/phptidy.php suffix ".$pathApi);


            //index
            $pathIndex = __DIR__."/../public_html/index.php";
            ConfigureIndex::generate($pathIndex, $basePath);

            $formatCode = exec("php ". __DIR__."/Utils/phptidy.php suffix ".$pathIndex);

            //settings

            $settings = new Setting();
            $settings->addDatabases($databases);
            $pathSettings = __DIR__."/../config/settings.php";
            $settings->generate($pathSettings);
            $formatCode = exec("php ". __DIR__."/Utils/phptidy.php suffix ".$pathSettings);

            //middleware
            $pathMiddleware = __DIR__."/../config/middleware.php";
            Middleware::generate($pathMiddleware, $AUTHENTICATION, $ROOT_PATH, $basePath);
            $formatCode = exec("php ". __DIR__."/Utils/phptidy.php suffix ".$pathMiddleware);

            $doc = $serverProtocol . $domain . $basePath . $swagger->getPathDoc();

            $endpoint = $serverProtocol . $domain . $basePath . "/" . strtolower(Normalize::normalizeUCWords($module)) . "/" . strtolower(Normalize::normalizeUCWords($queryName));

            $queryNameFinal->saveQuery(array(
                "name"=>$queryName,
                "query" => $rawSql,
                "endpoint" => $endpoint,
                "method" => $method,
                "request_body" => json_encode($arrayRequest["request"]),
                "response_body" => json_encode($arrayResponse),
                "doc" => $doc
            ));
        }else{
            $classButton = "danger";
            $mensajeError = "Ocurrió un error al procesar la consulta DROP. Intenta de nuevo.";
        }

    }
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
<style>
pre {outline: 1px solid #ccc; padding: 5px; margin: 5px; }
.string { color: green; }
.number { color: darkorange; }
.boolean { color: blue; }
.null { color: magenta; }
.key { color: red; }


    </style>
</head>
<body translate="no">
    <div class="wrap">
        <div class="container">
            <form class="cool-b4-form" id="dataForm" method="POST" action="#" onsubmit="return false">
                <h2 class="text-center pt-4">Resultado</h2>
                <div class="form-row">
                    <div class="col-md-12">
                        <div class="form-group" style="margin:auto; text-align:center;">

                            <?php 
                          
                                if ($mensajeError == ""){
                                    echo SqlFormatter::format($sqlParameters);

                                    echo "<br />";

                                }else{
                                    echo $mensajeError;
                                    echo "<br />";
                                }
                            ?>
                          
                        </div>
                        <div class="form-group" style="text-align:center;">
                        <?php 
                          
                          if ($mensajeError == ""){
                            echo '<div><span>Método:  </span><h3 style="display:inline-block;">'.$data["method"].'</h3></div>';
                            echo "<br />";
                          }

                          ?>
                        </div>

                        <div class="form-group">
                            
                          <?php
                          if ($mensajeError == ""){
                            echo '<div style="text-align:center"><span style="font-weight:bold;">Cuerpo de petición esperado:</span></div>';
                            echo "<br />";
                          ?>

                            <pre id="json"></pre>
                            <?php
                          
                            echo "<br />";
                          }


                        ?>
                        </div>

                        <div class="form-group">
                            
                          <?php
                          if ($mensajeError == ""){
                            echo '<div style="text-align:center"><span style="font-weight:bold;">Respuesta exitosa de la petición:</span></div>';
                            echo "<br />";
                          ?>

                            <pre id="json2"></pre>
                            <?php
                          
                            echo "<br />";
                          }


                        ?>
                        </div>

                        <div class="form-group" style="text-align:center;">
                        <?php
                        if ($mensajeError == ""){
                            echo "<span>Para ver la documentación completa del endpoint, haz click aquí: <a target='_blank' href='".$doc."'>Documentación</a>. Recuerda que los tipos de datos de los JSON pueden diferir de los tipos Swagger.";
                            
                            //echo "<span>Para ver la documentación completa del endpoint, haz click aquí: <a target='_blank' href='http://".$domain.$basePath.$swagger->getPathDoc()."'>Documentación</a>. Recuerda que los tipos de datos de los JSON pueden diferir de los tipos Swagger.";
                            echo "<br />";
                        }
                        ?>
                        </div>
              
                        <div class="form-group" style="text-align:center;">
                        <?php 
                          
                          if ($mensajeError == ""){
                            echo "<div><span style='font-weight:bold;'>Url:   </span><a href='#' style='display:inline-block;'>".$endpoint."</a></div>";

                            //echo "<div><span style='font-weight:bold;'>Url:   </span><a href='#' style='display:inline-block;'>http://".$domain.$basePath."/".strtolower(Normalize::normalizeUCWords($module))."/".strtolower($queryName)."</a></div>";
                          }
                        ?>
                        </div>
                    </div>
                  
                </div>
                <div class="col-md-10 mx-auto mt-3">
                    <button type="button" class="btn btn-lg btn-<?php echo $classButton; ?> btn-block" onclick="window.location.href = 'generator.php'">Volver</button>
                </div>
            </form>
        </div>
    </div>

<script src='https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js'></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/5.4.0/bootbox.min.js"></script>

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

function syntaxHighlight(json) {
    json = json.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    return json.replace(/("(\\u[a-zA-Z0-9]{4}|\\[^u]|[^\\"])*"(\s*:)?|\b(true|false|null)\b|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?)/g, function (match) {
        var cls = 'number';
        if (/^"/.test(match)) {
            if (/:$/.test(match)) {
                cls = 'key';
            } else {
                cls = 'string';
            }
        } else if (/true|false/.test(match)) {
            cls = 'boolean';
        } else if (/null/.test(match)) {
            cls = 'null';
        }
        return '<span class="' + cls + '">' + match + '</span>';
    });
}

var obj = JSON.parse('<?php echo json_encode($arrayRequest["request"]); ?>')
var obj2 = JSON.parse('<?php echo json_encode($arrayResponse); ?>');
console.log(obj2)

$("#json").html(syntaxHighlight(JSON.stringify(obj, undefined, 4)));
$("#json2").html(syntaxHighlight(JSON.stringify(obj2, undefined, 4)));
    </script>
</body>
</html>
