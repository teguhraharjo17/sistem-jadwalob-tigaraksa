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

use App\Services\MileniaApiService;

class LaporanHarianController extends Controller
{
    protected $mileniaApi;

    public function __construct(MileniaApiService $mileniaApi)
    {
        $this->mileniaApi = $mileniaApi;
    }

    public function index(Request $request)
    {
        $bulan = (int) $request->input('bulan', now()->month);
        $tahun = (int) $request->input('tahun', now()->year);

        $pekerjaanList = Checklist::where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->select('id', 'pekerjaan')
            ->orderBy('pekerjaan')
            ->get();

        $now = Carbon::createFromDate($tahun, $bulan, 1);
        $areaList = Checklist::select('area')->distinct()->pluck('area');
        
        $todayDate = Carbon::today()->toDateString();
        $tomorrowDate = Carbon::tomorrow()->toDateString();

        // Ambil daftar hari libur dari Milenia API
        $holidayData = $this->mileniaApi->getHolidays();
        $holidayDates = collect($holidayData)
            ->pluck('tanggal')
            ->map(fn($t) => Carbon::parse($t)->format('Y-m-d'))
            ->toArray();

        $holidayMap = collect($holidayData)
            ->pluck('keterangan', 'tanggal')
            ->toArray();

        // Bersihkan status terdaftar pada hari libur / weekend jika statusnya belum selesai (status 0)
        if (!empty($holidayDates)) {
            ChecklistStatus::whereIn('tanggal', $holidayDates)
                ->where('status', 0)
                ->delete();
        }

        // 1 Single consolidated query for today's and tomorrow's schedules
        $statuses = ChecklistStatus::with(['checklist:id,pekerjaan'])
            ->whereIn('tanggal', [$todayDate, $tomorrowDate])
            ->get();

        $todayCarbon = Carbon::today();
        $tomorrowCarbon = Carbon::tomorrow();

        $isTodayHoliday = $todayCarbon->isWeekend() || in_array($todayDate, $holidayDates);
        $isTomorrowHoliday = $tomorrowCarbon->isWeekend() || in_array($tomorrowDate, $holidayDates);

        $todayHolidayName = null;
        if ($isTodayHoliday) {
            $todayHolidayName = $holidayMap[$todayDate] ?? ('Akhir Pekan (' . $todayCarbon->translatedFormat('l') . ')');
        }

        $tomorrowHolidayName = null;
        if ($isTomorrowHoliday) {
            $tomorrowHolidayName = $holidayMap[$tomorrowDate] ?? ('Akhir Pekan (' . $tomorrowCarbon->translatedFormat('l') . ')');
        }

        $mapStatus = function ($items) {
            return $items->map(function ($status) {
                return [
                    'pekerjaan' => $status->checklist->pekerjaan ?? '(Tidak ditemukan)',
                    'status' => $status->status,
                ];
            })->values()->all();
        };

        $jadwalHariIniPagi  = $isTodayHoliday ? [] : $mapStatus($statuses->where('tanggal', $todayDate)->where('shift', 'Pagi'));
        $jadwalHariIniSiang = $isTodayHoliday ? [] : $mapStatus($statuses->where('tanggal', $todayDate)->where('shift', 'Siang'));
        $jadwalBesokPagi   = $isTomorrowHoliday ? [] : $mapStatus($statuses->where('tanggal', $tomorrowDate)->where('shift', 'Pagi'));
        $jadwalBesokSiang  = $isTomorrowHoliday ? [] : $mapStatus($statuses->where('tanggal', $tomorrowDate)->where('shift', 'Siang'));

        return view('pages.laporanharian.index', compact(
            'now',
            'pekerjaanList',
            'areaList',
            'jadwalHariIniPagi',
            'jadwalHariIniSiang',
            'jadwalBesokPagi',
            'jadwalBesokSiang',
            'isTodayHoliday',
            'isTomorrowHoliday',
            'todayHolidayName',
            'tomorrowHolidayName'
        ));
    }

