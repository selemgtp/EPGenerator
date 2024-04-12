<?php
declare(strict_types=1);
namespace Generator\Classes;

class Routes{

    private $pathCreate;
    private $pathRead;
    private $defaultNamespace;

    function __construct(string $pathCreate, string $pathRead, string $defaultNamespace){
        $this->pathCreate = $pathCreate;
        $this->pathRead = $pathRead;
        $this->defaultNamespace = $defaultNamespace;
    }

    function generate(){
        if (file_exists($this->pathCreate)){
            @unlink($this->pathCreate);
        }

        $routes = fopen($this->pathCreate, "w");

        fwrite($routes, "<?php" . PHP_EOL);

        fwrite($routes, "declare(strict_types=1);" . PHP_EOL);

        fwrite($routes, 'use Psr\\Http\Message\\ResponseInterface as Response;'. PHP_EOL);
        
        fwrite($routes, 'use Psr\\Http\Message\\ServerRequestInterface as Request;'. PHP_EOL);
        fwrite($routes, 'use Slim\\App;'. PHP_EOL);
        fwrite($routes, 'use Slim\\Interfaces\\RouteCollectorProxyInterface as Group;'. PHP_EOL);

        fwrite($routes,'use Slim\\Exception\\HttpNotFoundException;'.PHP_EOL);

        fwrite($routes,'use EndpointsApi\Actions\Specification as Specification;'.PHP_EOL);

        $directoryToList = opendir($this->pathRead);
        $exclude =  array('.', '..');

        $arrayFolders = array();

        $arrayFiles = array();

        while ($f = readdir($directoryToList)) {
            if (is_dir("$this->pathRead/$f") && !in_array($f, $exclude)) {
                array_push($arrayFolders,$f);
            }
        }
        closedir($directoryToList);

        for ($i = 0; $i < count($arrayFolders); $i++){

            $dir = opendir($this->pathRead."/".$arrayFolders[$i]);
            $arrayFilesTemp = array();

            while ($f = readdir($dir)) {
                if (is_file($this->pathRead."/".$arrayFolders[$i]."/".$f) && !in_array($f, $exclude)) {
                    $infoFile = pathinfo($this->pathRead."/".$arrayFolders[$i]."/".$f);
                    if ($infoFile['extension'] == "php"){
                        array_push($arrayFilesTemp,$f);
                    }
                }
            }
            if (count($arrayFilesTemp) > 0){
                array_push($arrayFiles, array("folder"=>$arrayFolders[$i], "files"=>$arrayFilesTemp));
            }
            closedir($dir);

        }

   //rutas use
        for($i = 0; $i < count($arrayFiles); $i++){

            for ($j = 0; $j < count($arrayFiles[$i]["files"]); $j++){
                
                $infoFile = pathinfo($this->pathRead."/".$arrayFiles[$i]["folder"]."/".$arrayFiles[$i]["files"][$j]);

                fwrite($routes,'use '.$this->defaultNamespace."EndpointsApi\\".$arrayFiles[$i]["folder"]."\\".$infoFile["filename"]." as ".$arrayFiles[$i]["folder"].$infoFile["filename"].";".PHP_EOL);

            }

        }


        fwrite($routes,'return function (App $app) {'.PHP_EOL);
        //rutas 
        $group = "";
        for($i = 0; $i < count($arrayFiles); $i++){

            $group .= '
            $app->group("/'.strtolower($arrayFiles[$i]["folder"]).'", function (Group $group) {';
                        
            for ($j = 0; $j < count($arrayFiles[$i]["files"]); $j++){
                require $this->pathRead."/".$arrayFiles[$i]["folder"]."/".$arrayFiles[$i]["files"][$j];
                $infoFile = pathinfo($this->pathRead."/".$arrayFiles[$i]["folder"]."/".$arrayFiles[$i]["files"][$j]);
                //echo call_user_func($this->defaultNamespace.$arrayFiles[$i]["folder"]."\\".$infoFile["filename"]."::getMethod");
                $group .= '
                $group->'.call_user_func($this->defaultNamespace."EndpointsApi\\".$arrayFiles[$i]["folder"]."\\".$infoFile["filename"]."::getMethod").'("/'.strtolower($infoFile["filename"]).'", '.$arrayFiles[$i]["folder"].$infoFile["filename"].'::class .":execute");
                ';

            }
                        
            $group .= '
                    });
                    ';

        }

        fwrite($routes, $group.PHP_EOL);

        fwrite($routes, '
        $app->get("/specification", Specification::class . ":yaml");
        '.PHP_EOL);


        fwrite($routes, '
        $app->map(["GET", "POST", "PUT", "DELETE", "PATCH"], "/{routes:.+}", function ($request, $response) {
            throw new HttpNotFoundException($request);
        });
        '.PHP_EOL);

        fwrite($routes,'};'.PHP_EOL);

        fwrite($routes, "?>".PHP_EOL);

        fclose($routes);

    }
}