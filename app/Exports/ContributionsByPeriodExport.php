<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ContributionsByPeriodExport implements FromView, ShouldAutoSize, WithStyles
{
    protected $contributions;
    protected $request;

    public function __construct($contributions, $request)
    {
        $this->contributions = $contributions;
        $this->request = $request;
    }

    public function view(): View
    {
        return view('admin.contributions.reports.by_period_excel', [
            'contributions' => $this->contributions,
            'request'       => $this->request,
        ]);
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '004080'],
                ],
                'font' => ['color' => ['rgb' => 'FFFFFF']],
            ],
        ];
    }
}