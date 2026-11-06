<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class JokiCheckoutRequest extends FormRequest
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
            'service_id' => ['required', 'integer', 'exists:joki_services,id'],
            'account_id' => ['required', 'string', 'max:50'],
            'account_server' => ['nullable', 'string', 'max:50'],
            'account_nickname' => ['nullable', 'string', 'max:50'],
            'login_via' => ['required', 'string', 'max:50'],
            'account_notes' => ['nullable', 'string', 'max:1000'],
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
            'service_id.required' => 'Silakan pilih layanan joki yang ingin dipesan.',
            'service_id.exists' => 'Layanan joki tidak valid atau sudah tidak aktif.',
            'account_id.required' => 'ID Akun game / Nickname wajib diisi untuk pengerjaan joki.',
            'login_via.required' => 'Metode login akun wajib dipilih (misal: Moonton, Riot, Google, atau Mabar).',
            'payment_method.required' => 'Silakan pilih metode pembayaran.',
            'payment_method.in' => 'Metode pembayaran tidak valid.',
            'customer_name.required' => 'Nama lengkap pemesan wajib diisi.',
            'customer_email.required' => 'Alamat email wajib diisi untuk penerimaan invoice dan bukti transaksi.',
            'customer_whatsapp.required' => 'Nomor WhatsApp aktif wajib diisi untuk koordinasi dan konfirmasi pengerjaan booster.',
        ];
    }
}
