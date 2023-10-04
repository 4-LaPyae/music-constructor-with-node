<?php

namespace App\Fileoperations;

use App\Models\Media;
use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Traits\ImagePathTraits;
use Illuminate\Support\Facades\Storage;
use App\Models\Song;
use App\Models\Singer;

class SongOperation
{
    use ImagePathTraits;
    private $songId;
    private $base64ImageData;
    private $formDataImage;



    public function __construct($songId, $base64ImageData = null, $formDataImage = null)
    {
        $this->songId = $songId;
        $this->base64ImageData = $base64ImageData;
        $this->formDataImage = $formDataImage;
    }

    public function StoreSongImage()
    {
        $filePath = '/medias/img/songs/' . $this->songId . '/' . Carbon::now()->format('Y');
        $path = Storage::disk('public_uploads')->put($filePath, $this->formDataImage);
        return $this->url . '/' . $path;
    }

    public function StoreSongBase64Image()
    {
        $base64Image = new Base64ImageDecoder($this->base64ImageData);
        $file = $base64Image->decoder();
        $filePath = '/medias/img/songs/' . $this->songId . '/' . Carbon::now()->format('Y') . '/' . Str::uuid() . '.'  . $file['type'];
        Storage::disk('public_uploads')->put($filePath, $file['data']);
        return $this->url . $filePath;
    }

    public function exportSingerDetail($songId)
    {
        $song = Song::where('id', (int) $songId)->first();

        if ($song && $song->singer_id) {

            $singerArr = explode(',', $song->singer_id);

            $singer = array_map(function ($element) {
                return (int)$element;
            }, $singerArr);

            return Singer::whereIn('id', $singer)->select('singer_key', 'name')->get();
        }
    }

    public function exportContractDetail($songId)
    {
        // $song = Song::find($songId);
        $song = Song::where('id', (int)$songId)->first();

        if ($song && $song->contract_id) {

            $contractArr = explode(',', $song->contract_id);

            $contract = array_map(function ($element) {
                return (int)$element;
            }, $contractArr);

            return Media::whereIn('id', $contract)->select('media_link')->get();
        }
    }
}
