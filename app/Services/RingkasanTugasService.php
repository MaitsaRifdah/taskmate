<?php

namespace App\Services;

use App\Models\Tugas;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Menghitung ringkasan dashboard milik satu pengguna (BR-08, BR-09, BR-11, BR-12).
 */
class RingkasanTugasService
{
    /** Ambang "mendekati tenggat" (PRD Bab 1.6 dan BR-09). */
    private const BATAS_HARI = 3;

    /**
     * Konstruktor: menerima id pengguna saat objek dibuat dan
     * langsung menyimpannya sebagai property private (constructor promotion).
     */
    public function __construct(private int $userId)
    {
    }

    /**
     * Method public: angka ringkasan untuk kartu dashboard.
     */
    public function ringkasan(): array
    {
        $perStatus = $this->hitungPerStatus();

        $belum = (int) ($perStatus['belum_dikerjakan'] ?? 0);
        $sedang = (int) ($perStatus['sedang_dikerjakan'] ?? 0);
        $selesai = (int) ($perStatus['selesai'] ?? 0);

        return [
            // Total = jumlah ketiga status, sehingga selalu konsisten (BR-12).
            'total' => $belum + $sedang + $selesai,
            'belum_dikerjakan' => $belum,
            'sedang_dikerjakan' => $sedang,
            'selesai' => $selesai,
            'terlambat' => $this->hitungTerlambat(),
        ];
    }

    /**
     * Overloading versi PHP: parameter $hari boleh diisi atau dikosongkan.
     *   daftarMendekati()    -> memakai batas bawaan (3 hari)
     *   daftarMendekati(7)   -> memakai 7 hari
     *
     * BR-09: belum selesai, tenggat belum lewat, dan paling lama $hari dari sekarang.
     */
    public function daftarMendekati(?int $hari = null): Collection
    {
        $hari ??= self::BATAS_HARI;
        $sekarang = now();

        return $this->belumSelesai()
            ->with('mataKuliah')
            ->whereBetween('tenggat', [$sekarang, $sekarang->copy()->addDays($hari)])
            ->orderBy('tenggat')
            ->get();
    }

    /**
     * Getter: nilai konstanta private dibuka lewat method, bukan diakses langsung.
     */
    public function batasHari(): int
    {
        return self::BATAS_HARI;
    }

    // ---- Method private: hanya dipakai di dalam class ini (enkapsulasi) ----

    /** Semua query difilter user_id (BR-02, NFR-02). */
    private function tugasMilikku(): Builder
    {
        return Tugas::where('user_id', $this->userId);
    }

    private function belumSelesai(): Builder
    {
        return $this->tugasMilikku()->where('status', '!=', 'selesai');
    }

    private function hitungPerStatus(): Collection
    {
        return $this->tugasMilikku()
            ->selectRaw('status, count(*) as jumlah')
            ->groupBy('status')
            ->pluck('jumlah', 'status');
    }

    /** BR-08: belum selesai dan tenggat sudah lewat. */
    private function hitungTerlambat(): int
    {
        return $this->belumSelesai()
            ->where('tenggat', '<', now())
            ->count();
    }
}