<?php
namespace App\Fileoperations;

use App\Traits\ImagePathTraits;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;


class DistributorOperation {
    private $distributorId;
    private $base64ImageData;
    private $formDataImage;
    use ImagePathTraits;

    public function __construct($distributorId,$base64ImageData = null,$formDataImage = null)
    {
        $this->distributorId = $distributorId;
        $this->base64ImageData = $base64ImageData;
        $this->formDataImage = $formDataImage;
    }

    public function storeDistributorImage(){
        $filePath = '/medias/img/producer/'.$this->distributorId. '/'.Carbon::now()->format('Y');
        $path = Storage::disk('public_uploads')->put($filePath, $this->formDataImage);
        return $this->url. '/' . $path;
    }


}