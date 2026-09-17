@extends('layouts.app')

@section('content')
    <div class="max-w-full mx-auto bg-white shadow-md rounded-xl p-8 mt-6">
        <form method="POST"
            action="{{ isset($tarif_ter) ? route('tax_rates.update', $tarif_ter->id) : route('tax_rates.store') }}">
            @csrf
            @if (isset($tarif_ter))
                @method('PUT')
            @endif

            {{-- Error --}}
            @if ($errors->any())
                <div class="mb-4 text-red-600 bg-red-100 p-4 rounded-md">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- PTKP --}}
            <div class="mb-5">
                <label for="ptkp_id" class="block text-sm font-medium text-gray-700 mb-1">Golongan PTKP</label>
                <select name="ptkp_id" id="ptkp_id"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">-- Pilih Golongan --</option>
                    @foreach ($ptkp as $g)
                        <option value="{{ $g->id }}"
                            {{ isset($tarif_ter) && $tarif_ter->ptkp_id == $g->id ? 'selected' : '' }}>
                            {{ $g->nama }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Min Penghasilan --}}
            <div class="mb-5">
                <label for="min_penghasilan" class="block text-sm font-medium text-gray-700 mb-1">Min Penghasilan
                    (Rp)</label>
                <div class="flex">
                    <span
                        class="inline-flex items-center px-3 rounded-l-md bg-gray-100 border border-r-0 border-gray-300 text-gray-600 text-sm">Rp</span>
                    <input type="text" id="min_penghasilan" name="min_penghasilan"
                        value="{{ isset($tarif_ter) && is_numeric($tarif_ter->min_penghasilan) ? number_format($tarif_ter->min_penghasilan, 0, ',', '.') : '' }}"
                        class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="4500000">
                </div>
            </div>

            {{-- Max Penghasilan --}}
            <div class="mb-5">
                <label for="max_penghasilan" class="block text-sm font-medium text-gray-700 mb-1">Max Penghasilan
                    (Rp)</label>
                <div class="flex">
                    <span
                        class="inline-flex items-center px-3 rounded-l-md bg-gray-100 border border-r-0 border-gray-300 text-gray-600 text-sm">Rp</span>
                    <input type="text" id="max_penghasilan" name="max_penghasilan"
                        value="{{ isset($tarif_ter) && is_numeric($tarif_ter->max_penghasilan) ? number_format($tarif_ter->max_penghasilan, 0, ',', '.') : '' }}"
                        class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="6000000">
                </div>
            </div>

            {{-- Tarif TER --}}
            <div class="mb-6">
                <label for="tarif_ter" class="block text-sm font-medium text-gray-700 mb-1">
                    Tarif TER (%)
                    <span class="text-xs text-gray-400">(Contoh: 1.25)</span>
                </label>
                <input type="text" id="tarif_ter" name="tarif_ter" value="{{ $tarif_ter->tarif_ter ?? '' }}"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="1.25">
            </div>

            {{-- Tombol --}}
            <div class="flex justify-end">
                <a href="{{ route('tax_rates.index') }}"
                    class="mr-3 inline-block px-4 py-2 bg-gray-300 text-gray-800 rounded-lg hover:bg-gray-400">
                    Batal
                </a>
                <button type="submit"
                    class="inline-block px-6 py-2 bg-green-600 text-white font-semibold rounded-lg hover:bg-green-700 transition">
                    {{ isset($tarif_ter) ? '💾 Update' : '✅ Simpan' }}
                </button>
            </div>
        </form>
    </div>
@endsection
<script>
    document.addEventListener('DOMContentLoaded', function() {
        function formatRupiah(inputId) {
            const input = document.getElementById(inputId);
            input.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                value = new Intl.NumberFormat('id-ID').format(value);
                e.target.value = value;
            });

            const form = input.closest('form');
            form.addEventListener('submit', function() {
                input.value = input.value.replace(/\./g, '');
            });
        }

        formatRupiah('min_penghasilan');
        formatRupiah('max_penghasilan');
    });
</script>
