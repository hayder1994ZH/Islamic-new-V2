<?php
namespace App\Helpers;

class Utilities {

    public static function wrap($data,$code)
    {
        return response()->json($data, $code);
    }

    public static function wrapStatus($data, int $httpCode)
    {
        return response()->json($data, $httpCode);
    }

}
