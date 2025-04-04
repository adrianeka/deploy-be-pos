<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StorePembelianRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // You can add authorization logic here
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'id_pemilik' => 'required|integer|exists:pemilik,id_pemilik',
            'id_pemasok' => 'required|integer|exists:pemasok,id_pemasok',
            'tanggal_pembelian' => 'required|date',
            'total_harga' => 'required|numeric|min:0',
            'status_pembelian' => 'required|string|in:diproses,selesai,dibatalkan',
            'produk' => 'required|array|min:1',
            'produk.*.id_produk' => 'required|integer|exists:produk,id_produk',
            'produk.*.jumlah_produk' => 'required|integer|min:1',
        ];
    }

    /**
     * Custom messages for validation
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'id_pemilik.required' => 'ID pemilik harus diisi',
            'id_pemilik.exists' => 'ID pemilik tidak valid',
            'id_pemasok.required' => 'ID pemasok harus diisi',
            'id_pemasok.exists' => 'ID pemasok tidak valid',
            'produk.required' => 'Data produk harus diisi',
            'produk.min' => 'Minimal harus ada 1 produk',
            'produk.*.id_produk.required' => 'ID produk harus diisi',
            'produk.*.id_produk.exists' => 'ID produk tidak valid',
            'produk.*.jumlah_produk.required' => 'Jumlah produk harus diisi',
            'produk.*.jumlah_produk.min' => 'Jumlah produk minimal 1',
        ];
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     *
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'status' => 'error',
            'message' => 'Validasi gagal',
            'errors' => $validator->errors()
        ], 422));
    }
}
