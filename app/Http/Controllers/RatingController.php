<?php

namespace App\Http\Controllers;

use App\Models\Files;
use App\Models\Rating;
use App\Helpers\Utilities;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Repository\FilesRepository;
use App\Repository\RatingRepository;

class RatingController extends Controller
{
                
    private $RatingRepository;
    private $FilesRepository;
    private $id;
    public function __construct()
    {
        $this->RatingRepository = new RatingRepository(new Rating());
        $this->FilesRepository = new FilesRepository(new Files());
    }

    //Add Rating
    public function add(Request $request)
    {
        //Validations
        $data = $request->validate([
            'stars' => 'required|integer|gte:1|lte:5',
            'file_id' => 'exists:files,id'
        ]);
        $data['user_id'] = auth()->user()->id;
        $check = Rating::where('file_id', $data['file_id'])
                        ->where('user_id', $data['user_id'])
                        ->first();
        if($check) {
            return response()->json(["message" => "you can't rating this file agine"], 401);
        } else {
           
            $this->RatingRepository->create($data);
            $rate = $this->groupBy($data['file_id']);
            $this->FilesRepository->update($data['file_id'], $rate);
            return response()->json(["message" => "rating successfuly"], 200);
        }
    }
    public function groupBy($id)
    {
        $countUsers = Rating::where('file_id', $id)
        ->get()->count();
        $ads = Rating::where('file_id', $id)
                 ->select('stars', DB::raw('count(*) as total'))
                 ->groupBy('stars')
                 ->get()
        ->map(function ($item) {
            if($item->stars == 1 )
                $data['stars'] = $item->stars == 1 ? $item->total*2 : 0;

            if($item->stars == 2 )
            $data['stars'] = $item->stars == 2 ? $item->total*4 : 0;

            if($item->stars == 3 )
                $data['stars'] = $item->stars == 3 ? $item->total*6 : 0;

            if($item->stars == 4 )
                $data['stars'] = $item->stars == 4 ? $item->total*8 : 0;

            if($item->stars == 5 )
                $data['stars'] = $item->stars == 5 ? $item->total*10 : 0;
            return $data;
        });
        if ($ads->isEmpty()) {
           return  ['rating' => 0];
        } else {
            $rateCount = 0;
            foreach($ads as $rate){
                $rateCount += $rate['stars'];
            }
             if($rateCount == 0){
                 return ['rating' => 0];
             } else{
                return ['rating' => $rateCount/$countUsers];
             }
        }
        
    }
}
