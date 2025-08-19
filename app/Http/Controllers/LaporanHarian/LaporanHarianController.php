<?php

namespace App\Http\Controllers\LaporanHarian;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Checklist;
use App\Models\ChecklistStatus;
use App\Models\LaporanHarian;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

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

        return view('pages.laporanharian.index', compact(
            'now',
            'pekerjaanList',
            'laporanList'
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

        $parafPath = null;
        if ($request->hasFile('paraf')) {
            $parafPath = $request->file('paraf')->store('paraf_approve', 'public');
        }

        if ($validated['jam_selesai'] < $validated['jam_mulai']) {
            $error = ['jam_selesai' => ['Jam selesai tidak boleh lebih awal dari jam mulai.']];

            if ($request->ajax()) {
                return response()->json(['errors' => $error], 422);
            }

            return back()->withErrors($error)->withInput();
        }

        $buktiPath = null;
        if ($request->hasFile('bukti')) {
            $buktiPath = $request->file('bukti')->store('bukti_laporan', 'public');
        }

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

        return response()->json([
            'laporan' => $laporan,
            'pekerjaanList' => $pekerjaanList
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
            $error = ['jam_selesai' => ['Jam selesai tidak boleh lebih awal dari jam mulai.']];

            if ($request->ajax()) {
                return response()->json(['errors' => $error], 422);
            }

            return back()->withErrors($error)->withInput();
        }

        $parafPath = $laporan->paraf;
        if ($request->hasFile('paraf')) {
            if ($parafPath) {
                Storage::disk('public')->delete($parafPath);
            }
            $parafPath = $request->file('paraf')->store('paraf_approve', 'public');
        }

        $buktiPath = $laporan->bukti;
        if ($request->hasFile('bukti')) {
            if ($buktiPath) {
                Storage::disk('public')->delete($buktiPath);
            }
            $buktiPath = $request->file('bukti')->store('bukti_laporan', 'public');
        }

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

}
