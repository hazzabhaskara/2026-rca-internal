@extends('layouts.app')

@section('content')
    <div class="p-6 bg-white rounded shadow">
        <h2 class="text-xl font-bold mb-4">Filter Report Target Wahana</h2>

        <form action="{{ route('report.target_wahana.result') }}" method="GET" class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm">Unit Kerja</label>
                <select name="unit_id" id="unit_id" class="w-full border rounded px-2 py-1">
                    <option value="">-- Semua Unit --</option>
                    @foreach ($units as $unit)
                        <option value="{{ $unit->id }}">{{ $unit->nama_unit }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm">Wahana</label>
                <select name="wahana_id" id="wahana_id" class="w-full border rounded px-2 py-1">
                    <option value="">-- Semua Wahana --</option>
                </select>
            </div>

            <div>
                <label class="block text-sm">Jenis Hari</label>
                <select name="jenis_hari_id" class="w-full border rounded px-2 py-1">
                    <option value="">-- Semua Jenis Hari --</option>
                    @foreach ($jenisHaris as $jh)
                        <option value="{{ $jh->id }}">{{ $jh->nama }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm">Tahun</label>
                <input type="number" name="tahun" class="w-full border rounded px-2 py-1" placeholder="2025">
            </div>

            <div>
                <label class="block text-sm">Bulan</label>
                <input type="number" name="bulan" class="w-full border rounded px-2 py-1" placeholder="1-12">
            </div>

            <div class="col-span-2">
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Tampilkan</button>
            </div>
        </form>
    </div>
    <script>
        document.getElementById('unit_id').addEventListener('change', function() {
            let unitId = this.value;
            let wahanaSelect = document.getElementById('wahana_id');

            // reset dropdown
            wahanaSelect.innerHTML = '<option value="">-- Semua Wahana --</option>';

            if (unitId) {
                fetch(`/get-wahana-by-unit/${unitId}`)
                    .then(res => res.json())
                    .then(data => {
                        data.forEach(wahana => {
                            let option = document.createElement('option');
                            option.value = wahana.id;
                            option.textContent = wahana.nama_wahana;
                            wahanaSelect.appendChild(option);
                        });
                    })
                    .catch(err => console.error(err));
            }
        });
    </script>
@endsection
