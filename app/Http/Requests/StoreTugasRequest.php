<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

/**
 * Validasi formulir tambah tugas (FR-11).
 * Class ini mewarisi FormRequest dan meng-override beberapa method-nya.
 */
class StoreTugasRequest extends FormRequest
{
    /** Hanya pengguna yang sudah login yang boleh menambah tugas. */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /** Merapikan input sebelum divalidasi (spasi di awal, akhir, dan berlebih). */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'judul' => trim((string) $this->input('judul')),
            'mata_kuliah' => preg_replace('/\s+/', ' ', trim((string) $this->input('mata_kuliah'))),
        ]);
    }

    public function rules(): array
    {
        return [
            'judul' => ['required', 'string', 'max:200'],
            'mata_kuliah' => ['required', 'string', 'max:150'],
            'deskripsi' => ['nullable', 'string', 'max:5000'],
            // BR-07: tenggat tugas baru tidak boleh sebelum hari ini.
            'tenggat' => ['required', 'date', 'after_or_equal:today'],
            // BR-04: prioritas hanya Rendah, Sedang, atau Tinggi.
            'prioritas' => ['required', 'in:rendah,sedang,tinggi'],
        ];
    }

    public function messages(): array
    {
        return [
            'judul.required' => 'Judul tugas wajib diisi.',
            'judul.max' => 'Judul tugas maksimal 200 karakter.',
            'mata_kuliah.required' => 'Mata kuliah wajib diisi.',
            'mata_kuliah.max' => 'Nama mata kuliah maksimal 150 karakter.',
            'tenggat.required' => 'Tenggat wajib diisi.',
            'tenggat.date' => 'Format tenggat tidak valid.',
            'tenggat.after_or_equal' => 'Tenggat tidak boleh sebelum hari ini.',
            'prioritas.required' => 'Prioritas wajib dipilih.',
            'prioritas.in' => 'Pilih prioritas Rendah, Sedang, atau Tinggi.',
        ];
    }

    public function attributes(): array
    {
        return [
            'judul' => 'judul',
            'mata_kuliah' => 'mata kuliah',
            'deskripsi' => 'deskripsi',
            'tenggat' => 'tenggat',
            'prioritas' => 'prioritas',
        ];
    }
}