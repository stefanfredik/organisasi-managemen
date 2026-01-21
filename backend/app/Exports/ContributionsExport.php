<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ContributionsExport implements FromCollection, WithHeadings, WithMapping
{
    protected $contributions;

    public function __construct($contributions)
    {
        $this->contributions = $contributions;
    }

    public function collection()
    {
        return $this->contributions;
    }

    public function headings(): array
    {
        return [
            'Anggota',
            'Kode Anggota',
            'Jenis Iuran',
            'Tanggal Pembayaran',
            'Status',
            'Jumlah',
            'Catatan'
        ];
    }

    public function map($contribution): array
    {
        return [
            $contribution->member->full_name ?? '-',
            $contribution->member->member_code ?? '-',
            $contribution->type->name ?? '-',
            $contribution->payment_date,
            ucfirst($contribution->status),
            $contribution->amount,
            $contribution->notes
        ];
    }
}
