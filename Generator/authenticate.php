<?php 
$jsonUsers = json_decode(file_get_contents(__DIR__."/users.json"), true);
list($user, $password) = array($_POST["user"], $_POST["password"]);
$access = false;

for ($i = 0; $i < count($jsonUsers); $i++){
    if ($jsonUsers[$i]["user"] == $user){
        if ($jsonUsers[$i]["password"] == sha1($password)){
            $access = true;
        }
        $i = count($jsonUsers);
    }
}

if ($access){
    session_start();
    $_SESSION["id"] = base64_encode($user."-".sha1($password));
    echo 1;
}else{
    echo 0;
}
?>