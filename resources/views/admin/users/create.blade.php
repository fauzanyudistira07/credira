@extends('layouts.admin', [
    'title' => 'Tambah User',
    'heading' => 'Tambah User',
    'subheading' => 'Buat akun baru untuk admin, marketing, atau ceo dengan validasi yang aman.',
])

@section('content')
    <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-6">
        @csrf
        @include('admin.users._form')
    </form>
@endsection
