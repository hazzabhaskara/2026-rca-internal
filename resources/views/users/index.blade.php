@extends('layouts.app')

@section('content')
@php
    $themeColor = \App\Setting::get('theme_color', '#D552A3');
@endphp

<div class="py-6 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto" x-data="{ tab: '{{ $activeTab ?? 'roles' }}' }">
    <!-- Header Section -->
    <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-gray-500 mb-1 font-medium">
                <a href="{{ url('/') }}" class="hover:text-gray-700 transition-colors">Dashboard</a>
                <span>/</span>
                <span class="text-gray-700 font-semibold">Pengaturan Sistem</span>
                <span>/</span>
                <span style="color: {{ $themeColor }}" class="font-semibold">Users & Roles</span>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight flex items-center gap-2.5">
                <span class="w-2.5 h-7 rounded-full inline-block" style="background-color: {{ $themeColor }}"></span>
                Manajemen Akses & Pengguna
            </h1>
            <p class="text-sm text-gray-500 mt-1">
                Atur peran (roles), izin akses terperinci per modul (permissions), dan akun pengguna sistem RCA.
            </p>
        </div>

        <!-- Custom Tab Navigation Switcher -->
        <div class="inline-flex p-1.5 bg-gray-100/90 rounded-2xl border border-gray-200/80 shadow-inner self-start md:self-auto">
            <button @click="tab = 'roles'"
                :class="tab === 'roles' ? 'bg-white text-gray-900 shadow-sm font-semibold' : 'text-gray-600 hover:text-gray-900 font-medium'"
                class="flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm transition-all duration-200">
                <i class="fa-solid fa-shield-halved" :style="tab === 'roles' ? 'color: {{ $themeColor }}' : ''"></i>
                <span>Roles & Permissions</span>
                <span class="ml-1 px-2 py-0.5 text-xs rounded-full bg-gray-100 text-gray-600 font-bold border border-gray-200">
                    {{ $roles->count() }}
                </span>
            </button>
            <button @click="tab = 'users'"
                :class="tab === 'users' ? 'bg-white text-gray-900 shadow-sm font-semibold' : 'text-gray-600 hover:text-gray-900 font-medium'"
                class="flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm transition-all duration-200">
                <i class="fa-solid fa-users" :style="tab === 'users' ? 'color: {{ $themeColor }}' : ''"></i>
                <span>Pengguna (Users)</span>
                <span class="ml-1 px-2 py-0.5 text-xs rounded-full bg-gray-100 text-gray-600 font-bold border border-gray-200">
                    {{ $users->count() }}
                </span>
            </button>
        </div>
    </div>

    <!-- Alert Notifications -->
    @if (session('success'))
        <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center font-bold shrink-0">
                    <i class="fa-solid fa-circle-check text-base"></i>
                </div>
                <div>
                    <span class="font-bold">Berhasil!</span> {{ session('success') }}
                </div>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700 p-1">
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>
        </div>
    @endif

    @if (isset($errors) && $errors->any())
        <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm shadow-xs">
            <div class="flex items-center gap-2 font-bold mb-1.5 text-rose-900">
                <i class="fa-solid fa-triangle-exclamation"></i> Terjadi Kesalahan:
            </div>
            <ul class="list-disc list-inside space-y-0.5 text-xs">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Tab Content: Roles -->
    <div x-show="tab === 'roles'" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
        @include('users._roles')
    </div>

    <!-- Tab Content: Users -->
    <div x-show="tab === 'users'" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
        @include('users._users')
    </div>
</div>
@endsection
