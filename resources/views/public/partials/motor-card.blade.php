<article class="group overflow-hidden rounded-[1.8rem] border border-white/10 bg-[linear-gradient(180deg,rgba(10,14,28,0.98),rgba(6,9,20,0.98))] shadow-[0_26px_80px_-56px_rgba(0,0,0,0.72)] transition duration-300 ease-out hover:-translate-y-1.5 hover:border-orange-300/30 hover:shadow-[0_32px_90px_-54px_rgba(255,98,37,0.28)]">
    <div class="relative aspect-[4/3] overflow-hidden border-b border-white/6 bg-[linear-gradient(180deg,#f8fafc,#eef2f7)]">
        <div class="absolute left-4 top-4 z-10 rounded-full border border-slate-200 bg-white/90 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.22em] text-slate-600 backdrop-blur-sm">
            {{ $motor->jenisMotor->jenis ?? 'Motor' }}
        </div>
        @if ($motor->primary_image_url)
            <img
                src="{{ $motor->primary_image_url }}"
                alt="{{ $motor->nama_motor }}"
                class="h-full w-full object-contain p-6 transition duration-300 group-hover:scale-[1.04]"
            >
        @else
            <div class="flex h-full w-full items-center justify-center p-6 text-center text-sm font-semibold uppercase tracking-[0.22em] text-slate-500">
                Gambar belum tersedia
            </div>
        @endif
    </div>

    <div class="space-y-5 p-5 sm:p-6">
        <div class="flex items-start justify-between gap-3">
            <div>
                <p class="text-[11px] uppercase tracking-[0.28em] text-orange-200">{{ strtoupper($motor->merk) }}</p>
                <h3 class="mt-2 text-[1.7rem] font-semibold leading-tight tracking-[-0.03em] text-white">{{ $motor->nama_motor }}</h3>
            </div>
            <x-status-badge status="{{ $motor->stok > 0 ? 'tersedia' : 'habis' }}" />
        </div>

        <div class="rounded-[1.4rem] border border-white/8 bg-white/[0.03] p-4">
            <p class="text-sm text-slate-400">Harga tunai</p>
            <p class="mt-2 text-[2rem] font-semibold leading-none tracking-[-0.04em] text-white">Rp {{ number_format($motor->harga_jual, 0, ',', '.') }}</p>
            @if (! empty($motor->starting_installment))
                <p class="mt-3 text-sm leading-6 text-slate-300">
                    Estimasi cicilan mulai Rp {{ number_format($motor->starting_installment, 0, ',', '.') }}/bulan
                </p>
            @endif
        </div>

        <div class="grid grid-cols-2 gap-3">
            <a href="{{ route('motors.show', $motor) }}" class="btn-secondary w-full justify-center !min-h-12 !rounded-full !border-white/10 !bg-white !px-4 !py-3 !text-slate-950">
                Lihat Detail
            </a>
            @auth
                <a href="{{ route('dashboard') }}" class="btn-accent w-full justify-center !min-h-12 !px-4 !py-3">
                    Dashboard
                </a>
            @else
                <a href="{{ route('login') }}" class="btn-accent w-full justify-center !min-h-12 !px-4 !py-3">
                    Masuk
                </a>
            @endauth
        </div>
    </div>
</article>
