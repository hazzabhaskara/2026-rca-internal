<?php

namespace App\Exports;

use App\Project;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ProjectExport implements FromCollection, WithHeadings
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Project::select(
            'nama_project',
            'start_date',
            'end_date',
            'revenue',
            'expens',
            'status',
        )->get();
    }
    public function headings(): array
    {
        return [
            'nama_project',
            'start_date',
            'end_date',
            'revenue',
            'expens',
            'status',
        ];
    }
}
