<?php

namespace App\Exports;

use App\Models\Contribution;
use App\Models\ContributionType;
use App\Models\Member;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ContributionMatrixExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $type;
    protected $year;
    protected $periods;
    protected $contributions;

    public function __construct(ContributionType $type, $year)
    {
        $this->type = $type;
        $this->year = $year;
        $this->periods = $this->generatePeriods($type, $year);
        
        // Pre-fetch contributions
        $this->contributions = Contribution::where('contribution_type_id', $type->id)
            ->whereIn('status', ['paid', 'pending', 'partial'])
            ->when($type->period !== 'once', function($q) use ($year) {
               $q->where('payment_period', 'like', "$year%"); 
            })
            ->get()
            ->groupBy('member_id');
    }

    private function generatePeriods($type, $year)
    {
        $periods = [];
        if ($type->period === 'monthly') {
            for ($m = 1; $m <= 12; $m++) {
                $periods[] = sprintf('%s-%02d', $year, $m);
            }
        } elseif ($type->period === 'yearly') {
            $periods[] = (string)$year;
        } elseif ($type->period === 'weekly') {
            $dto = new Carbon();
            $dto->setISODate($year, 53);
            $weeksInYear = ($dto->format('W') === '53' ? 53 : 52);
            for ($w = 1; $w <= $weeksInYear; $w++) {
                $periods[] = sprintf('%s-%02d', $year, $w);
            }
        } else {
            $periods[] = 'once'; 
        }
        return $periods;
    }

    private function getPeriodLabel($period) 
    {
        if ($this->type->period === 'monthly') return Carbon::createFromFormat('Y-m', $period)->translatedFormat('M');
        if ($this->type->period === 'weekly') return 'W' . substr($period, -2);
        if ($this->type->period === 'yearly') return $period;
        if ($this->type->period === 'once') return 'Status';
        return $period;
    }

    public function collection()
    {
        return Member::active()->orderBy('full_name')->get();
    }

    public function headings(): array
    {
        $headers = ['Kode Anggota', 'Nama Anggota'];
        foreach ($this->periods as $period) {
            $headers[] = $this->getPeriodLabel($period);
        }
        return $headers;
    }

    public function map($member): array
    {
        $memberContribs = $this->contributions->get($member->id, collect());
        
        $row = [
            $member->member_code,
            $member->full_name,
        ];

        foreach ($this->periods as $period) {
            if ($this->type->period === 'once') {
                    $c = $memberContribs->first();
            } else {
                    $c = $memberContribs->firstWhere('payment_period', $period);
            }
            
            $status = '-';
            if ($c) {
                if ($c->status === 'paid') $status = 'Lunas';
                elseif ($c->status === 'pending') $status = 'Proses';
                elseif ($c->status === 'partial') $status = 'Sebagian';
                else $status = $c->status;
            }
            $row[] = $status;
        }
        
        return $row;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
