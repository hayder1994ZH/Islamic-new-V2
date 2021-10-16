<?php

namespace App\Http\Middleware;
use Closure;
use JWTAuth;
use Exception;
use Illuminate\Support\Facades\Auth;

use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;
use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    public function handle($request, Closure $next)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
        } catch (Exception $e) {
            if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException){
                return response()->json(['status' => 'Token is Invalid'], 400);
            }else if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException){
                return response()->json(['status' => 'Token is Expired'], 401);
            }else{
                return response()->json(['status' => 'Authorization Token not found'],401);
            }
        }
        return $next($request);
    }
}













// namespace App\Http\Middleware;

// use Illuminate\Auth\Middleware\Authenticate as Middleware;

// class Authenticate extends Middleware
// {
//     /**
//      * Get the path the user should be redirected to when they are not authenticated.
//      *
//      * @param  \Illuminate\Http\Request  $request
//      * @return string|null
//      */
//     protected function redirectTo($request)
//     {
//         if (! $request->expectsJson()) {
//             return route('login');
//         }
//     }
// }
