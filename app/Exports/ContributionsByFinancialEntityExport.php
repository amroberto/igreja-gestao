<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ContributionsByFinancialEntityExport implements FromView, ShouldAutoSize, WithStyles
{
    protected $contributions;
    protected $request;
    protected $financialEntity;

    public function __construct($contributions, $request, $financialEntity)
    {
        $this->contributions = $contributions;
        $this->request = $request;
        $this->financialEntity = $financialEntity;
    }

    public function view(): View
    {
        return view('admin.contributions.reports.by_financial_entity_excel', [
            'contributions'   => $this->contributions,
            'request'         => $this->request,
            'financialEntity' => $this->financialEntity,
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