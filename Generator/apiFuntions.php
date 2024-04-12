<?php
function getHttpStatusMessage($statusCode){
    $httpStatus = array(
        100 => 'Continue',  
        101 => 'Switching Protocols',  
        200 => 'OK',
        201 => 'Created',  
        202 => 'Accepted',  
        203 => 'Non-Authoritative Information',  
        204 => 'No Content',  
        205 => 'Reset Content',  
        206 => 'Partial Content',  
        300 => 'Multiple Choices',  
        301 => 'Moved Permanently',  
        302 => 'Found',  
        303 => 'See Other',  
        304 => 'Not Modified',  
        305 => 'Use Proxy',  
        306 => '(Unused)',  
        307 => 'Temporary Redirect',  
        400 => 'Bad Request',  
        401 => 'Unauthorized',  
        402 => 'Payment Required',  
        403 => 'Forbidden',  
        404 => 'Not Found',  
        405 => 'Method Not Allowed',  
        406 => 'Not Acceptable',  
        407 => 'Proxy Authentication Required',  
        408 => 'Request Timeout',  
        409 => 'Conflict',  
        410 => 'Gone',  
        411 => 'Length Required',  
        412 => 'Precondition Failed',  
        413 => 'Request Entity Too Large',  
        414 => 'Request-URI Too Long',  
        415 => 'Unsupported Media Type',  
        416 => 'Requested Range Not Satisfiable',  
        417 => 'Expectation Failed',  
        500 => 'Internal Server Error',  
        501 => 'Not Implemented',  
        502 => 'Bad Gateway',  
        503 => 'Service Unavailable',  
        504 => 'Gateway Timeout',  
        505 => 'HTTP Version Not Supported');
    return ($httpStatus[$statusCode]) ? $httpStatus[$statusCode] : $status[500];
}

function consumeApi($body){

        $URL_CONSUME = 'http://node.gtp.bz:8000/v1/ast';
        $ch = curl_init();
        $headers = array(
            'Content-Type:application/json'
        /*'Content-Type: application/json',
        'Authorization: Basic '.base64_encode('basic_user:e3dc9e1697dbb6dd97af0afcd3e822d811d2fe2e')*/
        );
        curl_setopt($ch, CURLOPT_URL, $URL_CONSUME);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $body = json_encode($body, JSON_NUMERIC_CHECK);

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST"); 
        curl_setopt( $ch, CURLOPT_POSTFIELDS, $body );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
        // Timeout in seconds
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
        $result = curl_exec($ch);
        $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        return array("status"=>$http_status, "body"=>$result);

}

?>