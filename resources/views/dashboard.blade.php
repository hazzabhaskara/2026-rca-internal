@extends('layouts.app')

@section('content')
    @php
        use Carbon\Carbon;
        $hour = Carbon::now()->format('H');
        $user = Auth::user()->name;

        $userBg = \App\Setting::get('background', 'logo.jpg');
        $bgImage = 'storage/' . $userBg;

        if ($hour >= 5 && $hour < 11) {
            $greeting = "Selamat pagi, $user";
            $tagline = 'Awali hari dengan energi positif dan penuh semangat.';
        } elseif ($hour >= 11 && $hour < 15) {
            $greeting = "Selamat siang, $user";
            $tagline = 'Tetap produktif di tengah hari yang cerah.';
        } elseif ($hour >= 15 && $hour < 18) {
            $greeting = "Selamat sore, $user";
            $tagline = 'Waktunya menyelesaikan pekerjaan dengan tuntas.';
        } else {
            $greeting = "Selamat malam, $user";
            $tagline = 'Istirahat sejenak, persiapkan hari esok.';
        }
    @endphp


    <div class="relative w-full h-screen flex items-start justify-end text-right text-white overflow-hidden p-0 pt-16"
        style="background-image: url('{{ asset($bgImage) }}'); background-size: cover; background-position: center; background-repeat: no-repeat;">


        <!-- Overlay -->
        <div class=""></div>

        <!-- Partikel -->

        <!-- Konten -->
        <div class="relative max-w-xl z-10">
            <h1
                class="text-2xl md:text-2xl font-extrabold mb-4 drop-shadow-2xl 
            bg-clip-text text-transparent 
            bg-gradient-to-r from-sky-300 via-cyan-400 to-blue-500 
            animate-fadeInUp">
                {{ $greeting }}
            </h1>

            <p class="text-sm md:text-sm mb-6 text-gray-700 drop-shadow-md animate-fadeIn delay-300">
                <span class="px-2 py-1 rounded-lg backdrop-blur-sm">
                    {{ $tagline }}
                </span>
            </p>
        </div>
    </div>
@endsection