    public function data(Request $request)
    {
        $bulan = (int) $request->input('bulan', now()->month);
        $tahun = (int) $request->input('tahun', now()->year);

        $startDate = Carbon::createFromDate($tahun, $bulan, 1)->startOfMonth()->toDateString();
        $endDate   = Carbon::createFromDate($tahun, $bulan, 1)->endOfMonth()->toDateString();

        $laporan = LaporanHarian::with(['checklist:id,pekerjaan'])
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->orderBy('tanggal', 'desc');

        return DataTables::of($laporan)
            ->addIndexColumn()
            ->editColumn('tanggal', fn($row) => \Carbon\Carbon::parse($row->tanggal)->format('d-m-Y'))
            ->addColumn('pekerjaan', fn($row) => $row->checklist->pekerjaan ?? '-')
            ->editColumn('bukti', function($row) {
                if (!$row->bukti) return '-';
                $decoded = json_decode($row->bukti, true);
                $buktiList = is_array($decoded) ? $decoded : [$row->bukti];
                $fallback = asset('assets/media/svg/files/blank-image.svg');

                return collect($buktiList)->map(function($b) use ($fallback) {
                    $url = asset('storage/'.$b);
                    return "<img src='$url' loading='lazy' onerror=\"this.onerror=null;this.src='$fallback';\" class='img-thumbnail bukti-thumb me-1 mb-1' style='max-height:80px; cursor:pointer'>";
                })->implode('');
            })
            ->editColumn('paraf', function($row) {
                $fallback = asset('assets/media/svg/files/blank-image.svg');
                return $row->paraf
                    ? "<img src='".asset('storage/'.$row->paraf)."' loading='lazy' onerror=\"this.onerror=null;this.src='$fallback';\" class='img-thumbnail img-paraf-preview' style='max-height:50px;'>"
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

        // Upload file paraf dengan kompresi
        $parafPath = null;
        if ($request->hasFile('paraf')) {
            $parafPath = $this->storeCompressedImage($request->file('paraf'), 'paraf_approve');
        }

        // Upload bukti kerja dengan kompresi & resize
        $buktiPaths = [];
        if ($request->hasFile('bukti')) {
            foreach ($request->file('bukti') as $file) {
                $buktiPaths[] = $this->storeCompressedImage($file, 'bukti_laporan');
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

    /**
     * Helper function to compress and resize uploaded image using PHP GD
     */
    private function storeCompressedImage($file, $folder = 'bukti_laporan')
    {
        $extension = strtolower($file->getClientOriginalExtension());
        
        // If it's a PDF or non-standard image, store normally
        if (!in_array($extension, ['jpg', 'jpeg', 'png', 'webp'])) {
            return $file->store($folder, 'public');
        }

        try {
            $imageString = file_get_contents($file->getRealPath());
            $image = @imagecreatefromstring($imageString);

            if (!$image) {
                return $file->store($folder, 'public');
            }

            // Auto-rotate if EXIF orientation data exists (common on smartphones)
            if (function_exists('exif_read_data') && in_array($extension, ['jpg', 'jpeg'])) {
                try {
                    $exif = @exif_read_data($file->getRealPath());
                    if (!empty($exif['Orientation'])) {
                        switch ($exif['Orientation']) {
                            case 3:
                                $image = imagerotate($image, 180, 0);
                                break;
                            case 6:
                                $image = imagerotate($image, -90, 0);
                                break;
                            case 8:
                                $image = imagerotate($image, 90, 0);
                                break;
                        }
                    }
                } catch (\Throwable $e) {
                    // Ignore EXIF read errors
                }
            }

            $width = imagesx($image);
            $height = imagesy($image);
            $maxDimension = 1200;

            // Resize if dimensions exceed maxDimension
            if ($width > $maxDimension || $height > $maxDimension) {
                if ($width > $height) {
                    $newWidth = $maxDimension;
                    $newHeight = (int) ($height * ($maxDimension / $width));
                } else {
                    $newHeight = $maxDimension;
                    $newWidth = (int) ($width * ($maxDimension / $height));
                }

                $resizedImage = imagecreatetruecolor($newWidth, $newHeight);

                // Preserve PNG/WebP transparency
                if (in_array($extension, ['png', 'webp'])) {
                    imagealphablending($resizedImage, false);
                    imagesavealpha($resizedImage, true);
                }

                imagecopyresampled($resizedImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                imagedestroy($image);
                $image = $resizedImage;
            }

            // Generate unique filename
            $filename = Str::random(40) . '.jpg';
            $path = $folder . '/' . $filename;
            $fullPath = storage_path('app/public/' . $path);

            // Ensure directory exists
            $dir = dirname($fullPath);
            if (!file_exists($dir)) {
                mkdir($dir, 0755, true);
            }

            // Save as optimized JPEG with 75% quality
            imagejpeg($image, $fullPath, 75);
            imagedestroy($image);

            return $path;
        } catch (\Throwable $e) {
            // Fallback to standard store if compression fails
            return $file->store($folder, 'public');
        }
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
                $parafPath = $this->storeCompressedImage($request->file('paraf'), 'paraf_approve');
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
                $newPath = $this->storeCompressedImage($newFile, 'bukti_laporan');

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
                $buktiFinal[] = $this->storeCompressedImage($file, 'bukti_laporan');
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
