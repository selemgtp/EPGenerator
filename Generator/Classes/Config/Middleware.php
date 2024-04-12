<?php
declare(strict_types=1);
namespace CrudGenerator\Config;
use CrudGenerator\Utils\Normalize;
use CrudGenerator\Config\AuthenticationTypeApi;

class Middleware{

    public static function shemaGlobal(string $value){
        switch($value){

            case "".AuthenticationTypeApi::BASIC."":
                return '
                *  @OA\SecurityScheme(
                    *      securityScheme="basicAuth",
                    *      in="header",
                    *      name="Authorization",
                    *      type="http",
                    *      scheme="Basic"
                    * )
                ';
            break;

            case "".AuthenticationTypeApi::JWT."":
                return '
                * @OA\SecurityScheme(
                    *      securityScheme="bearerAuth",
                    *      in="header",
                    *      name="bearerAuth",
                    *      type="http",
                    *      scheme="bearer",
                    *      bearerFormat="JWT",
                    * ),
                ';
            break;
            

        }
    }

    public static function generate(string $pathFile, string $value, array $databases, $basePath){
       
        if (file_exists($pathFile)){
            @unlink($pathFile);
        }

        $middleware = fopen($pathFile, "w");

        fwrite($middleware, "<?php" . PHP_EOL);

        fwrite($middleware, "declare(strict_types=1);" . PHP_EOL);
        fwrite($middleware, "use Slim\App;". PHP_EOL);
        fwrite($middleware, "use Crud\Middleware\SessionMiddleware;" . PHP_EOL);

        fwrite($middleware, "use Tuupola\Middleware\CorsMiddleware;" . PHP_EOL);

        switch($value){

            case "".AuthenticationTypeApi::BASIC."":
                fwrite($middleware, "use Tuupola\Middleware\HttpBasicAuthentication;" . PHP_EOL);
            break;

            case "".AuthenticationTypeApi::JWT."":
                fwrite($middleware, "use Tuupola\Middleware\JwtAuthentication;" . PHP_EOL);
            break;

        }

        fwrite($middleware, 'return function (App $app) {'.PHP_EOL);
        fwrite($middleware, '$app->add(SessionMiddleware::class);'.PHP_EOL);
        fwrite($middleware, '$app->addBodyParsingMiddleware();'.PHP_EOL);

        fwrite($middleware, '$env = require __DIR__ . "/env.php";'.PHP_EOL);

        $stringClassAuthentication = '';

        $databasesNameArray = array();

        foreach($databases as $database){

            array_push($databasesNameArray, $database["database"]);

        }

        switch($value){

            case "".AuthenticationTypeApi::BASIC."":
                $stringClassAuthentication = '
                $app->add(new HttpBasicAuthentication([
                    "path" => ['.sprintf('"'.$basePath.'/%s"',implode('","'.$basePath.'/',  $databasesNameArray)).'],
                    "ignore"=>["'.$basePath.'/specification"],
                    "realm" => "Protected",
                    "secure" => false,
                    "users" => $env["basic_authentication_users"],
                    //respuesta en caso de error
                    "error" => function ($response, $arguments) {
                        $data = [];
                        $data["status"] = "error";
                        $data["message"] = $arguments["message"].". Username and password not valid.";
            
                        //respuesta con código HTTP 401 que indica la falta de acceso
                        $response->getBody()->write(json_encode($data));
                        return $response
                            ->withHeader("Content-Type", "application/json")
                            ->withStatus(401);
                        }
                ]));
                ';
            break;

            case "".AuthenticationTypeApi::JWT."":
                $stringClassAuthentication = '
                $app->add(new JwtAuthentication([
                    "path" => ['.sprintf('"'.$basePath.'/%s"',implode('","'.$basePath.'/',  $databasesNameArray)).'],// grupo de paths protegido
                    "ignore" => ["'.$basePath.'/specification", "'.$basePath.'/token"],//path ignorado del grupo de paths protegidos para obtener el Token
                    "secret" => $env["jwt_secret"],//clave secreta de encriptación
                    "algorithm" => ["HS256", "HS384"],
                    //función de error al no poder autenticar
                    "error" => function ($response, $arguments) {
                        $data["status"] = "error";
                        $data["message"] = $arguments["message"];
                        $response->getBody()->write((string)json_encode($data));
                        return $response->withHeader("Content-Type", "application/json")->withStatus(401);
                    }
                ]));
                ';
            break;

        }

        fwrite($middleware, $stringClassAuthentication.PHP_EOL);
        
        fwrite($middleware, '
        $app->add(new CorsMiddleware([
            "origin" => ["*"],
            "methods" => ["GET", "POST", "PUT", "PATCH", "DELETE","OPTIONS"],
            "headers.allow" => ["Authorization", "If-Match", "If-Unmodified-Since"],
            "headers.expose" => ["Authorization", "Etag"],
            "credentials" => true,
            "cache" => 60,
            //respuesta de error
            "error" => function ($request, $response, $arguments) {
                $data["status"] = "error";
                $data["message"] = $arguments["message"];
                $response->getBody()->write((string)json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
                return $response->withHeader("Content-Type", "application/json")->withStatus(401);
            }
        ]));
        '.PHP_EOL);

        
        fwrite($middleware, '};' . PHP_EOL);

        fwrite($middleware, "?>".PHP_EOL);

        fclose($middleware);


    }

}


?>