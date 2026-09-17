<table border="1" cellspacing="0" cellpadding="5" width="100%">
    <thead>
        <tr>
            <th>Unit</th>
            <th>Wahana</th>
            <th>Jenis Hari</th>
            <th>Target Harian</th>
            <th>Bulan</th>
            <th>Tahun</th>
            <th>Keterangan</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($results as $row)
            <tr>
                <td>{{ $row->wahana->unitKerja->nama_unit }}</td>
                <td>{{ $row->wahana->nama_wahana }}</td>
                <td>{{ $row->jenis_hari->nama }}</td>
                <td align="right">{{ number_format($row->target_harian) }}</td>
                <td>{{ $row->bulan }}</td>
                <td>{{ $row->tahun }}</td>
                <td>{{ $row->keterangan }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
