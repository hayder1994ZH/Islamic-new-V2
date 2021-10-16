<?php

namespace App\Models;

use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable;
   
    protected $guarded =[];
    protected $hidden = [
        'is_deleted',
        'password',
    ];


    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }
    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function roles(){
        return $this->belongsTo(Roles::class, 'role_id');
    }
    
    public function files()
    {
        return $this->hasMany(Files::class)->where('is_deleted', 0)->where('aproved', 0)->orderBy('created_at', 'desc');
    }
            
    public function filesSize()
    {
        return $this->hasMany(Files::class);
    }
    // public function fileUser()
    // {
    //     return $this->hasMany(Files::class)->with('user');
    // }
    // public function fileSubcategories()
    // {
    //     return $this->hasMany(Files::class)->with('subcategories');
    // }

    public function favorite()
    {
        return $this->hasMany(Favorite::class)->where('is_deleted', 0);
    }
    public function download()
    {
        return $this->hasMany(Downloads::class)->with('fileer')->where('is_deleted', 0);
    }
    public function company(){
        return $this->belongsTo(Companies::class, 'id', 'user_id')->where('is_deleted', 0);
    }
    
}
