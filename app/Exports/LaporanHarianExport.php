<?php

namespace App\Exports;

use App\Models\LaporanHarian;
use Carbon\Carbon;
use App\Models\LaporanHarianApproval;
use Maatwebsite\Excel\Concerns\{
    FromCollection, WithHeadings, WithMapping, WithStyles,
    WithColumnWidths, WithDrawings, WithEvents
};
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class LaporanHarianExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    WithStyles,
    WithColumnWidths,
    WithDrawings,
    WithEvents
{
    protected $bulan, $tahun, $data, $approval;

    public function __construct($bulan, $tahun, $approval)
    {
        $this->bulan = $bulan;
        $this->tahun = $tahun;
        $this->approval = $approval;
    }

    public function collection()
    {
        return $this->data = LaporanHarian::with('checklist')
            ->whereMonth('tanggal', $this->bulan)
            ->whereYear('tanggal', $this->tahun)
            ->get();
    }

    public function headings(): array
    {
        return [[
            'Tanggal',
            'Shift',
            'Jam Mulai',
            'Jam Selesai',
            'Item Pekerjaan',
            'Area',
            'Hasil Pekerjaan',
            'Mengetahui',
            'Paraf',
            'Bukti Kerja',
        ]];
    }

    public function map($row): array
    {
        return [
            Carbon::parse($row->tanggal)->format('d-m-Y'),
            $row->shift,
            $row->jam_mulai,
            $row->jam_selesai,
            $row->checklist->pekerjaan ?? '-',
            $row->area,
            $row->hasil_pekerjaan,
            $row->mengetahui,
            '', // paraf
            '', // bukti
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 14,
            'B' => 12,
            'C' => 14,
            'D' => 14,
            'E' => 30,
            'F' => 25,
            'G' => 25,
            'H' => 25,
            'I' => 35,
            'J' => 35,
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
                $startRow = 3;
                $headerRow = 2;
                $judulRow = 1;
                $totalRows = $dataCount + 2;

                // Judul
                $bulanNama = Carbon::createFromDate($this->tahun, $this->bulan, 1)->translatedFormat('F Y');
                $judul = "LAPORAN HARIAN ($bulanNama)";
                $sheet->insertNewRowBefore($judulRow, 1);
                $sheet->mergeCells("A1:J1");
                $sheet->setCellValue("A1", $judul);
                $sheet->getStyle("A1")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(30);

                // Header style
                $sheet->getStyle("A{$headerRow}:J{$headerRow}")->applyFromArray([
                    'font' => ['bold' => true],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'wrapText' => true,
                    ],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                ]);

                // Data style
                $sheet->getStyle("A{$startRow}:J{$totalRows}")->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'wrapText' => true,
                    ],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                ]);

                // Tinggi baris data
                for ($i = $startRow; $i <= $totalRows; $i++) {
                    $sheet->getRowDimension($i)->setRowHeight(80);
                }

                // Baris tambahan untuk approval
                $approvalRow1 = $totalRows + 2;
                $approvalRow2 = $approvalRow1 + 1;
                $approvalRow3 = $approvalRow2 + 1;

                // 1. Label "Menyetujui"
                $sheet->mergeCells("J{$approvalRow1}:J{$approvalRow1}");
                $sheet->setCellValue("J{$approvalRow1}", 'Menyetujui');
                $sheet->getStyle("J{$approvalRow1}")->applyFromArray([
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    'font' => ['bold' => true],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                ]);

                // 2. Row kosong dengan tinggi
                $sheet->getRowDimension($approvalRow2)->setRowHeight(80);
                $sheet->getStyle("J{$approvalRow2}")->applyFromArray([
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                ]);

                // 3. Nama
                $sheet->mergeCells("J{$approvalRow3}:J{$approvalRow3}");
                $sheet->setCellValue("J{$approvalRow3}", $this->approval->nama ?? '');
                $sheet->getStyle("J{$approvalRow3}")->applyFromArray([
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                ]);
            }
        ];
    }

    public function drawings(): array
    {
        $drawings = [];

        foreach ($this->data as $index => $row) {
            $excelRow = $index + 2;

            if ($row->paraf && file_exists(storage_path('app/public/' . $row->paraf))) {
                $drawingParaf = new Drawing();
                $drawingParaf->setPath(storage_path('app/public/' . $row->paraf));
                $drawingParaf->setHeight(60);
                $drawingParaf->setCoordinates('I' . $excelRow);
                $drawingParaf->setOffsetX(12);
                $drawingParaf->setOffsetY(10);
                $drawings[] = $drawingParaf;
            }

            if ($row->bukti && file_exists(storage_path('app/public/' . $row->bukti))) {
                $drawingBukti = new Drawing();
                $drawingBukti->setPath(storage_path('app/public/' . $row->bukti));
                $drawingBukti->setHeight(60);
                $drawingBukti->setCoordinates('J' . $excelRow);
                $drawingBukti->setOffsetX(12);
                $drawingBukti->setOffsetY(10);
                $drawings[] = $drawingBukti;
            }
        }

        // Tambah TTD approval di bawah kolom J
        $approvalRow = count($this->data) + 3 + 1; // totalRows + 2 + 1 = posisi gambar
        if ($this->approval && file_exists(storage_path('app/public/' . $this->approval->ttd_path))) {
            $approvalTTD = new Drawing();
            $approvalTTD->setPath(storage_path('app/public/' . $this->approval->ttd_path));
            $approvalTTD->setHeight(60);
            $approvalTTD->setCoordinates('J' . $approvalRow);
            $approvalTTD->setOffsetX(12);
            $approvalTTD->setOffsetY(10);
            $drawings[] = $approvalTTD;
        }

        return $drawings;
    }
}