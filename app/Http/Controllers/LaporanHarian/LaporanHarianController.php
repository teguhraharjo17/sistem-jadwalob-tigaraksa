<?php

namespace App\Http\Controllers\LaporanHarian;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Checklist;
use App\Models\ChecklistStatus;
use App\Models\LaporanHarian;
use App\Models\LaporanHarianApproval;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Exports\LaporanHarianExport;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class LaporanHarianController extends Controller
{
    public function index(Request $request)
    {
        $bulan = $request->input('bulan', now()->month);
        $tahun = $request->input('tahun', now()->year);

        $pekerjaanList = Checklist::where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->select('id', 'pekerjaan')
            ->orderBy('pekerjaan')
            ->get();

        $laporanList = LaporanHarian::with('checklist')
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->orderBy('tanggal', 'desc')
            ->orderBy('jam_selesai', 'desc')
            ->get();

        $now = Carbon::createFromDate($tahun, $bulan, 1);
        $areaList = Checklist::select('area')->distinct()->pluck('area');
        $today = Carbon::today();
        $tomorrow = Carbon::tomorrow();

        $jadwalHariIniPagi = ChecklistStatus::with(['checklist'])
            ->whereDate('tanggal', $today)
            ->where('shift', 'Pagi')
            ->get()
            ->map(function ($status) {
                return [
                    'pekerjaan' => $status->checklist->pekerjaan ?? '(Tidak ditemukan)',
                    'status' => $status->status,
                ];
            });

        $jadwalHariIniSiang = ChecklistStatus::with(['checklist'])
            ->whereDate('tanggal', $today)
            ->where('shift', 'Siang')
            ->get()
            ->map(function ($status) {
                return [
                    'pekerjaan' => $status->checklist->pekerjaan ?? '(Tidak ditemukan)',
                    'status' => $status->status,
                ];
            });

        $jadwalBesokPagi = ChecklistStatus::with(['checklist'])
            ->whereDate('tanggal', $tomorrow)
            ->where('shift', 'Pagi')
            ->get()
            ->map(function ($status) {
                return [
                    'pekerjaan' => $status->checklist->pekerjaan ?? '(Tidak ditemukan)',
                    'status' => $status->status,
                ];
            });

        $jadwalBesokSiang = ChecklistStatus::with(['checklist'])
            ->whereDate('tanggal', $tomorrow)
            ->where('shift', 'Siang')
            ->get()
            ->map(function ($status) {
                return [
                    'pekerjaan' => $status->checklist->pekerjaan ?? '(Tidak ditemukan)',
                    'status' => $status->status,
                ];
            });

        return view('pages.laporanharian.index', compact(
            'now',
            'pekerjaanList',
            'laporanList',
            'areaList',
            'jadwalHariIniPagi',
            'jadwalHariIniSiang',
            'jadwalBesokPagi',
            'jadwalBesokSiang'
        ));
    }

    public function data(Request $request)
    {
        $bulan = $request->input('bulan', now()->month);
        $tahun = $request->input('tahun', now()->year);

        $laporan = LaporanHarian::with('checklist')
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->orderBy('tanggal','desc');

        return DataTables::of($laporan)
            ->addIndexColumn()
            ->editColumn('tanggal', fn($row) => \Carbon\Carbon::parse($row->tanggal)->format('d-m-Y'))
            ->addColumn('pekerjaan', fn($row) => $row->checklist->pekerjaan ?? '-')
            ->editColumn('bukti', function($row) {
                if (!$row->bukti) return '-';
                $decoded = json_decode($row->bukti, true);
                $buktiList = is_array($decoded) ? $decoded : [$row->bukti];

                return collect($buktiList)->map(function($b) {
                    $url = asset('storage/'.$b);
                    return "<img src='$url' loading='lazy' class='img-thumbnail bukti-thumb me-1 mb-1' style='max-height:80px; cursor:pointer'>";
                })->implode('');
            })
            ->editColumn('paraf', function($row) {
                return $row->paraf
                    ? "<img src='".asset('storage/'.$row->paraf)."' loading='lazy' class='img-thumbnail img-paraf-preview' style='max-height:50px;'>"
                    : '-';
            })
            ->addColumn('opsi', function($row) {
                $buttons = '<div class="d-flex justify-content-center align-items-center gap-1 flex-nowrap">';
                if (auth()->user()->hasPermission('laporanharian_approve')) {
                    $buttons .= '
                        <button class="btn btn-sm btn-primary px-3 py-2 approve-btn" data-id="'.$row->id.'">
                            <i class="fas fa-check-double fs-9"></i> <span class="fs-9">Setujui</span>
                        </button>';
                }
                if (auth()->user()->hasPermission('laporanharian_edit')) {
                    $buttons .= '
                        <button class="btn btn-sm btn-light border px-3 py-2 edit-btn" data-id="'.$row->id.'">
                            <i class="fas fa-edit fs-9"></i> <span class="fs-9">Edit</span>
                        </button>
                        <button class="btn btn-sm btn-light-danger border px-3 py-2 btn-delete-laporan" data-id="'.$row->id.'">
                            <i class="fas fa-trash-alt fs-9 text-danger"></i> <span class="fs-9 text-danger">Hapus</span>
                        </button>';
                }
                $buttons .= '</div>';
                return $buttons;
            })
            ->rawColumns(['bukti','paraf','opsi'])
            ->make(true);
    }

    public function store(Request $request)
    {
        if (!auth()->user()->hasPermission('laporanharian_create')) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'tanggal' => 'required|date',
            'shift' => 'required|in:Pagi,Siang',
            'jam_mulai' => 'required',
            'jam_selesai' => 'required',
            'item_pekerjaan' => 'required|exists:checklists,id',
            'area' => 'required|string|max:255',
            'bukti.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'hasil_pekerjaan' => 'nullable|string',
            'mengetahui' => 'nullable|string|max:255',
            'paraf' => 'nullable|image|mimes:jpg,jpeg,png|max:4096',
        ]);

        // Validasi jam selesai tidak lebih awal dari jam mulai
        if ($validated['jam_selesai'] < $validated['jam_mulai']) {
            if ($request->ajax()) {
                return response()->json([
                    'errors' => ['jam_selesai' => ['Jam selesai tidak boleh lebih awal dari jam mulai.']]
                ], 422);
            }
            return back()->withErrors([
                'jam_selesai' => 'Jam selesai tidak boleh lebih awal dari jam mulai.'
            ])->withInput();
        }

        // Cek apakah tanggal dan shift ini memang dijadwalkan
        $checklist = Checklist::findOrFail($validated['item_pekerjaan']);
        $statusAda = ChecklistStatus::where('checklist_id', $checklist->id)
            ->where('tanggal', $validated['tanggal'])
            ->where('shift', $validated['shift'])
            ->exists();

        if (!$statusAda) {
            if ($request->ajax()) {
                return response()->json([
                    'errors' => ['tanggal' => ['Pekerjaan ini tidak dijadwalkan pada tanggal dan shift tersebut.']]
                ], 422);
            }
            return back()->withErrors([
                'tanggal' => 'Pekerjaan ini tidak dijadwalkan pada tanggal dan shift tersebut.'
            ])->withInput();
        }

        // Upload file paraf
        $parafPath = null;
        if ($request->hasFile('paraf')) {
            $parafPath = $request->file('paraf')->store('paraf_approve', 'public');
        }

        // Upload bukti kerja
        $buktiPaths = [];
        if ($request->hasFile('bukti')) {
            foreach ($request->file('bukti') as $file) {
                $buktiPaths[] = $file->store('bukti_laporan', 'public');
            }
        }


        // Simpan laporan
        LaporanHarian::create([
            'tanggal' => $validated['tanggal'],
            'shift' => $validated['shift'],
            'jam_mulai' => $validated['jam_mulai'],
            'jam_selesai' => $validated['jam_selesai'],
            'checklist_id' => $validated['item_pekerjaan'],
            'rincian_pekerjaan' => $checklist->pekerjaan,
            'area' => $validated['area'],
            'bukti' => $buktiPaths ? json_encode($buktiPaths) : null,
            'hasil_pekerjaan' => $validated['hasil_pekerjaan'] ?? '',
            'mengetahui' => $validated['mengetahui'] ?? '',
            'paraf' => $parafPath,
        ]);

        // Update status checklist
        ChecklistStatus::updateOrCreate(
            [
                'checklist_id' => $validated['item_pekerjaan'],
                'tanggal' => $validated['tanggal'],
                'shift' => $validated['shift'],
            ],
            ['status' => 1]
        );

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Laporan Harian berhasil disimpan.'
            ]);
        }

        return redirect()
            ->route('laporanharian.index')
            ->with('success', 'Laporan Harian berhasil disimpan.');
    }

    public function edit($id)
    {
        if (!auth()->user()->hasPermission('laporanharian_edit') && !auth()->user()->hasPermission('laporanharian_approve')) {
            abort(403, 'Unauthorized action.');
        }

        $laporan = LaporanHarian::findOrFail($id);

        // Normalisasi bukti jadi array
        $buktiList = [];
        if ($laporan->bukti) {
            $decoded = json_decode($laporan->bukti, true);
            $buktiList = is_array($decoded) ? $decoded : [$laporan->bukti];
        }

        $laporan->bukti_list = $buktiList; // kirim array ke JS

        $pekerjaanList = Checklist::select('id', 'pekerjaan', 'area')->orderBy('pekerjaan')->get();
        $areaList = Checklist::select('area')->distinct()->pluck('area');

        return response()->json([
            'laporan' => $laporan,
            'pekerjaanList' => $pekerjaanList,
            'areaList' => $areaList
        ]);
    }

    public function update(Request $request, $id)
    {
        if (!auth()->user()->hasPermission('laporanharian_edit') && !auth()->user()->hasPermission('laporanharian_approve')) {
            abort(403, 'Unauthorized action.');
        }

        $laporan = LaporanHarian::findOrFail($id);

        // Case A: Approval Form Submission
        if ($request->has('mengetahui') && !$request->has('tanggal')) {
            if (!auth()->user()->hasPermission('laporanharian_approve')) {
                abort(403, 'Unauthorized action.');
            }

            $validated = $request->validate([
                'hasil_pekerjaan' => 'required|string|max:255',
                'mengetahui' => 'required|string|max:255',
                'paraf' => 'nullable|image|mimes:jpg,jpeg,png|max:4096',
                'paraf_signature' => 'nullable|string',
            ]);

            $parafPath = $laporan->paraf;

            // Handle signature pad
            if ($request->filled('paraf_signature')) {
                if ($parafPath && Storage::disk('public')->exists($parafPath)) {
                    Storage::disk('public')->delete($parafPath);
                }

                $base64 = str_replace(['data:image/png;base64,', ' '], ['', '+'], $request->input('paraf_signature'));
                $filename = 'paraf_' . \Illuminate\Support\Str::random(10) . '.png';
                $path = "paraf_approve/{$filename}";
                Storage::disk('public')->put($path, base64_decode($base64));
                $parafPath = $path;
            }

            // Handle file upload paraf
            if ($request->hasFile('paraf')) {
                if ($parafPath && Storage::disk('public')->exists($parafPath)) {
                    Storage::disk('public')->delete($parafPath);
                }
                $parafPath = $request->file('paraf')->store('paraf_approve', 'public');
            }

            $laporan->update([
                'hasil_pekerjaan' => $validated['hasil_pekerjaan'],
                'mengetahui' => $validated['mengetahui'],
                'paraf' => $parafPath,
            ]);

            // Update status checklist to approved (status = 1)
            ChecklistStatus::updateOrCreate(
                [
                    'checklist_id' => $laporan->checklist_id,
                    'tanggal' => $laporan->tanggal,
                    'shift' => $laporan->shift,
                ],
                ['status' => 1]
            );

            if ($request->ajax()) {
                return response()->json(['message' => 'Persetujuan berhasil disimpan.']);
            }

            return redirect()->route('laporanharian.index')->with('success', 'Persetujuan berhasil disimpan.');
        }

        // Case B: Standard Edit Form Submission
        if (!auth()->user()->hasPermission('laporanharian_edit')) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'tanggal' => 'required|date',
            'shift' => 'required|in:Pagi,Siang',
            'jam_mulai' => 'required',
            'jam_selesai' => 'required',
            'item_pekerjaan' => 'required|exists:checklists,id',
            'area' => 'required|string|max:255',
            'bukti.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'bukti_lama' => 'nullable|array',
            'bukti_ganti.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        if ($validated['jam_selesai'] < $validated['jam_mulai']) {
            return back()->withErrors([
                'jam_selesai' => 'Jam selesai tidak boleh lebih awal dari jam mulai.'
            ])->withInput();
        }

        $checklist = Checklist::findOrFail($validated['item_pekerjaan']);
        $statusAda = ChecklistStatus::where('checklist_id', $checklist->id)
            ->where('tanggal', $validated['tanggal'])
            ->where('shift', $validated['shift'])
            ->exists();

        if (!$statusAda) {
            return back()->withErrors([
                'tanggal' => 'Pekerjaan ini tidak dijadwalkan pada tanggal dan shift tersebut.'
            ])->withInput();
        }

        $oldChecklistId = $laporan->checklist_id;
        $oldTanggal = $laporan->tanggal;
        $oldShift = $laporan->shift;

        // Bukti Processing
        $buktiFinal = [];
        $buktiLama = $request->input('bukti_lama', []);

        foreach ($buktiLama as $index => $oldPath) {
            if ($request->hasFile("bukti_ganti.$index")) {
                $newFile = $request->file("bukti_ganti.$index");
                $newPath = $newFile->store('bukti_laporan', 'public');

                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
                $buktiFinal[] = $newPath;
            } else {
                $buktiFinal[] = $oldPath;
            }
        }

        if ($request->hasFile('bukti')) {
            foreach ($request->file('bukti') as $file) {
                $buktiFinal[] = $file->store('bukti_laporan', 'public');
            }
        }

        $laporan->update([
            'tanggal' => $validated['tanggal'],
            'shift' => $validated['shift'],
            'jam_mulai' => $validated['jam_mulai'],
            'jam_selesai' => $validated['jam_selesai'],
            'checklist_id' => $validated['item_pekerjaan'],
            'rincian_pekerjaan' => $checklist->pekerjaan,
            'area' => $validated['area'],
            'bukti' => count($buktiFinal) > 1 ? json_encode($buktiFinal) : ($buktiFinal[0] ?? null),
        ]);

        $hasChanged = ($oldChecklistId != $laporan->checklist_id) || 
                      ($oldTanggal != $laporan->tanggal) || 
                      ($oldShift != $laporan->shift);

        if ($hasChanged) {
            $oldCombinationHasOtherReports = LaporanHarian::where('checklist_id', $oldChecklistId)
                ->where('tanggal', $oldTanggal)
                ->where('shift', $oldShift)
                ->where('id', '!=', $laporan->id)
                ->exists();
            
            if (!$oldCombinationHasOtherReports) {
                ChecklistStatus::where([
                    'checklist_id' => $oldChecklistId,
                    'tanggal' => $oldTanggal,
                    'shift' => $oldShift,
                ])->update(['status' => 0]);
            }
        }

        ChecklistStatus::updateOrCreate(
            [
                'checklist_id' => $laporan->checklist_id,
                'tanggal' => $laporan->tanggal,
                'shift' => $laporan->shift,
            ],
            ['status' => 1]
        );

        if ($request->ajax()) {
            return response()->json(['message' => 'Laporan berhasil diperbarui.']);
        }

        return redirect()->route('laporanharian.index')->with('success', 'Laporan berhasil diperbarui.');
    }

    public function getPekerjaanTersedia(Request $request)
    {
        $tanggal = $request->input('tanggal');
        $shift = $request->input('shift');

        if (!$tanggal || !$shift) {
            return response()->json([]);
        }

        // Ambil semua checklist_id yang valid dan status-nya 0 di tanggal dan shift ini
        $checklistIds = ChecklistStatus::where('tanggal', $tanggal)
            ->where('shift', $shift)
            ->where('status', 0) // hanya yang belum dikerjakan
            ->pluck('checklist_id');

        // Filter checklist hanya yang benar-benar dijadwalkan untuk shift ini
        $pekerjaanList = Checklist::whereIn('id', $checklistIds)
            ->where(function ($query) use ($shift) {
                $query->where('frequency_count', 2)
                    ->orWhere(function ($q) use ($shift) {
                        $q->where('frequency_count', 1)
                            ->where('default_shift', $shift);
                    });
            })
            ->select('id', 'pekerjaan', 'area')
            ->orderBy('pekerjaan')
            ->get();

        return response()->json($pekerjaanList);
    }

    public function exportExcel(Request $request)
    {
        $bulan = $request->input('bulan', now()->month);
        $tahun = $request->input('tahun', now()->year);
        $ajax = $request->boolean('ajax', false);

        $approval = LaporanHarianApproval::where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->first();

        if (!$approval) {
            if ($ajax) {
                return response()->json([
                    'needs_approval' => true,
                    'message' => 'Laporan bulan ini belum disetujui. Silakan isi nama & tanda tangan untuk menyetujui.'
                ]);
            } else {
                abort(403, 'Laporan belum disetujui.');
            }
        }

        $bulanNama = \Carbon\Carbon::createFromDate($tahun, $bulan, 1)->translatedFormat('F');
        $namaFile = "LaporanHarian_{$bulanNama}_{$tahun}.xlsx";

        return Excel::download(new LaporanHarianExport($bulan, $tahun, $approval), $namaFile);
    }


    public function storeApproval(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'ttd_base64' => 'required|string',
            'bulan' => 'required|numeric|min:1|max:12',
            'tahun' => 'required|numeric',
        ]);

        $exists = LaporanHarianApproval::where('bulan', $request->bulan)
            ->where('tahun', $request->tahun)
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'Sudah disetujui sebelumnya'], 409);
        }

        $base64 = str_replace(['data:image/png;base64,', ' '], ['', '+'], $request->ttd_base64);
        $filename = 'ttd_' . uniqid() . '.png';
        $path = "paraf_menyetujui/{$filename}";
        Storage::disk('public')->put($path, base64_decode($base64));

        $approval = LaporanHarianApproval::create([
            'bulan' => $request->bulan,
            'tahun' => $request->tahun,
            'nama' => $request->nama,
            'ttd_path' => $path,
        ]);

        return response()->json(['message' => 'Disetujui dan disimpan.', 'approval' => $approval]);
    }

    public function destroy($id)
    {
        if (!auth()->user()->hasPermission('laporanharian_edit')) {
            abort(403, 'Unauthorized action.');
        }

        $laporan = LaporanHarian::findOrFail($id);

        if ($laporan->paraf && Storage::disk('public')->exists($laporan->paraf)) {
            Storage::disk('public')->delete($laporan->paraf);
        }

        if ($laporan->bukti) {
            $buktiList = json_decode($laporan->bukti, true);
            $buktiList = is_array($buktiList) ? $buktiList : [$laporan->bukti];

            foreach ($buktiList as $filePath) {
                if (Storage::disk('public')->exists($filePath)) {
                    Storage::disk('public')->delete($filePath);
                }
            }
        }

        $otherReportsExist = LaporanHarian::where('checklist_id', $laporan->checklist_id)
            ->where('tanggal', $laporan->tanggal)
            ->where('shift', $laporan->shift)
            ->where('id', '!=', $laporan->id)
            ->exists();

        if (!$otherReportsExist) {
            ChecklistStatus::where([
                'checklist_id' => $laporan->checklist_id,
                'tanggal' => $laporan->tanggal,
                'shift' => $laporan->shift,
            ])->update(['status' => 0]);
        }

        $laporan->delete();

        return response()->json(['message' => 'Laporan berhasil dihapus.']);
    }


}
