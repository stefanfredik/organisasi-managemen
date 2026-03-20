<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePositionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->role === 'admin';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required', 
                'string', 
                'max:50', 
                'alpha_dash', 
                Rule::unique('positions', 'code')->ignore($this->route('position'))
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'nama posisi',
            'code' => 'kode posisi',
        ];
    }
}
