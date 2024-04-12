<?php

/**
 *                 @OA\Schema(
 *                     schema="Unauthorized",
 *                     @OA\Property(
 *                         property="status",
 *                         type="string",
 *                         description="Estado"
 *                     ),
 *                     @OA\Property(
 *                         property="message",
 *                         type="string",
 *                         description="Descripción del mensaje"
 *                     ),
 *                     example={
 *                       "status": "error",
 *                       "message": "Authentication failed. Username and password not valid."
 *                       }
 *                 )
 */

/**
 *                 @OA\Schema(
 *                     schema="serverError",
 *                     @OA\Property(
 *                         property="statusCode",
 *                         type="integer",
 *                         description="Codigo de estado"
 *                     ),
 *                     @OA\Property(
 *                         property="error",
 *                         type="object",
 *                          @OA\Items(
 *                              @OA\Property(
            *                         property="type",
            *                         type="string",
            *                         description="Tipo"
            *                   ),
            *                   @OA\Property(
            *                         property="description",
            *                         type="string",
            *                         description="Descripción del mensaje"
            *                   ),
 *                          )
 *                     ),
 *                     example={
 *                          "statusCode":500,
 *                          "error":{
 *                              "type": "SERVER_ERROR",
 *                              "description": "syntax error, unexpected ..."
 *                          }
 *                     }
 *                 )
 */

/**
 *                 @OA\Schema(
 *                     schema="ResponseInsert",
 *                     @OA\Property(
 *                         property="id",
 *                         type="integer",
 *                         description="Id del nuevo registro"
 *                     ),
 *                     @OA\Property(
 *                         property="message",
 *                         type="string",
 *                         description="Descripción del mensaje"
 *                     ),
 *                     example={
 *                       "id": 0,
 *                       "message": "Registro creado con éxito."
 *                       }
 *                 )
 */

 /**
 *                 @OA\Schema(
 *                     schema="ResponseMessage",
 *                     @OA\Property(
 *                         property="message",
 *                         type="string",
 *                         description="Descripción del mensaje"
 *                     ),
 *                     example={
 *                       "message": "Operación realizada con éxito."
 *                       }
 *                 )
 */

/**
 *                 @OA\Schema(
 *                     schema="RequestJWT",
 *                     @OA\Property(
 *                         property="user",
 *                         type="string",
 *                         description="Usuario"
 *                     ),
 *                     @OA\Property(
 *                         property="pwd",
 *                         type="string",
 *                         description="Password"
 *                     ),
 *                     example={
 *                       "user": "JWTuser",
 *                       "pwd": "JWTpwd"
 *                       }
 *                 )
 */

/**
 *                 @OA\Schema(
 *                     schema="ResponseJWT",
 *                     @OA\Property(
 *                         property="token",
 *                         type="string",
 *                         description="Token generado"
 *                     ),
 *                     @OA\Property(
 *                         property="expires",
 *                         type="string",
 *                         description="Timestamp de expiración del token"
 *                     ),
 *                     example={
 *                       "token": "token121324nioniovionionfiownofnoifnweionfioefnio",
 *                       "pwd": "234567890987654567890"
 *                       }
 *                 )
 */

?>