<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMemberRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $member = $this->route('member');
        return $this->user()->can('update', $member);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $memberId = $this->route('member')->id;

        return [
            'nik' => ['nullable', 'string', 'max:32', 'unique:members,nik,' . $memberId],
            'full_name' => ['required', 'string', 'max:255'],
            'nickname' => ['nullable', 'string', 'max:255'],
            'gender' => ['nullable', 'in:male,female'],
            'religion' => ['nullable', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:255', 'unique:members,email,' . $memberId],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string'],
            'domicile_address' => ['nullable', 'string'],
            'living_status' => ['nullable', 'in:kos,rumah'],
            'marital_status' => ['nullable', 'string', 'max:50'],
            'occupation' => ['nullable', 'string', 'max:100'],
            'arrival_date_bali' => ['nullable', 'date'],
            'origin_hamlet' => ['nullable', 'string', 'max:100'],
            'origin_village' => ['nullable', 'string', 'max:100'],
            'origin_subdistrict' => ['nullable', 'string', 'max:100'],
            'origin_regency' => ['nullable', 'string', 'max:100'],
            'origin_province' => ['nullable', 'string', 'max:100'],
            'bpjs_health_active' => ['boolean'],
            'bpjs_employment_active' => ['boolean'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'join_date' => ['required', 'date'],
            'status' => ['required', 'in:active,inactive'],
            'photo' => ['nullable', 'image', 'max:2048'],
            'ktp_photo' => ['nullable', 'image', 'max:4096'],
            'notes' => ['nullable', 'string'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'nik' => 'nik',
            'full_name' => 'nama lengkap',
            'nickname' => 'nama panggilan',
            'gender' => 'jenis kelamin',
            'religion' => 'agama',
            'email' => 'email',
            'phone' => 'nomor telepon',
            'address' => 'alamat',
            'domicile_address' => 'alamat domisili',
            'living_status' => 'status tempat tinggal',
            'marital_status' => 'status pernikahan',
            'occupation' => 'pekerjaan',
            'arrival_date_bali' => 'waktu tiba di bali',
            'origin_hamlet' => 'kampung asal',
            'origin_village' => 'desa',
            'origin_subdistrict' => 'kecamatan',
            'origin_regency' => 'kabupaten',
            'origin_province' => 'provinsi',
            'bpjs_health_active' => 'status bpjs kesehatan',
            'bpjs_employment_active' => 'status bpjs tenaga kerja',
            'date_of_birth' => 'tanggal lahir',
            'join_date' => 'tanggal bergabung',
            'status' => 'status',
            'photo' => 'foto',
            'ktp_photo' => 'foto KTP',
            'notes' => 'catatan',
        ];
    }
}
