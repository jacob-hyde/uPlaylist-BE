<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CuratorOrderCreateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth('api')->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'user_track_uuid' => 'required|exists:user_tracks,uuid',
            'playlists' => 'required|array',
            'playlists.*' => 'exists:curator_playlists,id',
        ];
    }
}
