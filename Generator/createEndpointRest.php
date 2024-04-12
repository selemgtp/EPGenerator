<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Authorization, Key, Token, key, token, X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Allow:  POST, OPTIONS");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS')
{

    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
    // may also be using PUT, PATCH, HEAD etc
    header("Access-Control-Allow-Methods: POST, OPTIONS");

    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

    exit(0);
}

require __DIR__ . '/apiFuntions.php';

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
use Generator\Classes\NameQuery;
use Generator\Classes\Utils\Normalize;

const FOLDER_CRUD_GENERATOR = 'Generator';

$resposeCode = 201;
$responseMessage = "Endpoint creado con éxito";
$sqlParameters = "";
$requestBody = file_get_contents('php://input');
$arrayRequest = array();
$arrayResponse = array();
$swagger = null;
$endpoint = '';
$doc = '';
$method = '';
$serverProtocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') ? "https" : "http";

/*$file = fopen(__DIR__."/json3.txt", "a");
        fwrite($file, json_decode($requestBody, true). PHP_EOL);
        fclose($file);*/

if ($requestBody == '' or $requestBody == null)
{
    $resposeCode = 400;
    $responseMessage = "Cuerpo depetición vacío o nulo";
}
else
{
    try
    {
        $requestBody = json_decode($requestBody, true);

        $basePath = '';
        $domain = '';
        if (isset($argc))
        {
            if (isset($argv[1]))
            {
                $domain = $argv[1];
            }
            if (isset($argv[2]))
            {
                $basePath = $argv[2];
            }
        }
        else
        {
            $domain = $_SERVER['HTTP_HOST'];

            $host_upper = strtoupper($domain);
            $path = rtrim(dirname($_SERVER['PHP_SELF']) , '/\\');
            $partsPath = explode("/", $path);

            $partsPath = array_filter($partsPath, "strlen");
            $partsPath = array_values($partsPath);
            $indexEnd = 0;
            for ($i = 0;$i < count($partsPath);$i++)
            {
                if ($partsPath[$i] == FOLDER_CRUD_GENERATOR)
                {
                    $indexEnd = $i;
                    $i = count($partsPath);
                }
            }
            if ($indexEnd > 0)
            {
                for ($j = 0;$j < $indexEnd;$j++)
                {
                    $basePath .= "/";
                    $basePath .= $partsPath[$j];
                }
            }
        }

        $databases = require __DIR__ . '/Database/databases.php';

        require __DIR__ . '/../vendor/autoload.php';

        $AUTHENTICATION = AuthenticationTypeApi::BASIC;

        $ROOT_PATH = __DIR__."/../src/EndpointsApi";

        $DEFAULT_NAMESPACE = "EndpointsApi\\";

        $PATH_ROUTES = __DIR__."/../config/routes.php";

        $module = (isset($requestBody["module"]) and $requestBody["module"] != '') ? Normalize::normalizeUCWords($requestBody["module"]) : '';

        $queryName = (isset($requestBody["name"]) and $requestBody["name"] != '') ? $requestBody["name"] : '';


        //$rawSql = $_POST["sql"];
        $rawSql = $requestBody["sqlUser"];

        $arrayParameters = array();

        $arrayColumsNames = array();

        $queryType = $requestBody["query_type"];

        //method

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

        $responseApi = consumeApi(array(
            "sql" => $rawSql,
            "query_type" => $queryType,
            "db" => implode(",", array_values(array_column($databases, 'database')))
        ));

        if ($responseApi["status"] != 200)
        {
            $resposeCode = ($responseApi["status"] == 0) ? 503 : $responseApi["status"];
            $res = json_decode($responseApi["body"], true);
            $endMessage =  ($res["description"] != '') ? $res["description"] : getHttpStatusMessage($resposeCode);
            $responseMessage = "Error consumiendo el api AST: " .$endMessage;
        }
        else
        {
            $res = json_decode($responseApi["body"], true);


            $data = $res["data"];
            $rawSql = $data["sql"];
            $method = $data["method"];


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
                $arrayResponse = json_decode($searchQuery[0]["response_body"], true);
                $endpoint = $searchQuery[0]["endpoint"];
                $doc = $searchQuery[0]["doc"];
                $method = $searchQuery[0]["method"];
               
            }else{
             
                $db = isset($requestBody["db"]) ? $requestBody["db"] : '';


                if (isset($data["arrayTables"]))
                {

                    $validaBD = false;
                    $databaseSelect = array();
                    $arrayDatabases = array();

                    if ($queryType == "UNIQ")
                    {
                        $module = ($module == '') ? $db : $module;

                        for ($i = 0;$i < count($databases);$i++)
                        {
                            if ($databases[$i]["database"] == $db)
                            {
                                $validaBD = true;
                                $databaseSelect = $databases[$i];
                                $i = count($databases);
                            }
                        }
                    }
                    else
                    {
                        $validaBD = true;

                        $module = ($module == '') ? $data["arrayTables"][0]["db"] : $module;

                        for ($i = 0;$i < count($data["arrayTables"]);$i++)
                        {
                            array_push($arrayDatabases, $data["arrayTables"][$i]["db"]);
                        }

                        for ($i = 0;$i < count($data["arrayTables"]);$i++)
                        {
                            if (!in_array($data["arrayTables"][$i]["db"], $arrayDatabases))
                            {
                                $validaBD = false;
                                $i = count($databases);
                            }
                        }

                    }
                  
                    if ($validaBD)
                    {
                          
                        $specialPost = array(
                            "ok" => 1
                        );
                        if ($data["tableCreate"] != '')
                        {
                            $specialPost = CreateTable::validate($databases, $data["tableCreate"], $data["typeInsertTable"], $databaseSelect);
                        }

                        $analyzer = new Analyzer();

                        $arrayAnalyze = $analyzer->validate($databases, $data, $databaseSelect);

                        if (isset($arrayAnalyze["error"]))
                        {

                            $resposeCode = 400;
                            $responseMessage = $arrayAnalyze["error"];

                        }
                        else if (isset($specialPost["error"]))
                        {

                            $resposeCode = 400;
                            $responseMessage = $specialPost["error"];

                        }
                        else
                        {
                            
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

                            for ($i = 0;$i < count($arrayRequest["fields"]);$i++)
                            {

                                for ($j = 0;$j < count($arrayRequest["fields"][$i]["parameter"]);$j++)
                                {
                                    array_push($arrayParameters, $arrayRequest["fields"][$i]["parameter"][$j]);
                                }

                            }

                            $sqlParameters = PdoDebugger::show($rawSql, $arrayParameters);

                            $responseShema = '';

                            $arrayResponse = array();

                            $doc = $serverProtocol . $domain . $basePath . $swagger->getPathDoc();

                            $endpoint = $serverProtocol . $domain . $basePath . "/" . strtolower(Normalize::normalizeUCWords($module)) . "/" . strtolower(Normalize::normalizeUCWords($queryName));


                            if ($data["method"] == "get")
                            {
                                $responseShema = $swagger->constructShemaResponseSelect($arrayAnalyze["ok"]["response"]);

                                for ($i = 0;$i < count($arrayAnalyze["ok"]["response"]);$i++)
                                {
                                    $arrayResponse[$arrayAnalyze["ok"]["response"][$i]["name"]] = $arrayAnalyze["ok"]["response"][$i]["type"];
                                }
                                $arrayResponse = array(
                                    $arrayResponse
                                );
                            }
                            else
                            {
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



                        }

                    }
                    else
                    {

                        $responseMessage = "La base de datos no fue encontrada dentro del listado. Verifica y vuelve a intentarlo.";
                    }

                }
                else
                {
                    $validaBD = false;
                    $databaseSelect = array();
                    $arrayParameters = array();

                    if ($queryType == "UNIQ")
                    {
                        $module = ($module == '') ? $db : $module;

                        for ($i = 0;$i < count($databases);$i++)
                        {
                            if ($databases[$i]["database"] == $db)
                            {
                                $validaBD = true;
                                $databaseSelect = $databases[$i];
                                $i = count($databases);
                            }
                        }
                        $arrayParameters = array(
                            "table:value"
                        );

                    }
                    else
                    {
                        $validaBD = true;
                        $arrayParameters = array(
                            "database.table:value"
                        );
                    }

                    if ($validaBD == true)
                    {

                        if ($queryName == ''){
                            $queryName = $queryNameFinal->getNameQuery($data, $data["method"], $module, $db);
                        }else{
                            $searchQueryName =  $queryNameFinal->searchName($queryName);
                            if ($searchQuery != null and count($searchQueryName) > 0){
                                $queryName .=  uniqid();
                            } 
                        }

                        $sqlParameters = PdoDebugger::show($rawSql, $arrayParameters);
                        
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
                    }
                    else
                    {

                        $responseMessage = "Ocurrió un error al procesar la consulta DROP. Intenta de nuevo.";
                    }

                }
            }
        }

    }
    catch(Throwable $t)
    {
        $resposeCode = 500;
        $responseMessage = $t->getMessage();
    }
}

$arrayResponseFinal = array(
    "status" => $resposeCode,
    "message" => $responseMessage
);

if ($resposeCode == 201)
{
    $arrayResponseFinal["api"] = array(
        "name"=>$queryName,
        "query" => $rawSql,
        "endpoint" => $endpoint,
        "method" => $method,
        "requestBody" => $arrayRequest["request"],
        "responseBody" => $arrayResponse,
        "doc" => $doc
    );
}

header($_SERVER["SERVER_PROTOCOL"] . " " . $resposeCode . " " . getHttpStatusMessage($resposeCode));
header('Content-Type: application/json');
echo json_encode($arrayResponseFinal, JSON_NUMERIC_CHECK);

?>