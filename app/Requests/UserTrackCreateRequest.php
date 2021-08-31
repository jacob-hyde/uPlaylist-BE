<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserTrackCreateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|string',
            'url' => 'required|string|regex:/^(https:\/\/open\.spotify\.com\/)(.+)$/i',
            'genre_id' => 'required|exists:genres,id',
        ];
    }
}
