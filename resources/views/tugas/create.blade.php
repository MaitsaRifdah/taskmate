@extends('layouts.app')

@section('content')
<div class="container py-4" style="max-width: 720px;">
    <h1 class="h3 mb-1">Tambah tugas</h1>
    <p class="text-muted mb-4">Isi data tugas kuliahmu. Kolom bertanda * wajib diisi.</p>

    @if (session('galat'))
        <div class="alert alert-danger" role="alert">{{ session('galat') }}</div>
    @endif

    <form method="POST" action="{{ route('tugas.store') }}" novalidate>
        @csrf

        <div class="mb-3">
            <label for="judul" class="form-label">Judul tugas *</label>
            <input type="text" id="judul" name="judul" maxlength="200"
                class="form-control @error('judul') is-invalid @enderror"
                value="{{ old('judul') }}" required>
            @error('judul')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="mata_kuliah" class="form-label">Mata kuliah *</label>
            <input type="text" id="mata_kuliah" name="mata_kuliah" maxlength="150"
                list="saran-mata-kuliah" autocomplete="off"
                class="form-control @error('mata_kuliah') is-invalid @enderror"
                value="{{ old('mata_kuliah') }}" required>
            <datalist id="saran-mata-kuliah">
                @foreach ($namaMataKuliah as $nama)
                    <option value="{{ $nama }}"></option>
                @endforeach
            </datalist>
            @error('mata_kuliah')
                <div class="invalid-feedback">{{ $message }}</div>
            @else
                <div class="form-text">Ketik nama mata kuliah. Kalau belum ada, otomatis dibuat.</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="deskripsi" class="form-label">Deskripsi</label>
            <textarea id="deskripsi" name="deskripsi" rows="4" maxlength="5000"
                    class="form-control @error('deskripsi') is-invalid @enderror">{{ old('deskripsi') }}</textarea>
            @error('deskripsi')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-7">
                <label for="tenggat" class="form-label">Tenggat *</label>
                <input type="datetime-local" id="tenggat" name="tenggat"
                    min="{{ now()->format('Y-m-d') }}T00:00"
                    class="form-control @error('tenggat') is-invalid @enderror"
                    value="{{ old('tenggat') }}" required>
                @error('tenggat')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-md-5">
                <label for="prioritas" class="form-label">Prioritas *</label>
                <select id="prioritas" name="prioritas"
                        class="form-select @error('prioritas') is-invalid @enderror" required>
                    @foreach (['rendah' => 'Rendah', 'sedang' => 'Sedang', 'tinggi' => 'Tinggi'] as $nilai => $label)
                        <option value="{{ $nilai }}" @selected(old('prioritas', 'sedang') === $nilai)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('prioritas')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">Simpan tugas</button>
            <a href="{{ route('tugas.index') }}" class="btn btn-outline-secondary">Batal</a>
        </div>
    </form>
</div>
@endsection
