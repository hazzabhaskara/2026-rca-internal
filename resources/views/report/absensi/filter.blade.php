@extends('layouts.app')

@section('content')
    <div class="p-6 bg-white rounded-lg shadow">
        <h2 class="text-xl font-bold mb-4">Filter Rekap Absensi</h2>

        <form method="GET" action="{{ route('report.absensi.hasil') }}" class="space-y-4">
            <div>
                <label class="block font-medium">Jenis Filter</label>
                <select name="filter" id="filterSelect" class="border rounded px-2 py-1 w-full">
                    <option value="weekly">Mingguan</option>
                    <option value="monthly">Bulanan</option>
                    <option value="custom">Custom</option>
                </select>
            </div>

            <div id="dateFields" class="hidden">
                <div>
                    <label class="block font-medium">Tanggal Awal</label>
                    <input type="date" name="start_date" class="border rounded px-2 py-1 w-full">
                </div>

                <div>
                    <label class="block font-medium">Tanggal Akhir</label>
                    <input type="date" name="end_date" class="border rounded px-2 py-1 w-full">
                </div>
            </div>

            <div>
                <label class="block font-medium">Unit</label>
                <select name="unit_id" class="border rounded px-2 py-1 w-full">
                    <option value="">Semua Unit</option>
                    @foreach ($units as $unit)
                        <option value="{{ $unit->id }}">{{ $unit->nama_unit }}</option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">
                Lihat Hasil
            </button>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const filterSelect = document.getElementById('filterSelect');
            const dateFields = document.getElementById('dateFields');

            function toggleDateFields() {
                if (filterSelect.value === 'custom') {
                    dateFields.classList.remove('hidden');
                } else {
                    dateFields.classList.add('hidden');
                }
            }

            // initial check
            toggleDateFields();

            // event listener
            filterSelect.addEventListener('change', toggleDateFields);
        });
    </script>
@endsection
