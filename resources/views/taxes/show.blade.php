@extends('layouts.app')

@section('content')
    <div class="py-10">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow rounded-lg p-6">
                <!-- Informasi Klasifikasi Akun -->
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4 border-b pb-2">Informasi Taxes</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-700">

                        <div class="card">
                            <div class="card-body">
                                <p><strong>Bulan:</strong> {{ $tax->bulan }}</p>
                                <p><strong>Tahun:</strong> {{ $tax->tahun }}</p>
                                <p><strong>Jenis Pajak:</strong> {{ $tax->jenis_pajak }}</p>
                                <p><strong>Jenis Dokumen:</strong> {{ $tax->jenis_dokumen }}</p>
                                <p><strong>File:</strong>
                                    @if ($tax->file_path)
                                        <a href="{{ asset('storage/' . $tax->file_path) }}" target="_blank">Lihat File</a>
                                    @else
                                        Tidak ada file
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="px-6 py-4 border-t bg-gray-50 flex justify-between">
                <a href="{{ route('taxes.edit', $tax->id) }}"
                    class="inline-flex items-center px-4 py-2 bg-yellow-500 text-white rounded hover:bg-yellow-600 transition">
                    <i class="fas fa-edit mr-2"></i>Edit
                </a>
                <a href="{{ route('taxes.index') }}"
                    class="inline-flex items-center px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400 transition">
                    <i class="fas fa-arrow-left mr-2"></i>Kembali
                </a>
            </div>
        </div>
    </div>
@endsection
