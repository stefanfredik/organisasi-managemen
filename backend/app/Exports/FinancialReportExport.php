<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class FinancialReportExport implements FromCollection, WithHeadings, WithMapping
{
    protected $transactions;

    public function __construct($transactions)
    {
        $this->transactions = $transactions;
    }

    public function collection()
    {
        return $this->transactions;
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'Tipe',
            'Kategori',
            'Keterangan',
            'Jumlah',
            'Dompet',
            'Dicatat Oleh'
        ];
    }

    public function map($transaction): array
    {
        return [
            $transaction->transaction_date,
            $transaction->type == 'in' ? 'Pemasukan' : 'Pengeluaran',
            $transaction->category,
            $transaction->description,
            $transaction->amount,
            $transaction->wallet->name ?? '-',
            $transaction->creator->name ?? '-'
        ];
    }
}
