<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMemberRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Member::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', 'unique:members,email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'join_date' => ['required', 'date'],
            'status' => ['required', 'in:active,inactive'],
            'photo' => ['nullable', 'image', 'max:2048'], // 2MB max
            'notes' => ['nullable', 'string'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'full_name' => 'nama lengkap',
            'email' => 'email',
            'phone' => 'nomor telepon',
            'address' => 'alamat',
            'date_of_birth' => 'tanggal lahir',
            'join_date' => 'tanggal bergabung',
            'status' => 'status',
            'photo' => 'foto',
            'notes' => 'catatan',
        ];
    }
}
