@php
    $toasts = [];

    if (session('status')) {
        $toasts[] = [
            'type' => 'success',
            'title' => 'Berhasil',
            'message' => session('status'),
        ];
    }

    if (session('error')) {
        $toasts[] = [
            'type' => 'error',
            'title' => 'Terjadi kendala',
            'message' => session('error'),
        ];
    }

    if (session('warning')) {
        $toasts[] = [
            'type' => 'error',
            'title' => 'Perlu perhatian',
            'message' => session('warning'),
        ];
    }

    foreach ($errors->all() as $error) {
        $toasts[] = [
            'type' => 'error',
            'title' => 'Periksa kembali',
            'message' => $error,
        ];
    }
@endphp

@if ($toasts !== [])
    <script>
        window.__CREDIRA_TOASTS = (window.__CREDIRA_TOASTS || []).concat(@json($toasts));
    </script>
@endif
