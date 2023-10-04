<?php

namespace App\Fileoperations;

use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Traits\ImagePathTraits;
use Illuminate\Support\Facades\Storage;

class VendorUserOperation
{
    use ImagePathTraits;
    private $userID;
    private $base64ImageData;
    private $formDataImage;



    public function __construct($userID, $base64ImageData = null, $formDataImage = null)
    {
        $this->userID = $userID;
        $this->base64ImageData = $base64ImageData;
        $this->formDataImage = $formDataImage;
    }

    public function StoreUserImage()
    {
        $filePath = '/medias/img/users/' . $this->userID . '/' . Carbon::now()->format('Y');
        $path = Storage::disk('public_uploads')->put($filePath, $this->formDataImage);
        return $this->url . '/' . $path;
    }


    public function StoreUserBase64Image()
    {
        $base64Image = new Base64ImageDecoder($this->base64ImageData);
        $file = $base64Image->decoder();
        $filePath = '/medias/img/users/' . $this->userID . '/' . Carbon::now()->format('Y') . '/' . Str::uuid() . '.'  . $file['type'];
        Storage::disk('public_uploads')->put($filePath, $file['data']);
        return $this->url . $filePath;
    }
}
