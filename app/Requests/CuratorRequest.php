<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CuratorRequest extends FormRequest
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
            'external_user_id' => 'required',
            'email' => 'required',
            'first_name' => 'required',
            'last_name' => 'required'
        ];
    }
}
