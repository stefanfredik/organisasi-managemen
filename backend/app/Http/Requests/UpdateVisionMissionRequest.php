<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVisionMissionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled by policy
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'vision' => 'required|string',
            'missions' => 'required|array|min:1',
            'missions.*' => 'required|string|max:500',
            'period_start' => 'nullable|integer|min:1900|max:2100',
            'period_end' => 'nullable|integer|min:1900|max:2100|gte:period_start',
            'status' => 'required|in:active,inactive',
        ];
    }

    /**
     * Get custom attribute names for validator errors.
     */
    public function attributes(): array
    {
        return [
            'vision' => 'visi',
            'missions' => 'misi',
            'missions.*' => 'misi',
            'period_start' => 'tahun mulai',
            'period_end' => 'tahun selesai',
            'status' => 'status',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'vision.required' => 'Visi harus diisi.',
            'missions.required' => 'Minimal harus ada 1 misi.',
            'missions.array' => 'Format misi tidak valid.',
            'missions.min' => 'Minimal harus ada 1 misi.',
            'missions.*.required' => 'Misi tidak boleh kosong.',
            'missions.*.max' => 'Misi maksimal 500 karakter.',
            'period_start.integer' => 'Tahun mulai harus berupa angka.',
            'period_start.min' => 'Tahun mulai minimal 1900.',
            'period_start.max' => 'Tahun mulai maksimal 2100.',
            'period_end.integer' => 'Tahun selesai harus berupa angka.',
            'period_end.min' => 'Tahun selesai minimal 1900.',
            'period_end.max' => 'Tahun selesai maksimal 2100.',
            'period_end.gte' => 'Tahun selesai harus lebih besar atau sama dengan tahun mulai.',
            'status.required' => 'Status harus dipilih.',
            'status.in' => 'Status tidak valid.',
        ];
    }
}
