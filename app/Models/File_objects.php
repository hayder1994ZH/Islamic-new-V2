<?php

namespace App\Models;

use App\Helpers\Utilities;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;

class File_objects extends Model
{
    protected $guarded =[];
    
    protected $hidden =[
        'is_deleted','files_id', 'key', 'buket', 'updated_at'
    ];
    protected $appends = ['imageFile','file_audio', 'file_video' ];

    public function getImageFileAttribute(){
        if($this->buket == 'islamic_images'){
            return ($this->key != null)? request()->get('host') . Utilities::$imageBuket . $this->key:null;
        }
        return null;

    }
    public function getFileAudioAttribute(){
        if($this->buket == 'islamic_audio'){
            return ($this->key != null)? request()->get('host') . Utilities::$audioBuket . $this->key:null;
        }
        return null;
    }

    public function getFileVideoAttribute(){
        if($this->buket == 'islamic_videos'){
            return ($this->key != null)? request()->get('host') . Utilities::$vedioBuket . $this->key:null;
        }
        return null;
    }
    
    public function file()
    {
        return $this->belongsTo(Files::class, 'files_id', 'id');
    }
    public function domain(Request $request)
    {
        return $request->get('host');
    }

}
