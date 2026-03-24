<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ContributionsByCommunityExport implements FromView, ShouldAutoSize, WithStyles
{
    protected $contributions;
    protected $request;
    protected $community;

    public function __construct($contributions, $request, $community)
    {
        $this->contributions = $contributions;
        $this->request = $request;
        $this->community = $community;
    }

    public function view(): View
    {
        return view('admin.contributions.reports.by_community_excel', [
            'contributions' => $this->contributions,
            'request'       => $this->request,
            'community'     => $this->community,
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