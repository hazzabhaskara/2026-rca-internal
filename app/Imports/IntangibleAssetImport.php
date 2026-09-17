<?php

namespace App\Imports;

use App\IntangibleAsset;
use App\KategoriAsset;
use App\Lokasi;
use App\MasaManfaat;
use App\MetodePenyusutan;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class IntangibleAssetImport implements ToCollection, WithHeadingRow
{
    /**
     * @param Collection $collection
     */
    public $errors = [];

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2; // +2 karena heading row di baris 1
            // Cari ID unit kerja dari nama
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
            // $assetFullName = trim(
            //     "{$row['nama_asset']} {$row['components']} {$row['merk']} {$row['type']}"
            // );


            IntangibleAsset::create([
                'kode_asset'   => $row['kode_asset'],
                'nama_asset'   => $row['nama_asset'],
                'brand'        => $row['brand'],
                'deskripsi'     => $row['deskripsi'] ?? null,
                'dalam_tahun'     => $row['dalam_tahun'] ?? null,
                'lokasi_id' => $lokasi->id ?? null,
                'kategori_id' => $kategori->id ?? null,
                'golongan_id' => $golongan->id ?? null,
                'metode_penyusutan_id' => $metode_penyusutan->id ?? null,
                'tarif_amortisasi' => $row['tarif_amortisasi'],
            ]);
        }
    }
}
