@extends('layouts.admin', [
    'title' => 'Edit Motor',
    'heading' => 'Edit Motor',
    'subheading' => 'Perbarui data unit, status tampil, stok, dan visual tanpa menghapus data yang tidak perlu.',
])

@section('content')
    <form method="POST" action="{{ route('admin.motors.update', $motor) }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')
        @include('admin.motors._form')
    </form>
@endsection
