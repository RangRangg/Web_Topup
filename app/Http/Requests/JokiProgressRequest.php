<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class JokiProgressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'order_status' => ['required', 'in:processing,completed'],
            'progress_note' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'order_status.required' => 'Status pengerjaan wajib dipilih.',
            'order_status.in' => 'Status hanya boleh PROCESSING atau COMPLETED.',
            'progress_note.max' => 'Catatan progres maksimal 1000 karakter.',
        ];
    }
}
