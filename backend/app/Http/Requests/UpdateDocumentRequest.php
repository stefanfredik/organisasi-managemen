<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'file' => ['nullable', 'file', 'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,txt,zip,rar', 'max:10240'], // 10MB
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama dokumen wajib diisi.',
            'file.mimes' => 'Format file tidak didukung. Gunakan: PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, TXT, ZIP, RAR.',
            'file.max' => 'Ukuran file maksimal 10MB.',
        ];
    }
}
