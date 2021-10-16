<?php

namespace App\Http\Middleware;


use Closure;
use JWTAuth;
use Exception;
use App\Models\Roles;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;

class ClintMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
        } catch (Exception $e) {
            if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException){
                return response()->json(['status' => 'Token is Invalid']);
            }else if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException){
                return response()->json(['status' => 'Token is Expired']);
            }else{
                return response()->json(['status' => 'Authorization Token not found']);
            }
        }
       
    // $roles_id = auth()->user()->roles_id;
    // $admin = Roles::where('id', $roles_id)->first();
    // if(!$admin){
    //    return response()->json(['error'=> 'Unauthorized'], 401); 
    // }
    // if($admin->name == 'clints')
    // {
       return $next($request);
    // }
    //  return response()->json(['error'=> 'Unauthorized'], 401); 
    // }
    }
}
