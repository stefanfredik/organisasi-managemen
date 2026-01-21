<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class DonationsExport implements FromCollection, WithHeadings, WithMapping
{
    protected $donations;

    public function __construct($donations)
    {
        $this->donations = $donations;
    }

    public function collection()
    {
        return $this->donations;
    }

    public function headings(): array
    {
        return [
            'Nama Campaign',
            'Status',
            'Target (Rp)',
            'Terkumpul (Rp)',
            'Sisa (Rp)',
            'Pencapaian (%)',
            'Selesai Pada'
        ];
    }

    public function map($donation): array
    {
        return [
            $donation->program_name,
            ucfirst($donation->status),
            $donation->target_amount,
            $donation->collected_amount,
            $donation->target_amount - $donation->collected_amount,
            number_format(($donation->collected_amount / $donation->target_amount) * 100, 2),
            $donation->completed_at ?? '-'
        ];
    }
}
