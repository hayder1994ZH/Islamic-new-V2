<?php
namespace App\Repository;

use App\Models\Roles;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

//                        <----------- Welcome To RolesRepository Page ----------->

class RolesRepository extends BaseRepository {

     
    //Repo to Get all users
    public function getListRoles($skip, $take)
    {
        $result = Roles::where('is_deleted', 0)
                            ->orderBy("id", "desc");
        $totalCount = Roles::where('is_deleted', 0)->get();

        return  $resp = [
                    'items' => $result->skip($skip)->take($take)->get(),
                    'totalCount' => $totalCount->count()
                ];
    }

    
}

//  