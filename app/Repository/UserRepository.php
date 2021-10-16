<?php

namespace App\Repository;

use App\Models\Roles;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Spatie\QueryBuilder\QueryBuilder;

//                        <----------- Welcome To UserRepository Page ----------->

class UserRepository extends BaseRepository
{
    // Repo to Get all employee wireless
    public function getListUsers($skip, $take, $domain)
    {
        $result = QueryBuilder::for(User::class)
            ->allowedIncludes('roles')
            ->allowedFilters('full_name', 'name', 'email', 'phone', 'status', 'role_id')
            ->allowedSorts('id')
            ->where('is_deleted', 0);

        $resp = [
            'totalCount' => $result->get()->count(),
            'items' => $result->skip($skip)->take($take)->get()
                ->map(function ($item) use ($domain) {
                    $data['id'] = $item->id;
                    $data['full_name'] = $item->full_name;
                    $data['email'] = $item->email;
                    $data['phone'] = $item->phone;
                    $data['status'] = $item->status;
                    $data['role_id'] = $item->role_id;
                    $data['image'] =  ($item->image != null)? $domain . $this->imageBuket . $item->image:null;
                    $data['created_at'] = $item->created_at;
                    $data['updated_at'] = $item->updated_at;
                    $data['roles'] = $item->roles;
                    return $data;
                }),
        ];
        return $response = array('message' => $resp, 'code' => 200);
    }

    //Repo for Login 
    public function authenticate($request)
    {
        $user = User::where('email', $request['email'])
            ->where('is_deleted', 0)
            ->with('roles')
            ->first();
            if(!$user){
                return $response = ['message' => ['error' =>  'invalid_credentials'], 'code' => 401];
            }

        if (!Hash::check($request['password'], $user->password)) { //check password
            return $response = ['message' => 'The password is invalid', 'code' => 401];
        }

        $active = User::where('email', $request['email'])->where('status', 0)->where('is_deleted', 0)->first();

        if (!$active) { //check user active
            return $response = ['message' => 'This user not active', 'code' => 400];
        }

        try {
            JWTAuth::factory()->setTTL(60 * 24 * 360 * 20);
            if (!$token = JWTAuth::fromUser($user)) {
                return $response = ['message' =>  'invalid_credentials', 'code' => 401];
            }
        } catch (JWTException $e) {
            return $response = ['message' => 'could_not_create_token', 'code' => 400];
        }
         $baseToken =  auth()->claims([
            'user_id' => $user->role_id,
            'full_name' => $user->full_name,
            'email' => $user->email,
         ])->fromUser($user);
        return ['message' => ['token' => $baseToken], 'code' => 200];
    }

    //Repo for registration 
    public function registerUser($request)
    {
        $request['password'] = Hash::make($request['password']);
        $user = User::create($request);
        $response = ['message' => 'Registration successfully', 'userData' => $user, 'code' => 200];
        return $response;
    }

    //Repo for Logout user
    public function logoutUser()
    {
        auth()->logout();
        return  $response = ['message' => 'Successfully logged out', 'code' => 200];
    }

    //Repo for user details
    public function me($domain)
    {
        $id = auth()->user()->id;
        $user = User::where('id', $id)->with('roles')->get()->map(function ($item) use ($domain) {
            $data['id'] = $item->id;
            $data['full_name'] = $item->full_name;
            $data['email'] = $item->email;
            $data['phone'] = $item->phone;
            $data['status'] = $item->status;
            $data['role_id'] = $item->role_id;
            $data['image'] = ($item->image != null)? $domain . $this->imageBuket . $item->image:null;
            $data['created_at'] = $item->created_at;
            $data['updated_at'] = $item->updated_at;
            $data['roles'] = $item->roles;
            return $data;
        });
        return  $user;
    }




    public function find($id, $domain)
    {
        User::where('id', $id)->firstOrFail();
        $users = QueryBuilder::for(User::class)
            ->where('id', $id)
            ->allowedIncludes('files', 'files.user', 'files.categories', 'total')
            ->get()
            ->map(function ($item) use ($domain) {
                $data['id'] = $item->id;
                $data['full_name'] = $item->full_name;
                $data['email'] = $item->email;
                $data['phone'] = $item->phone;
                $data['status'] = $item->status;
                $data['role_id'] = $item->role_id;
                $data['image'] = ($item->image != null)? $domain . $this->imageBuket . $item->image:null;
                $data['created_at'] = $item->created_at;
                $data['updated_at'] = $item->updated_at;
                $size = 0;
                $dataFiles = $item->files->map(function ($item) use ($domain) {
                $filesObject['id'] = $item->id;
                $filesObject['title'] = $item->title;
                $filesObject['size'] = $item->totale_size;
                $filesObject['categories'] = [
                    'categoriesId' => $item->categories->id,
                    'categoriesName' => $item->categories->name,
                    'categoryIcon' => $item->categories->icon,
                ];
                $filesObject['user']=[
                              'id' => $item->user->id,
                              'full_name' => $item->user->full_name,
                              'image' => ($item->user->image != null)? $domain . $this->imageBuket . $item->user->image:null,
                ]; 
                return $filesObject ;
            });

                $sizeFiles = $item->files->map(function ($item) use ($domain) {
                    return (int)$item->totale_size;
                });
                $size = 0;
                foreach($sizeFiles as $total){
                    $size += $total;
                }
                $data['total_size'] = $size;
                $data['files'] = $dataFiles;
                $data['roles'] = $item->roles;
                $data['total'] = $item->total;
                return $data;
            });

        return $users[0];
    }
}

//                        <----------- Thank You For Read The Code ----------->
