@extends('layouts.dashboard', [
    'title' => 'Edit Pelanggan',
    'role' => 'marketing',
    'pageTitle' => 'Edit Pelanggan',
    'pageDescription' => 'Perbarui data pelanggan milik Anda tanpa membuka akses ke data marketing lain.',
])

@section('content')
    <div class="marketing-page">
        <section class="marketing-hero">
            <p class="marketing-hero__eyebrow">Edit Pelanggan</p>
            <h2 class="marketing-hero__title">Rapikan data pelanggan tanpa keluar dari alur kerja marketing.</h2>
            <p class="marketing-hero__copy">Perbarui identitas, kontak, alamat, dan dokumen inti sambil tetap menjaga ownership data sesuai akun marketing.</p>
        </section>

        <form method="POST" action="{{ route('marketing.pelanggan.update', $pelanggan) }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')
            @include('marketing.pelanggan._form', ['submitLabel' => 'Simpan Perubahan'])
        </form>
    </div>
@endsection
