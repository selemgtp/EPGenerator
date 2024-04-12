<?php
declare(strict_types=1);
namespace Generator\Classes;
use Generator\Classes\Utils\Normalize;

class Api{

    public static $stringApiInit = '
    /**
     * @OA\Info(
     *   title="Genrador de endpoints a partir de SQL Queries",
     *   version="0.0.1",
     *   description="Esta es un API generada automaticamente a partir de un generador de Endpoints a partir de SQL Queries",
     *   @OA\Contact(
     *     email="josnikh@hotmail.com"
     *   )
     * )
    ';

    public static  $stringApiFinal = '
    */
    ';

    public static function generate($pathFile, $securityShema, $rootPath, string $authenticationType, string $domain, string $basePath){
        if (file_exists($pathFile)){
            @unlink($pathFile);
        }

        $stringApi = self::$stringApiInit;

        $stringApi .= '
        '.$securityShema.'
        ';

        $stringApi .= '
        *     @OA\Shemes(
        *       @OA\Server(url="http://'.$domain.$basePath.'"),
        *       @OA\Server(url="https://'.$domain.$basePath.'")
        *     ),
        ';

        /*foreach ($databases as $database) {
            $stringApi .= '
            * @OA\Tag(
                *     name="'.strtolower(Normalize::normalizeUCWords($database["database"])).'",
                *     description="Tenant '.$database["database"].'"
                * )
            ';
        }*/

        $directoryToList = opendir($rootPath);
        $exclude =  array('.', '..');

        $arrayFolders = array();

        $arrayFiles = array();

        while ($f = readdir($directoryToList)) {
            if (is_dir("$rootPath/$f") && !in_array($f, $exclude)) {
                $stringApi .= '
                * @OA\Tag(
                    *     name="'.$f.'",
                    *     description="Módulo '.$f.'"
                    * )
                ';
            }
        }
        closedir($directoryToList);



        switch($authenticationType){

            case "".AuthenticationTypeApi::JWT."":
                $stringApi .= '
                * @OA\Tag(
                    *     name="autenticacion",
                    *     description="Autenticación"
                    * )
                ';
            break;

        }

        $stringApi .= self::$stringApiFinal;
        switch($authenticationType){

            case "".AuthenticationTypeApi::JWT."":
                $stringApi .= '
                /**
                 *
                 * @OA\Post(
                 *   path="/token",
                 *   tags={"autenticacion"},
                 *   summary="Genera un token JWT",
                 *   @OA\RequestBody(
                 *         @OA\MediaType(
                 *             mediaType="application/json",
                 *             @OA\Schema(ref="#/components/schemas/RequestJWT"),
                 *         )
                 *     ),
                 *   @OA\Response(
                 *     response=201,
                 *     description="Token creado",
                 *     @OA\MediaType(
                 *         mediaType="application/json",
                 *         @OA\Schema(ref="#/components/schemas/ResponseJWT")
                 *     )
                 *   ),
                 *   @OA\Response(
                 *     response=500,
                 *     description="an ""unexpected"" error",
                 *     @OA\MediaType(
                 *         mediaType="application/json",
                 *         @OA\Schema(ref="#/components/schemas/serverError")
                 *     )
                 *   ),
                 * )
                 */
                ';

         break;

        }

        $apiSwagger = fopen($pathFile, "w");

        fwrite($apiSwagger, "<?php" . PHP_EOL);


        fwrite($apiSwagger, $stringApi . PHP_EOL);

        fwrite($apiSwagger, "?>".PHP_EOL);

        fclose($apiSwagger);
    }
}
?>