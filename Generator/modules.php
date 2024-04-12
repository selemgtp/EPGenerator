<?php
try{
    $URL_CONSUME = "https://ms.gtp.bz/crud/rad/modulo";
    $ch = curl_init();
    $headers = array(
    'Content-Type: application/json',
    'Authorization: Basic '.base64_encode('basic_user:e3dc9e1697dbb6dd97af0afcd3e822d811d2fe2e')
    );
    curl_setopt($ch, CURLOPT_URL, $URL_CONSUME);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    $body = '{"filter":{},"select":[]}';

    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET"); 
    curl_setopt( $ch, CURLOPT_POSTFIELDS, $body );
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // Timeout in seconds
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

    $result = curl_exec($ch);

    echo $result;
}catch(Throwable $t){
    echo json_encode(array("error"=>"Ocurrió un error al obtener los módulos: ".$t->getMessage()));
}
?>