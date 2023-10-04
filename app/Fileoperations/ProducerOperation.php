<?php

namespace App\Fileoperations;

use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Traits\ImagePathTraits;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\Admin\RecordingRequest;

class ProducerOperation
{
    use ImagePathTraits;
    private $producerId;
    private $base64ImageData;
    private $formDataImage;



    public function __construct($producerId, $base64ImageData = null, $formDataImage = null)
    {
        $this->producerId = $producerId;
        $this->base64ImageData = $base64ImageData;
        $this->formDataImage = $formDataImage;
    }

    public function StoreProducerImage()
    {
        $filePath = '/medias/img/producer/' . $this->producerId . '/' . Carbon::now()->format('Y');
        $path = Storage::disk('public_uploads')->put($filePath, $this->formDataImage);
        return $this->url . '/' . $path;
    }


    public function StoreContractImage()
    {
        $filePath = '/medias/img/contract/' . $this->producerId . '/' . Carbon::now()->format('Y');
        // return $this->formDataImage;
        $path = Storage::disk('public_uploads')->put($filePath, $this->formDataImage);
        return $this->url . '/' . $path;
    }

    public function StoreContractBase64Image()
    {
        $base64Image = new Base64ImageDecoder($this->base64ImageData);
        $file = $base64Image->decoder();
        $filePath = '/medias/img/contract/' . $this->producerId . '/' . Carbon::now()->format('Y') . '/' . Str::uuid() . '.'  . $file['type'];
        Storage::disk('public_uploads')->put($filePath, $file['data']);
        return $this->url . $filePath;
    }
}
