<?php

namespace App\Fileoperations;

use App\Traits\ImagePathTraits;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BandOperation
{
    use ImagePathTraits;
    private $bandId;
    private $base64ImageData;
    private $formDataImage;



    public function __construct($bandId, $base64ImageData = null, $formDataImage = null)
    {
        $this->bandId = $bandId;
        $this->base64ImageData = $base64ImageData;
        $this->formDataImage = $formDataImage;
    }

    public function StoreBandImage()
    {
        $filePath = '/medias/img/band/' . $this->bandId . '/' . Carbon::now()->format('Y');
        $path = Storage::disk('public_uploads')->put($filePath, $this->formDataImage);
        return $this->url . '/' . $path;
    }

    public function StoreMediaBase64Image()
    {
        $base64Image = new Base64ImageDecoder($this->base64ImageData);
        $file = $base64Image->decoder();
        $filePath = '/medias/img/contract/' . $this->bandId . '/' . Carbon::now()->format('Y') . '/' . Str::uuid() . '.'  . $file['type'];
        Storage::disk('public_uploads')->put($filePath, $file['data']);
        return $this->url . $filePath;
    }
}
