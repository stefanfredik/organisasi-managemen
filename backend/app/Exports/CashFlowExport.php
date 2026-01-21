<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CashFlowExport implements FromCollection, WithHeadings
{
    protected $monthlyData;

    public function __construct($monthlyData)
    {
        $this->monthlyData = collect($monthlyData);
    }

    public function collection()
    {
        return $this->monthlyData;
    }

    public function headings(): array
    {
        return [
            'Bulan',
            'Pemasukan',
            'Pengeluaran',
            'Net'
        ];
    }
}
