<?php

namespace App\Exports;

use App\TangibleAsset;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TangibleAssetExport implements FromCollection, WithHeadings
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $data = TangibleAsset::with('kategori', 'lokasi', 'golongan', 'metode_penyusutan')->get();

        return $data->map(function ($item) {
            return [
                $item->kode_asset,
                $item->nama_asset,
                $item->kategori->nama_kategori,
                $item->components,
                $item->capacity,
                $item->merk,
                $item->type,
                $item->lokasi->nama_lokasi,
                $item->golongan->nama_golongan,
                $item->dalam_tahun,
                $item->metode_penyusutan->nama_metode,
                $item->tarif_penyusutan,

            ];
        });
    }
    public function headings(): array
    {
        return [
            'kode asset',
            'nama asset',
            'kategor',
            'component',
            'capacity',
            'brand',
            'type',
            'unit/location',
            'golongan',
            'masa manfaat',
            'metode penyusutan',
            'tarif penyusutan'
        ];
    }
}
