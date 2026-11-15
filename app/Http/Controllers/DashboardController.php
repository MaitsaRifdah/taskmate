<?php

namespace App\Http\Controllers;

use App\Models\Tugas;
use Illuminate\Contracts\View\View;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    /** Ambang "mendekati tenggat" (PRD Bab 1.6 dan BR-09). */
    private const BATAS_HARI = 3;

    public function index(): View
    {
        $userId = Auth::id();
        $sekarang = now();

        $ringkasan = [
            'total' => 0,
            'belum_dikerjakan' => 0,
            'sedang_dikerjakan' => 0,
            'selesai' => 0,
            'terlambat' => 0,
            'mendekati_jumlah' => 0,
        ];
        $mendekati = new Collection();
        $galat = false;

        try {
            // Semua query difilter user_id (BR-02, NFR-02).
            $perStatus = Tugas::where('user_id', $userId)
                ->selectRaw('status, count(*) as jumlah')
                ->groupBy('status')
                ->pluck('jumlah', 'status');

            $ringkasan['belum_dikerjakan'] = (int) ($perStatus['belum_dikerjakan'] ?? 0);
            $ringkasan['sedang_dikerjakan'] = (int) ($perStatus['sedang_dikerjakan'] ?? 0);
            $ringkasan['selesai'] = (int) ($perStatus['selesai'] ?? 0);

            // Total = jumlah ketiga status, sehingga selalu konsisten (BR-12).
            $ringkasan['total'] = $ringkasan['belum_dikerjakan']
                + $ringkasan['sedang_dikerjakan']
                + $ringkasan['selesai'];

            $belumSelesai = fn () => Tugas::where('user_id', $userId)
                ->where('status', '!=', 'selesai');

            // BR-08: belum selesai dan tenggat sudah lewat.
            $ringkasan['terlambat'] = $belumSelesai()
                ->where('tenggat', '<', $sekarang)
                ->count();

            // BR-09: belum selesai, tenggat belum lewat, paling lama 3 hari lagi.
            $mendekati = $belumSelesai()
                ->with('mataKuliah')
                ->whereBetween('tenggat', [$sekarang, $sekarang->copy()->addDays(self::BATAS_HARI)])
                ->orderBy('tenggat')
                ->get();

            $ringkasan['mendekati_jumlah'] = $mendekati->count();
        } catch (QueryException $e) {
            // NFR-06: kegagalan basis data ditangani dan pengguna diberi pesan jelas.
            Log::error('Dashboard gagal memuat data: '.$e->getMessage());
            $galat = true;
        }

        return view('dashboard', [
            'ringkasan' => $ringkasan,
            'mendekati' => $mendekati,
            'batasHari' => self::BATAS_HARI,
            'galat' => $galat,
        ]);
    }
}
