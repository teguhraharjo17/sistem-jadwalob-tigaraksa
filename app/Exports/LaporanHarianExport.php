<?php

namespace App\Exports;

use App\Models\LaporanHarian;
use Carbon\Carbon;
use App\Models\LaporanHarianApproval;
use Maatwebsite\Excel\Concerns\{
    FromCollection, WithHeadings, WithMapping, WithStyles,
    WithColumnWidths, WithDrawings, WithEvents, WithCustomStartCell
};
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class LaporanHarianExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    WithCustomStartCell,
    WithColumnWidths,
    WithDrawings,
    WithEvents
{
    protected $bulan, $tahun, $data, $approval;
    protected $rowNumber = 0;

    public function __construct($bulan, $tahun, $approval)
    {
        $this->bulan = $bulan;
        $this->tahun = $tahun;
        $this->approval = $approval;
    }

    public function startCell(): string
    {
        return 'A3';
    }

    public function collection()
    {
        $this->data = LaporanHarian::with('checklist')
            ->whereMonth('tanggal', $this->bulan)
            ->whereYear('tanggal', $this->tahun)
            ->orderBy('tanggal', 'desc')
            ->orderBy('jam_selesai', 'desc')
            ->get();

        return $this->data;
    }

    public function headings(): array
    {
        return [[
            'NO',
            'TANGGAL',
            'SHIFT',
            'JAM MULAI',
            'JAM SELESAI',
            'ITEM PEKERJAAN',
            'AREA',
            'BUKTI PEKERJAAN',
            'HASIL PEKERJAAN',
            'MENGETAHUI',
            'PARAF',
        ]];
    }

    public function map($row): array
    {
        $this->rowNumber++;

        // Determine text note for Bukti column if any
        $buktiText = '';
        if ($row->bukti) {
            $decoded = json_decode($row->bukti, true);
            $buktiList = is_array($decoded) ? $decoded : [$row->bukti];
            $hasPdf = false;
            $hasImg = false;
            foreach ($buktiList as $b) {
                $ext = strtolower(pathinfo($b, PATHINFO_EXTENSION));
                if ($ext === 'pdf') {
                    $hasPdf = true;
                } elseif (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                    $hasImg = true;
                }
            }
            if ($hasPdf && !$hasImg) {
                $buktiText = '[Dokumen PDF]';
            }
        } else {
            $buktiText = '-';
        }

        return [
            $this->rowNumber,
            Carbon::parse($row->tanggal)->format('d-m-Y'),
            $row->shift ?? '-',
            $row->jam_mulai ?? '-',
            $row->jam_selesai ?? '-',
            $row->checklist->pekerjaan ?? ($row->rincian_pekerjaan ?? '-'),
            $row->area ?? '-',
            $buktiText,
            $row->hasil_pekerjaan ?: '-',
            $row->mengetahui ?: '-',
            $row->paraf ? '' : '-',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 6,   // No
            'B' => 14,  // Tanggal
            'C' => 10,  // Shift
            'D' => 13,  // Jam Mulai
            'E' => 13,  // Jam Selesai
            'F' => 32,  // Item Pekerjaan
            'G' => 22,  // Area
            'H' => 28,  // Bukti Pekerjaan
            'I' => 18,  // Hasil Pekerjaan
            'J' => 18,  // Mengetahui
            'K' => 20,  // Paraf
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [];
    }

    public function registerEvents(): array
    {
        return [
            \Maatwebsite\Excel\Events\AfterSheet::class => function ($event) {
                $sheet = $event->sheet->getDelegate();
                $dataCount = count($this->data);
                $headerRow = 3;
                $startDataRow = 4;
                $endDataRow = 3 + $dataCount;

                // Title (Row 1)
                $sheet->mergeCells("A1:K1");
                $sheet->setCellValue("A1", "LAPORAN KERJA HARIAN OFFICE BOY (OB)");
                $sheet->getRowDimension(1)->setRowHeight(36);
                $sheet->getStyle("A1:K1")->applyFromArray([
                    'font' => [
                        'name' => 'Calibri',
                        'bold' => true,
                        'size' => 14,
                        'color' => ['rgb' => 'FFFFFF'],
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '1E3A8A'], // Deep Corporate Navy
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);

                // Subtitle / Info (Row 2)
                $bulanNama = Carbon::createFromDate($this->tahun, $this->bulan, 1)->locale('id')->translatedFormat('F Y');
                $sheet->mergeCells("A2:K2");
                $sheet->setCellValue("A2", "Periode: " . $bulanNama . "   |   Lokasi: PT Milenia Mega Mandiri Tigaraksa");
                $sheet->getRowDimension(2)->setRowHeight(22);
                $sheet->getStyle("A2:K2")->applyFromArray([
                    'font' => [
                        'name' => 'Calibri',
                        'bold' => true,
                        'size' => 10,
                        'color' => ['rgb' => '334155'],
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'F1F5F9'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                    'borders' => [
                        'bottom' => [
                            'borderStyle' => Border::BORDER_MEDIUM,
                            'color' => ['rgb' => '1E3A8A'],
                        ],
                    ],
                ]);

                // Table Headings (Row 3)
                $sheet->getRowDimension($headerRow)->setRowHeight(30);
                $sheet->getStyle("A{$headerRow}:K{$headerRow}")->applyFromArray([
                    'font' => [
                        'name' => 'Calibri',
                        'bold' => true,
                        'size' => 10,
                        'color' => ['rgb' => 'FFFFFF'],
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '2563EB'], // Royal Blue
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'wrapText' => true,
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '1E40AF'],
                        ],
                    ],
                ]);

                // Data Rows (Row 4 to $endDataRow)
                if ($dataCount > 0) {
                    for ($i = $startDataRow; $i <= $endDataRow; $i++) {
                        $sheet->getRowDimension($i)->setRowHeight(75);

                        $bgColor = ($i % 2 === 0) ? 'FFFFFF' : 'F8FAFC';
                        $sheet->getStyle("A{$i}:K{$i}")->applyFromArray([
                            'font' => [
                                'name' => 'Calibri',
                                'size' => 9.5,
                                'color' => ['rgb' => '1E293B'],
                            ],
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['rgb' => $bgColor],
                            ],
                            'borders' => [
                                'allBorders' => [
                                    'borderStyle' => Border::BORDER_THIN,
                                    'color' => ['rgb' => 'E2E8F0'],
                                ],
                            ],
                            'alignment' => [
                                'vertical' => Alignment::VERTICAL_CENTER,
                                'wrapText' => true,
                            ],
                        ]);

                        // Alignment refinements
                        $sheet->getStyle("A{$i}:E{$i}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                        $sheet->getStyle("F{$i}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                        $sheet->getStyle("G{$i}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                        $sheet->getStyle("H{$i}:K{$i}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    }
                }

                // Approval Box Section (Columns J & K)
                $approvalStartRow = ($dataCount > 0 ? $endDataRow : $headerRow) + 2;
                $sheet->getRowDimension($approvalStartRow - 1)->setRowHeight(16);

                // Row 1: Header "Menyetujui,"
                $sheet->mergeCells("J{$approvalStartRow}:K{$approvalStartRow}");
                $sheet->setCellValue("J{$approvalStartRow}", "Menyetujui,");
                $sheet->getRowDimension($approvalStartRow)->setRowHeight(24);
                $sheet->getStyle("J{$approvalStartRow}:K{$approvalStartRow}")->applyFromArray([
                    'font' => ['name' => 'Calibri', 'bold' => true, 'size' => 10, 'color' => ['rgb' => '0F172A']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E2E8F0']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '94A3B8']]],
                ]);

                // Row 2: Signature Space
                $approvalSignRow = $approvalStartRow + 1;
                $sheet->mergeCells("J{$approvalSignRow}:K{$approvalSignRow}");
                $sheet->getRowDimension($approvalSignRow)->setRowHeight(75);
                $sheet->getStyle("J{$approvalSignRow}:K{$approvalSignRow}")->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFFFF']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '94A3B8']]],
                ]);

                // Row 3: Approver Name
                $approvalNameRow = $approvalSignRow + 1;
                $sheet->mergeCells("J{$approvalNameRow}:K{$approvalNameRow}");
                $namaPenyetuju = $this->approval->nama ?? '........................................';
                $sheet->setCellValue("J{$approvalNameRow}", "( {$namaPenyetuju} )");
                $sheet->getRowDimension($approvalNameRow)->setRowHeight(24);
                $sheet->getStyle("J{$approvalNameRow}:K{$approvalNameRow}")->applyFromArray([
                    'font' => ['name' => 'Calibri', 'bold' => true, 'size' => 10, 'color' => ['rgb' => '0F172A']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F8FAFC']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '94A3B8']]],
                ]);

                // Row 4: Approver Role
                $approvalRoleRow = $approvalNameRow + 1;
                $sheet->mergeCells("J{$approvalRoleRow}:K{$approvalRoleRow}");
                $sheet->setCellValue("J{$approvalRoleRow}", "Supervisor / Penanggung Jawab");
                $sheet->getRowDimension($approvalRoleRow)->setRowHeight(20);
                $sheet->getStyle("J{$approvalRoleRow}:K{$approvalRoleRow}")->applyFromArray([
                    'font' => ['name' => 'Calibri', 'italic' => true, 'size' => 9, 'color' => ['rgb' => '64748B']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F8FAFC']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '94A3B8']]],
                ]);

                // Enable gridlines
                $sheet->setShowGridLines(true);
            }
        ];
    }

    public function drawings(): array
    {
        $drawings = [];

        foreach ($this->data as $index => $row) {
            $excelRow = $index + 4; // Data rows start at 4 precisely

            // Bukti Drawing
            if ($row->bukti) {
                $decoded = json_decode($row->bukti, true);
                $buktiList = is_array($decoded) ? $decoded : [$row->bukti];

                $imgOffsetCount = 0;
                foreach ($buktiList as $bIndex => $filePath) {
                    $fullPath = storage_path('app/public/' . $filePath);
                    $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));

                    if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']) && file_exists($fullPath)) {
                        $drawingBukti = new Drawing();
                        $drawingBukti->setName('Bukti_' . $index . '_' . $bIndex);
                        $drawingBukti->setDescription('Bukti Kerja');
                        $drawingBukti->setPath($fullPath);
                        $drawingBukti->setHeight(55);
                        $drawingBukti->setCoordinates('H' . $excelRow);
                        $drawingBukti->setOffsetX(15 + ($imgOffsetCount * 65));
                        $drawingBukti->setOffsetY(10);
                        $drawings[] = $drawingBukti;
                        $imgOffsetCount++;
                    }
                }
            }

            // Paraf Drawing
            if ($row->paraf && file_exists(storage_path('app/public/' . $row->paraf))) {
                $drawingParaf = new Drawing();
                $drawingParaf->setName('Paraf_' . $index);
                $drawingParaf->setDescription('Paraf Petugas');
                $drawingParaf->setPath(storage_path('app/public/' . $row->paraf));
                $drawingParaf->setHeight(55);
                $drawingParaf->setCoordinates('K' . $excelRow);
                $drawingParaf->setOffsetX(25);
                $drawingParaf->setOffsetY(10);
                $drawings[] = $drawingParaf;
            }
        }

        // Approval Signature Drawing (Row = 4 + count + 2)
        $approvalSignRow = count($this->data) + 4 + 2; 
        if ($this->approval && $this->approval->ttd_path && file_exists(storage_path('app/public/' . $this->approval->ttd_path))) {
            $approvalTTD = new Drawing();
            $approvalTTD->setName('Approval_TTD');
            $approvalTTD->setDescription('Tanda Tangan Penyetuju');
            $approvalTTD->setPath(storage_path('app/public/' . $this->approval->ttd_path));
            $approvalTTD->setHeight(58);
            $approvalTTD->setCoordinates('J' . $approvalSignRow);
            $approvalTTD->setOffsetX(55);
            $approvalTTD->setOffsetY(10);
            $drawings[] = $approvalTTD;
        }

        return $drawings;
    }
}