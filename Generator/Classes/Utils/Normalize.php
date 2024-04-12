<?php
declare(strict_types=1);
namespace Generator\Classes\Utils;

class Normalize{
    public static function normalizeUCWords(string $string) : string{
        return str_replace(" ", "",trim(ucwords(preg_replace('([^A-Za-z0-9 ])', ' ', $string))));
    }

    public static function normalizeColumnName(string $string) : string{

        $newString = trim(preg_replace('([^A-Za-z0-9 ])', ' ', $string));
        $parts = explode(" ", $newString);
        if (count($parts) == 1){
            return $newString;
        }else{
            $finalString = "";
            for ($i = 0; $i < count($parts); $i++){
                if ($i == 0){
                    $finalString .= $parts[$i];
                }else{
                    $finalString .= ucfirst($parts[$i]);
                }
            }
        }
        return (count($parts) == 1) ? $newString : $finalString;
    }
}

?>