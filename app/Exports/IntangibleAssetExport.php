<?php

namespace App\Exports;

use App\IntangibleAsset;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class IntangibleAssetExport implements FromCollection, WithHeadings
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $data = IntangibleAsset::with('kategori', 'lokasi', 'golongan', 'metode_penyusutan')->get();

        return $data->map(function ($item) {
            return [
                $item->kode_asset,
                $item->nama_asset,
                $item->kategori->nama_kategori,
                $item->brand,
                $item->deskripsi,
                $item->dalam_tahun,
                $item->lokasi->nama_lokasi,
                $item->golongan->nama_golongan,
                $item->metode_penyusutan->nama_metode,
                $item->tarif_amortisasi,

            ];
        });
    }
    public function headings(): array
    {
        return [
            'kode asset',
            'nama asset',
            'kategori',
            'brand',
            'deskripsi',
            'dalam_tahun',
            'unit/location',
            'golongan',
            'metode penyusutan',
            'tarif Amortisasi'
        ];
    }
}
