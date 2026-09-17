@extends('layouts.app')

@section('content')
    <div class="min-h-screen flex flex-col justify-center items-center bg-gray-100">
        {{-- Ilustrasi error --}}
        <div class="mb-6">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-32 h-32 text-red-500 animate-bounce" fill="none"
                viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M18.364 5.636l-12.728 12.728M5.636 5.636l12.728 12.728" />
            </svg>
        </div>

        {{-- Judul --}}
        <h1 class="text-4xl font-bold text-gray-800 mb-4">Oops! Terjadi Kesalahan Server</h1>

        {{-- Pesan --}}
        <p class="text-lg text-gray-600 mb-8 text-center max-w-md">
            Silakan coba beberapa saat lagi.
        </p>

        {{-- Tombol kembali --}}
        <a href="{{ url('/') }}"
            class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg shadow transition duration-300">
            Kembali ke Beranda
        </a>
    </div>
@endsection
