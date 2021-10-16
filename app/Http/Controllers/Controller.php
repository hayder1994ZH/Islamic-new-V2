<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    public $imageSize = '';
    // public $imageSize = '?h=256';
    public $imageBuket = '/images/';
    public $vedioBuket = ':7777/islamic_videos/';
    public $audioBuket = ':7777/islamic_audio/';
    public $socialBuket = ':7777/islamic_social/';
    //    public $imageSize = '';
    // // public $imageSize = '?h=256';
    // public $imageBuket = '/storage/';
    // public $vedioBuket = '/storage/';
    // public $audioBuket = '/storage/';
    // public $socialBuket = '/storage/';
}
