<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMeetingMinuteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', \App\Models\MeetingMinute::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'meeting_date' => ['required', 'date'],
            'agenda' => ['required', 'string', 'max:255'],
            'participants' => ['nullable', 'array'],
            'participants.*' => ['integer', 'exists:members,id'],
            'notes' => ['nullable', 'string'],
            'files' => ['nullable', 'array'],
            'files.*' => ['file', 'mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,gif', 'max:10240'], // 10MB
        ];
    }
}
