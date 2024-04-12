<?php
declare(strict_types=1);
namespace Generator\Classes;
use Generator\Classes\Utils\Normalize;

class MethodDDL{

    private $queryName;
    private $queryType;
    private $method;
    private $annotations;
    private $module;
    private $defauldNamespace;
    private $rootPath;
    private $pathFile;
    private $rawQuery;
    private $databaseName;

    public function pathConfigure(string $module, string $queryName, string $rootPath){
        $this->module = $module;
        $this->queryName = $queryName;
        $this->rootPath = $rootPath;
        $pathModule = $this->rootPath."/".Normalize::normalizeUCWords($this->module);
        
        if (!is_dir($pathModule)){
            mkdir($pathModule);
        }

        $this->pathFile = $pathModule. "/".Normalize::normalizeUCWords($this->queryName).".php";

    }

    public function setConfigMethod(string $defauldNamespace, string $rawQuery, string $queryType, string $method, string $annotations, string $databaseName){
        $this->defauldNamespace = $defauldNamespace;
        $this->queryType = $queryType;
        $this->method = $method;
        $this->annotations = $annotations;
        $this->rawQuery = $rawQuery;
        $this->databaseName = $databaseName;

        if (!file_exists($this->pathFile)){
            @unlink($this->pathFile);
        }
        
    }

    public function generate(){

        $classMethod = fopen($this->pathFile, "w");
            
        fwrite($classMethod, "<?php" . PHP_EOL);

        fwrite($classMethod, "declare(strict_types=1);" . PHP_EOL);


        fwrite($classMethod, "namespace ".$this->defauldNamespace."EndpointsApi\\".Normalize::normalizeUCWords($this->module).";" . PHP_EOL);
        
        fwrite($classMethod, "use Psr\Http\Message\ServerRequestInterface as Request;" . PHP_EOL);
        fwrite($classMethod, "use Psr\Http\Message\ResponseInterface as Response;" . PHP_EOL);
        fwrite($classMethod, "use Doctrine\DBAL\DriverManager;" . PHP_EOL);
        fwrite($classMethod, "use Psr\Container\ContainerInterface;". PHP_EOL);

        fwrite($classMethod, "class ".Normalize::normalizeUCWords($this->queryName)."{" . PHP_EOL);
        
        $stringMethod = '';

        $stringMethod .= '
            '.$this->annotations.'
        ';

        $stringMethod .= '  
                        /**
                         * @var Database connection name
                         */
                         
                         private $databaseConnection = "'.$this->databaseName.'";

                        /**
                         * @var ContainerInterface
                         */
                        protected $container;

                         /**
                         * @var Connection The database connection
                         */

                        private $connection;

                        /**
                         * return method name
                         */
                         
                        public static function getMethod(){
                            return "'.$this->method.'";
                        }

                        /**
                         * The constructor.
                         *
                         * @param Connection $connection The database connection
                         */
                        /*public function __construct(Connection $connection)
                        {
                            $this->connection = $connection;
                        }*/

                        public function __construct(ContainerInterface $container){
                            $this->container = $container;
                        }

                        /**
                         *
                         * @param Request  $request
                         * @param Response $response
                         * @param array    $args
                         * @return json
                         */
                        public function execute (Request $request, Response $response, array $args){
                            
                            try{
                                $body = $request->getParsedBody();
                                if ($body == null) {
                                    $response->getBody()->write(json_encode(array("message"=>"Sin payload")));
                                    return $response
                                    ->withHeader("Content-Type", "application/json")
                                    ->withStatus(400);
                                }else {
                            ';

                        if ($this->queryType == "UNIQ"){

                            $stringMethod .= '
                                if (!isset($body["table"])){
                                    $response->getBody()->write(json_encode(array("message"=>"No se recibió tabla")));
                                    return $response
                                    ->withHeader("Content-Type", "application/json")
                                    ->withStatus(400);
                                }else{
                                    $this->connection = DriverManager::getConnection($this->container->get("settings")["databases"][$this->databaseConnection]);
                                    $schemaManager = $this->connection->getSchemaManager();
                                    $table = $schemaManager->listTableDetails($body["table"]);

                                    if (count($table->getColumns()) == 0) {
                                        $response->getBody()->write(json_encode(array("message"=>"Tabla inexistente")));
                                        return $response
                                        ->withHeader("Content-Type", "application/json")
                                        ->withStatus(400);

                                    }else{
                                        $sql = "'.$this->rawQuery.'";
                                        $sql = str_replace("?",$body["table"],$sql);
                                        $stmt = $this->connection->prepare($sql);
                                        $stmt->execute(); 

                                        $json = json_encode(array("message"=>"Tabla eliminada con éxito"));
                                        $response->getBody()->write($json);
                                        return $response
                                        ->withHeader("Content-Type", "application/json")
                                        ->withStatus(201);
                                    }
                                }
                            ';

                        }else {
                            $stringMethod .= '
                                if (!isset($body["table"]) or !isset($body["database"])){
                                    $response->getBody()->write(json_encode(array("message"=>"No se recibió tabla o base de datos")));
                                    return $response
                                    ->withHeader("Content-Type", "application/json")
                                    ->withStatus(400);
                                }else{

                                    if (!isset($this->container->get("settings")["databases"][$body["database"]])){
                                        $response->getBody()->write(json_encode(array("message"=>"La base de datos no existe")));
                                        return $response
                                        ->withHeader("Content-Type", "application/json")
                                        ->withStatus(400);
                                    }else{


                                        $this->connection = DriverManager::getConnection($this->container->get("settings")["databases"][$body["database"]]);

                                        $schemaManager = $this->connection->getSchemaManager();
                                        $table = $schemaManager->listTableDetails($body["table"]);

                                        if (count($table->getColumns()) == 0) {
                                            $response->getBody()->write(json_encode(array("message"=>"Tabla inexistente")));
                                            return $response
                                            ->withHeader("Content-Type", "application/json")
                                            ->withStatus(400);

                                        }else{

                                            $sql = "'.$this->rawQuery.'";
                                            $sql = str_replace("?",$body["table"],$sql);
                                            $stmt = $this->connection->prepare($sql);
                                            $stmt->execute(); 

                                            $json = json_encode(array("message"=>"Tabla eliminada con éxito"));
                                            $response->getBody()->write($json);
                                            return $response
                                            ->withHeader("Content-Type", "application/json")
                                            ->withStatus(201);
                                        }

                                    }


                                }
                            ';
                        }

        

                        $stringMethod .= '

                            }

                        }catch(Throwable $t){
                            $json = json_encode(array("statusCode"=>500,"error"=>array("type"=>"SERVER_ERROR", "message"=>$t->getMessage())));
                            $response->getBody()->write($json);
                            return $response
                            ->withHeader("Content-Type", "application/json")
                            ->withStatus(500);
                        }

                    }
                    ';

        fwrite($classMethod,$stringMethod . PHP_EOL);


        fwrite($classMethod, "}".PHP_EOL);

        fclose($classMethod);
    }

    public function getPathFile(){
        return $this->pathFile;
    }
}
?>