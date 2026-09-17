@extends('layouts.app')

@section('content')
    <div class="max-w-full mx-auto py-6 sm:px-6 lg:px-8">
        <div class="bg-white shadow-md rounded px-6 py-4">

            <div>
                <ul class="flex border-b mb-4 space-x-4 text-sm font-medium text-gray-600">
                    <li><a href="{{ route('trial_balance.export', ['end_date' => $tanggalAkhir, 'format' => 'excel']) }}">Export
                            to Excel</a></li>
                    <li><a href="{{ route('trial_balance.export', ['end_date' => $tanggalAkhir, 'format' => 'pdf']) }}">Export
                            to PDF</a></li>
                    {{-- <li><a href="#pricing" class="tab-link">Print</a></li> --}}
                    <li><a onclick="document.getElementById('fileModify').classList.toggle('hidden')"
                            class="tab-link">Modify</a>
                    </li>
                    <li><a href="#linked" class="tab-link"></a></li>
                </ul>
            </div>
            <div id="fileModify"
                class="fixed inset-0 z-50 hidden bg-black bg-opacity-30 backdrop-blur-sm flex items-center justify-center">
                <div class="bg-white rounded-xl shadow-lg p-6 w-full max-w-7xl">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4 flex items-center">
                        <i class="fas fa-file-alt mr-2 text-blue-400"></i> File Aksi
                    </h3>
                    <div class="space-y-3 text-sm text-gray-700">
                        <form method="GET" action="{{ route('trial_balance.trial_balance_report') }}" class="mb-4">
                            <div class="flex items-center space-x-4">
                                <label for="end_date">Sampai Tanggal:</label>
                                <input type="date" name="end_date" id="end_date"
                                    value="{{ request('end_date') ?? \Carbon\Carbon::now()->toDateString() }}"
                                    class="border border-gray-300 rounded px-2 py-1">
                                <button type="submit"
                                    class="bg-blue-500 text-white px-4 py-1 rounded hover:bg-blue-600">Filter</button>
                            </div>
                        </form>
                    </div>
                    <div class="mt-4 text-right">
                        <button onclick="document.getElementById('fileModify').classList.add('hidden')"
                            class="text-sm text-gray-500 hover:text-gray-700">Tutup</button>
                    </div>
                </div>
            </div>
            <h2 class="text-2xl font-bold mb-4">Trial Balance</h2>



            <table class="w-full table-auto border border-collapse mt-4 text-sm">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="border px-3 py-2 text-left">Account</th>
                        <th class="border px-3 py-2 text-right">Debet</th>
                        <th class="border px-3 py-2 text-right">Kredit</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $totalDebet = 0;
                        $totalKredit = 0;
                    @endphp
                    @forelse ($trialBalances as $account)
                        @php
                            $debet = $account['saldo_debit'];
                            $kredit = $account['saldo_kredit'];
                            $totalDebet += $debet;
                            $totalKredit += $kredit;
                        @endphp
                        <tr>
                            <td class="border px-3 py-1">
                                <a href="{{ route('buku_besar.buku_besar_report', [
                                    'start_date' => request('start_date') ?? \Carbon\Carbon::parse($tanggalAkhir)->startOfYear()->toDateString(),
                                    'end_date' => request('end_date') ?? $tanggalAkhir,
                                    'selected_accounts' => $account['kode_akun'],
                                ]) }}"
                                    class="no-link">
                                    {{ $account['kode_akun'] }} - {{ $account['nama_akun'] }}
                                </a>

                            </td>


                            <td class="border px-3 py-1 text-right">{{ number_format($debet, 2) }}</td>
                            <td class="border px-3 py-1 text-right">{{ number_format($kredit, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="border px-3 py-2 text-center">Tidak ada data</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="font-semibold bg-gray-200">
                        <td colspan="1" class="border px-3 py-2 text-right">Total</td>
                        <td class="border px-3 py-2 text-right">{{ number_format($totalDebet, 2) }}</td>
                        <td class="border px-3 py-2 text-right">{{ number_format($totalKredit, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    <style>
        .no-link {
            color: inherit;
            /* ikut warna teks normal */
            text-decoration: none;
            /* hilangkan underline */
            cursor: pointer;
            /* tetap kelihatan bisa diklik */
        }

        .no-link:hover {
            background-color: #f3f4f6;
            /* opsional: efek hover */
        }
    </style>
@endsection
