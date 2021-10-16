<?php
namespace App\Repository;

use App\Models\Version;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;


class VersionRepository extends BaseRepository {

public function getAllVersion($take, $skip, $domain){

    $result = Version::where('is_deleted', 0)
    ->orderBy("id", "desc");
    $totalCount = Version::where('is_deleted', 0)->get();

    return [
        'totalCount' => $totalCount->count(),
        'items' => $result->skip($skip)->take($take)->get()
    ];
}
    
}