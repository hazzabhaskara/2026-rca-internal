<table border="1">
    <thead>
        <tr>
            <th>Kode Akun</th>
            <th>Nama Akun</th>
            @foreach ($departemens as $dept)
                <th>{{ $dept->deskripsi }}</th>
            @endforeach
            <th>Total</th>
        </tr>
    </thead>
    <tbody>
        {{-- Pendapatan --}}
        <tr>
            <td colspan="{{ 3 + count($departemens) }}"><strong>PENDAPATAN</strong></td>
        </tr>
        @foreach ($incomeStatement as $row)
            @if (strtolower($row['tipe_akun']) === 'pendapatan')
                <tr>
                    <td>{{ $row['kode_akun'] }}</td>
                    <td>{{ $row['nama_akun'] }}</td>
                    @foreach ($departemens as $dept)
                        <td style="text-align:right;">
                            {{ number_format($row['per_departemen'][$dept->deskripsi] ?? 0, 2, ',', '.') }}
                        </td>
                    @endforeach
                    <td style="text-align:right;">{{ number_format($row['saldo'], 2, ',', '.') }}</td>
                </tr>
            @endif
        @endforeach
        <tr style="font-weight:bold;">
            <td colspan="{{ 2 + count($departemens) }}">TOTAL PENDAPATAN</td>
            <td style="text-align:right;">{{ number_format($totalPendapatan, 2, ',', '.') }}</td>
        </tr>

        {{-- Beban --}}
        <tr>
            <td colspan="{{ 3 + count($departemens) }}"><strong>BEBAN</strong></td>
        </tr>
        @foreach ($incomeStatement as $row)
            @if (strtolower($row['tipe_akun']) === 'beban')
                <tr>
                    <td>{{ $row['kode_akun'] }}</td>
                    <td>{{ $row['nama_akun'] }}</td>
                    @foreach ($departemens as $dept)
                        <td style="text-align:right;">
                            {{ number_format($row['per_departemen'][$dept->deskripsi] ?? 0, 2, ',', '.') }}
                        </td>
                    @endforeach
                    <td style="text-align:right;">{{ number_format($row['saldo'], 2, ',', '.') }}</td>
                </tr>
            @endif
        @endforeach
        {{-- <tr style="font-weight:bold;">
            <td colspan="{{ 2 + count($departemens) }}">TOTAL BEBAN</td>
            <td style="text-align:right;">{{ number_format($totalBeban, 2, ',', '.') }}</td>
        </tr>

        <tr style="font-weight:bold; background:#eee;">
            <td colspan="{{ 2 + count($departemens) }}">LABA BERSIH</td>
            <td style="text-align:right;">{{ number_format($labaBersih, 2, ',', '.') }}</td>
        </tr> --}}
    </tbody>
</table>
