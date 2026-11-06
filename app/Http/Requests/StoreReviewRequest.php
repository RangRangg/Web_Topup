<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['required', 'string', 'min:5', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'rating.required' => 'Pilih bintang penilaian 1 hingga 5.',
            'rating.integer' => 'Rating harus berupa angka bulat 1 sampai 5.',
            'comment.required' => 'Tuliskan ulasan / review pengalaman Anda.',
            'comment.min' => 'Ulasan minimal 5 karakter.',
            'comment.max' => 'Ulasan maksimal 1000 karakter.',
        ];
    }
}
