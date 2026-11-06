<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadProofRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'payment_proof' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'payment_proof.required' => 'File bukti transfer wajib diunggah.',
            'payment_proof.image' => 'File harus berupa gambar.',
            'payment_proof.mimes' => 'Format file yang didukung: JPEG, PNG, JPG, WEBP.',
            'payment_proof.max' => 'Ukuran file bukti transfer maksimal 5 MB.',
        ];
    }
}
