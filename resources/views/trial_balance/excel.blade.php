<div class="max-w-full mx-auto py-6 sm:px-6 lg:px-8">
    <div class="bg-white shadow-md rounded px-6 py-4">

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
                        <td class="border px-3 py-1">{{ $account['kode_akun'] }} - {{ $account['nama_akun'] }}</td>
                        <td align="right" class="border px-3 py-1">{{ number_format($debet, 2) }}</td>
                        <td align="right" class="border px-3 py-1">{{ number_format($kredit, 2) }}</td>
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
                    <td align="right" class="border px-3 py-2">{{ number_format($totalDebet, 2) }}</td>
                    <td align="right" class="border px-3 py-2">{{ number_format($totalKredit, 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
