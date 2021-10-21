<?php
namespace App\Repository;

use App\Models\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Spatie\QueryBuilder\QueryBuilder;


class CollectionRepository extends BaseRepository {

    public function getAllCollection($take, $skip, $domain){

        $result =  QueryBuilder::for(Collection::class)->where('is_deleted', 0)
        ->withCount('files')
        ->with('user')
        ->allowedFilters(['name'])
        ->orderBy("id", "desc");

        return [
            'totalCount' => $result->count(),
            'items' => $result->skip($skip)->take($take)->get()
        ];
    }
}