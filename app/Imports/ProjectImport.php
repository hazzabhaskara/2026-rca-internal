<?php

namespace App\Imports;

use App\Project;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithStartRow;
use PhpOffice\PhpSpreadsheet\Shared\Date;


class ProjectImport implements ToModel, WithStartRow
{
    /**
     * @param Collection $collection
     */
    public function startRow(): int
    {
        return 2;
    }

    public function model(array $row)
    {
        return new Project([
            'nama_project'      => $row[0], // kolom A
            'start_date'   => $this->transformDate($row[1]),
            'end_date'     => $this->transformDate($row[2]),
            'revenue'      => $row[3], // kolom A
            'expens'    => $row[4], // kolom B
            'status'  => $row[5] ?? null, // kolom C
        ]);
    }
    private function transformDate($value)
    {
        try {
            if (is_numeric($value)) {
                return Date::excelToDateTimeObject($value)->format('Y-m-d');
            }

            return \Carbon\Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }
}
