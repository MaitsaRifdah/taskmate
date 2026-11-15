<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTugasRequest;
use App\Models\MataKuliah;
use App\Models\Tugas;
use Illuminate\Contracts\View\View;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Modul 3: daftar tugas, formulir tambah tugas, dan penyimpanan.
 * Method show, edit, update, destroy, dan ubah status ditambahkan Modul 4.
 */
class TugasController extends Controller
{
    /** FR-12: daftar tugas milik sendiri, diurutkan dari tenggat terdekat. */
    public function index(Request $request): View
    {
        $cari = trim((string) $request->query('cari', ''));
        $daftarTugas = collect();
        $galat = false;

        try {
            $daftarTugas = Tugas::where('user_id', Auth::id())
                ->with('mataKuliah')
                // FR-21 (Should): cari berdasarkan judul.
                ->when($cari !== '', function ($query) use ($cari) {
                    $query->whereRaw('lower(judul) like ?', ['%'.mb_strtolower($cari).'%']);
                })
                ->orderBy('tenggat')
                ->get();
        } catch (QueryException $e) {
            Log::error('Daftar tugas gagal dimuat: '.$e->getMessage());
            $galat = true;
        }

        return view('tugas.index', [
            'daftarTugas' => $daftarTugas,
            'cari' => $cari,
            'galat' => $galat,
        ]);
    }

    /** FR-10: tampilkan formulir tambah tugas. */
    public function create(): View
    {
        $namaMataKuliah = collect();

        try {
            // Dipakai sebagai saran pada kolom mata kuliah.
            $namaMataKuliah = MataKuliah::where('user_id', Auth::id())
                ->orderBy('nama')
                ->pluck('nama');
        } catch (QueryException $e) {
            Log::error('Saran mata kuliah gagal dimuat: '.$e->getMessage());
        }

        return view('tugas.create', ['namaMataKuliah' => $namaMataKuliah]);
    }

    /** FR-10, FR-19: simpan tugas baru dengan user_id pemilik dan status awal. */
    public function store(StoreTugasRequest $request): RedirectResponse
    {
        // Validasi sudah dijalankan StoreTugasRequest (FR-11).
        $data = $request->validated();
        $userId = Auth::id();

        try {
            DB::transaction(function () use ($data, $userId) {
                // Mata kuliah diketik pengguna. Bila belum ada, dibuat otomatis.
                $mataKuliah = MataKuliah::where('user_id', $userId)
                    ->whereRaw('lower(nama) = ?', [mb_strtolower($data['mata_kuliah'])])
                    ->first();

                if ($mataKuliah === null) {
                    $mataKuliah = new MataKuliah();
                    $mataKuliah->user_id = $userId;
                    $mataKuliah->nama = $data['mata_kuliah'];
                    $mataKuliah->save();
                }

                $tugas = new Tugas();
                $tugas->user_id = $userId;
                $tugas->mata_kuliah_id = $mataKuliah->id;
                $tugas->judul = $data['judul'];
                $tugas->deskripsi = $data['deskripsi'] ?? null;
                $tugas->tenggat = $data['tenggat'];
                $tugas->prioritas = $data['prioritas'];
                $tugas->status = 'belum_dikerjakan'; // BR-05
                $tugas->save();
            });
        } catch (QueryException $e) {
            // NFR-06: kegagalan basis data ditangani dengan pesan yang jelas.
            Log::error('Tugas gagal disimpan: '.$e->getMessage());

            return back()
                ->withInput()
                ->with('galat', 'Tugas belum bisa disimpan karena koneksi basis data bermasalah. Coba lagi sebentar lagi.');
        }

        return redirect()
            ->route('tugas.index')
            ->with('sukses', 'Tugas berhasil ditambahkan.');
    }
}
