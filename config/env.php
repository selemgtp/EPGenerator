<?php
$env = [
    /* Este es el array que va a contener los usuarios a usarsen en la autenticación basada en JWT.
    Para iniciar, solo se va a usar un único usuario; si se necesita agregar mas se puede hacer; o bien si desea
    elimanr el acceso al api a un usuario en particular, solo se debe eliminar del array
    formato 'usuario'=>'password'
    */
    'jwt_authentication_users'=>[
        'jwt_user'=>'330fcf814961b3a3b4bf66d980fcb487a47a89d9'
    ],
    'jwt_secret'=>'ms-gtp',
    /* Este es el array que va a contener los usuarios a usarsen en la autenticación básica.Para iniciar, 
    solo se va a usar un único usuario; si se necesita agregar mas se puede hacer; o bien si desea
    elimanr el acceso al api a un usuario en particular, solo se debe eliminar del array
    formato 'usuario'=>'password'
    */
    'basic_authentication_users'=>[
        'basic_user'=>'e3dc9e1697dbb6dd97af0afcd3e822d811d2fe2e'
    ]
];
return $env;
?>