@extends('layouts.app')

@section('content')
    <div class="py-10">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-md rounded-lg p-6">
                @if (session('success'))
                    <div class="mb-4 text-green-600 font-semibold">
                        {{ session('success') }}
                    </div>
                @endif
                @if (session('error'))
                    <div class="mb-4 text-red-600 font-semibold">
                        {{ session('error') }}
                    </div>
                @endif

                {{-- Form pilih departemen --}}
                <div class="flex items-end mb-4">
                    <form method="GET" action="{{ route('departemen.assign') }}" class="flex-grow">
                        <label class="block mb-2 font-medium">Pilih Departemen</label>
                        <select name="departemen_id" class="w-full border rounded p-2" onchange="this.form.submit()">
                            <option value="">-- Pilih Departemen --</option>
                            @foreach ($departemenList as $dep)
                                <option value="{{ $dep->id }}"
                                    {{ isset($departemenId) && $departemenId == $dep->id ? 'selected' : '' }}>
                                    {{ $dep->deskripsi }}
                                </option>
                            @endforeach
                        </select>
                    </form>
                    
                    <button type="button" onclick="document.getElementById('fileModal').classList.remove('hidden')" class="ml-4 bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-50 h-10 flex items-center whitespace-nowrap shadow-sm transition-colors font-medium">
                        <i class="fas fa-file-alt mr-2 text-blue-500"></i> File Aksi
                    </button>
                </div>

                @if ($departemenId)
                    <form method="POST" action="{{ route('departemen.assign.store') }}" id="assign-form">
                        @csrf
                        <input type="hidden" name="departemen_id" value="{{ $departemenId }}">
                        <input type="hidden" name="_method" value="POST" id="form-method">

                        <div class="grid grid-cols-12 gap-4 items-center">
                            {{-- Akun yang Belum Diassign --}}
                            <div class="col-span-5">
                                <h3 class="font-semibold mb-2">Akun Non-Department</h3>
                                <ul class="border p-2 rounded h-64 overflow-y-auto bg-gray-50" id="left-list">
                                    @forelse ($nonDepartemenAccounts as $akun)
                                        <li class="py-1">
                                            <label class="flex items-center space-x-2 cursor-pointer">
                                                <input type="checkbox" name="akun_ids[]" value="{{ $akun->id }}" class="rounded text-blue-500 left-checkbox">
                                                <span class="text-sm">{{ $akun->kode_akun }} {{ $akun->nama_akun }}</span>
                                            </label>
                                        </li>
                                    @empty
                                        <li class="text-gray-400 text-sm">Semua akun sudah didepartemenkan ke departemen ini</li>
                                    @endforelse
                                </ul>
                            </div>

                            {{-- Tombol Aksi di Tengah --}}
                            <div class="col-span-2 flex flex-col space-y-3 px-2">
                                <button type="button" onclick="selectAllLeft()" class="w-full bg-blue-100 text-blue-700 py-2 rounded shadow-sm text-sm font-semibold hover:bg-blue-200 transition-colors">
                                    Select All
                                </button>
                                <button type="button" onclick="submitAssign()" class="w-full bg-green-100 text-green-700 py-2 rounded shadow-sm text-sm font-semibold hover:bg-green-200 transition-colors">
                                    Assign &rarr;
                                </button>
                                <button type="button" onclick="submitRemove()" class="w-full bg-yellow-100 text-yellow-700 py-2 rounded shadow-sm text-sm font-semibold hover:bg-yellow-200 transition-colors">
                                    &larr; Remove
                                </button>
                                <button type="button" onclick="submitRemoveAll()" class="w-full bg-red-100 text-red-700 py-2 rounded shadow-sm text-sm font-semibold hover:bg-red-200 transition-colors">
                                    Remove All
                                </button>
                            </div>

                            {{-- Akun yang Sudah Diassign --}}
                            <div class="col-span-5">
                                <h3 class="font-semibold mb-2">Akun Department</h3>
                                <ul class="border p-2 rounded h-64 overflow-y-auto bg-gray-50" id="right-list">
                                    @forelse ($departemenAccounts as $entry)
                                        <li class="py-1">
                                            <label class="flex items-center space-x-2 cursor-pointer">
                                                <input type="checkbox" name="remove_akun_ids[]" value="{{ $entry->id }}" class="rounded text-blue-500 right-checkbox">
                                                <span class="text-sm">
                                                    {{ $entry->chartOfAccount->kode_akun ?? '-' }}
                                                    {{ $entry->chartOfAccount->nama_akun ?? '-' }}
                                                    <span class="text-xs text-gray-500">- {{ $entry->departemen->deskripsi ?? '-' }}</span>
                                                </span>
                                            </label>
                                        </li>
                                    @empty
                                        <li class="text-gray-400 text-sm">Belum ada akun ditempatkan ke departemen ini</li>
                                    @endforelse
                                </ul>
                            </div>
                        </div>

                        <div class="mt-6">
                            <a href="{{ route('departemen.index') }}"
                                class="inline-flex items-center px-6 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 text-sm font-medium rounded-md shadow-sm transition-colors">
                                Back
                            </a>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>

    {{-- Script Select All / Remove All --}}
    <script>
        function selectAllLeft() {
            document.querySelectorAll('#left-list input[type="checkbox"]').forEach(cb => cb.checked = true);
        }

        function submitAssign() {
            const form = document.getElementById('assign-form');
            form.action = "{{ route('departemen.assign.store') }}";
            document.getElementById('form-method').value = "POST";
            
            // Validate if anything is checked
            const checked = document.querySelectorAll('#left-list input[type="checkbox"]:checked');
            if (checked.length === 0) {
                alert('Pilih setidaknya satu akun dari kolom Akun Non-Department untuk di-assign.');
                return;
            }
            form.submit();
        }

        function submitRemove() {
            const form = document.getElementById('assign-form');
            form.action = "{{ route('departemen.assign.destroyBulk') }}";
            document.getElementById('form-method').value = "DELETE";

            // Hapus value name dari left-checkbox agar tidak dikirim
            document.querySelectorAll('.left-checkbox').forEach(cb => cb.removeAttribute('name'));
            
            const selected = document.querySelectorAll('.right-checkbox:checked');
            if(selected.length === 0) {
                alert('Pilih minimal satu akun untuk dihapus.');
                return;
            }
            if(confirm('Apakah Anda yakin ingin menghapus akun yang dipilih dari departemen ini?')) {
                form.submit();
            }
        }

        function submitRemoveAll() {
            const form = document.getElementById('assign-form');
            form.action = "{{ route('departemen.assign.destroyBulk') }}";
            document.getElementById('form-method').value = "DELETE";

            // Hapus value name dari left-checkbox agar tidak dikirim
            document.querySelectorAll('.left-checkbox').forEach(cb => cb.removeAttribute('name'));
            
            // Cek semua right-checkbox
            const checkboxes = document.querySelectorAll('.right-checkbox');
            if(checkboxes.length === 0) {
                alert('Tidak ada akun yang bisa dihapus.');
                return;
            }
            
            checkboxes.forEach(cb => cb.checked = true);

            if(confirm('Apakah Anda yakin ingin menghapus SEMUA akun dari departemen ini?')) {
                form.submit();
            }
        }
    </script>
    
    <!-- Modal File Aksi -->
    <div id="fileModal" class="fixed inset-0 z-50 flex items-center justify-center hidden bg-black bg-opacity-50 transition-opacity">
        <div class="bg-white rounded-xl p-6 w-full max-w-sm shadow-xl">
            <h3 class="text-lg font-bold mb-6 text-gray-800 flex items-center">
                <i class="fas fa-file-alt text-blue-500 mr-2 text-xl"></i> File Aksi
            </h3>
            
            <div class="flex flex-col space-y-4 mb-6">
                <a href="{{ route('export.template-departemen-akun') }}" class="text-gray-700 hover:text-blue-600 flex items-center text-sm font-medium">
                    <i class="fas fa-file-excel text-green-600 mr-3 w-5 text-center text-lg"></i> Download Template Excel
                </a>
                
                <a href="{{ route('export.departemen-akun') }}" class="text-gray-700 hover:text-blue-600 flex items-center text-sm font-medium">
                    <i class="fas fa-file-export text-blue-500 mr-3 w-5 text-center text-lg"></i> Export Account
                </a>
            </div>

            <form action="{{ route('import.departemen-akun') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Import File Excel:</label>
                    <input type="file" name="file" class="block w-full text-sm text-gray-500 border border-gray-300 rounded p-1" required accept=".csv, .xlsx, .xls">
                </div>
                
                <button type="submit" class="w-full py-2 bg-green-500 text-white rounded font-semibold hover:bg-green-600 flex items-center justify-center transition-colors">
                    <i class="fas fa-file-import mr-2"></i> Import
                </button>
            </form>
            
            <div class="flex justify-end mt-4">
                <button type="button" onclick="document.getElementById('fileModal').classList.add('hidden')" class="text-gray-500 hover:text-gray-800 text-sm font-medium">
                    Tutup
                </button>
            </div>
        </div>
    </div>
@endsection
