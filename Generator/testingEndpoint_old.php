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
use Generator\Classes\SwaggerAnnotation;
use Generator\Classes\Utils\Normalize;

const FOLDER_CRUD_GENERATOR = 'Generator';

$resposeCode = 201;
$responseMessage = "Endpoint creado con éxito";
$sqlParameters = "";
$requestBody = file_get_contents('php://input');
$arrayRequest = array();
$arrayResponse = array();
$swagger = null;

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

        $module = Normalize::normalizeUCWords($requestBody["module"]);

        $queryName = $requestBody["name"];


        //$rawSql = $_POST["sql"];
        $rawSql = $requestBody["sqlUser"];

        $arrayParameters = array();

        $arrayColumsNames = array();

        $queryType = $requestBody["query_type"];

        //method
        

        $responseApi = consumeApi(array(
            "sql" => $rawSql,
            "query_type" => $queryType,
            "db" => implode(",", array_values(array_column($databases, 'database')))
        ));

        if ($responseApi["status"] != 200)
        {
            $resposeCode = $responseApi["status"];
            $res = json_decode($responseApi["body"], true);
            $responseMessage = "Error consumiendo el api AST: " . $res["description"];
        }
        else
        {
            $res = json_decode($responseApi["body"], true);

            $data = $res["data"];
            $rawSql = $data["sql"];

            $db = isset($requestBody["db"]) ? $requestBody["db"] : '';


            if ($queryName == ''){

            }else{

                //throw new Exception('División por cero.');
            }

            if (isset($data["arrayTables"]))
            {

                $validaBD = false;
                $databaseSelect = array();
                $arrayDatabases = array();

                if ($queryType == "UNIQ")
                {

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

                    $sqlParameters = PdoDebugger::show($rawSql, $arrayParameters);

                    $swagger = new SwaggerAnnotation();

                    $swagger->config($data["method"], $queryName, $module, 'DROP');

                    $analyzerArray = AnalyzerDDL::drop($queryType);

                    $arrayRequest = $swagger->validateRequstFields($analyzerArray["ok"]["request"]);

                    $requestShema = $swagger->requestShema($arrayRequest["fields"]);

                    $responseShema = '';

                    $arrayResponse = $analyzerArray["ok"]["response"];

                }
                else
                {

                    $responseMessage = "Ocurrió un error al procesar la consulta DROP. Intenta de nuevo.";
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
        "query" => $sqlParameters,
        "endpoint" => "http://" . $domain . $basePath . "/" . strtolower(Normalize::normalizeUCWords($module)) . "/" . strtolower($queryName) ,
        "method" => $data["method"],
        "requestBody" => $arrayRequest["request"],
        "responseBody" => $arrayResponse,
        "doc" => "http://" . $domain . $basePath . $swagger->getPathDoc()
    );
}

header($_SERVER["SERVER_PROTOCOL"] . " " . $resposeCode . " " . getHttpStatusMessage($resposeCode));
header('Content-Type: application/json');
echo json_encode($arrayResponseFinal, JSON_NUMERIC_CHECK);

