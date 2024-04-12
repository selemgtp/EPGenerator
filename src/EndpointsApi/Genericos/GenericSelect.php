<?php
/**
 *
 * @package default
 */


declare(strict_types=1);
namespace EndpointsApi\EndpointsApi\Genericos;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Doctrine\DBAL\DriverManager;
use Psr\Container\ContainerInterface;

class GenericSelect {


	/**
	 *
	 * @OA\Schema(
	 * schema="GenericEndpointSelectRequest",
	 * description="Cuerpo de petición esperado.",
	 * @OA\Property(
	 *  property="select",
	 *  type="string",
	 *  description="Strins de la consulta."
	 * ),
	 * example = {
	 * "select": "select * from `rad`.`menu` where idusuario = 580 and menupadre = 0 order by orden asc"
	 * }
	 * )
	 */

	/**
	 *
	 * @OA\Schema(
	 * 		schema="GenericEndpointSelectResponse",
	 *      type="array",
	 *      @OA\Items()
	 * )
	 */

	/**
	 *
	 * @OA\Get(
	 *   path="/genericos/genericselect",
	 *   tags={"Genericos"},
	 *   summary="Endpoint que recibe una consulta tipo select, la procesa y devuelve el resultado.",
	 *   security={{"basicAuth": {}}},
	 *   @OA\RequestBody(
	 *         @OA\MediaType(
	 *             mediaType="application/json",
	 *             @OA\Schema(ref="#/components/schemas/GenericEndpointSelectRequest"),
	 *         )
	 *     ),
	 *   @OA\Response(
	 *     response=200,
	 *     description="Respuesta de ejecución correcta",
	 *     @OA\MediaType(
	 *         mediaType="application/json",
	 *         @OA\Schema(ref="#/components/schemas/GenericEndpointSelectResponse")
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
     *   @OA\Response(
	 *     response=400,
	 *     description="No se recibió Payload correcto.",
	 *     @OA\MediaType(
	 *         mediaType="application/json",
	 *         @OA\Schema(ref="#/components/schemas/BadRequest")
	 *     )
	 *   ),
	 * @OA\Response(
	 *     response=401,
	 *     description="Invalid authentication",
	 *     @OA\MediaType(
	 *         mediaType="application/json",
	 *         @OA\Schema(ref="#/components/schemas/Unauthorized")
	 *     )
	 *   )
	 * )
	 */

     /**
     *  @OA\Schema(
     *      schema="BadRequest",
     *      @OA\Property(
     *          property="statusCode",
     *          type="integer",
     *          description="Codigo de estado"
     *      ),
     *      @OA\Property(
     *          property="error",
     *          type="object",
     *          @OA\Property(
     *             property="type",
     *             type="string",
     *             description="Tipo"
     *          ),
     *          @OA\Property(
     *             property="description",
     *             type="string",
     *             description="Descripción del mensaje"
     *          ),
     *                          
     *       ),
     *       example={
     *           "statusCode":400,
     *           "error":{
     *                 "type": "BAD_REQUEST",
     *                 "description": "No se recibió Payload correcto."
     *                   }
     *            }
     *   )
     */

	/**
	 *
	 * @var Database connection name
	 */
	private $databaseConnection = "proyectos";

	/**
	 *
	 * @var ContainerInterface
	 */
	protected $container;

	/**
	 *
	 * @var Connection The database connection
	 */
	private $connection;

	/**
	 * return method name
	 *
	 * @return unknown
	 */
	public static function getMethod() {
		return "get";
	}

	/**
	 *
	 * @param ContainerInterface $container
	 */
	public function __construct(ContainerInterface $container) {
		$this->connection = DriverManager::getConnection($container->get("settings")["databases"][$this->databaseConnection]);
	}


	/**
	 *
	 * @param Request  $request
	 * @param Response $response
	 * @param array    $args
	 * @return json
	 */
	public function execute(Request $request, Response $response, array $args) {
        $statusCode = 200;
        $result = array();
		try{
			$body = $request->getParsedBody();

            if (!isset($body["select"])){
                $statusCode = 400;
                $result = array("statusCode"=>400, "error"=>array("type"=>"BAD_REQUEST", "message"=>"No se recibió Payload correcto."));
            }else{
                $badRequestSelect = $this->analyzeSelect($body["select"]);
                if ($badRequestSelect != ""){
                    $statusCode = 400;
                    $result = array("statusCode"=>$statusCode, "error"=>array("type"=>"BAD_REQUEST", "message"=>$badRequestSelect));
                }else{
                    $stmt = $this->connection->prepare($body["select"]);
                    $stmt->execute();
                    $result = $stmt->fetchAll();
                }
            }


		}catch(Throwable $t) {
            $statusCode = 500;
			$result = array("statusCode"=>500, "error"=>array("type"=>"SERVER_ERROR", "message"=>$t->getMessage()));
		}

        $response->getBody()->write(json_encode($result, JSON_NUMERIC_CHECK ));
        return $response
        ->withHeader("Content-Type", "application/json")
        ->withStatus($statusCode);


	}

    public function analyzeSelect(string $sql){
        $sql = trim($sql);
        $sqlArray = explode(" ",strtolower($sql));
        if ($sqlArray[0] != "select"){
            return "Solo se acepta consultas tipo select";
        }else if (!in_array("where", $sqlArray) and  !in_array("limit", $sqlArray)){
            return "Debe existir un where o limit en la consulta";
        }else{
            return "";
        }
    }


}
