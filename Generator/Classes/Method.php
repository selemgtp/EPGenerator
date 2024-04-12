<?php
declare(strict_types=1);
namespace Generator\Classes;
use Generator\Classes\Utils\Normalize;

class Method{

    private $queryName;
    private $sqlData;
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
       
        if (!file_exists($pathModule)){
            mkdir($pathModule,0777, true);
        }

        $this->pathFile = $pathModule. "/".Normalize::normalizeUCWords($this->queryName).".php";

    }

    public function setConfigMethod(string $defauldNamespace, string $rawQuery, array $sqlData, string $method, string $annotations, string $databaseName){
        $this->defauldNamespace = $defauldNamespace;
        $this->sqlData = $sqlData;
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

            $stringMethod = '
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
                            $this->connection = DriverManager::getConnection($container->get("settings")["databases"][$this->databaseConnection]);
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
                            ';

                          
                                    //$sql = "INSERT INTO '.$this->sqlData["table"][0]["table"].' ('.implode(",",$this->sqlData["columns"]).') VALUES ('.sprintf('"'.$basePath.':%s"',implode('",:', $this->sqlData["columns"])).')
                                    $stringMethod .= '
                                        $sql = "'.$this->rawQuery.'";
                                        $stmt = $this->connection->prepare($sql);
                                    ';
                                    $indexParam = 1;
                                    for ($i = 0; $i < count($this->sqlData["arrayColumns"]); $i++){
                                        if ($this->sqlData["arrayColumns"][$i]["operator"] != ""){

                                            if ($this->sqlData["arrayColumns"][$i]["operator"] == "BETWEEN"){
                                                $numParameters =  count($this->sqlData["arrayColumns"][$i]["parameter"]);

                                                if ($numParameters == 1){
                                                    $stringMethod .= '
                                                        $stmt->bindValue('.($indexParam).', $body["'.$this->sqlData["arrayColumns"][$i]["columnName"].'"]);
                                                    ';
                                                    $indexParam++;
                                                }else if ($numParameters == 2){
                                                    $stringMethod .= '
                                                        $stmt->bindValue('.($indexParam).', $body["'.$this->sqlData["arrayColumns"][$i]["columnName"].'"][0]);
                                                    ';
                                                    $indexParam++;
                                                $stringMethod .= '
                                                    $stmt->bindValue('.($indexParam).', $body["'.$this->sqlData["arrayColumns"][$i]["columnName"].'"][1]);
                                                ';
                                                    $indexParam++;
                                                }

                                                
                                                
                                            }else{
                                                $stringMethod .= '
                                                $stmt->bindValue('.($indexParam).', $body["'.$this->sqlData["arrayColumns"][$i]["columnName"].'"]);
                                                ';
                                                $indexParam++;
                                            }

                                            


                                        }

                                    }

                             

                                   

                            


                        $stringMethod .= '
                                $stmt->execute(); 
                        ';


                switch($this->method){

                    case "post":
                        $responseCode = 201;

                        if ($this->sqlData["tableCreate"] == ''){
                            $stringMethod .= '
                            $json = json_encode(array("id"=>$this->connection->lastInsertId(), "message"=>"Creación exitosa"));

                            $response->getBody()->write($json);
                            return $response
                            ->withHeader("Content-Type", "application/json")
                            ->withStatus(201);
                            ';
                        }else{
                            $stringMethod .= '
                            $json = json_encode(array("message"=>"Actualización exitosa"));
                            $response->getBody()->write($json);
                            return $response
                            ->withHeader("Content-Type", "application/json")
                            ->withStatus(201);
                            ';
                        }
                    break;

                    case "put":

                        $stringMethod .= '
                        $json = json_encode(array("message"=>"Actualización exitosa"));
                        $response->getBody()->write($json);
                        return $response
                        ->withHeader("Content-Type", "application/json")
                        ->withStatus(201);
                        ';
                       
                    break;

                    case "delete":
                        $stringMethod .= '
                        $json = json_encode(array("message"=>"Eliminación exitosa"));
                        $response->getBody()->write($json);
                        return $response
                        ->withHeader("Content-Type", "application/json")
                        ->withStatus(200);
                        ';
                    break;

                    case "get":
                       $stringMethod .= '
                            $result = $stmt->fetchAll();
                            $response->getBody()->write(json_encode($result, JSON_NUMERIC_CHECK ));
                            return $response
                            ->withHeader("Content-Type", "application/json")
                            ->withStatus(200);
                       ';
                    break;
                }


                $stringMethod .= '


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