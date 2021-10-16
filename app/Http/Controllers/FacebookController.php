<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Exception;
use Laravel\Socialite\Facades\Socialite;

use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class FacebookController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function redirect(Request $request)
    {
        config(['services.facebook.redirect' =>  $request->get('host').'/auth/facebook/callback']);
        return Socialite::driver('facebook')->redirect();
    }

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function callback(Request $request)
    {
        config(['services.facebook.redirect' =>  $request->get('host').'/auth/facebook/callback']);
        $user = Socialite::driver('facebook')->user();
            $finduser = User::where('facebook_id', $user['id'])
                            ->orWhere('email', $user['email'])
                            ->with('roles')
                            ->first();
            if(!$finduser) {
                User::create([
                    'full_name' => $user['name'],
                    'email' => $user['email'],
                    'facebook_id'=> $user['id'],
                    'password' => encrypt('1234'),
                    'roles_id' => 3,
                ]); 
                $finduser = User::where('facebook_id', $user['id'])
                                ->orWhere('email', $user['email'])
                                ->first();
            } 
            JWTAuth::factory()->setTTL(60*24*360*20);
            $token = JWTAuth::fromUser($finduser);
            $token = auth()->claims([
            'user_id' => $finduser->roles_id,
            ])->fromUser($finduser);
                $response = [
                    'user' => $finduser,
                    'token' => $token
                 ];
                 return redirect($request->get('host').'/#/token/'. $token);
    
    }

}
