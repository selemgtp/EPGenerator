<?php
declare(strict_types=1);
namespace CrudGenerator\Config;
use CrudGenerator\Utils\Normalize;

class Setting {

    private $databases;

    public function addDatabases(array $databases){
        $this->databases = $databases;
    }

    public function generate(string $pathFile){

        if (file_exists($pathFile)){
            @unlink($pathFile);
        }
        $setting = fopen($pathFile, "w");

        fwrite($setting, "<?php" . PHP_EOL);

        fwrite($setting, "declare(strict_types=1);" . PHP_EOL);

        fwrite($setting, "use DI\ContainerBuilder;" . PHP_EOL);

        //fwrite($setting, "use Monolog\Logger;" . PHP_EOL);

        fwrite($setting, 'return function (ContainerBuilder $containerBuilder) {' . PHP_EOL);


        fwrite($setting, '$containerBuilder->addDefinitions([' . PHP_EOL);

        fwrite($setting, '"settings"=>['. PHP_EOL);

        fwrite($setting, '"databases"=>[');

        $databaseArrayString = '';

        foreach($this->databases as $database){

            //https://www.bradcypert.com/building-a-restful-api-in-php-using-slim-eloquent/
            $connectionName = strtolower(Normalize::normalizeUCWords($database["database"]));
            $driver = explode("_",$database["driver"]);
            $driverElocuent = $driver[1];
            $databaseArrayString .= '

                "'.$connectionName.'"=>[
                    "driver" => "'.$driverElocuent.'",
                    "host" => "'.$database["host"].'",
                    "database" => "'.$database["database"].'",
                    "username" => "'.$database["username"].'",
                    "password" => "'.$database["password"].'",
                    "charset" => "'.$database["charset"].'"
                ],
            
            ';

        }

        fwrite($setting, $databaseArrayString . PHP_EOL);

        fwrite($setting, ']' . PHP_EOL);

        fwrite($setting, ']' . PHP_EOL);

        fwrite($setting, ']);' . PHP_EOL);

        fwrite($setting, '};' . PHP_EOL);

        fwrite($setting, "?>".PHP_EOL);

        fclose($setting);



    }
}
?>