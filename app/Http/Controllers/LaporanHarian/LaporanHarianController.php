<?php

namespace App\Http\Controllers\LaporanHarian;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Checklist;
use App\Models\ChecklistStatus;
use App\Models\LaporanHarian;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
            ->orderBy('tanggal', 'asc')
            ->get();

        $now = Carbon::createFromDate($tahun, $bulan, 1);
        $areaList = Checklist::select('area')->distinct()->pluck('area');
        $today = Carbon::today();

        $jadwalHariIniPagi = ChecklistStatus::with('checklist')
            ->whereDate('tanggal', $today)
            ->where('shift', 'Pagi')
            ->where('status', 0)
            ->get()
            ->map(fn ($status) => $status->checklist->pekerjaan)
            ->filter()
            ->toArray();

        $jadwalHariIniSiang = ChecklistStatus::with('checklist')
            ->whereDate('tanggal', $today)
            ->where('shift', 'Siang')
            ->where('status', 0)
            ->get()
            ->map(fn ($status) => $status->checklist->pekerjaan)
            ->filter()
            ->toArray();

        return view('pages.laporanharian.index', compact(
            'now', 'pekerjaanList', 'laporanList', 'areaList',
            'jadwalHariIniPagi', 'jadwalHariIniSiang'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tanggal' => 'required|date',
            'shift' => 'required|in:Pagi,Siang',
            'jam_mulai' => 'required',
            'jam_selesai' => 'required',
            'item_pekerjaan' => 'required|exists:checklists,id',
            'area' => 'required|string|max:255',
            'bukti' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'hasil_pekerjaan' => 'nullable|string',
            'mengetahui' => 'nullable|string|max:255',
            'paraf' => 'nullable|image|mimes:jpg,jpeg,png|max:4096',
        ]);

        // Validasi jam selesai tidak lebih awal dari jam mulai
        if ($validated['jam_selesai'] < $validated['jam_mulai']) {
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
        $buktiPath = null;
        if ($request->hasFile('bukti')) {
            $buktiPath = $request->file('bukti')->store('bukti_laporan', 'public');
        }

        // Simpan laporan
        LaporanHarian::create([
            'tanggal' => $validated['tanggal'],
            'shift' => $validated['shift'],
            'jam_mulai' => $validated['jam_mulai'],
            'jam_selesai' => $validated['jam_selesai'],
            'checklist_id' => $validated['item_pekerjaan'],
            'area' => $validated['area'],
            'bukti' => $buktiPath,
            'hasil_pekerjaan' => $validated['hasil_pekerjaan'] ?? null,
            'mengetahui' => $validated['mengetahui'] ?? null,
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

        return redirect()
            ->route('laporanharian.index')
            ->with('success', 'Laporan Harian berhasil disimpan.');
    }

    public function edit($id)
    {
        $laporan = LaporanHarian::findOrFail($id);
        $pekerjaanList = Checklist::select('id', 'pekerjaan')->orderBy('pekerjaan')->get();
        $areaList = Checklist::select('area')->distinct()->pluck('area');

        return response()->json([
            'laporan' => $laporan,
            'pekerjaanList' => $pekerjaanList,
            'areaList' => $areaList
        ]);
    }

    public function update(Request $request, $id)
    {
        $laporan = LaporanHarian::findOrFail($id);

        $validated = $request->validate([
            'tanggal' => 'required|date',
            'shift' => 'required|in:Pagi,Siang',
            'jam_mulai' => 'required',
            'jam_selesai' => 'required',
            'item_pekerjaan' => 'required|exists:checklists,id',
            'area' => 'required|string|max:255',
            'bukti' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'hasil_pekerjaan' => 'nullable|string',
            'mengetahui' => 'nullable|string|max:255',
            'paraf' => 'nullable|image|mimes:jpg,jpeg,png|max:4096',
        ]);

        if ($validated['jam_selesai'] < $validated['jam_mulai']) {
            return back()->withErrors([
                'jam_selesai' => 'Jam selesai tidak boleh lebih awal dari jam mulai.'
            ])->withInput();
        }

        // Validasi: hanya bisa update jika tanggal & shift sesuai dengan jadwal checklist
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

        // Paraf (via gambar atau signature)
        $parafPath = $laporan->paraf;
        if ($request->filled('paraf_signature_edit')) {
            if ($parafPath) {
                Storage::disk('public')->delete($parafPath);
            }

            $base64 = str_replace(['data:image/png;base64,', ' '], ['', '+'], $request->input('paraf_signature_edit'));
            $filename = 'paraf_' . Str::random(10) . '.png';
            $path = "paraf_approve/{$filename}";
            Storage::disk('public')->put($path, base64_decode($base64));
            $parafPath = $path;
        }

        if ($request->hasFile('paraf')) {
            if ($parafPath) {
                Storage::disk('public')->delete($parafPath);
            }
            $parafPath = $request->file('paraf')->store('paraf_approve', 'public');
        }

        // Bukti
        $buktiPath = $laporan->bukti;
        if ($request->hasFile('bukti')) {
            if ($buktiPath) {
                Storage::disk('public')->delete($buktiPath);
            }
            $buktiPath = $request->file('bukti')->store('bukti_laporan', 'public');
        }

        // Update laporan
        $laporan->update([
            'tanggal' => $validated['tanggal'],
            'shift' => $validated['shift'],
            'jam_mulai' => $validated['jam_mulai'],
            'jam_selesai' => $validated['jam_selesai'],
            'checklist_id' => $validated['item_pekerjaan'],
            'area' => $validated['area'],
            'bukti' => $buktiPath,
            'hasil_pekerjaan' => $validated['hasil_pekerjaan'] ?? null,
            'mengetahui' => $validated['mengetahui'] ?? null,
            'paraf' => $parafPath,
        ]);

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
            ->select('id', 'pekerjaan')
            ->orderBy('pekerjaan')
            ->get();

        return response()->json($pekerjaanList);
    }
}
