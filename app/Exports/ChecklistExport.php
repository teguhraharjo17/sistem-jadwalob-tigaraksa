<?php

namespace App\Exports;

use App\Models\Checklist;
use App\Models\ChecklistStatus;
use App\Models\LaporanHarian;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\{
    FromArray, WithHeadings, WithStyles, WithEvents, WithColumnWidths
};
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use Illuminate\Support\Facades\Http;

class ChecklistExport implements FromArray, WithHeadings, WithStyles, WithEvents, WithColumnWidths
{
    protected $bulan, $tahun, $data, $tanggalCount, $statusMap, $parafMap;

    public function __construct($bulan, $tahun)
    {
        $this->bulan = (int)$bulan;
        $this->tahun = (int)$tahun;

        $this->data = Checklist::where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->orderBy('area')
            ->get()
            ->groupBy('area');

        $this->tanggalCount = Carbon::create($tahun, $bulan)->daysInMonth;

        $this->statusMap = ChecklistStatus::whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->get()
            ->mapWithKeys(function ($item) {
                $key = $item->checklist_id . '_' . $item->tanggal . '_' . $item->shift;
                return [$key => $item->status];
            })->toArray();

        $this->parafMap = LaporanHarian::whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->whereNotNull('paraf')
            ->get()
            ->mapWithKeys(function ($lap) {
                $key = $lap->checklist_id . '_' . $lap->tanggal . '_' . $lap->shift;
                return [$key => true];
            })->toArray();
    }

    public function headings(): array
    {
        $bulanNama = Carbon::createFromDate($this->tahun, $this->bulan, 1)->translatedFormat('F Y');

        // Row 1: Title
        $row1 = ['LAPORAN CHECKLIST PEMBERSIHAN AREA'];

        // Row 2: Subtitle
        $row2 = ['PERIODE: ' . strtoupper($bulanNama)];

        // Row 3: Spacing
        $row3 = [];

        // Row 4: Table Header Row 1
        $row4 = ['No', 'Pekerjaan', 'Periodic Cleaning'];
        for ($i = 1; $i <= $this->tanggalCount; $i++) {
            $row4[] = $i;
            $row4[] = '';
        }
        $row4[] = 'Keterangan';

        // Row 5: Table Header Row 2
        $row5 = ['', '', ''];
        for ($i = 1; $i <= $this->tanggalCount; $i++) {
            $row5[] = 'P';
            $row5[] = 'S';
        }
        $row5[] = '';

        return [$row1, $row2, $row3, $row4, $row5];
    }

    public function array(): array
    {
        $rows = [];
        $counter = 1;

        foreach ($this->data as $area => $items) {
            // Area Row Placeholder
            $rows[] = [$area];

            foreach ($items as $item) {
                $frequencyText = $item->frequency_count . 'x ' . match($item->frequency_unit) {
                    'per_hari'     => 'per Hari',
                    'per_x_hari'   => 'per ' . $item->frequency_interval . ' Hari',
                    'per_minggu'   => 'per Minggu',
                    'per_x_minggu' => 'per ' . $item->frequency_interval . ' Minggu',
                    'per_bulan'    => 'per Bulan',
                    default        => '',
                };

                $row = [
                    $counter++,
                    $item->pekerjaan,
                    $frequencyText
                ];

                for ($i = 1; $i <= $this->tanggalCount; $i++) {
                    $row[] = '';
                    $row[] = '';
                }

                $row[] = $item->keterangan ?? '';
                $rows[] = $row;
            }
        }

        return $rows;
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
                $sheet->setShowGridLines(true);

                $colCount = 3 + ($this->tanggalCount * 2) + 1;
                $lastCol = Coordinate::stringFromColumnIndex($colCount);

                // --- 1. TITLE BLOCK STYLING (Rows 1-3) ---
                $sheet->mergeCells("A1:{$lastCol}1");
                $sheet->setCellValue("A1", "LAPORAN CHECKLIST PEMBERSIHAN AREA");
                $sheet->getStyle("A1")->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 16,
                        'color' => ['rgb' => '1E3A8A'],
                        'name' => 'Calibri',
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(32);

                $bulanNama = Carbon::createFromDate($this->tahun, $this->bulan, 1)->translatedFormat('F Y');
                $sheet->mergeCells("A2:{$lastCol}2");
                $sheet->setCellValue("A2", "PERIODE: " . strtoupper($bulanNama));
                $sheet->getStyle("A2")->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 11,
                        'color' => ['rgb' => '475569'],
                        'name' => 'Calibri',
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);
                $sheet->getRowDimension(2)->setRowHeight(20);
                $sheet->getRowDimension(3)->setRowHeight(10);

