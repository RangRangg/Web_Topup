<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'product_id' => ['required', 'integer', 'exists:game_products,id'],
            'account_id' => ['required', 'string', 'max:50'],
            'account_server' => ['nullable', 'string', 'max:50'],
            'account_nickname' => ['nullable', 'string', 'max:50'],
            'quantity' => ['required', 'integer', 'min:1', 'max:100'],
            'payment_method' => ['required', 'string', 'in:qris,bca,mandiri,briva,bni,dana,gopay,ovo,shopeepay,alfamart,indomaret'],
            'customer_name' => ['required', 'string', 'max:100'],
            'customer_email' => ['required', 'email', 'max:100'],
            'customer_whatsapp' => ['required', 'string', 'max:20'],
            'promo_code' => ['nullable', 'string', 'max:30'],
            'idempotency_key' => ['nullable', 'string', 'max:64'],
        ];
    }

    /**
     * Get custom error messages for validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'product_id.required' => 'Silakan pilih nominal produk top up yang ingin dibeli.',
            'product_id.exists' => 'Produk top up yang dipilih tidak valid atau sudah tidak tersedia.',
            'account_id.required' => 'User ID game wajib diisi.',
            'quantity.min' => 'Jumlah pembelian minimal 1 item.',
            'payment_method.required' => 'Silakan pilih metode pembayaran.',
            'payment_method.in' => 'Metode pembayaran tidak valid.',
            'customer_name.required' => 'Nama lengkap pemesan wajib diisi.',
            'customer_email.required' => 'Alamat email wajib diisi untuk pengiriman bukti pembayaran.',
            'customer_whatsapp.required' => 'Nomor WhatsApp aktif wajib diisi untuk konfirmasi instan.',
        ];
    }
}
