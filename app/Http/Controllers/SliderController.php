<?php

namespace App\Http\Controllers;
use App\Models\Slider;
use App\Repository\SliderRepository;
use Illuminate\Http\Request;

class SliderController extends Controller
{
    private $SliderRepository;
    public function __construct()
    {
        $this->SliderRepository = new SliderRepository(new Slider());
        $this->middleware('role:Admin,owner', ['only' => ['store', 'destroy', 'update']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $request->validate([
            'skip' => 'Integer',
            'take' => 'required|Integer'
        ]);
            
        $type = $request->validate([
            'type' => 'required|Integer'
        ]);
        
        $take = $request->take;
        $skip = $request->skip;
        $domain = $request->get('host');
        $imageSize = $request->get('imageSize');
        $data = $this->SliderRepository->index($take, $skip, $domain, $imageSize, $type['type']);
        return $data;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'file_id' => 'required|exists:files,id',
        ]);
        $this->SliderRepository->create($data);
        return response()->json(["message" => "slider created"]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Slider  $slider
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Slider  $slider
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'file_id' => 'required|exists:files,id',
        ]);
        $this->SliderRepository->update($id, $data);
        return response()->json(["message" => "slider Updated"]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Slider  $slider
     * @return \Illuminate\Http\Response
     */
    public function destroy(Slider $slider)
    {
        $delete = $this->SliderRepository->softDelete($slider);
        return $delete;
    }
}
