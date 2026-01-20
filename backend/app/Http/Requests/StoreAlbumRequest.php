<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAlbumRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|in:event,routine,official,other',
            'event_id' => 'nullable|exists:events,id',
            'status' => 'required|in:public,private',
            'cover_image' => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
        ];
    }

    /**
     * Get custom attribute names for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'nama album',
            'description' => 'deskripsi',
            'category' => 'kategori',
            'event_id' => 'kegiatan',
            'status' => 'status',
            'cover_image' => 'gambar cover',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama album harus diisi.',
            'category.required' => 'Kategori harus dipilih.',
            'category.in' => 'Kategori tidak valid.',
            'status.required' => 'Status harus dipilih.',
            'status.in' => 'Status tidak valid.',
            'event_id.exists' => 'Kegiatan tidak ditemukan.',
            'cover_image.image' => 'File harus berupa gambar.',
            'cover_image.mimes' => 'Gambar harus berformat jpg, jpeg, atau png.',
            'cover_image.max' => 'Ukuran gambar maksimal 5MB.',
        ];
    }
}
