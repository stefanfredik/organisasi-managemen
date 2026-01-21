<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAnnouncementRequest extends FormRequest
{
    public function authorize(): bool
    {
        $announcement = $this->route('announcement');
        return $this->user()?->can('update', $announcement) ?? false;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
            'publish_date' => ['nullable', 'date'],
            'status' => ['required', 'in:draft,published'],
            'target_audience' => ['required', 'in:all,roles'],
            'target_roles' => ['nullable', 'array'],
            'target_roles.*' => ['in:admin,ketua,bendahara,sekretaris,anggota'],
        ];
    }
}

