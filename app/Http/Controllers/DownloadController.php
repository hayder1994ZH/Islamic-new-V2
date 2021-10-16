<?php

namespace App\Http\Controllers;
use App\Models\Downloads;
use App\Repository\DownloadsRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class DownloadController extends Controller
{
    private $DownloadsRepository;
    public function __construct()
    {
        $this->DownloadsRepository = new DownloadsRepository(new Downloads());
    }

        /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $downloads = $this->DownloadsRepository->index($request->get('host'));
        return $downloads;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // $response = $this->DownloadsRepository->destroy($id);
        // return $response;
    }

    public function destroyAll()
    {
        // $response = $this->DownloadsRepository->destroyAll();
        // return $response;
    }

    public function count()
    {
        $fromDate = date('Y-m-d');
        $toDate = date('Y-m-d');
        $check = DB::table('downloads')->where('user_id' , auth()->user()->id)
        ->whereRaw(
              "(created_at >= ? AND created_at <= ?)", 
              [$fromDate." 00:00:00", $toDate." 23:59:59"]
            )->get()->count();
        return ['downloadCount' => $check];        
    }
}
