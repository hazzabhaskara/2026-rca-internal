<?php

namespace App\Imports;

use App\chartOfAccount;
use App\Item;
use App\itemCategory;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ItemsImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        $account = ChartOfAccount::where('kode_akun', $row['kode_akun'])->first();
        $category = ItemCategory::where('nama_kategori', $row['kategori'])->first();

        return new Item([
            'item_number'      => $row['item_number'],
            'item_name'        => $row['item_name'],
            'item_description' => $row['item_description'],
            'unit'             => $row['unit'],
            'base_price'       => $row['base_price'],
            'tax_rate'         => $row['tax_rate'] ?? 0,
            'account_id'       => $account?->id,         // NULL jika tidak ditemukan
            'category_id'      => $category?->id,        // NULL jika tidak ditemukan
            'is_active'        => $row['is_active'] ?? true,
            'brand'            => $row['brand'],
            'stock_quantity'   => $row['stock_quantity'],
            'purchase_price'   => $row['purchase_price'],
            'barcode'          => $row['barcode'],
            'image'            => null, // default null, atau bisa diubah jika diupload lewat form
        ]);
    }
}
