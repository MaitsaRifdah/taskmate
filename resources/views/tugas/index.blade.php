@extends('layouts.app')

@section('content')
@php
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
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h1 class="h3 mb-0">Daftar tugas</h1>
        <a href="{{ route('tugas.create') }}" class="btn btn-primary">Tambah tugas</a>
    </div>

    @if (session('sukses'))
        <div class="alert alert-success" role="alert">{{ session('sukses') }}</div>
    @endif

    @if ($galat)
        <div class="alert alert-danger" role="alert">
            Daftar tugas belum bisa dimuat karena koneksi ke basis data bermasalah.
            Periksa koneksi, lalu muat ulang halaman ini.
        </div>
    @endif

    {{-- FR-21: cari berdasarkan judul --}}
    <form method="GET" action="{{ route('tugas.index') }}" class="row g-2 mb-3">
        <div class="col-sm-8 col-md-6">
            <label for="cari" class="visually-hidden">Cari judul tugas</label>
            <input type="search" id="cari" name="cari" class="form-control"
                placeholder="Cari judul tugas" value="{{ $cari }}">
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-outline-secondary">Cari</button>
            @if ($cari !== '')
                <a href="{{ route('tugas.index') }}" class="btn btn-link">Hapus pencarian</a>
            @endif
        </div>
    </form>

    @if ($daftarTugas->isEmpty())
        <div class="card">
            <div class="card-body text-center py-5">
                @if ($cari !== '')
                    <p class="mb-0 text-muted">Tidak ada tugas dengan judul "{{ $cari }}". Coba kata kunci lain.</p>
                @else
                    <p class="mb-3">Belum ada tugas. Tambahkan tugas pertamamu untuk mulai memantau tenggat.</p>
                    <a href="{{ route('tugas.create') }}" class="btn btn-primary">Tambah tugas</a>
                @endif
            </div>
        </div>
    @else
        {{-- FR-12: judul, mata kuliah, tenggat, prioritas, dan status --}}
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th scope="col">Judul</th>
                        <th scope="col">Mata kuliah</th>
                        <th scope="col">Tenggat</th>
                        <th scope="col">Prioritas</th>
                        <th scope="col">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($daftarTugas as $tugas)
                        @php
                            $kodePrioritas = $tugas->prioritas->value ?? $tugas->prioritas;
                            $kodeStatus = $tugas->status->value ?? $tugas->status;
                            [$teksPrioritas, $kelasPrioritas] = $labelPrioritas[$kodePrioritas] ?? [ucfirst($kodePrioritas), 'bg-secondary'];
                            // Tautan detail dibuat Modul 4. Selama belum ada, judul tampil sebagai teks biasa.
                            $urlDetail = Route::has('tugas.show') ? route('tugas.show', $tugas) : null;
                        @endphp
                        <tr>
                            <td>
                                @if ($urlDetail)
                                    <a href="{{ $urlDetail }}" class="fw-semibold text-decoration-none">{{ $tugas->judul }}</a>
                                @else
                                    <span class="fw-semibold">{{ $tugas->judul }}</span>
                                @endif
                            </td>
                            <td>{{ $tugas->mataKuliah->nama ?? '-' }}</td>
                            <td class="text-nowrap">{{ $tugas->tenggat->translatedFormat('d M Y, H:i') }}</td>
                            <td><span class="badge {{ $kelasPrioritas }}">{{ $teksPrioritas }}</span></td>
                            <td><span class="badge text-bg-light border">{{ $labelStatus[$kodeStatus] ?? $kodeStatus }}</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
