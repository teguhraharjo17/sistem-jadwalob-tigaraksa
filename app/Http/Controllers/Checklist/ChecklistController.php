<?php

namespace App\Http\Controllers\Checklist;

use App\Models\Checklist;
use App\Http\Controllers\Controller;
use App\Models\ChecklistStatus;
use Illuminate\Support\Carbon;
use \Illuminate\Http\Request;

class ChecklistController extends Controller
{
    public function index(Request $request)
    {
        $bulan = $request->get('bulan', now()->month);
        $tahun = $request->get('tahun', now()->year);
        $now = \Carbon\Carbon::create($tahun, $bulan, 1);

        $checklists = Checklist::where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->get()
            ->groupBy('area');

        $areas = Checklist::select('area')->distinct()->pluck('area');

        $statuses = ChecklistStatus::whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->get()
            ->mapWithKeys(function ($item) {
                $key = $item->checklist_id . '_' . $item->tanggal . '_' . $item->shift;
                return [$key => $item->status];
            })
            ->toArray();

        $statusData = [];
        foreach ($statuses as $key => $status) {
            $statusData[$key] = $status;
        }

        $parafStatuses = \App\Models\LaporanHarian::whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->whereNotNull('paraf')
            ->get()
            ->mapWithKeys(function ($laporan) {
                $key = $laporan->checklist_id . '_' . $laporan->tanggal . '_' . $laporan->shift;
                return [$key => 1];
            })->toArray();

        return view('pages.checklist.index', compact(
            'checklists',
            'now',
            'areas',
            'statusData',
            'parafStatuses'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'area' => 'required|string|max:255',
            'pekerjaan' => 'required|string|max:255',
            'periodic' => 'required|string|max:100',
            'bulan' => 'required|integer|min:1|max:12',
            'tahun' => 'required|integer|min:2000',
            'keterangan' => 'nullable|string',
        ]);

        Checklist::create([
            'area' => $validated['area'],
            'pekerjaan' => $validated['pekerjaan'],
            'periodic_cleaning' => $validated['periodic'],
            'bulan' => $validated['bulan'],
            'tahun' => $validated['tahun'],
            'keterangan' => $validated['keterangan'],
        ]);

        return response()->json(['success' => true, 'message' => 'Data berhasil disimpan.']);
    }
}
