@extends('layouts.app')
@section('content')
    <div class="py-10">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-md rounded-lg p-6">
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-700 mb-2">Informasi Karyawan</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-800">
                        <div><strong>Nama Karyawan:</strong> {{ $pembayaran->employee->nama_karyawan }}</div>
                        <div><strong>Periode:</strong> {{ $pembayaran->periode_awal }} s.d {{ $pembayaran->periode_akhir }}
                        </div>
                        <div><strong>Tanggal Pembayaran:</strong> {{ $pembayaran->tanggal_pembayaran }}</div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full border border-gray-200 table-auto">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-4 py-2 border">No</th>
                                <th class="px-4 py-2 border">Nama Komponen</th>
                                <th class="px-4 py-2 border">Nilai</th>
                                <th class="px-4 py-2 border">Jumlah Hari</th>
                                <th class="px-4 py-2 border">Potongan</th>
                                <th class="px-4 py-2 border">Total Nilai</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($details as $index => $detail)
                                <tr>
                                    <td class="px-4 py-2 border text-center">{{ $index + 1 }}</td>
                                    <td class="px-4 py-2 border">{{ $detail->komponen->nama_komponen }}</td>
                                    <td class="px-4 py-2 border text-right">{{ number_format($detail->nilai, 2) }}</td>
                                    <td class="px-4 py-2 border text-center">{{ $detail->jumlah_hari }}</td>
                                    <td class="px-4 py-2 border text-right">{{ number_format($detail->potongan, 2) }}</td>
                                    <td class="px-4 py-2 border text-right">
                                        {{ number_format($detail->nilai * $detail->jumlah_hari + $detail->potongan * $detail->jumlah_hari, 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="bg-gray-100 font-semibold">
                                <td colspan="5" class="px-4 py-2 border text-right">Total Keseluruhan</td>
                                <td class="px-4 py-2 border text-right">
                                    {{ number_format(
                                        $details->sum(function ($detail) {
                                            return $detail->nilai * $detail->jumlah_hari - $detail->potongan * $detail->jumlah_hari;
                                        }),
                                        2,
                                    ) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>


                <div class="mt-6">
                    <a href="{{ route('pembayaran_gaji_nonstaff.index') }}" class="text-blue-600 hover:underline">← Kembali
                        ke
                        Daftar</a>
                </div>

            </div>
        </div>
    </div>
@endsection
