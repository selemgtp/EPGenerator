# Generador de Endpoints Rest

Esta es una herramienta que genera Endpoinst para una Api REST con su documentación a partir de sentencias SQL. Usa [Slim4](https://www.slimframework.com/docs/v4/) como Framework para Api, [Doctrine DBAL](https://github.com/doctrine/dbal) para obtener metadatos de la(s) Base de Datos y  generar documentación [OpenApi](https://www.openapis.org/), [node-sql-parser](https://www.npmjs.com/package/node-sql-parser) para validar las sentencias SQL.

## Instalación

Clonar el repositorio y ubicarse en el directorio principal. Usar [composer](https://getcomposer.org/) para instalar dependencias con el comando:

```bash
composer install
```
## Ejecución

La herramienta funciona solo con subirse a un servidor cualquiera. Se debe tener en cuenta que cada vez que se crea un nuevo Endpoint, se crean nuevos ficheros que van a quedar fuera de Control de Versiones, por lo que se debe realizar el respaldo de ellos.  

## Estructra

- `config` : Esta carpeta contiene ficheros de configuración del Api REST Slim4. Se modifican cada vez que se genera un nuevo Endpoint (excepto el fichero `env.php`).
- `src` : Esta carpeta va a contener todos los fichero `.php` que van a contener la lógica del nuevo Endpoint.
- `Generator`: Contiene todo lo relacionado al generador. 

## Uso

Existen 2 formas de crear Endpoints: 

- Interfaz gráfica: `https://domain/Generator/login.html` permite ingresar con un usuario y contraseña configurados en el archivo `Generator/users.json`. El password está encriptado con `Sha1`.  No obstante, se pretende migrar a login mediante Base de Datos.
- Endpoint: Puede consumirse el Endpoint `https://domain/Generator/createEndpointRest.php` mediante `POST`.
```php
<?php
function consumeApi($requestBody){
    //URL a consumir
    $URL_CONSUME = 'http://domain/Generator/createEndpointRest.php';
    //inicialización curl
    $ch = curl_init();
    //header para indicvar que se debe enviar un json
    $headers = array(
        'Content-Type:application/json'
    );
    curl_setopt($ch, CURLOPT_URL, $URL_CONSUME);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    //se convierte el array de parámetro en JSON
    $body = json_encode($requestBody, JSON_NUMERIC_CHECK);
    //Método POST
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST"); 
    curl_setopt( $ch, CURLOPT_POSTFIELDS, $body);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // Timeout en segundos
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    //ejecución curl
    $result = curl_exec($ch);

    //se obtiene el código de estado HTTP de la petición
    $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    //retorno de la respuesta
    return array("status"=>$http_status, "body"=>$result);

}

//ejemplo cuerpo a enviar
$requestBody = array(
    
        "module"=>"Cotizador", //nombre del módulo. 
        "name"=>"selectmultiple",//nombre de la consulta. 
        //La consulta debe ser preparada (con signo ? en los parámetros)
        "sqlUser"=>"SELECT promo.prepago, cliente.saldo_disponible, usuario.activo FROM `rad`.`usuario` INNER JOIN `rad`.`rad_cfg_promo` ON usuario.usupromo = rad_cfg_promo.rad_promo INNER JOIN `maestra`.`cliente` ON rad_cfg_promo.pid_cfg = cliente.id INNER JOIN `rad`.`promo` ON usuario.usupromo = promo.pnom WHERE usuario.id = ?",
        "query_type"=>"MULTIPLE",//tipo múltiple 
        "db"=>""//se debe enviar vacío cuando es múltiple; si es una Base de Datos en concreto, se debe enviar.
     
    );

    //ejecución de la función para realizar la petición
$responseApi = consumeApi($requestBody);

$responseStatus = $responseApi["status"]; //código de estado: 201, 400, 500
$responseBody = json_encode($responseApi["body"],true); // respuesta de petición

echo $responseBody;


?>
```

También existe posibilidad de simular la creación del endpoint medinate el Endpoint `https://domain/Generator/testingEndpoint.php`.

## Contenido de los Endpoints

- Se pueden crear Endpoints tipo `GET, POST, PUT y DELETE` dependiendo de la sentencia trabajada.
- Se agrega toda la documentación OpenApi que permite tener el Swagger del Endpoint.
- Cuando se genera un nuevo Endpoint, se retorna toda la información asociada al suceso. Se puede ver en el fichero :
```php
<?php
 $arrayResponseFinal["api"] = array(
        "name"=>$queryName,
        "query" => $rawSql,
        "endpoint" => $endpoint,
        "method" => $method,
        "requestBody" => $arrayRequest["request"],
        "responseBody" => $arrayResponse,
        "doc" => $doc
    );
?>
```
- Cada nuevo Endpoint genera un fichero `.php` con toda la lógica de conexión, validaciones, documentación y ejecución.
_Documentación en construcción..._