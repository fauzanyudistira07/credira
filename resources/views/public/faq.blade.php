@extends('layouts.public', ['title' => 'FAQ Credira'])

@section('content')
    <section class="shell pt-28 pb-14 sm:pt-32 lg:pt-36 lg:pb-20">
        <div class="page-banner">
            <span class="section-kicker section-kicker-dark">FAQ</span>
            <h1>Pertanyaan yang paling sering diajukan mengenai pengajuan kredit motor di Credira.</h1>
            <p>Ringkasan ini membantu calon nasabah memahami proses, persyaratan, dan ekspektasi layanan sebelum memulai pengajuan.</p>
        </div>

        <div class="mx-auto mt-8 max-w-5xl space-y-4">
            @foreach ($faqs as $faq)
                <article x-data="{ open: {{ $loop->first ? 'true' : 'false' }} }" class="content-panel overflow-hidden">
                    <button
                        type="button"
                        class="flex w-full items-center justify-between gap-4 px-6 py-5 text-left transition duration-200 hover:bg-white/4 focus:outline-none focus-visible:ring-2 focus-visible:ring-orange-200"
                        @click="open = !open"
                        :aria-expanded="open.toString()"
                    >
                        <span class="text-lg font-semibold leading-7 text-white">{{ $faq->question }}</span>
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full border border-white/10 text-slate-300 transition duration-300" :class="open ? 'rotate-45 border-orange-300/30 bg-orange-500/10 text-orange-200' : ''">
                            +
                        </span>
                    </button>
                    <div
                        x-show="open"
                        x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 -translate-y-2"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-200"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 -translate-y-2"
                        class="px-6 pb-6 text-sm leading-7 text-slate-300"
                    >
                        {{ $faq->answer }}
                    </div>
                </article>
            @endforeach
        </div>
    </section>
@endsection
