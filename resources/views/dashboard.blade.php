@extends('layouts.app')

@section('content')
@php
    $pengguna = auth()->user();
    $namaPengguna = $pengguna->name ?: $pengguna->email;

    // Tautan aman: bila route modul lain belum ada, tombol tidak membuat error.
    $urlTambah = Route::has('tugas.create') ? route('tugas.create') : '#';

    $labelPrioritas = [
        'rendah' => ['Rendah', 'bg-secondary'],
        'sedang' => ['Sedang', 'bg-warning text-dark'],
        'tinggi' => ['Tinggi', 'bg-danger'],
    ];
    $labelStatus = [
        'belum_dikerjakan' => 'Belum Dikerjakan',
        'sedang_dikerjakan' => 'Sedang Dikerjakan',
        'selesai' => 'Selesai',
    ];
@endphp

<div class="container py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h1 class="h3 mb-1">Halo, {{ $namaPengguna }}</h1>
            <p class="text-muted mb-0">Ringkasan tugas kuliahmu hari ini.</p>
        </div>
        <a href="{{ $urlTambah }}" class="btn btn-primary">Tambah tugas</a>
    </div>

    @if ($galat)
        <div class="alert alert-danger" role="alert">
            Data dashboard belum bisa dimuat karena koneksi ke basis data bermasalah.
            Periksa koneksi, lalu muat ulang halaman ini.
        </div>
    @endif

    {{-- Kartu ringkasan: total dan jumlah per status --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card h-100 border-primary">
                <div class="card-body">
                    <div class="text-muted small">Total tugas</div>
                    <div class="display-6 fw-semibold">{{ $ringkasan['total'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted small">Belum Dikerjakan</div>
                    <div class="display-6 fw-semibold">{{ $ringkasan['belum_dikerjakan'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted small">Sedang Dikerjakan</div>
                    <div class="display-6 fw-semibold">{{ $ringkasan['sedang_dikerjakan'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted small">Selesai</div>
                    <div class="display-6 fw-semibold">{{ $ringkasan['selesai'] }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tugas terlambat: ditandai dengan teks, bukan hanya warna --}}
    @if ($ringkasan['terlambat'] > 0)
        <div class="alert alert-danger d-flex align-items-center" role="alert">
            <strong class="me-1">Terlambat:</strong>
            {{ $ringkasan['terlambat'] }} tugas belum selesai dan tenggatnya sudah lewat.
        </div>
    @else
        <p class="text-muted">Terlambat: 0. Tidak ada tugas yang melewati tenggat.</p>
    @endif

    {{-- Daftar mendekati tenggat --}}
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span class="fw-semibold">Mendekati tenggat ({{ $batasHari }} hari ke depan)</span>
            <span class="badge bg-dark">{{ $ringkasan['mendekati_jumlah'] }}</span>
        </div>

        @forelse ($mendekati as $tugas)
            @php
                $kodePrioritas = $tugas->prioritas->value ?? $tugas->prioritas;
                $kodeStatus = $tugas->status->value ?? $tugas->status;
                [$teksPrioritas, $kelasPrioritas] = $labelPrioritas[$kodePrioritas] ?? [ucfirst($kodePrioritas), 'bg-secondary'];
                $urlDetail = Route::has('tugas.show') ? route('tugas.show', $tugas) : '#';
            @endphp
            @if ($loop->first)
                <ul class="list-group list-group-flush">
            @endif
                <li class="list-group-item">
                    <div class="d-flex flex-wrap justify-content-between gap-2">
                        <div>
                            <a href="{{ $urlDetail }}" class="fw-semibold text-decoration-none">{{ $tugas->judul }}</a>
                            <div class="text-muted small">{{ $tugas->mataKuliah->nama ?? '-' }}</div>
                        </div>
                        <div class="text-md-end">
                            <div>{{ $tugas->tenggat->translatedFormat('d M Y, H:i') }}</div>
                            <div class="text-muted small">{{ $tugas->tenggat->diffForHumans() }}</div>
                        </div>
                    </div>
                    <div class="mt-2">
                        <span class="badge {{ $kelasPrioritas }}">Prioritas {{ $teksPrioritas }}</span>
                        <span class="badge text-bg-light border">{{ $labelStatus[$kodeStatus] ?? $kodeStatus }}</span>
                    </div>
                </li>
            @if ($loop->last)
                </ul>
            @endif
        @empty
            <div class="card-body text-center py-5">
                @if ($ringkasan['total'] === 0)
                    <p class="mb-3">Belum ada tugas. Tambahkan tugas pertamamu untuk mulai memantau tenggat.</p>
                    <a href="{{ $urlTambah }}" class="btn btn-primary">Tambah tugas</a>
                @else
                    <p class="mb-0 text-muted">Tidak ada tugas yang jatuh tempo dalam {{ $batasHari }} hari ke depan.</p>
                @endif
            </div>
        @endforelse
    </div>
</div>
@endsection
