@extends('layouts.admin', [
    'title' => 'Edit User',
    'heading' => 'Edit User',
    'subheading' => 'Perbarui data user tanpa mengganggu akses dan relasi existing yang sudah berjalan.',
])

@section('content')
    <form method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-6">
        @csrf
        @method('PUT')
        @include('admin.users._form')
    </form>
@endsection
