@extends('layouts.app')

@section('content')
    <div class="py-10">

        <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8" x-data="{ tab: '{{ request('view', 'tabel') }}' }">
            <!-- Main Card -->
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden border border-gray-200">
                <!-- Sticky Card Header -->
                @php
                    $themeColor = \App\Setting::get('theme_color', '#4F46E5');
                @endphp

                <div class="sticky top-0 z-20 px-6 py-5 border-b border-gray-100 flex justify-between items-center"
                    style="background: {{ $themeColor }};">
                    <h3 class="text-xl font-bold text-white flex items-center">
                        <i class="fas fa-list mr-3 text-white text-xl"></i>
                        Scheduling Employee
                    </h3>

                    <div class="flex flex-wrap gap-2">
                        <button onclick="document.getElementById('fileModal').classList.remove('hidden')"
                            class="inline-flex items-center px-3 py-2 text-sm rounded-md border border-gray-300 bg-white text-gray-700 hover:bg-gray-50">
                            <i class="fas fa-file-export text-blue-500 mr-2"></i> File
                        </button>
                        <button onclick="document.getElementById('filterPanel').classList.toggle('hidden')"
                            class="inline-flex items-center px-3 py-2 text-sm rounded-md border border-gray-300 bg-white text-gray-700 hover:bg-gray-50">
                            <i class="fas fa-filter text-gray-500 mr-2"></i> Filter
                        </button>

                        @can('shift_karyawan.create')
                            <a href="{{ route('shift_karyawan.create') }}"
                                class="inline-flex items-center px-5 py-2.5 bg-white text-indigo-600 font-semibold rounded-lg shadow hover:bg-gray-100 transition-all">
                                <i class="fas fa-plus mr-2"></i> Add Scheduling Employee
                            </a>
                        @endcan

                        <button @click="tab = 'tabel'"
                            :class="tab === 'tabel' ? 'border-b-2 border-blue-600 text-blue-600' :
                                'text-gray-100 bg-white/10 hover:bg-white/20'"
                            class="px-4 py-2 focus:outline-none rounded">
                            Tabel
                        </button>
                        <button @click="tab = 'kalender'"
                            :class="tab === 'kalender' ? 'border-b-2 border-blue-600 text-blue-600' :
                                'text-gray-100 bg-white/10 hover:bg-white/20'"
                            class="px-4 py-2 focus:outline-none rounded">
                            Input Per Tanggal
                        </button>
                    </div>
                </div>
            </div>

            {{-- FILTER --}}
            <div id="filterPanel"
                class="{{ request('search') || request('filter_tipe') || request('filter_wahana') || request('filter_jenis_hari') || request('filter_status') ? '' : 'hidden' }} px-6 py-4 border-b border-gray-100 bg-gray-50">
                <form method="GET" action="{{ route('shift_karyawan.index') }}">
                    <div class="flex flex-wrap gap-4">
                        <!-- Search Input -->
                        <div class="relative">
                            <input type="text" name="search" value="{{ request('search') }}"
                                placeholder="Cari unit, wahana, dan jenis hari"
                                class="pl-10 pr-4 py-2 rounded-lg border border-gray-300 text-sm w-64 shadow-sm" />
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <i class="fas fa-search text-gray-400 text-sm"></i>
                            </div>
                        </div>

                        <!-- Unit Kerja -->
                        <select name="filter_tipe" class="py-2 px-3 border rounded-lg text-sm border-gray-300 shadow-sm">
                            <option value="">Semua Unit Kerja</option>
                            @foreach ($unitkerja as $tipe)
                                <option value="{{ $tipe }}" {{ request('filter_tipe') == $tipe ? 'selected' : '' }}>
                                    {{ $tipe }}
                                </option>
                            @endforeach
                        </select>

                        <!-- Wahana -->
                        <select name="filter_wahana" class="py-2 px-3 border rounded-lg text-sm border-gray-300 shadow-sm">
                            <option value="">Semua Wahana</option>
                            @foreach ($wahana as $whn)
                                <option value="{{ $whn }}"
                                    {{ request('filter_wahana') == $whn ? 'selected' : '' }}>
                                    {{ $whn }}
                                </option>
                            @endforeach
                        </select>

                        <!-- Jenis Hari -->
                        <select name="filter_jenis_hari"
                            class="py-2 px-3 border rounded-lg text-sm border-gray-300 shadow-sm">
                            <option value="">Semua Jenis Hari</option>
                            @foreach ($jenis_hari as $js)
                                <option value="{{ $js }}"
                                    {{ request('filter_jenis_hari') == $js ? 'selected' : '' }}>
                                    {{ $js }}
                                </option>
                            @endforeach
                        </select>

                        <!-- Status -->
                        <select name="filter_status" class="py-2 px-3 border rounded-lg text-sm border-gray-300 shadow-sm">
                            <option value="">Semua Status</option>
                            @foreach (['Penetapan', 'Perubahan', 'Tambahan'] as $st)
                                <option value="{{ $st }}"
                                    {{ request('filter_status') === $st ? 'selected' : '' }}>
                                    {{ $st }}
                                </option>
                            @endforeach
                        </select>

                        <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-blue-500 text-white rounded shadow-sm hover:bg-blue-600 text-sm">
                            <i class="fas fa-search mr-1"></i> Filter
                        </button>

                        <a href="{{ route('shift_karyawan.index') }}"
                            class="inline-flex items-center px-3 py-2 text-sm rounded-md border border-gray-300 bg-white text-gray-700 hover:bg-gray-50">
                            <i class="fas fa-times mr-1 text-gray-400"></i> Reset
                        </a>
                    </div>
                </form>
            </div>
            <!-- File Modal -->
            <div id="fileModal"
                class="fixed inset-0 z-50 hidden bg-black bg-opacity-30 backdrop-blur-sm flex items-center justify-center">
                <div class="bg-white rounded-xl shadow-lg p-6 w-full max-w-md">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4 flex items-center">
                        <i class="fas fa-file-alt mr-2 text-blue-400"></i> File Aksi
                    </h3>
                    <div class="space-y-3 text-sm text-gray-700">
                        <a href="{{ asset('template/template_import_shift_karyawan.xlsx') }}" download
                            class="block hover:bg-gray-50 p-2 rounded-lg">
                            <i class="fas fa-file-excel mr-2 text-green-600"></i> Download Template Excel
                        </a>
                        <a href="{{ route('export.ShiftKaryawan') }}" class="block hover:bg-gray-50 p-2 rounded-lg">
                            <i class="fas fa-file-download mr-2 text-blue-500"></i> Export
                        </a>
                        <form action="{{ route('export.ShiftKaryawanTabel') }}" method="GET" class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700">Export Format Tabel (Dengan Rentang
                                Tanggal):</label>
                            <div class="flex space-x-2">
                                <input type="date" name="tgl_awal" class="border rounded px-2 py-1 text-sm w-1/2"
                                    required>
                                <input type="date" name="tgl_akhir" class="border rounded px-2 py-1 text-sm w-1/2"
                                    required>
                            </div>
                            <button type="submit"
                                class="bg-blue-500 text-white w-full py-1 rounded hover:bg-blue-600 text-sm mt-2">
                                <i class="fas fa-file-download mr-1"></i> Export Format Tabel
                            </button>
                        </form>

                        <div class="border rounded">
                            <label for="font-bold text-lg mt-4">PDF Document</label>
                            <form action="{{ route('shift_karyawan.ShiftKaryawanPDF') }}" method="GET" class="space-y-2">
                                <label class="block text-sm font-medium text-gray-700">Export Format PDF (Rentang
                                    Tanggal):</label>
                                <div class="flex space-x-2">
                                    <input type="date" name="tgl_awal" class="border rounded px-2 py-1 text-sm w-1/2"
                                        required>
                                    <input type="date" name="tgl_akhir" class="border rounded px-2 py-1 text-sm w-1/2"
                                        required>
                                </div>
                                <button type="submit"
                                    class="bg-red-500 text-white w-full py-1 rounded hover:bg-red-600 text-sm mt-2">
                                    <i class="fas fa-file-pdf mr-1"></i> Export Format PDF
                                </button>
                            </form>

                        </div>
                        <form action="{{ route('import.ShiftKaryawan') }}" method="POST" enctype="multipart/form-data"
                            class="space-y-2">
                            @csrf
                            <label class="block text-sm font-medium text-gray-700">Import File Excel:</label>
                            <input type="file" name="file" class="block w-full text-sm border rounded px-2 py-1"
                                required>
                            <button type="submit"
                                class="bg-green-500 text-white w-full py-1 rounded hover:bg-green-600 text-sm">
                                <i class="fas fa-file-upload mr-1"></i> Import
                            </button>
                        </form>
                    </div>
                    <div class="mt-4 text-right">
                        <button onclick="document.getElementById('fileModal').classList.add('hidden')"
                            class="text-sm text-gray-500 hover:text-gray-700">Tutup</button>
                    </div>
                </div>
            </div>

            {{-- TAB TABEL --}}
            <div x-show="tab === 'tabel'" class="relative overflow-x-auto"
                style="max-height: calc(100vh - 250px); overflow-y: auto;">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50 sticky top-0 z-10">
                        <tr>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Nama Karyawan</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Crew</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Unit Kerja</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Level Karyawan</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Nama Wahana</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Tanggal</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Jenis Hari</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Jam
                                Mulai</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Jam
                                Selesai</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Lama Jam</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Persentase Jam</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Keterangan</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($data as $item)
                            <tr class="hover:bg-gray-50 transition-colors duration-150">

                                <td class="px-4 py-2 text-center ">{{ optional($item->karyawan)->nama_karyawan ?? '-' }}
                                </td>
                                <td class="px-4 py-2 text-center ">{{ optional($item->crewShift)->nama ?? '-' }}
                                </td>
                                <td class="px-4 py-2 text-center ">{{ optional($item->unitKerja)->nama_unit ?? '-' }}</td>
                                <td class="px-4 py-2 text-center ">
                                    {{ optional(optional($item->karyawan)->levelKaryawan)->nama_level ?? '-' }}</td>
                                <td class="px-4 py-2 text-center ">{{ optional($item->wahana)->nama_wahana ?? '-' }}</td>
                                <td class="px-4 py-2 text-center ">
                                    {{ $item->tanggal ? \Carbon\Carbon::parse($item->tanggal)->format('d M Y') : '-' }}
                                </td>
                                <td class="px-4 py-2 text-center ">{{ optional($item->jenisHari)->nama ?? '-' }}</td>
                                <td class="px-4 py-2 text-center ">{{ $item->jam_mulai ?? '-' }}</td>
                                <td class="px-4 py-2 text-center ">{{ $item->jam_selesai ?? '-' }}</td>
                                <td class="px-4 py-2 text-center ">{{ $item->lama_jam ?? '-' }}</td>
                                <td class="px-4 py-2 text-center ">{{ $item->persentase_jam ?? '-' }}</td>
                                <td class="px-4 py-2 text-center ">{{ $item->status ?? '-' }}</td>
                                <td class="px-4 py-2 text-center ">{{ $item->keterangan ?? '-' }}</td>
                                <td class="px-4 py-2 text-right">
                                    <div class="flex justify-end space-x-3">
                                        @can('shift_karyawan.view')
                                            <a href="{{ route('shift_karyawan.show', $item->id) }}"
                                                class="text-blue-500 hover:text-blue-700 p-2 rounded-full hover:bg-blue-50 transition-colors"
                                                title="View"><i class="fas fa-eye"></i></a>
                                        @endcan

                                        @can('shift_karyawan.update')
                                            <a href="{{ route('shift_karyawan.edit', $item->id) }}"
                                                class="text-yellow-500 hover:text-yellow-700 p-2 rounded-full hover:bg-yellow-50 transition-colors"
                                                title="Edit"><i class="fas fa-edit"></i></a>
                                        @endcan

                                        @can('shift_karyawan.delete')
                                            <form id="delete-form-{{ $item->id }}"
                                                action="{{ route('shift_karyawan.destroy', $item->id) }}" method="POST"
                                                class="hidden">
                                                @csrf @method('DELETE')
                                            </form>
                                            <button type="button" onclick="confirmDelete({{ $item->id }})"
                                                class="text-red-500 hover:text-red-700 p-2 rounded-full hover:bg-red-50 transition-colors"
                                                title="Delete"><i class="fas fa-trash"></i></button>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="15" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center text-gray-400">
                                        <i class="fas fa-exclamation-circle text-4xl mb-3"></i>
                                        <p class="text-lg font-medium">Belum ada Scheduling Employee</p>
                                        @can('shift_karyawan.create')
                                            <a href="{{ route('shift_karyawan.create') }}"
                                                class="mt-4 inline-flex items-center px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition">
                                                <i class="fas fa-plus mr-2"></i> Tambah
                                            </a>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{-- Tab Kalender --}}
            <div x-data="kalenderPage()" x-init="init()" x-show="tab === 'kalender'" class="p-4 space-y-4">

                {{-- Filter Bar --}}
                <div class="flex flex-wrap items-end gap-4">
                    <div>
                        <label class="block text-xs text-gray-600 mb-1">Tanggal</label>
                        <input x-model="tanggal" type="date" name="tanggal"
                            class="px-3 py-2 border rounded w-48 bg-white">
                    </div>

                    <div>
                        <label class="block text-xs text-gray-600 mb-1">Unit Kerja</label>
                        <select x-model="unitId" @change="loadWahana()"
                            class="w-64 border border-gray-300 rounded-lg px-3 py-2 bg-white">
                            <option value="">-- Pilih Unit Kerja --</option>
                            @foreach ($unitKerja as $u)
                                <option value="{{ $u->id }}">{{ $u->nama_unit }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grow"></div>
                </div>

                {{-- Tabel Wahana & Petugas --}}


                <div class="relative overflow-x-auto" style="max-height: calc(100vh - 330px); overflow-y: auto;">
                    <table class="min-w-full border border-gray-200 text-sm">
                        <thead class="bg-gray-50 sticky top-0 z-10">
                            <tr>
                                <th class="px-2 py-1 text-left font-semibold text-gray-700 border">Wahana</th>
                                @foreach ($crew_shift as $crew)
                                    <th class="px-2 py-1 text-left font-semibold text-gray-700 border">
                                        {{ $crew->nama }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            <template x-for="w in wahanas" :key="w.id">
                                <tr>
                                    <td class="border px-2 py-1" x-text="w.nama_wahana"></td>

                                    <template x-for="crew in crew_shift" :key="crew.id">
                                        <td class="border px-2 py-1">
                                            <div class="flex items-center justify-between gap-2">
                                                <span class="truncate text-gray-700"
                                                    x-text="displayPetugas(w.id, crew.id)"></span>
                                                <button type="button"
                                                    class="px-2 py-1 bg-blue-600 text-white rounded hover:bg-blue-700"
                                                    @click="openModal(w, crew)">
                                                    Set
                                                </button>
                                            </div>
                                        </td>
                                    </template>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>


                {{-- PANEL OFF: di bawah tabel, rata kiri --}}
                <div class="mt-4 flex flex-col md:flex-row gap-4">
                    <div class="w-full md:w-1/3">
                        <div class="border rounded-lg p-3 bg-white">
                            <div class="flex items-center justify-between">
                                <h4 class="font-semibold text-sm">Karyawan OFF ( <span x-text="tanggal"></span> )</h4>
                                <span class="text-xs text-gray-500" x-text="offs.length + ' orang'"></span>
                            </div>

                            {{-- Daftar OFF --}}
                            <div class="mt-3 space-y-2 max-h-60 overflow-auto">
                                <template x-if="offs.length === 0">
                                    <div class="text-xs text-gray-500">Belum ada karyawan OFF.</div>
                                </template>

                                <template x-for="o in offs" :key="o.id">
                                    <div class="flex items-center justify-between bg-gray-50 border rounded px-2 py-1">
                                        <div class="text-sm truncate" x-text="o.nama_karyawan"></div>
                                        <button class="text-xs px-2 py-1 bg-red-500 text-white rounded"
                                            @click="deleteOff(o.id)">Hapus</button>
                                    </div>
                                </template>
                            </div>

                            {{-- Tambah OFF (multi) --}}
                            <div class="mt-3 border-t pt-3">
                                <label class="block text-xs text-gray-600 mb-1">Tambah OFF (bisa pilih lebih dari
                                    satu)</label>
                                <select multiple size="6" x-model="newOffEmployeeIds"
                                    class="w-full border rounded px-2 py-1 bg-white">
                                    <template x-for="c in offCandidates" :key="c.id">
                                        <option :value="c.id" x-text="c.nama_karyawan"
                                            :disabled="offIds.includes(c.id)">
                                        </option>
                                    </template>
                                </select>

                                <div class="flex items-center gap-2 mt-2">
                                    <input type="text" x-model="newOffNote" placeholder="Catatan (opsional)"
                                        class="flex-1 border rounded px-2 py-1 text-sm">
                                    <button class="px-3 py-1 bg-amber-600 text-white rounded text-sm" @click="addOffs()">+
                                        Tandai OFF</button>
                                </div>

                                <p class="text-[11px] text-gray-500 mt-2">Tip: tekan Ctrl/⌘ untuk memilih banyak nama.</p>
                            </div>
                        </div>
                    </div>

                    {{-- Spacer/area kanan bisa untuk konten lain jika diperlukan --}}
                    <div class="flex-1"></div>
                </div>


                {{-- ===================== MODAL ===================== --}}
                <div x-show="showModal" x-transition.opacity class="fixed inset-0 z-40 flex items-center justify-center">
                    <div class="absolute inset-0 bg-black/40" @click="showModal=false"></div>

                    <div class="relative z-50 w-[90%] max-w-5xl bg-white rounded-xl shadow-xl">
                        <div class="flex items-center justify-between px-4 py-3 border-b">
                            <h3 class="font-semibold">
                                Atur <span x-text="modal.posisi?.nama"></span> •
                                <span x-text="modal.wahana?.nama_wahana"></span>
                            </h3>
                            <button class="px-3 py-1 rounded bg-gray-100 hover:bg-gray-200"
                                @click="showModal=false">Tutup</button>
                        </div>

                        <div class="p-4">
                            {{-- Form as requested (diprefill & disesuaikan ke modal state) --}}
                            <form method="POST"
                                action="{{ isset($shift_karyawan) ? route('shift_karyawan.update', $shift_karyawan->id ?? 0) : route('shift_karyawan.store') }}">
                                @csrf
                                @if (isset($shift_karyawan))
                                    @method('PUT')
                                @endif

                                {{-- Error block (server-side) --}}
                                @if ($errors->any())
                                    <div class="mb-4 text-red-600 bg-red-100 p-4 rounded-md">
                                        <ul class="list-disc list-inside">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                {{-- Hidden prefill dari modal --}}
                                <input type="hidden" name="wahana_id" :value="modal.wahana?.id ?? ''">
                                <input type="hidden" name="unit_kerja_id" :value="unitId">
                                <input type="hidden" name="tanggal" :value="tanggal">
                                <input type="hidden" name="crew_id" :value="modal.posisi?.id ?? ''">


                                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-sm">
                                    <div class="mb-2 md:col-span-2">
                                        <label for="employee_id"
                                            class="block text-sm font-medium text-gray-700 mb-1">Employee</label>
                                        <select name="employee_id" id="employee_id"
                                            class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-white">
                                            <option value="">-- Pilih Employee --</option>
                                            @foreach ($karyawan as $g)
                                                <option value="{{ $g->id }}"
                                                    {{ isset($shift_karyawan) && ($shift_karyawan->employee_id ?? null) == $g->id ? 'selected' : '' }}>
                                                    {{ $g->nama_karyawan }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <p class="text-xs text-gray-500 mt-1">
                                            Posisi: <span class="font-medium" x-text="modal.posisi?.nama"></span>
                                        </p>
                                    </div>


                                    <div class="mb-2">
                                        <label for="jenis_hari_id"
                                            class="block text-sm font-medium text-gray-700 mb-1">Jenis
                                            Hari</label>
                                        <select name="jenis_hari_id" id="jenis_hari_id"
                                            class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-white">
                                            <option value="">-- Pilih Jenis Hari --</option>
                                            <template x-for="jh in jenisHaris" :key="jh.id">
                                                <option :value="jh.id" x-text="jh.nama"></option>
                                            </template>
                                        </select>

                                    </div>

                                    <div class="mb-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal</label>
                                        <input type="date" class="w-full border rounded px-3 py-2 bg-white"
                                            :value="tanggal" disabled>
                                    </div>

                                    <div class="mb-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Jam Mulai</label>
                                        <input type="time" name="jam_mulai"
                                            class="w-full border rounded px-3 py-2 bg-white" required>
                                    </div>
                                    <div class="mb-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Jam Selesai</label>
                                        <input type="time" name="jam_selesai"
                                            class="w-full border rounded px-3 py-2 bg-white" required>
                                    </div>

                                    <div class="mb-2">
                                        <label for="status"
                                            class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                        <select name="status" id="status" required
                                            class="w-full border rounded px-3 py-2 bg-white">
                                            <option value="">-- Pilih --</option>
                                            @php $stOld = old('status', $shift_karyawan->status ?? '') @endphp
                                            <option value="Penetapan" {{ $stOld === 'Penetapan' ? 'selected' : '' }}>
                                                Penetapan</option>
                                            <option value="Perubahan" {{ $stOld === 'Perubahan' ? 'selected' : '' }}>
                                                Perubahan</option>
                                            <option value="Tambahan" {{ $stOld === 'Tambahan' ? 'selected' : '' }}>
                                                Tambahan</option>
                                        </select>
                                    </div>

                                    <div class="mb-2 md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Wahana</label>
                                        <input type="text" class="w-full border rounded px-3 py-2 bg-gray-100"
                                            :value="modal.wahana?.nama_wahana ?? ''" disabled>
                                    </div>
                                </div>

                                <div class="mb-2">
                                    <label for="keterangan"
                                        class="block text-sm font-medium text-gray-700 mb-1">Keterangan</label>
                                    <textarea name="keterangan" id="keterangan" class="w-full border rounded px-3 py-2 bg-white"></textarea>
                                </div>

                                <div class="flex justify-end gap-2 pt-2">
                                    <button type="button" @click="showModal=false"
                                        class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300">
                                        Cancel
                                    </button>
                                    <button type="submit"
                                        class="px-6 py-2 bg-green-600 text-white font-semibold rounded-lg hover:bg-green-700">
                                        {{ isset($shift_karyawan) ? '💾 Update' : ' Process' }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <style>
        .sticky-header {
            position: sticky;
            top: 0;
            z-index: 20;
            backdrop-filter: blur(5px);
            background-color: rgba(255, 255, 255, 0.9);
        }

        table thead {
            position: sticky;
            top: 68px;
            z-index: 10;
        }

        .overflow-x-auto::-webkit-scrollbar {
            height: 8px;
        }

        .overflow-x-auto::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .overflow-x-auto::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 10px;
        }

        .overflow-x-auto::-webkit-scrollbar-thumb:hover {
            background: #a1a1a1;
        }

        tr {
            transition: background-color 0.2s ease;
        }
    </style>

    {{-- Alpine Component --}}
    <script>
        function kalenderPage() {
            return {
                unitId: '',
                tanggal: (new Date()).toISOString().slice(0, 10),
                wahanas: [],
                jenisHaris: [],
                assignments: {},

                // 🧩 tambahkan baris ini:
                crew_shift: JSON.parse(`{!! json_encode($crew_shift) !!}`),

                // ---- OFF state ----
                offs: [],
                offCandidates: [],
                offIds: [],
                newOffEmployeeIds: [],
                newOffNote: '',

                showModal: false,
                modal: {
                    wahana: null,
                    posisi: null
                },

                init() {
                    this.$watch('unitId', () => {
                        this.refreshData();
                        this.loadJenisHari();
                    });
                    this.$watch('tanggal', () => {
                        this.refreshData()
                    });
                    this.csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
                },

                async refreshData() {
                    this.wahanas = [];
                    this.assignments = {};
                    this.offs = [];
                    this.offCandidates = [];
                    this.offIds = [];
                    this.newOffEmployeeIds = [];

                    if (!this.unitId || !this.tanggal) return;

                    await this.loadWahana();
                    await this.loadAssignments();
                    await this.loadOffs();
                },

                async loadWahana() {
                    if (!this.unitId) return;
                    try {
                        const url = "{{ route('wahana.byUnit', ['unit' => 'UNIT_PLACEHOLDER']) }}".replace(
                            'UNIT_PLACEHOLDER', this.unitId);
                        const res = await fetch(url, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });
                        if (!res.ok) throw new Error('Gagal memuat wahana');
                        this.wahanas = await res.json();
                    } catch (e) {
                        console.error(e);
                        alert('Tidak dapat memuat daftar wahana untuk unit terpilih.');
                    }
                },

                async loadJenisHari() {
                    this.jenisHaris = [];
                    if (!this.unitId) return;

                    try {
                        const res = await fetch(`/get-jenis-hari/${this.unitId}`);
                        if (!res.ok) throw new Error('Gagal memuat jenis hari');
                        this.jenisHaris = await res.json();
                    } catch (e) {
                        console.error(e);
                        alert('Tidak dapat memuat jenis hari untuk unit terpilih.');
                    }
                },

                async loadAssignments() {
                    try {
                        const params = new URLSearchParams({
                            unit_id: this.unitId,
                            tanggal: this.tanggal
                        });
                        const res = await fetch("{{ route('shift_wahana.assignments') }}?" + params.toString(), {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });
                        if (!res.ok) throw new Error('Gagal memuat assignments');
                        const data = await res.json();
                        this.assignments = data.assignments || {};
                    } catch (e) {
                        console.error(e);
                        this.assignments = {};
                    }
                },

                async loadOffs() {
                    try {
                        const params = new URLSearchParams({
                            tanggal: this.tanggal,
                            unit_id: this.unitId
                        });
                        const res = await fetch("{{ route('off_days.index') }}?" + params.toString(), {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });
                        if (!res.ok) throw new Error('Gagal memuat OFF');
                        const data = await res.json();
                        this.offs = data.offs || [];
                        this.offCandidates = data.candidates || [];
                        this.offIds = data.off_ids || [];
                    } catch (e) {
                        console.error(e);
                        this.offs = [];
                        this.offCandidates = [];
                        this.offIds = [];
                    }
                },

                async addOffs() {
                    if (!this.newOffEmployeeIds.length) {
                        alert('Pilih minimal satu karyawan untuk ditandai OFF.');
                        return;
                    }
                    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

                    try {
                        const res = await fetch("{{ route('off_days.store') }}", {
                            method: 'POST',
                            credentials: 'same-origin',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': csrf,
                            },
                            body: JSON.stringify({
                                tanggal: this.tanggal,
                                employee_id: this.newOffEmployeeIds,
                                catatan: this.newOffNote || null,
                            })
                        });

                        if (!res.ok) throw new Error(await res.text() || 'Gagal menyimpan OFF');

                        await this.loadOffs();
                        await this.loadAssignments();
                        this.newOffEmployeeIds = [];
                        this.newOffNote = '';
                    } catch (e) {
                        console.error(e);
                        alert('Gagal menambahkan OFF.');
                    }
                },

                async deleteOff(offId) {
                    if (!confirm('Hapus status OFF ini?')) return;

                    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                    try {
                        const url = "{{ route('off_days.destroy', ['off' => 'OFF_ID']) }}".replace('OFF_ID', offId);
                        const res = await fetch(url, {
                            method: 'DELETE',
                            credentials: 'same-origin',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': csrf,
                            }
                        });

                        if (!res.ok) throw new Error('Gagal menghapus OFF');

                        await this.loadOffs();
                        await this.loadAssignments();
                    } catch (e) {
                        console.error(e);
                        alert('Gagal menghapus OFF.');
                    }
                },

                openModal(wahana, crew) {
                    if (!this.unitId) return alert('Pilih Unit Kerja dahulu.');
                    if (!this.tanggal) return alert('Isi tanggal dahulu.');
                    this.modal.wahana = wahana;
                    this.modal.posisi = crew;
                    this.showModal = true;
                },

                labelPosisi(p) {
                    const map = {
                        petugas_1: 'Petugas 1',
                        petugas_2: 'Petugas 2',
                        petugas_3: 'Petugas 3',
                        petugas_4: 'Petugas 4',
                        pengganti: 'Pengganti'
                    };
                    return map[p] || p;
                },

                displayPetugas(wahanaId, crewId) {
                    const w = this.assignments[wahanaId];
                    if (!w) return '-';
                    const slot = w[crewId];
                    return slot ? slot.name : '-';
                },

                async afterSaved() {
                    await this.loadAssignments();
                    this.showModal = false;
                },
            };
        }
    </script>


@endsection