                // --- 2. TABLE HEADERS MERGING & STYLING (Rows 4 & 5) ---
                $sheet->mergeCells("A4:A5");
                $sheet->mergeCells("B4:B5");
                $sheet->mergeCells("C4:C5");
                $sheet->mergeCells("{$lastCol}4:{$lastCol}5");

                for ($i = 1; $i <= $this->tanggalCount; $i++) {
                    $startCol = 4 + (($i - 1) * 2);
                    $endCol = $startCol + 1;
                    $startLetter = Coordinate::stringFromColumnIndex($startCol);
                    $endLetter = Coordinate::stringFromColumnIndex($endCol);
                    $sheet->mergeCells("{$startLetter}4:{$endLetter}4");
                }

                // Row 4 Header Style
                $sheet->getStyle("A4:{$lastCol}4")->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => 'FFFFFF'],
                        'size' => 10,
                        'name' => 'Calibri',
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'color' => ['rgb' => '1E3A8A'], // Dark Navy
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'wrapText' => true,
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '3B82F6'],
                        ],
                    ],
                ]);

                // Row 5 Header Style (P & S)
                $sheet->getStyle("A5:{$lastCol}5")->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => 'FFFFFF'],
                        'size' => 9,
                        'name' => 'Calibri',
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'color' => ['rgb' => '2563EB'], // Royal Blue Accent
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '60A5FA'],
                        ],
                    ],
                ]);

                $sheet->getRowDimension(4)->setRowHeight(24);
                $sheet->getRowDimension(5)->setRowHeight(20);

                // Fetch Holidays
                $holidays = collect(app(\App\Services\MileniaApiService::class)->getHolidays())
                    ->filter(fn($item) => is_array($item) && isset($item['tanggal']))
                    ->pluck('tanggal')
                    ->map(fn($t) => Carbon::parse($t)->format('Y-m-d'))
                    ->toArray();

                // --- 3. DATA ROWS & AREA SECTIONS ---
                $currentRow = 6;

                foreach ($this->data as $area => $items) {
                    // Merge Area Row
                    $sheet->mergeCells("A{$currentRow}:{$lastCol}{$currentRow}");
                    $sheet->setCellValue("A{$currentRow}", "📍 AREA: " . strtoupper($area));
                    $sheet->getStyle("A{$currentRow}:{$lastCol}{$currentRow}")->applyFromArray([
                        'font' => [
                            'bold' => true,
                            'size' => 11,
                            'color' => ['rgb' => '0F172A'],
                            'name' => 'Calibri',
                        ],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_LEFT,
                            'vertical' => Alignment::VERTICAL_CENTER,
                        ],
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'color' => ['rgb' => 'E2E8F0'], // Soft Ice Slate
                        ],
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['rgb' => 'CBD5E1'],
                            ],
                        ],
                    ]);
                    $sheet->getRowDimension($currentRow)->setRowHeight(24);
                    $currentRow++;

                    foreach ($items as $item) {
                        $sheet->getRowDimension($currentRow)->setRowHeight(24);

                        // Base Data Row Style
                        $sheet->getStyle("A{$currentRow}:{$lastCol}{$currentRow}")->applyFromArray([
                            'alignment' => [
                                'vertical' => Alignment::VERTICAL_CENTER,
                            ],
                            'borders' => [
                                'allBorders' => [
                                    'borderStyle' => Border::BORDER_THIN,
                                    'color' => ['rgb' => 'CBD5E1'],
                                ],
                            ],
                        ]);

                        // Specific Alignments
                        $sheet->getStyle("A{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                        $sheet->getStyle("B{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setWrapText(true);
                        $sheet->getStyle("C{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setWrapText(true);
                        $sheet->getStyle("{$lastCol}{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setWrapText(true);

                        // Shift Cell Formatting
                        for ($i = 1; $i <= $this->tanggalCount; $i++) {
                            $date = Carbon::create($this->tahun, $this->bulan, $i)->format('Y-m-d');
                            $isWeekend = in_array(Carbon::parse($date)->dayOfWeek, [Carbon::SATURDAY, Carbon::SUNDAY]);
                            $isHoliday = in_array($date, $holidays);

                            foreach (['Pagi', 'Siang'] as $shiftIndex => $shift) {
                                $key = $item->id . '_' . $date . '_' . $shift;
                                $status = $this->statusMap[$key] ?? 0;
                                $paraf = $this->parafMap[$key] ?? false;

                                $colIndex = 4 + (($i - 1) * 2) + $shiftIndex;
                                $cellLetter = Coordinate::stringFromColumnIndex($colIndex);
                                $cell = $cellLetter . $currentRow;

                                // Alignment for Shift Cells
                                $sheet->getStyle($cell)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                                // Background & Value Logic
                                if ($isHoliday || $isWeekend) {
                                    if ($status && $paraf) {
                                        // Completed on weekend/holiday
                                        $sheet->setCellValue($cell, '✓');
                                        $sheet->getStyle($cell)->applyFromArray([
                                            'font' => ['bold' => true, 'color' => ['rgb' => '006100']],
                                            'fill' => [
                                                'fillType' => Fill::FILL_SOLID,
                                                'color' => ['rgb' => 'C6EFCE'],
                                            ],
                                        ]);
                                    } else {
                                        // Holiday / Weekend default
                                        $sheet->getStyle($cell)->getFill()->applyFromArray([
                                            'fillType' => Fill::FILL_SOLID,
                                            'color' => ['rgb' => 'FEE2E2'], // Soft Red
                                        ]);
                                    }
                                } else {
                                    if ($status && $paraf) {
                                        // Completed & Paraf
                                        $sheet->setCellValue($cell, '✓');
                                        $sheet->getStyle($cell)->applyFromArray([
                                            'font' => ['bold' => true, 'color' => ['rgb' => '006100']],
                                            'fill' => [
                                                'fillType' => Fill::FILL_SOLID,
                                                'color' => ['rgb' => 'C6EFCE'], // Soft Green
                                            ],
                                        ]);
                                    } elseif (isset($this->statusMap[$key]) && !$status) {
                                        // Scheduled but not done
                                        $sheet->getStyle($cell)->getFill()->applyFromArray([
                                            'fillType' => Fill::FILL_SOLID,
                                            'color' => ['rgb' => 'DBEAFE'], // Soft Blue
                                        ]);
                                    }
                                }
                            }
                        }

                        $currentRow++;
                    }
                }

                // --- 4. LEGEND / KETERANGAN WARNA SECTION ---
                $legendStartRow = $currentRow + 2;

                $sheet->mergeCells("B{$legendStartRow}:D{$legendStartRow}");
                $sheet->setCellValue("B{$legendStartRow}", "KETERANGAN STATUS & WARNA:");
                $sheet->getStyle("B{$legendStartRow}")->getFont()->setBold(true)->setSize(11)->setColor(new Color('0F172A'));

                // Item 1: Selesai & Paraf
                $r1 = $legendStartRow + 1;
                $sheet->setCellValue("B{$r1}", "✓");
                $sheet->getStyle("B{$r1}")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => '006100']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'C6EFCE']],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CBD5E1']]],
                ]);
                $sheet->mergeCells("C{$r1}:E{$r1}");
                $sheet->setCellValue("C{$r1}", "Selesai & Paraf");
                $sheet->getStyle("C{$r1}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getRowDimension($r1)->setRowHeight(20);

                // Item 2: Dijadwalkan
                $r2 = $legendStartRow + 2;
                $sheet->setCellValue("B{$r2}", "");
                $sheet->getStyle("B{$r2}")->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'DBEAFE']],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CBD5E1']]],
                ]);
                $sheet->mergeCells("C{$r2}:E{$r2}");
                $sheet->setCellValue("C{$r2}", "Dijadwalkan (Belum Selesai)");
                $sheet->getStyle("C{$r2}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getRowDimension($r2)->setRowHeight(20);

                // Item 3: Hari Libur / Weekend
                $r3 = $legendStartRow + 3;
                $sheet->setCellValue("B{$r3}", "");
                $sheet->getStyle("B{$r3}")->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'FEE2E2']],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CBD5E1']]],
                ]);
                $sheet->mergeCells("C{$r3}:E{$r3}");
                $sheet->setCellValue("C{$r3}", "Hari Libur / Akhir Pekan");
                $sheet->getStyle("C{$r3}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getRowDimension($r3)->setRowHeight(20);
            }
        ];
    }

    public function columnWidths(): array
    {
        $cols = [
            'A' => 6,
            'B' => 42,
            'C' => 22,
        ];

        $colIndex = 4;
        $totalTanggalCol = $this->tanggalCount * 2;

        for ($i = 0; $i < $totalTanggalCol; $i++) {
            $colLetter = Coordinate::stringFromColumnIndex($colIndex++);
            $cols[$colLetter] = 3.8;
        }

        $colLetter = Coordinate::stringFromColumnIndex($colIndex);
        $cols[$colLetter] = 30;

        return $cols;
    }
}
