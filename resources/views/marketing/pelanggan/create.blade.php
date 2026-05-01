@extends('layouts.dashboard', [
    'title' => 'Tambah Pelanggan',
    'role' => 'marketing',
    'pageTitle' => 'Tambah Pelanggan',
    'pageDescription' => 'Buat akun pelanggan baru beserta identitas dan alamat utama agar siap dipakai dalam flow pengajuan kredit.',
])

@section('content')
    <div class="marketing-page">
        <section class="marketing-hero">
            <p class="marketing-hero__eyebrow">Tambah Pelanggan</p>
            <h2 class="marketing-hero__title">Buat profil pelanggan baru agar siap masuk ke flow pengajuan kredit.</h2>
            <p class="marketing-hero__copy">Lengkapi identitas, alamat, dan dokumen dasar dengan format yang konsisten dengan workspace Credira.</p>
        </section>

        <form method="POST" action="{{ route('marketing.pelanggan.store') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @include('marketing.pelanggan._form', ['submitLabel' => 'Simpan Pelanggan'])
        </form>
    </div>
@endsection
