<table>
    <thead>
        <tr>
            <th>Pegawai</th>
            <th>Level</th>
            <th>Unit</th>
            <th>Total Hari Kerja</th>
            <th>Total Jam Lembur</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($rekap as $r)
            <tr>
                <td>{{ $r['pegawai'] }}</td>
                <td>{{ $r['level'] }}</td>
                <td>{{ $r['unit'] }}</td>
                <td>{{ $r['total_hari'] }}</td>
                <td>{{ $r['total_lembur'] }} Jam</td>
            </tr>
        @endforeach
    </tbody>
</table>
