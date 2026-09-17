@extends('layouts.app')

@section('content')

    @if (session('errors_import'))
        <div class="alert alert-danger">
            <strong>Beberapa baris gagal diimport:</strong>
            <ul>
                @foreach (session('errors_import') as $error)
                    <li>Baris {{ $error['baris'] }}:
                        <ul>
                            @foreach ($error as $field => $message)
                                @if ($field != 'baris' && $message)
                                    <li>{{ $message }}</li>
                                @endif
                            @endforeach
                        </ul>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
    <div class="py-10">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden border border-gray-200">
                <!-- Header & Controls -->
                @php
                    $themeColor = \App\Setting::get('theme_color', '#4F46E5');
                @endphp

                <div class="sticky top-0 z-20 px-6 py-5 border-b border-gray-100 flex justify-between items-center"
                    style="background: {{ $themeColor }};">
                    <h3 class="text-xl font-bold text-white flex items-center">
                        <i class="fas fa-list mr-3 text-white text-xl"></i>
                        Employee
                    </h3>
                    <div class="flex flex-wrap gap-2">
                        <!-- Filter Button -->
                        <button onclick="document.getElementById('filterPanel').classList.toggle('hidden')"
                            class="inline-flex items-center px-3 py-2 text-sm rounded-md border border-gray-300 bg-white text-gray-700 hover:bg-gray-50">
                            <i class="fas fa-filter text-gray-500 mr-2"></i> Filter
                        </button>
                        <!-- File Button -->
                        <button onclick="document.getElementById('fileModal').classList.remove('hidden')"
                            class="inline-flex items-center px-3 py-2 text-sm rounded-md border border-gray-300 bg-white text-gray-700 hover:bg-gray-50">
                            <i class="fas fa-file-export text-blue-500 mr-2"></i> File
                        </button>
                        <!-- Add Button -->
                        @can('employee.create')
                            <a href="{{ route('employee.create') }}"
                                class="inline-flex items-center px-5 py-2.5 bg-white text-indigo-600 font-semibold rounded-lg shadow hover:bg-gray-100 transition-all">
                                <i class="fas fa-plus mr-2"></i> Add Employee
                            </a>
                        @endcan

                    </div>
                </div>

                <!-- Filter Panel -->
                <div id="filterPanel"
                    class="{{ request('search') || request('filter_tipe') ? '' : 'hidden' }} px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <form method="GET" action="{{ route('employee.index') }}">
                        <div class="flex flex-wrap gap-4">
                            <!-- Search Input -->
                            <div class="relative">
                                <input type="text" name="search" value="{{ request('search') }}"
                                    placeholder="Cari level,nama, dan tempat lahir"
                                    class="pl-10 pr-4 py-2 rounded-lg border border-gray-300 text-sm w-64 shadow-sm" />
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <i class="fas fa-search text-gray-400 text-sm"></i>
                                </div>
                            </div>

                            <!-- Filter Tipe Akun -->
                            <select name="filter_unit"
                                class="py-2 px-3 border rounded-lg text-sm border-gray-300 shadow-sm">
                                <option value="">Semua Unit</option>
                                @foreach ($unit as $tipe)
                                    <option value="{{ $tipe }}"
                                        {{ request('filter_unit') == $tipe ? 'selected' : '' }}>
                                        {{ $tipe }}
                                    </option>
                                @endforeach
                            </select>
                            <select name="filter_tipe"
                                class="py-2 px-3 border rounded-lg text-sm border-gray-300 shadow-sm">
                                <option value="">Semua Level Karyawan</option>
                                @foreach ($level_karyawan as $tipe)
                                    <option value="{{ $tipe }}"
                                        {{ request('filter_tipe') == $tipe ? 'selected' : '' }}>
                                        {{ $tipe }}
                                    </option>
                                @endforeach
                            </select>



                            <select name="filter_jenis_kelamin"
                                class="py-2 px-3 border rounded-lg text-sm border-gray-300 shadow-sm">
                                <option value="">Jenis Kelamin</option>
                                <option value="Laki Laki"
                                    {{ request('filter_jenis_kelamin') == 'Laki Laki' ? 'selected' : '' }}>
                                    Laki Laki
                                </option>
                                <option value="Perempuan"
                                    {{ request('filter_jenis_kelamin') == 'Perempuan' ? 'selected' : '' }}>
                                    Perempuan
                                </option>
                            </select>


                            <!-- Tombol Filter -->
                            <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-blue-500 text-white rounded shadow-sm hover:bg-blue-600 text-sm">
                                <i class="fas fa-search mr-1"></i> Filter
                            </button>

                            <!-- Tombol Reset -->
                            <a href="{{ route('employee.index') }}"
                                class="inline-flex items-center px-3 py-2 text-sm rounded-md border border-gray-300 bg-white text-gray-700 hover:bg-gray-50">
                                <i class="fas fa-times mr-1 text-gray-400"></i> Reset
                            </a>
                        </div>
                    </form>
                </div>

                <!-- Table Container -->
                <div class="relative overflow-x-auto" style="max-height: calc(100vh - 250px); overflow-y: auto;">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50 sticky top-0 z-10">
                            <tr>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    #
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Nama Sesuai KTP
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Nama Panggilan
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    NIK
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Tempat Lahir
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Tanggal Lahir
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Jenis Kelamin
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Golongan Darah
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Tinggi Badan
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Alamat
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Telepon
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Email
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Agama
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    kewarganegaraan
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status Pernikahan
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Kode PTKP
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Kode Jabatan
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Tanggal Masuk
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Tanggal Keluar
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status Pegawai
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Level Kepegawaian
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Unit Kerja
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    RFID Code
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Sertifikat
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Photo
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Foto KTP
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($employees as $Karyawan)
                                <tr class="hover:bg-gray-50 transition-colors duration-150">
                                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500">
                                        {{ $loop->iteration }}
                                    </td>
                                    <td class="px-4 py-2 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $Karyawan->nama_karyawan }}</div>
                                    </td>
                                    <td class="px-4 py-2 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $Karyawan->nama_panggilan }}</div>
                                    </td>
                                    <td class="px-4 py-2 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $Karyawan->nik }}</div>
                                    </td>
                                    <td class="px-4 py-2 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $Karyawan->tempat_lahir }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-2 text-center whitespace-nowrap">
                                        {{ $Karyawan->tanggal_lahir ? \Carbon\Carbon::parse($Karyawan->tanggal_lahir)->format('d M Y') : '-' }}
                                    </td>
                                    <td class="px-4 py-2 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $Karyawan->jenis_kelamin }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-2 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $Karyawan->golongan_darah }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-2 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $Karyawan->tinggi_badan }}</div>
                                    </td>
                                    <td class="px-4 py-2 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $Karyawan->alamat }}</div>
                                    </td>
                                    <td class="px-4 py-2 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $Karyawan->telepon }}</div>
                                    </td>
                                    <td class="px-4 py-2 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $Karyawan->email }}</div>
                                    </td>
                                    <td class="px-4 py-2 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $Karyawan->agama }}</div>
                                    </td>
                                    <td class="px-4 py-2 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $Karyawan->kewarganegaraan }}</div>
                                    </td>
                                    <td class="px-4 py-2 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $Karyawan->status_pernikahan }}</div>
                                    </td>
                                    <td class="px-4 py-2 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $Karyawan->ptkp->nama ?? '-' }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-2 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $Karyawan->jabatan->nama_jabatan ?? '-' }}</div>
                                    </td>
                                    <td class="px-4 py-2 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            {{ $Karyawan->tanggal_masuk ? \Carbon\Carbon::parse($Karyawan->tanggal_masuk)->format('d M Y') : '-' }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-2 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            {{ $Karyawan->tanggal_keluar ? \Carbon\Carbon::parse($Karyawan->tanggal_keluar)->format('d M Y') : '-' }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-2 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $Karyawan->status_pegawai }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-2 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            {{ $Karyawan->levelKaryawan->nama_level ?? '-' }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-2 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            {{ $Karyawan->unitKerja->nama_unit ?? '-' }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-2 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            {{ $Karyawan->rfid_code ?? '-' }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-2 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $Karyawan->sertifikat }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-2 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            @if ($Karyawan->photo)
                                                <img src="{{ asset('storage/' . $Karyawan->photo) }}" alt="Foto"
                                                    class="w-16 h-16 object-cover rounded">
                                            @else
                                                <span class="text-gray-400 italic">Tidak ada foto</span>
                                            @endif
                                        </div>
                                    </td>

                                    <td class="px-4 py-2 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            @if ($Karyawan->foto_ktp)
                                                <img src="{{ asset('storage/' . $Karyawan->foto_ktp) }}" alt="Foto KTP"
                                                    class="w-16 h-16 object-cover rounded">
                                            @else
                                                <span class="text-gray-400 italic">Tidak ada foto KTP</span>
                                            @endif
                                        </div>
                                    </td>


                                    <td class="px-4 py-2 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex justify-end space-x-3">
                                            @can('employee.view')
                                                <a href="{{ route('employee.show', $Karyawan->id) }}"
                                                    class="text-blue-500 hover:text-blue-700 p-2 rounded-full hover:bg-blue-50 transition-colors"
                                                    title="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            @endcan

                                            @can('employee.update')
                                                <a href="{{ route('employee.edit', $Karyawan->id) }}"
                                                    class="text-yellow-500 hover:text-yellow-700 p-2 rounded-full hover:bg-yellow-50 transition-colors"
                                                    title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            @endcan

                                            @can('employee.delete')
                                                <form id="delete-form-{{ $Karyawan->id }}"
                                                    action="{{ route('employee.destroy', $Karyawan->id) }}" method="POST"
                                                    style="display: none;">
                                                    @csrf
                                                    @method('DELETE')
                                                </form>

                                                <button type="button" onclick="confirmDelete({{ $Karyawan->id }})"
                                                    class="text-red-500 hover:text-red-700 p-2 rounded-full hover:bg-red-50 transition-colors"
                                                    title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            @endcan

                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="17" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center justify-center text-gray-400">
                                            <i class="fas fa-box-open text-4xl mb-4"></i>
                                            <p class="text-lg font-medium text-gray-500">Belum ada data karyawan</p>
                                            <a href="{{ route('employee.create') }}"
                                                class="mt-4 inline-flex items-center px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition">
                                                <i class="fas fa-plus mr-2"></i> Tambah
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <!-- File Modal -->
                <div id="fileModal"
                    class="fixed inset-0 z-50 hidden bg-black bg-opacity-30 backdrop-blur-sm flex items-center justify-center">
                    <div class="bg-white rounded-xl shadow-lg p-6 w-full max-w-md">
                        <h3 class="text-lg font-semibold text-gray-700 mb-4 flex items-center">
                            <i class="fas fa-file-alt mr-2 text-blue-400"></i> File Aksi
                        </h3>
                        <div class="space-y-3 text-sm text-gray-700">
                            <a href="{{ asset('template/template_import_employee.xlsx') }}" download
                                class="block hover:bg-gray-50 p-2 rounded-lg">
                                <i class="fas fa-file-excel mr-2 text-green-600"></i> Download Template Excel
                            </a>
                            <a href="{{ route('export.Employee') }}" class="block hover:bg-gray-50 p-2 rounded-lg">
                                <i class="fas fa-file-download mr-2 text-blue-500"></i> Export
                            </a>
                            <form action="{{ route('import.Employee') }}" method="POST" enctype="multipart/form-data"
                                class="space-y-2">
                                @csrf
                                <label class="block text-sm font-medium text-gray-700">Import File Excel:</label>
                                <input type="file" name="file"
                                    class="block w-full text-sm border rounded px-2 py-1" required>
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

                <!-- Simple Pagination -->
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-center">
                    {{ $employees->links() }}
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Improved sticky header implementation */
        .sticky-header {
            position: sticky;
            top: 0;
            z-index: 20;
            backdrop-filter: blur(5px);
            background-color: rgba(255, 255, 255, 0.9);
        }

        /* Table header sticky positioning */
        table thead {
            position: sticky;
            top: 68px;
            /* Height of the sticky-header */
            z-index: 10;
        }

        /* Beautiful scrollbar */
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

        /* Smooth transitions */
        tr {
            transition: background-color 0.2s ease;
        }
    </style>
    <script>
        document.getElementById('menu-button').addEventListener('click', function() {
            document.getElementById('dropdown-menu').classList.toggle('hidden');
        });

        window.addEventListener('click', function(e) {
            const button = document.getElementById('menu-button');
            const menu = document.getElementById('dropdown-menu');
            if (!button.contains(e.target) && !menu.contains(e.target)) {
                menu.classList.add('hidden');
            }
        });
    </script>
    <script>
        const searchInput = document.getElementById('searchInput');
        const filterTipeAkun = document.getElementById('filterTipeAkun');
        const rows = document.querySelectorAll('tbody tr');

        function filterTable() {
            const searchValue = searchInput.value.toLowerCase();
            const tipeValue = filterTipeAkun.value.toLowerCase();

            rows.forEach(row => {
                const rowText = row.innerText.toLowerCase();
                const tipeCell = row.querySelectorAll('td')[3]?.innerText.toLowerCase(); // kolom ke-3: tipe akun

                const matchSearch = rowText.includes(searchValue);
                const matchTipe = tipeValue === '' || tipeCell === tipeValue;

                row.style.display = (matchSearch && matchTipe) ? '' : 'none';
            });
        }

        searchInput.addEventListener('keyup', filterTable);
        filterTipeAkun.addEventListener('change', filterTable);
    </script>
@endsection
