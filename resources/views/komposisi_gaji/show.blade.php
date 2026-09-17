@extends('layouts.app')

@section('content')
    <div class="py-10">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-md rounded-lg p-6">

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Karyawan</label>
                    <input type="text" value="{{ $komposisi->employee->nama_karyawan }}" readonly
                        class="w-full border-gray-300 rounded-md shadow-sm bg-gray-100">
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full border border-gray-200 table-auto">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-4 py-2 border">No</th>
                                <th class="px-4 py-2 border">Nama Komponen</th>
                                <th class="px-4 py-2 border text-right">Nilai</th>
                                <th class="px-4 py-2 border text-center">Jumlah Hari</th>
                                <th class="px-4 py-2 border text-right">Potongan</th>
                                <th class="px-4 py-2 border text-right">Total Nilai</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($details as $index => $detail)
                                <tr>
                                    <td class="px-4 py-2 border text-center">{{ $index + 1 }}</td>
                                    <td class="px-4 py-2 border">{{ $detail->komponen->nama_komponen }}</td>
                                    <td class="px-4 py-2 border text-right">{{ number_format($detail->nilai) }}</td>
                                    <td class="px-4 py-2 border text-center">{{ $detail->jumlah_hari }}</td>
                                    <td class="px-4 py-2 border text-right">{{ number_format($detail->potongan) }}</td>
                                    <td class="px-4 py-2 border text-right">
                                        {{ number_format($detail->nilai * $detail->jumlah_hari + $detail->potongan * $detail->jumlah_hari) }}
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

                <div class="mt-6 flex justify-end gap-3">
                    <a href="{{ route('komposisi_gaji.index') }}"
                        class="inline-flex items-center px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400 transition">
                        Back
                    </a>
                    <a href="{{ route('komposisi_gaji.edit', $komposisi->id) }}"
                        class="inline-flex items-center px-4 py-2 bg-yellow-500 text-white rounded hover:bg-yellow-600 transition">
                        Edit
                    </a>
                </div>

            </div>
        </div>
    </div>
@endsection
