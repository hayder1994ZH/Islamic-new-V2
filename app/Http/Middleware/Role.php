<?php

namespace App\Http\Middleware;

use Illuminate\Support\Facades\Auth;
use Closure;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Tymon\JWTAuth\Exceptions\JWTException;

class Role extends Middleware
{
    /**
     * Handle an incoming request.
     * @param array $roles
     * @param $next
     * @param $request
     * @return mixed
     * @throws JWTException
     */
    public function handle($request, Closure $next,...$roles)
    {
        if (!Auth::guard('api')->check()) {
            return response()->json([
                "message" => "Unauthorised",
            ],401);
        }
        $user_role = auth('api')->user()->roles->name;
        foreach ($roles as $role) {
            if($role == $user_role) {
                return $next($request);
            }
        }
        return response()->json([
            "message" => "you don't have permission",
            "roles" => $roles
        ],401);

    }


    

    

}
