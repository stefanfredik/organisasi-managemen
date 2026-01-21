<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMeetingMinuteRequest extends FormRequest
{
    public function authorize(): bool
    {
        $meetingMinute = $this->route('meeting_minute');
        return $this->user()?->can('update', $meetingMinute) ?? false;
    }

    public function rules(): array
    {
        return [
            'meeting_date' => ['required', 'date'],
            'agenda' => ['required', 'string', 'max:255'],
            'participants' => ['nullable', 'array'],
            'participants.*' => ['integer', 'exists:members,id'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
