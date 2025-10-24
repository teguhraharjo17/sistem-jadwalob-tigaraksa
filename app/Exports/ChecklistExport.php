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
use Illuminate\Support\Facades\Http;

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
                foreach ($this->data as $items) {
                    $rowCount += 1 + count($items);
                }

                $colCount = 3 + ($this->tanggalCount * 2) + 1;

                // Merge header
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

                // Apply border + center
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

                // Hari libur
                $holidays = Http::get('http://192.168.0.8:8000/api/libur')
                    ->successful()
                    ? collect(Http::get('http://192.168.0.8:8000/api/libur')->json())
                        ->pluck('tanggal')
                        ->map(fn($t) => Carbon::parse($t)->format('Y-m-d'))
                        ->toArray()
                    : [];

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
                            $isWeekend = in_array(Carbon::parse($date)->dayOfWeek, [Carbon::SATURDAY, Carbon::SUNDAY]);
                            $isHoliday = in_array($date, $holidays);

                            foreach (['Pagi', 'Siang'] as $shiftIndex => $shift) {
                                $key = $item->id . '_' . $date . '_' . $shift;
                                $status = $this->statusMap[$key] ?? 0;
                                $paraf = $this->parafMap[$key] ?? false;

                                $colIndex = 4 + (($i - 1) * 2) + $shiftIndex;
                                $cell = Coordinate::stringFromColumnIndex($colIndex) . $currentRow;

                                // 🔴 Hari libur
                                if ($isHoliday || $isWeekend) {
                                    $sheet->getStyle($cell)->getFill()->applyFromArray([
                                        'fillType' => Fill::FILL_SOLID,
                                        'color' => ['rgb' => 'FFE5E5'], // Light red
                                    ]);
                                }

                                // ✅ Sudah dikerjakan dan paraf ✔️
                                if ($status && $paraf) {
                                    $sheet->getStyle($cell)->getFill()->applyFromArray([
                                        'fillType' => Fill::FILL_SOLID,
                                        'color' => ['rgb' => '92D050'], // Green
                                    ]);
                                }

                                // 🔵 Dijadwalkan tapi belum selesai (status 0)
                                elseif (isset($this->statusMap[$key]) && !$status) {
                                    $sheet->getStyle($cell)->getFill()->applyFromArray([
                                        'fillType' => Fill::FILL_SOLID,
                                        'color' => ['rgb' => '00B0F0'], // Blue
                                    ]);
                                }
                            }
                        }

                        $currentRow++;
                    }
                }
                // Tambahkan keterangan warna di bawah tabel
                $legendStartRow = $rowCount + 2;

                $sheet->setCellValue("B{$legendStartRow}", "Keterangan Warna:");
                $sheet->getStyle("B{$legendStartRow}")->getFont()->setBold(true);

                // Hijau (✔️ Sudah dikerjakan dan paraf)
                $sheet->setCellValue("C" . ($legendStartRow + 1), "✔️ Selesai & Paraf");
                $sheet->getStyle("B" . ($legendStartRow + 1))->getFill()->applyFromArray([
                    'fillType' => Fill::FILL_SOLID,
                    'color' => ['rgb' => '92D050'], // Green
                ]);

                // Biru (📅 Dijadwalkan, tapi belum dikerjakan)
                $sheet->setCellValue("C" . ($legendStartRow + 2), "📅 Dijadwalkan, belum selesai");
                $sheet->getStyle("B" . ($legendStartRow + 2))->getFill()->applyFromArray([
                    'fillType' => Fill::FILL_SOLID,
                    'color' => ['rgb' => '00B0F0'], // Blue
                ]);

                // Merah (🔴 Hari Libur / Weekend)
                $sheet->setCellValue("C" . ($legendStartRow + 3), "🔴 Hari Libur / Akhir Pekan");
                $sheet->getStyle("B" . ($legendStartRow + 3))->getFill()->applyFromArray([
                    'fillType' => Fill::FILL_SOLID,
                    'color' => ['rgb' => 'FFE5E5'], // Red
                ]);
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
