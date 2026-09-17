<?php

namespace App\Imports;

use App\KategoriAsset;
use App\Lokasi;
use App\MasaManfaat;
use App\MetodePenyusutan;
use App\TangibleAsset;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class TangibleAssetImport implements ToCollection, WithHeadingRow
{
    public $errors = [];

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2; // +2 karena heading row di baris 1

            $lokasi = Lokasi::where('nama_lokasi', $row['lokasi'])->first();
            $kategori = KategoriAsset::where('nama_kategori', $row['kategori'])->first();
            $golongan = MasaManfaat::where('nama_golongan', $row['golongan'])->first();
            $metode_penyusutan = MetodePenyusutan::where('nama_metode', $row['metode_penyusutan'])->first();

            // Kumpulkan error jika data tidak ditemukan
            if (!$lokasi || !$kategori || !$golongan || !$metode_penyusutan) {
                $this->errors[] = [
                    'baris' => $rowNumber,
                    'lokasi' => !$lokasi ? "Lokasi '{$row['lokasi']}' tidak ditemukan" : null,
                    'kategori' => !$kategori ? "Kategori '{$row['kategori']}' tidak ditemukan" : null,
                    'golongan' => !$golongan ? "Golongan '{$row['golongan']}' tidak ditemukan" : null,
                    'metode_penyusutan' => !$metode_penyusutan ? "Metode penyusutan '{$row['metode_penyusutan']}' tidak ditemukan" : null,
                ];
                continue; // Lewati insert untuk baris ini
            }
            $assetFullName = trim(
                "{$row['nama_asset']} {$row['components']} {$row['merk']} {$row['type']}"
            );
            // Simpan data jika semua valid
            TangibleAsset::create([
                'kode_asset'   => $row['kode_asset'],
                'nama_asset'   => $row['nama_asset'],
                'components'   => $row['components'],
                'capacity'     => $row['capacity'] ?? null,
                'merk'         => $row['merk'] ?? null,
                'type'         => $row['type'] ?? null,
                'lokasi_id'    => $lokasi->id,
                'kategori_id'  => $kategori->id,
                'golongan_id'  => $golongan->id,
                'metode_penyusutan_id' => $metode_penyusutan->id,
                'tarif_penyusutan'     => $row['tarif_penyusutan'],
                'dalam_tahun'          => $row['dalam_tahun'],
                'asset_full_name'      => $assetFullName,
            ]);
        }
    }
}
