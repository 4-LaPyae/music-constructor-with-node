<?php

namespace App\Fileoperations;

use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Traits\ImagePathTraits;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\Admin\RecordingRequest;

class RecordingOperation
{
    use ImagePathTraits;
    private $recordingId;
    private $base64ImageData;
    private $formDataImage;



    public function __construct($recordingId, $base64ImageData = null, $formDataImage = null)
    {
        $this->recordingId = $recordingId;
        $this->base64ImageData = $base64ImageData;
        $this->formDataImage = $formDataImage;
    }

    public function StoreRecordingImage()
    {
        $filePath = '/medias/img/recording/' . $this->recordingId . '/' . Carbon::now()->format('Y');
        $path = Storage::disk('public_uploads')->put($filePath, $this->formDataImage);
        return $this->url . '/' . $path;
    }

    public function StoreRecordingBase64Image()
    {
        $base64Image = new Base64ImageDecoder($this->base64ImageData);
        $file = $base64Image->decoder();
        $filePath = '/medias/img/contract/' . $this->recordingId . '/' . Carbon::now()->format('Y') . '/' . Str::uuid() . '.'  . $file['type'];
        Storage::disk('public_uploads')->put($filePath, $file['data']);
        return $this->url . $filePath;
    }
}
