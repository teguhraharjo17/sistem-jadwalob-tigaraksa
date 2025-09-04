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

class ChecklistExport implements FromArray, WithHeadings, WithStyles, WithEvents, WithColumnWidths
{
    protected $bulan, $tahun, $data, $tanggalCount, $statusMap, $parafMap;

    public function __construct($bulan, $tahun)
    {
        $this->bulan = $bulan;
        $this->tahun = $tahun;

        $this->data = Checklist::where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->orderBy('area')
            ->get()
            ->groupBy('area'); // ✅ dikelompokkan per area

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
        $headRow1 = ['No', 'Pekerjaan', 'Periodic Cleaning'];
        for ($i = 1; $i <= $this->tanggalCount; $i++) {
            $headRow1[] = $i;
            $headRow1[] = '';
        }
        $headRow1[] = 'Keterangan';

        $headRow2 = ['', '', ''];
        for ($i = 1; $i <= $this->tanggalCount; $i++) {
            $headRow2[] = 'P';
            $headRow2[] = 'S';
        }
        $headRow2[] = '';

        return [$headRow1, $headRow2];
    }

    public function array(): array
    {
        $rows = [];
        $counter = 1;

        foreach ($this->data as $area => $items) {
            $rows[] = [$area];

            foreach ($items as $item) {
                $row = [
                    $counter++,
                    $item->pekerjaan,
                    $item->frequency_count . 'x ' . match($item->frequency_unit) {
                        'per_hari'     => 'per Hari',
                        'per_x_hari'   => 'per ' . $item->frequency_interval . ' Hari',
                        'per_minggu'   => 'per Minggu',
                        'per_x_minggu' => 'per ' . $item->frequency_interval . ' Minggu',
                        'per_bulan'    => 'per Bulan',
                    }
                ];

                for ($i = 1; $i <= $this->tanggalCount; $i++) {
                    foreach (['Pagi', 'Siang'] as $shift) {
                        $row[] = '';
                    }
                }

                $row[] = $item->keterangan ?? '';
                $rows[] = $row;
            }
        }

        return $rows;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
            2 => ['font' => ['bold' => true]],
        ];
    }

    public function registerEvents(): array
    {
        return [
            \Maatwebsite\Excel\Events\AfterSheet::class => function ($event) {
                $sheet = $event->sheet->getDelegate();

                $rowCount = 2;
                foreach ($this->data as $area => $items) {
                    $rowCount += 1;
                    $rowCount += count($items);
                }

                $colCount = 3 + ($this->tanggalCount * 2) + 1;

                for ($i = 1; $i <= $this->tanggalCount; $i++) {
                    $startCol = 4 + (($i - 1) * 2);
                    $endCol = $startCol + 1;
                    $startLetter = Coordinate::stringFromColumnIndex($startCol);
                    $endLetter = Coordinate::stringFromColumnIndex($endCol);
                    $sheet->mergeCells("{$startLetter}1:{$endLetter}1");
                }

                $sheet->mergeCells("A1:A2");
                $sheet->mergeCells("B1:B2");
                $sheet->mergeCells("C1:C2");
                $lastCol = Coordinate::stringFromColumnIndex($colCount);
                $sheet->mergeCells("{$lastCol}1:{$lastCol}2");

                $sheet->getStyle("A1:{$lastCol}{$rowCount}")
                    ->applyFromArray([
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                            'vertical' => Alignment::VERTICAL_CENTER,
                            'wrapText' => true,
                        ],
                        'borders' => [
                            'allBorders' => ['borderStyle' => Border::BORDER_THIN],
                        ],
                    ]);

                for ($i = 1; $i <= $rowCount; $i++) {
                    $sheet->getRowDimension($i)->setRowHeight(25);
                }

                $currentRow = 3;
                foreach ($this->data as $area => $items) {
                    $lastCol = Coordinate::stringFromColumnIndex($colCount);
                    $sheet->mergeCells("A{$currentRow}:{$lastCol}{$currentRow}");
                    $sheet->setCellValue("A{$currentRow}", strtoupper($area));
                    $sheet->getStyle("A{$currentRow}:{$lastCol}{$currentRow}")->applyFromArray([
                        'font' => ['bold' => true],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'color' => ['rgb' => 'E0E0E0'],
                        ],
                    ]);

                    $currentRow++;

                    foreach ($items as $item) {
                        for ($i = 1; $i <= $this->tanggalCount; $i++) {
                            $date = Carbon::create($this->tahun, $this->bulan, $i)->format('Y-m-d');
                            foreach (['Pagi', 'Siang'] as $shiftIndex => $shift) {
                                $key = $item->id . '_' . $date . '_' . $shift;
                                $status = $this->statusMap[$key] ?? 0;
                                $paraf = $this->parafMap[$key] ?? false;

                                if ($status && $paraf) {
                                    $colIndex = 4 + (($i - 1) * 2) + $shiftIndex;
                                    $colLetter = Coordinate::stringFromColumnIndex($colIndex);
                                    $sheet->getStyle($colLetter . $currentRow)->getFill()->applyFromArray([
                                        'fillType' => Fill::FILL_SOLID,
                                        'color' => ['rgb' => '92D050'],
                                    ]);
                                }
                            }
                        }

                        $currentRow++;
                    }
                }
            }
        ];
    }

    public function columnWidths(): array
    {
        $cols = [
            'A' => 5,
            'B' => 40,
            'C' => 25,
        ];

        $colIndex = 4;
        $totalTanggalCol = $this->tanggalCount * 2;

        for ($i = 0; $i < $totalTanggalCol; $i++) {
            $colLetter = Coordinate::stringFromColumnIndex($colIndex++);
            $cols[$colLetter] = 5;
        }

        $colLetter = Coordinate::stringFromColumnIndex($colIndex);
        $cols[$colLetter] = 30;

        return $cols;
    }
}
