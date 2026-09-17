@extends('layouts.app')

@section('content')
    <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-2xl font-semibold text-gray-800 mb-2">Detail General Journal</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm text-gray-700">
                <div>
                    <strong>Source:</strong>
                    <p>{{ $journal->source }}</p>
                </div>
                <div>
                    <strong>Tanggal:</strong>
                    <p>{{ \Carbon\Carbon::parse($journal->tanggal)->format('d M Y') }}</p>
                </div>
                <div>
                    <strong>Comment:</strong>
                    <p>{{ $journal->comment ?? '-' }}</p>
                </div>
            </div>
        </div>

        <!-- Tabel Detail -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Detail Transaksi</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-left border-collapse">
                    <thead class="bg-gray-100 text-gray-700">
                        <tr>
                            <th class="border px-4 py-2">Kode Akun</th>
                            <th class="border px-4 py-2 text-right">Debit</th>
                            <th class="border px-4 py-2 text-right">Kredit</th>
                            <th class="border px-4 py-2">Comment</th>
                            @can('specpose.access')
                                <th class="border px-4 py-2">Specpose</th>
                            @endcan
                        </tr>
                    </thead>
                    @php
                        $totalDebit = 0;
                        $totalKredit = 0;
                    @endphp

                    <tbody class="bg-white text-gray-700">
                        @foreach ($journal->details as $detail)
                            @php
                                $totalDebit += $detail->debits;
                                $totalKredit += $detail->credits;
                            @endphp

                            <tr>
                                <td class="border px-4 py-2">
                                    @if ($detail->departemen_akun_id && $detail->departemenAkun && $detail->departemenAkun->departemen)
                                        {{ $detail->kode_akun }} -
                                        {{ $detail->departemenAkun->departemen->kode ?? '-' }} -
                                        {{ $detail->chartOfAccount->nama_akun ?? '-' }} -
                                        {{ $detail->departemenAkun->departemen->deskripsi ?? '-' }}
                                    @else
                                        {{ $detail->kode_akun }} -
                                        {{ $detail->chartOfAccount->nama_akun ?? '-' }}
                                    @endif
                                </td>
                                <td class="border px-4 py-2 text-right">
                                    {{ number_format($detail->debits, 2, ',', '.') }}
                                </td>
                                <td class="border px-4 py-2 text-right">
                                    {{ number_format($detail->credits, 2, ',', '.') }}
                                </td>
                                <td class="border px-4 py-2">
                                    {{ $detail->comment ?? '-' }}
                                </td>
                                @can('specpose.access')
                                    <td class="border px-4 py-2">
                                        {{ $detail->project->nama_project ?? 'Tidak Ada' }}
                                    </td>
                                @endcan
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-100 font-bold text-gray-800">
                        <tr>
                            <td class="border px-4 py-2 text-right">TOTAL</td>
                            <td class="border px-4 py-2 text-right">{{ number_format($totalDebit, 2, ',', '.') }}</td>
                            <td class="border px-4 py-2 text-right">{{ number_format($totalKredit, 2, ',', '.') }}</td>
                            <td class="border px-4 py-2"></td>
                            <td></td>
                        </tr>
                    </tfoot>


                </table>
            </div>
        </div>

        <!-- Tombol Kembali -->
        <div class="mt-8 flex flex-col sm:flex-row justify-between items-center gap-3">
            <a href="{{ route('journal_entry.index') }}"
                class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 rounded-md text-sm font-medium hover:bg-gray-200 transition">
                <i class="fas fa-arrow-left mr-2"></i> Kembali
            </a>

            <div class="flex gap-3">
                <a href="{{ route('journal_entry.edit', $journal->id) }}"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md text-sm font-medium hover:bg-blue-700 transition">
                    <i class="fas fa-edit mr-2"></i> Edit
                </a>

                {{-- <form id="delete-form-{{ $journal->id }}" action="{{ route('journal_entry.destroy', $journal->id) }}"
                    method="POST" style="display: none;">
                    @csrf
                    @method('DELETE')
                </form> --}}

                {{-- <button type="button" onclick="confirmDelete({{ $journal->id }})"
                    class="text-red-500 hover:text-red-700 p-2 rounded-full hover:bg-red-50 transition-colors"
                    title="Delete">
                    <i class="fas fa-trash"></i>
                </button> --}}
            </div>
        </div>
    </div>
@endsection
