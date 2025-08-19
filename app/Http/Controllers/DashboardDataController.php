<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;

class DashboardDataController extends Controller
{
    public function charts($year): JsonResponse
    {
        return response()->json([
            'checklist_progress' => DB::table('checklist_status')
                ->leftJoin('laporan_harian', function ($join) {
                    $join->on('checklist_status.checklist_id', '=', 'laporan_harian.checklist_id')
                        ->on('checklist_status.tanggal', '=', 'laporan_harian.tanggal')
                        ->on('checklist_status.shift', '=', 'laporan_harian.shift');
                })
                ->select(
                    'checklist_status.tanggal',
                    DB::raw('COUNT(*) as total'),
                    DB::raw("SUM(CASE WHEN laporan_harian.mengetahui IS NOT NULL AND laporan_harian.paraf IS NOT NULL THEN 1 ELSE 0 END) as selesai")
                )
                ->whereYear('checklist_status.tanggal', $year)
                ->groupBy('checklist_status.tanggal')
                ->orderBy('checklist_status.tanggal')
                ->get(),

            'area_distribution' => DB::table('checklists')
                ->select('area', DB::raw('COUNT(*) as jumlah'))
                ->where('tahun', $year)
                ->groupBy('area')
                ->get(),

            'laporan_perbulan' => DB::table('laporan_harian')
                ->select(DB::raw('MONTH(tanggal) as bulan'), DB::raw('COUNT(*) as jumlah'))
                ->whereYear('tanggal', $year)
                ->groupBy('bulan')
                ->orderBy('bulan')
                ->get(),

            'shift_comparison' => DB::table('checklist_status')
                ->select('tanggal', 'shift', DB::raw('COUNT(*) as jumlah'))
                ->whereYear('tanggal', $year)
                ->groupBy('tanggal', 'shift')
                ->orderBy('tanggal')
                ->get(),

            'top_jobs' => DB::table('laporan_harian')
                ->join('checklists', 'laporan_harian.checklist_id', '=', 'checklists.id')
                ->select('checklists.pekerjaan', DB::raw('COUNT(*) as jumlah'))
                ->whereYear('laporan_harian.tanggal', $year)
                ->whereNotNull('checklists.pekerjaan') // ✅ Tidak null
                ->where('checklists.pekerjaan', '!=', '') // ✅ Tidak kosong
                ->groupBy('checklists.pekerjaan')
                ->orderByDesc('jumlah')
                ->limit(10)
                ->get(),
        ]);
    }

}
