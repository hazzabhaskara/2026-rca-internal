@extends('layouts.app')

@section('content')
    <div class="py-10">
        <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
            @php
                $themeColor = \App\Setting::get('theme_color', '#4F46E5');
            @endphp
            <div class="mx-auto py-6">
                <div class="bg-white shadow-lg rounded-xl p-6 border-t-4" style="border-color:{{ $themeColor }}">
                    <h2 class="text-2xl font-semibold mb-6">Pengajuan Show</h2>
                    <!-- Informasi Utama purchase Order -->
                    <div>

                        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                            <div>
                                <strong>No Pengajuan:</strong>
                                <p>{{ $pengajuan->no_pengajuan }}</p>
                            </div>
                            <div>
                                <strong>Tanggal Pengajuan:</strong>
                                <p>{{ \Carbon\Carbon::parse($pengajuan->tgl_pengajuan)->format('d M Y') }}</p>
                            </div>
                            <div>
                                <strong>Tanggal Proses:</strong>
                                <p>{{ \Carbon\Carbon::parse($pengajuan->tgl_proses)->format('d M Y') }}</p>
                            </div>
                            <div>
                                <strong>Rekening:</strong>
                                <p>{{ $pengajuan->rekening->no_rek ?? '-' }}</p>
                            </div>
                            <div>
                                <strong>Status:</strong>
                                <p>{{ $pengajuan->status ?? '-' }}</p>
                            </div>
                            <div>
                                <strong>Keterangan:</strong>
                                <p>{{ $pengajuan->keterangan ?? 'Tidak Ada' }}</p>
                            </div>
                        </div>

                        <!-- Detail Items -->
                        <h3 class="text-xl font-semibold mb-2">Pengajuan Detail</h3>
                        <div class="overflow-auto">
                            <table class="w-full border-collapse border text-sm whitespace-nowrap">
                                <thead
                                    class="bg-gradient-to-r bg-[{{ $themeColor }}]  to-blue-600 text-white text-sm font-semibold">
                                    <tr>
                                        <th class="border px-3 py-2">Account</th>
                                        <th class="border px-3 text-center py-2">Qty</th>
                                        <th class="border text-right px-3 py-2">Harga</th>
                                        <th class="border text-right px-3 py-2">Total</th>
                                        <th class="border text-center px-3 py-2">Uraian</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $subtotal = 0;
                                        $total = 0;

                                    @endphp

                                    @foreach ($pengajuan->details as $item)
                                        @php
                                            $qty = $item->qty;
                                            $harga = $item->harga;
                                            $subtotal = $qty * $harga;
                                            $total += $subtotal;
                                        @endphp
                                        <tr>
                                            <td class="border px-3 py-2">
                                                {{ $item->account->kode_akun }} - {{ $item->account->nama_akun }}
                                            </td>
                                            <td class="border px-3 py-2 text-center">{{ $item->qty }}</td>
                                            <td class="border px-3 py-2 text-right">{{ number_format($item->harga, 2) }}
                                            </td>
                                            <td class="border px-3 py-2 text-right">{{ number_format($subtotal, 2) }}
                                            </td>
                                            <td class="border px-3 py-2 text-center">{{ $item->uraian }}</td>
                                        </tr>
                                    @endforeach

                                </tbody>

                                <tfoot>
                                    <tr>
                                        <td colspan="2"></td>
                                        <td class="pr-3 text-right font-semibold">Total :</td>
                                        <td class="w-32 border rounded text-right px-2 py-1 bg-gray-100">
                                            {{ number_format($total, 2) }}
                                        </td>
                                    </tr>
                                </tfoot>

                            </table>
                        </div>
                    </div>


                    <!-- Tombol Kembali -->
                    <div class="px-6 py-4 border-t bg-gray-50 flex justify-between">
                        <a href="{{ route('pengajuan.edit', $pengajuan->id) }}"
                            class="inline-flex items-center px-4 py-2 bg-yellow-500 text-white rounded hover:bg-yellow-600 transition">
                            <i class="fas fa-edit mr-2"></i>Edit
                        </a>
                        <a href="{{ route('pengajuan.index') }}"
                            class="inline-flex items-center px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400 transition">
                            <i class="fas fa-arrow-left mr-2"></i>Kembali
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
