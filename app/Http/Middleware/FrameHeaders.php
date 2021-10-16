<?php

namespace App\Http\Middleware;

use Closure;

class FrameHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $response = $next($request);
        $response->header('Access-Control-Allow-Origin', '*');
        $response->header('Access-Control-Allow-Credentials', 'true');
        $response->header('Access-Control-Allow-Methods', 'GET,HEAD,OPTIONS,POST,PUT');
        $response->header('Access-Control-Allow-Headers', 'XMLHttpRequest, Access-Control-Allow-Headers, Origin,Accept, X-Requested-With, Content-Type, Authorization, Access-Control-Request-Method, Access-Control-Request-Headers');
//        $response->header('X-Xss-Protection', '1; mode=block');
//        $response->header('X-Frame-Options', 'sameorigin');
//        $response->header('Feature-Policy', "vibrate 'none'; geolocation 'none'");
//        $response->header('Strict-Transport-Security', "max-age=15552000; includeSubDomains; preload");
//        $response->header('Content-Security-Policy', "upgrade-insecure-requests");
//        $response->header('X-Content-Type-Options', 'nosniff');
//        $response->header('X-Content-Security-Policy', "default-src 'self'; block-all-mixed-content; connect-src 'self'; font-src 'self' https://fonts.gstatic.com/; img-src 'self' https://ssl.google-analytics.com/ http://www.google-analytics.com/; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://ssl.google-analytics.com/; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com/");
        return $response;
    }
}
