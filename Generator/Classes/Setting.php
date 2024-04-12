<?php
declare(strict_types=1);
namespace Generator\Classes;
use Generator\Classes\Utils\Normalize;

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
            //$connectionName = strtolower(Normalize::normalizeUCWords($database["database"]));
           
            $databaseArrayString .= '

                "'.$database["database"].'"=>[
                    "driver" => "'.$database["driver"].'",
                    "host" => "'.$database["host"].'",
                    "dbname" => "'.$database["database"].'",
                    "user" => "'.$database["username"].'",
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