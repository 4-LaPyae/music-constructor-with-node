<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class SongRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'title' => 'required',
            'singers' => 'required',
            'album' => 'required',
            'artists' => 'nullable',
            'band' => ' nullable',
            'recording' => 'nullable',
            'media' => 'nullable',
            'generes' => 'nullable',
            'music_lists' => 'nullable',
            'producer' => 'nullable',
            'amount' => 'nullable',
            // 'mr_file' => 'required',
            'start' => 'string|nullable',
            'end' => 'string|nullable'
        ];
    }

    public function messages()
    {
        return [
            'title.required' => 'title is required',
            'singers.required' => 'singer is required',
            'album.required' => 'album is required',
            'start.string' => 'start must bu string',
            'end.string' => 'end must be string',
            // 'mr_file.required' => 'mr file reqired'
            // 'recording.required' => 'recording is required',

        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'error' => true,
            'message' => 'Validation errors',
            'data' => $validator->errors()
        ]));
    }
}
