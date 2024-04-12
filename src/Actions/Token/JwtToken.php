<?php

/**
 * Clase que crea un JWT válido para protección de los endpoints sc
 */

declare(strict_types=1);

namespace EndpointsApi\Actions\Token;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Firebase\JWT\JWT;
use Tuupola\Base62;

class JwtToken
{
/**
     *
     *
     * @param Request $request
     * @param Response $response
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        //importo el archivo env.php donde se aloja el array de usuarios de autenticación Basic
        $env = require __DIR__ . '../../../../config/env.php';
        $users = $env["jwt_authentication_users"];
        $requestBody = $request->getParsedBody();

        if ($requestBody != null){
            
            if (!isset($requestBody["user"]) or !isset($requestBody["pwd"])){
                //en caso de que no sea un payload correcto
                $response->getBody()->write((string)json_encode($this->ValidationError()));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
            }else{
                //se busca que el user y pwd recibido en el payload sean válidos
                $valid = false;
                foreach($users as $key=>$value){
                    if($key == $requestBody["user"] and $value == $requestBody["pwd"]){
                        $valid = true;
                        break;
                    }
                }
           
                if ($valid === false){
                    //si el user y el pwd recibidos no son válidos
                    $response->getBody()->write((string)json_encode($this->ValidationError()));
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
                }else{
                    //user y pwd válidos
                    $now = new \DateTime();
                    $future = new \DateTime("+1 minutes"); //expiración de un minuto
                    $jti = (new Base62)->encode(random_bytes(16)); //randomico en base62
                    //payload o carga del JWT
                    $payload = [
                        "iat" => $now->getTimeStamp(),
                        "exp" => $future->getTimeStamp(),
                        "jti" => $jti
                    ];
                    $secret = $env["jwt_secret"];//Clave secreta de encriptación
                    $token = JWT::encode($payload, $secret, "HS256");//encriptación y creación JWT
                    $data["token"] = $token;
                    $data["expires"] = $future->getTimeStamp();
                    //retorno de una respuesta en JSON
            
                    $response->getBody()->write((string)json_encode($data));
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
                }
                
            }
        }else{
            //en caso de que el payload este vacío
            $response->getBody()->write((string)json_encode($this->ValidationError()));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }


    }

    public function ValidationError(){
            $data = [];
            $data["status"] = "error";
            $data["message"] = "Malformed o invalid payload.";
            return $data;
    } 

}