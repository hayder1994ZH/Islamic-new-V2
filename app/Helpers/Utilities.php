<?php
namespace App\Helpers;

class Utilities {

    public static $imageSize = '';
    // public $imageSize = '?h=256';
    public static $imageBuket = '/images/';
    public static $vedioBuket = ':7777/islamic_videos/';
    public static $audioBuket = ':7777/islamic_audio/';
    public static $socialBuket = ':7777/islamic_social/';
    
    public static function wrap($data,$code)
    {
        return response()->json($data, $code);
    }

    public static function wrapStatus($data, int $httpCode)
    {
        return response()->json($data, $httpCode);
    }

}
