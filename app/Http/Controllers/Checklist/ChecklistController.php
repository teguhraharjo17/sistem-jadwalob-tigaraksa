<?php

namespace App\Http\Controllers\Checklist;

use App\Models\Checklist;
use App\Http\Controllers\Controller;
use App\Models\ChecklistStatus;
use App\Models\LaporanHarian;
use Illuminate\Support\Carbon;
use \Illuminate\Http\Request;
use App\Exports\ChecklistExport;
use Maatwebsite\Excel\Facades\Excel;

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

        $parafStatuses = LaporanHarian::whereMonth('tanggal', $bulan)
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
            'bulan' => 'required|integer|min:1|max:12',
            'tahun' => 'required|integer|min:2000',
            'start_date' => 'required|date',
            'keterangan' => 'nullable|string',
            'frequency_count' => 'required|integer|min:1',
            'frequency_unit' => 'required|in:per_hari,per_x_hari,per_minggu',
            'frequency_interval' => 'nullable|integer|min:1',
            'default_shift' => 'nullable|in:Pagi,Siang',
        ]);

        $startDate = \Carbon\Carbon::parse($validated['start_date']);
        if ($startDate->month != $validated['bulan'] || $startDate->year != $validated['tahun']) {
            return response()->json([
                'success' => false,
                'message' => 'Tanggal mulai harus sesuai dengan bulan dan tahun yang dipilih.'
            ], 422);
        }

        $checklist = Checklist::create([
            'area' => $validated['area'],
            'pekerjaan' => $validated['pekerjaan'],
            'bulan' => $validated['bulan'],
            'tahun' => $validated['tahun'],
            'start_date' => $validated['start_date'],
            'keterangan' => $validated['keterangan'] ?? null,
            'frequency_count' => $validated['frequency_count'],
            'frequency_unit' => $validated['frequency_unit'],
            'frequency_interval' => $validated['frequency_interval'] ?? null,
            'default_shift' => $validated['default_shift'] ?? null,
        ]);

        $this->generateSchedule($checklist);

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil disimpan.'
        ]);
    }


    private function generateSchedule(Checklist $checklist)
    {
        $dates = collect();

        $start = Carbon::parse($checklist->start_date);
        $end   = Carbon::create($checklist->tahun, $checklist->bulan)->endOfMonth();

        switch ($checklist->frequency_unit) {
            case 'per_hari':
                for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
                    if ($date->isWeekend()) {
                        continue;
                    }
                    $dates->push($date->copy());
                }
                break;

            case 'per_x_hari':
                $interval = $checklist->frequency_interval ?? 1;
                $date = $start->copy();

                while ($date->lte($end)) {
                    if (!$date->isWeekend()) {
                        $dates->push($date->copy());
                    }

                    $daysAdded = 0;
                    while ($daysAdded < $interval) {
                        $date->addDay();

                        if (!$date->isWeekend()) {
                            $daysAdded++;
                        }
                    }
                }
                break;

            case 'per_minggu':
                $weekStart = $start->copy();
                while ($weekStart->lte($end)) {
                    $targetDate = $weekStart->copy()->startOfWeek(Carbon::MONDAY);

                    if ($targetDate->isSaturday()) {
                        $targetDate->subDay();
                    } elseif ($targetDate->isSunday()) {
                        $targetDate->subDays(2);
                    }

                    if ($targetDate->month === $start->month) {
                        $dates->push($targetDate);
                    }

                    $weekStart->addWeek();
                }
                break;
        }

        foreach ($dates as $date) {
            $shifts = [];

            if ($checklist->frequency_unit === 'per_hari') {
                if ($checklist->frequency_count == 1) {
                    $shifts[] = $checklist->default_shift ?? 'Pagi';
                } else {
                    $shifts = ['Pagi', 'Siang'];
                }
            } else {
                if ($checklist->frequency_count == 2) {
                    $shifts = ['Pagi', 'Siang'];
                } else {
                    $shifts[] = $checklist->default_shift ?? 'Pagi';
                }
            }

            foreach ($shifts as $shift) {
                ChecklistStatus::firstOrCreate([
                    'checklist_id' => $checklist->id,
                    'tanggal' => $date->toDateString(),
                    'shift' => $shift,
                ], [
                    'status' => 0
                ]);
            }
        }
    }

    public function edit($id)
    {
        $checklist = Checklist::findOrFail($id);
        $areas = Checklist::select('area')->distinct()->pluck('area');

        return response()->json([
            'checklist' => $checklist,
            'areas' => $areas
        ]);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'area' => 'required|string|max:255',
            'pekerjaan' => 'required|string|max:255',
            'bulan' => 'required|integer|min:1|max:12',
            'tahun' => 'required|integer|min:2000',
            'start_date' => 'required|date',
            'keterangan' => 'nullable|string',
            'frequency_count' => 'required|integer|min:1',
            'frequency_unit' => 'required|in:per_hari,per_x_hari,per_minggu',
            'frequency_interval' => 'nullable|integer|min:1',
            'default_shift' => 'nullable|in:Pagi,Siang',
        ]);

        $startDate = \Carbon\Carbon::parse($validated['start_date']);
        if ($startDate->month != $validated['bulan'] || $startDate->year != $validated['tahun']) {
            return response()->json([
                'success' => false,
                'message' => 'Tanggal mulai harus sesuai dengan bulan dan tahun yang dipilih.'
            ], 422);
        }

        $checklist = Checklist::findOrFail($id);

        ChecklistStatus::where('checklist_id', $checklist->id)->delete();

        $checklist->update([
            'area' => $validated['area'],
            'pekerjaan' => $validated['pekerjaan'],
            'bulan' => $validated['bulan'],
            'tahun' => $validated['tahun'],
            'start_date' => $validated['start_date'],
            'keterangan' => $validated['keterangan'] ?? null,
            'frequency_count' => $validated['frequency_count'],
            'frequency_unit' => $validated['frequency_unit'],
            'frequency_interval' => $validated['frequency_interval'] ?? null,
            'default_shift' => $validated['default_shift'] ?? null,
        ]);

        $this->generateSchedule($checklist);

        return response()->json([
            'success' => true,
            'message' => 'Checklist berhasil diperbarui.'
        ]);
    }

    public function exportExcel(Request $request)
    {
        $bulan = $request->input('bulan', now()->month);
        $tahun = $request->input('tahun', now()->year);

        $namaFile = 'ChecklistPembersihan_' . \Carbon\Carbon::create($tahun, $bulan)->translatedFormat('F_Y') . '.xlsx';

        return Excel::download(new ChecklistExport($bulan, $tahun), $namaFile);
    }
}

