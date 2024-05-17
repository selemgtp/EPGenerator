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

- `config`: Esta carpeta contiene ficheros de configuración del Api REST Slim4. Se modifican cada vez que se genera un nuevo Endpoint (excepto el fichero `env.php`).
- `src`: Esta carpeta va a contener todos los fichero `.php` que van a contener la lógica del nuevo Endpoint.
- `Generator`: Contiene todo lo relacionado al generador. 
- `public_html`: Contiene el Swagger UI y l archivo `index.php` que permite que el API funcione y los endpoints estén disponible.
- `nodeApi`: Contiene el código de un API Rest `NodeJS express.js` que permite usar `node-sql-parser` en el contexto de PHP (consumiendo el API). Esta API también contiene docuemntación Swagger que explica su consumo.

Los archivos a tener en cuenta para configurar el entorno de trabajo de la herramienta son los siguientes:

- `Generator/Database/database.php` y `config/settings.php`: Contienen el listado de todas las Bases de Datos para las cuales se van a crear los endpoints. Deben ser las mismas en ambos ficheros.
- `Generator/apiFunctions.php`: Se debe modificar la variable `$URL_CONSUME` del método `consumeApi`. Este es el dominio donde se despliega la `NodeJS express.js` del directorio `nodeApi`.
- `Generator/users.json`: Contiene el listado de usuario con acceso a la herramienta Web. El password está encriptado con `Sha1`.  No obstante, se pretende migrar a login mediante Base de Datos a futuro.
- `Generator/modules.php`: Contiene un `CURL` que consume el nombre de los módulos a trabajar. Esto permite segmentar los endpoints por agrupaciones. Util para el orden y la documentación Swagger. Si no se obtienen desde un endpoint externo a la herramienta, es posible crear un array de esta forma:

    ```php
    $modules = array(
        array("nombre" => "Modulo 1"),
        array("nombre" => "Modulo 2"),
        array("nombre" => "Modulo 3")
    );

    echo json_encode($modules);
    ```

- `public_html/index.php`: En este fichero se debe configurar la `$app->setBasePath("/endpointSql");` con el directorio donde se va a alojar la herramienta a nivel de servidor.

- `.htaccess`: Se debe configurar la misma ruta configurada en `public_html` en la línea y agregando la carpeta `Generator`: `RewriteCond  %{REQUEST_URI} !^/endpointSql/Generator`
## Uso

Existen 2 formas de crear Endpoints: 

- Interfaz gráfica: `https://domain/Generator/login.html` permite ingresar con un usuario y contraseña configurados en el archivo `Generator/users.json`. 
- Endpoint: Puede ejecutar la acción mediante el consumo de `https://domain/Generator/createEndpointRest.php` usando el método `POST`.

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

También existe posibilidad de simular la creación del endpoint mediante el Endpoint `https://domain/Generator/testingEndpoint.php`.


Cuando se crean nuevos endpoints a través de solicitudes POST (no interfaz gráfica), se apoya en el consumo del API de `NodeJS express.js` para ejecutar [node-sql-parser](https://www.npmjs.com/package/node-sql-parser) en entorno de servidor y validar los SQL.  

## Contenido de los Endpoints

- Se pueden crear Endpoints tipo `GET, POST, PUT y DELETE` dependiendo de la sentencia trabajada.
- Se agrega toda la documentación OpenApi que permite tener el Swagger del Endpoint.
- Cuando se genera un nuevo Endpoint, se retorna toda la información asociada al suceso. Se puede ver en el fichero:
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
