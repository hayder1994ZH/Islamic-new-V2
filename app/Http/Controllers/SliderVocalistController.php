<?php

namespace App\Http\Controllers;
use App\Models\SliderVocalist;
use App\Repository\SliderVocalistRepository;
use Illuminate\Http\Request;

class SliderVocalistController extends Controller
{
    private $SliderVocalistRepository;
    public function __construct()
    {
        $this->SliderVocalistRepository = new SliderVocalistRepository(new SliderVocalist());
        $this->middleware('role:Admin,owner', ['only' => ['store', 'update']]);
        $this->middleware('role:owner', ['only' => ['destroy']]);
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
        $data = $this->SliderVocalistRepository->index($take, $skip, $domain, $imageSize, $type['type']);
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
            'vocalist_id' => 'required|exists:vocalists,id',
        ]);
        $this->SliderVocalistRepository->create($data);
        return response()->json(["message" => "SliderVocalist created"]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\SliderVocalist  $SliderVocalist
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\SliderVocalist  $SliderVocalist
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'vocalist_id' => 'required|exists:vocalists,id',
        ]);
        $this->SliderVocalistRepository->update($id, $data);
        return response()->json(["message" => "Slider Vocalist Updated"]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\SliderVocalist  $SliderVocalist
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $SliderVocalist = SliderVocalist::where('id', $id)->where('is_deleted', 0)->firstOrFail();
        $delete = $this->SliderVocalistRepository->softDelete($SliderVocalist);
        return $delete;
    }
}
