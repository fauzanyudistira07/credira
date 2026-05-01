@extends('layouts.admin', [
    'title' => 'Tambah Motor',
    'heading' => 'Tambah Motor',
    'subheading' => 'Masukkan data master motor baru lengkap dengan foto utama dan gallery tambahan.',
])

@section('content')
    <form method="POST" action="{{ route('admin.motors.store') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @include('admin.motors._form')
    </form>
@endsection
