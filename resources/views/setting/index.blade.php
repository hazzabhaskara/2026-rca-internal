@extends('layouts.app')


@section('content')
    <div class="max-w-3xl mx-auto py-6">
        @if (session('success'))
            <div class="bg-green-100 text-green-800 p-3 rounded mb-4">{{ session('success') }}</div>
        @endif

        <form method="POST" action="{{ route('setting.update') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf

            @if ($errors->any())
                <div class="mb-4 text-red-600 bg-red-100 p-4 rounded-md">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div>
                <label>Judul Website</label>
                <input type="text" name="site_title" class="w-full border rounded px-3 py-2"
                    value="{{ $tampilan['site_title'] ?? '' }}">
            </div>

            <div class="mb-4">
                <label class="block font-semibold mb-1">Warna Tema</label>

                <div class="flex items-center gap-3">
                    {{-- Color Picker --}}
                    <input type="color" id="theme_color_picker" class="w-12 h-10"
                        value="{{ $tampilan['theme_color'] ?? '#4F46E5' }}">

                    {{-- Text Input --}}
                    <input type="text" name="theme_color" id="theme_color_input"
                        value="{{ $tampilan['theme_color'] ?? '#4F46E5' }}" class="w-36 border rounded px-2 py-2 text-sm"
                        placeholder="#HEX">
                </div>
            </div>
            <div class="mb-4">
                <label class="block font-semibold mb-1">Warna Secondary</label>
                <div class="flex items-center gap-3">
                    {{-- Color Picker --}}
                    <input type="color" id="theme_color_secondary_picker" class="w-12 h-10"
                        value="{{ $tampilan['theme_secondary_color'] ?? '#4F46E5' }}">

                    {{-- Text Input --}}
                    <input type="text" name="theme_secondary_color" id="theme_color_secondary_input"
                        value="{{ $tampilan['theme_secondary_color'] ?? '#4F46E5' }}"
                        class="w-36 border rounded px-2 py-2 text-sm" placeholder="#HEX">
                </div>
            </div>


            <div>
                <label>Teks Footer</label>
                <input type="text" name="text_footer" class="w-full border rounded px-3 py-2"
                    value="{{ $tampilan['text_footer'] ?? '' }}">
            </div>

            <div>
                <label>Logo</label><br>
                @if (!empty($tampilan['logo']))
                    <img src="{{ asset('storage/' . $tampilan['logo']) }}" class="h-16 mb-2 rounded">
                @endif
                <input type="file" name="logo" class="w-full border px-2 py-2 rounded">
            </div>

            <div>
                <label>Background Website</label><br>
                @if (!empty($tampilan['background']))
                    <img src="{{ asset('storage/' . $tampilan['background']) }}" class="h-20 mb-2 rounded">
                @endif
                <input type="file" name="background" class="w-full border px-2 py-2 rounded">
            </div>

            <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded hover:bg-indigo-700">
                Simpan Pengaturan
            </button>
        </form>
    </div>
    <script>
        const colorInput = document.getElementById('theme_color_input');
        const colorPicker = document.getElementById('theme_color_picker');
        const colorInputSecondary = document.getElementById('theme_color_secondary_input');
        const colorPickerSecondary = document.getElementById('theme_color_secondary_picker');

        // Warna Utama
        colorPicker.addEventListener('input', function() {
            colorInput.value = this.value;
        });

        colorInput.addEventListener('input', function() {
            const value = this.value;
            if (/^#[0-9A-Fa-f]{6}$/.test(value)) {
                colorPicker.value = value;
            }
        });

        // Warna Sekunder
        colorPickerSecondary.addEventListener('input', function() {
            colorInputSecondary.value = this.value;
        });

        colorInputSecondary.addEventListener('input', function() {
            const value = this.value;
            if (/^#[0-9A-Fa-f]{6}$/.test(value)) {
                colorPickerSecondary.value = value;
            }
        });
    </script>

@endsection
