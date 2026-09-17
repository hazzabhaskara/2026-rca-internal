@extends('layouts.app')

@section('content')
    <div class="max-w-full mx-auto bg-white shadow-md rounded-xl p-6 mt-6">
        <h2 class="text-lg font-bold mb-4">Daftar Slip Gaji Non Staff</h2>

        <table class="min-w-full table-auto border border-gray-200">
            <thead>
                <tr class="bg-gray-100 text-left">
                    <th class="px-4 py-2 border">Nama Karyawan</th>
                    <th class="px-4 py-2 border">Periode</th>
                    <th class="px-4 py-2 border">Tanggal Pembayaran</th>
                    <th class="px-4 py-2 border text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($pembayarans as $pembayaran)
                    <tr>
                        <td class="px-4 py-2 border">{{ $pembayaran->employee->nama_karyawan }}</td>
                        <td class="px-4 py-2 border">
                            {{ $pembayaran->periode_awal }} s/d {{ $pembayaran->periode_akhir }}
                        </td>
                        <td class="px-4 py-2 border">{{ $pembayaran->tanggal_pembayaran }}</td>
                        <td class="px-4 py-2 border text-center space-x-2">
                            <a href="{{ route('slip.show', $pembayaran->id) }}"
                                class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm">Lihat</a>
                            <a href="{{ route('slip.download', $pembayaran->id) }}"
                                class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-sm">Download</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-4 text-center text-gray-500">
                            Belum ada slip gaji.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
