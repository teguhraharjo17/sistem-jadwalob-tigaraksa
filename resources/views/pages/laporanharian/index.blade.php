<x-default-layout>
    @section('title', 'Laporan Kerja Harian')

    @php
        $totalJadwalHariIni = count($jadwalHariIniPagi) + count($jadwalHariIniSiang);
        $totalSelesaiHariIni = collect($jadwalHariIniPagi)->where('status', 1)->count() + collect($jadwalHariIniSiang)->where('status', 1)->count();
    @endphp

    <div class="container py-4 py-lg-5">
        <section class="hero-laporan mb-4">
            <div>
                <h1 class="hero-laporan__title">Laporan Kerja Harian</h1>
                <p class="hero-laporan__subtitle mb-0">Menu ini digunakan untuk melihat, menambah, mengubah, dan mengekspor laporan kerja harian.</p>
            </div>

            <div class="hero-laporan__stats">
                <div class="hero-stat-card">
                    <span>Periode</span>
                    <strong>{{ $now->translatedFormat('F Y') }}</strong>
                </div>
                <div class="hero-stat-card">
                    <span>Pekerjaan Aktif</span>
                    <strong>{{ count($pekerjaanList) }}</strong>
                </div>
                <div class="hero-stat-card">
                    <span>Jadwal Hari Ini</span>
                    <strong>{{ $totalJadwalHariIni }}</strong>
                </div>
                <div class="hero-stat-card">
                    <span>Selesai Hari Ini</span>
                    <strong>{{ $totalSelesaiHariIni }}</strong>
                </div>
            </div>
        </section>

        <div class="p-4 p-lg-5 rounded-4 shadow-sm bg-white laporan-shell">
            <form method="GET" action="{{ route('laporanharian.index') }}" id="filterForm" class="row g-3 align-items-end mb-4">
                <div class="col-lg-6">
                    <div class="section-copy">
                        <span class="section-kicker">Filter Laporan</span>
                        <h2 class="section-title">Pilih periode kerja</h2>
                        <p class="text-muted mb-0">Tabel, jadwal, dan ekspor akan menyesuaikan bulan serta tahun yang dipilih.</p>
                    </div>
                </div>

                <div class="col-sm-6 col-lg-2">
                    <label for="filter_bulan" class="form-label">Bulan</label>
                    <select id="filter_bulan" name="bulan" class="form-select">
                        @for ($i = 1; $i <= 12; $i++)
                            <option value="{{ $i }}" {{ $now->month == $i ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create()->month($i)->translatedFormat('F') }}
                            </option>
                        @endfor
                    </select>
                </div>

                <div class="col-sm-6 col-lg-2">
                    <label for="filter_tahun" class="form-label">Tahun</label>
                    <select id="filter_tahun" name="tahun" class="form-select">
                        @for ($i = now()->year; $i >= now()->year - 5; $i--)
                            <option value="{{ $i }}" {{ $now->year == $i ? 'selected' : '' }}>
                                {{ $i }}
                            </option>
                        @endfor
                    </select>
                </div>

                <div class="col-lg-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter me-2"></i>Terapkan
                    </button>
                </div>
            </form>
            <div class="row g-4 mb-4">
                <div class="col-xl-8">
                    <div class="schedule-card">
                        <div class="schedule-card__head">
                            <div>
                                <span class="section-kicker">Hari Ini</span>
                                <h3 class="section-title mb-1">Jadwal pekerjaan aktif</h3>
                                <p class="text-muted mb-0">{{ \Carbon\Carbon::today()->translatedFormat('l, d F Y') }}</p>
                            </div>
                            <span class="schedule-badge">{{ $totalSelesaiHariIni }}/{{ $totalJadwalHariIni ?: 0 }} selesai</span>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="shift-card">
                                    <div class="shift-card__title">
                                        <strong>Shift Pagi</strong>
                                        <span>{{ count($jadwalHariIniPagi) }} tugas</span>
                                    </div>
                                    @if(count($jadwalHariIniPagi))
                                        <div class="shift-card__list">
                                            @foreach ($jadwalHariIniPagi as $item)
                                                <div class="shift-task {{ $item['status'] == 1 ? 'is-done' : '' }}">
                                                    <i class="fas {{ $item['status'] == 1 ? 'fa-check-circle' : 'fa-clock' }}"></i>
                                                    <span>{{ $item['pekerjaan'] }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="shift-card__empty">Tidak ada jadwal untuk shift pagi.</div>
                                    @endif
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="shift-card">
                                    <div class="shift-card__title">
                                        <strong>Shift Siang</strong>
                                        <span>{{ count($jadwalHariIniSiang) }} tugas</span>
                                    </div>
                                    @if(count($jadwalHariIniSiang))
                                        <div class="shift-card__list">
                                            @foreach ($jadwalHariIniSiang as $item)
                                                <div class="shift-task {{ $item['status'] == 1 ? 'is-done' : '' }}">
                                                    <i class="fas {{ $item['status'] == 1 ? 'fa-check-circle' : 'fa-clock' }}"></i>
                                                    <span>{{ $item['pekerjaan'] }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="shift-card__empty">Tidak ada jadwal untuk shift siang.</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4">
                    <div class="quick-guide">
                        <span class="section-kicker">Panduan Cepat</span>
                        <h3 class="section-title mb-3">Alur yang paling sering dipakai</h3>
                        <div class="guide-point">
                            <span>1</span>
                            <div>Pilih periode agar data tabel dan ekspor sesuai.</div>
                        </div>
                        <div class="guide-point">
                            <span>2</span>
                            <div>Pilih tanggal dan shift dulu saat menambah laporan.</div>
                        </div>
                        <div class="guide-point">
                            <span>3</span>
                            <div>Upload bukti seperlunya dengan preview yang lebih ringan.</div>
                        </div>
                        <div class="guide-point">
                            <span>4</span>
                            <div>Approval baru diminta saat ekspor diperlukan.</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table id="tableLaporanHarian" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th rowspan="2" class="text-center">No</th>
                            <th rowspan="2" class="text-center">Tanggal</th>
                            <th colspan="2" class="text-center">Jam Kerja</th>
                            <th rowspan="2" class="text-center">Item Pekerjaan</th>
                            <th rowspan="2" class="text-center">Area</th>
                            <th rowspan="2" class="text-center">Bukti</th>
                            <th rowspan="2" class="text-center">Hasil Pekerjaan</th>
                            <th colspan="2" class="text-center">Mengetahui</th>
                            @if(auth()->user()->hasRole('Admin'))
                                <th rowspan="2" class="text-center">Opsi</th>
                            @endif
                        </tr>
                        <tr>
                            <th class="text-center">Mulai</th>
                            <th class="text-center">Selesai</th>
                            <th class="text-center">Nama</th>
                            <th class="text-center">Paraf</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="modal fade" id="addLaporanHarian" tabindex="-1" aria-labelledby="addLaporanHarianLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <form id="formTambahLaporanHarian" method="POST" action="{{ route('laporanharian.store') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title" id="addLaporanHarianLabel">Tambah Laporan Harian</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                        </div>

                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="tanggal" class="form-label">Tanggal</label>
                                <input type="date" class="form-control" id="tanggal" name="tanggal" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Shift</label>
                                <div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="shift" id="shiftPagi" value="Pagi" checked>
                                        <label class="form-check-label" for="shiftPagi">Pagi</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="shift" id="shiftSiang" value="Siang">
                                        <label class="form-check-label" for="shiftSiang">Siang</label>
                                    </div>
                                </div>
                            </div>

                            <fieldset class="border p-3 mb-3">
                                <legend class="w-auto px-2">Jam Kerja</legend>
                                <div class="row">
                                    <div class="col-md-6">
                                        <label for="jam_mulai" class="form-label">Mulai</label>
                                        <input type="time" class="form-control" id="jam_mulai" name="jam_mulai" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="jam_selesai" class="form-label">Selesai</label>
                                        <input type="time" class="form-control" id="jam_selesai" name="jam_selesai" required>
                                    </div>
                                </div>
                            </fieldset>

                            <div class="mb-3">
                                <label for="item_pekerjaan" class="form-label">Item Pekerjaan</label>
                                <select class="form-select" id="item_pekerjaan" name="item_pekerjaan" required>
                                    <option value="" disabled selected>Pilih Item Pekerjaan</option>
                                    @foreach ($pekerjaanList as $pekerjaan)
                                        <option value="{{ $pekerjaan->id }}">{{ $pekerjaan->pekerjaan }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="area" class="form-label">Area</label>
                                <select class="form-select" id="area" name="area" required>
                                    <option value="" disabled selected>Pilih Area</option>
                                    @foreach ($areaList as $area)
                                        <option value="{{ $area }}">{{ $area }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Upload Bukti Kerja (dari Kamera / Galeri)</label>

                                <table class="table table-bordered" id="buktiUploadTable">
                                    <thead>
                                        <tr>
                                            <th>File</th>
                                            <th style="width: 50px;">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="buktiUploadBody">
                                        <tr>
                                            <td>
                                                <input type="file" name="bukti[]" accept="image/*,application/pdf" capture="environment" class="form-control mb-1 proof-input" required onchange="previewFile(this, 'preview_default')">
                                                <div id="preview_default" class="mt-1 proof-preview"></div>
                                            </td>
                                            <td class="text-center align-middle">
                                                <button type="button" class="btn btn-sm btn-danger" onclick="removeBuktiRow(this)">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>

                                <button type="button" class="btn btn-sm btn-secondary" onclick="addBuktiRow()">+ Tambah Bukti</button>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal fade" id="editLaporanModal" tabindex="-1" aria-labelledby="editLaporanLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <form id="formEditLaporan" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="modal-header">
                            <h5 class="modal-title" id="editLaporanLabel">Edit Laporan Harian</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="edit_tanggal" class="form-label">Tanggal</label>
                                <input type="date" class="form-control" id="edit_tanggal" name="tanggal" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Shift</label>
                                <div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="shift" id="edit_shiftPagi" value="Pagi">
                                        <label class="form-check-label" for="edit_shiftPagi">Pagi</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="shift" id="edit_shiftSiang" value="Siang">
                                        <label class="form-check-label" for="edit_shiftSiang">Siang</label>
                                    </div>
                                </div>
                            </div>

                            <fieldset class="border p-3 mb-3">
                                <legend class="w-auto px-2">Jam Kerja</legend>
                                <div class="row">
                                    <div class="col-md-6">
                                        <label for="edit_jam_mulai" class="form-label">Mulai</label>
                                        <input type="time" class="form-control" id="edit_jam_mulai" name="jam_mulai" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="edit_jam_selesai" class="form-label">Selesai</label>
                                        <input type="time" class="form-control" id="edit_jam_selesai" name="jam_selesai" required>
                                    </div>
                                </div>
                            </fieldset>

                            <div class="mb-3">
                                <label for="edit_item_pekerjaan" class="form-label">Item Pekerjaan</label>
                                <select class="form-select" id="edit_item_pekerjaan" name="item_pekerjaan" required>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="edit_area" class="form-label">Area</label>
                                <select class="form-select" id="edit_area" name="area" required>
                                    <option value="" disabled>Pilih Area</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Upload Bukti (Kamera / Galeri)</label>

                                <table class="table table-bordered" id="editBuktiTable">
                                    <thead>
                                        <tr>
                                            <th>File</th>
                                            <th style="width: 50px;">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="editBuktiBody">
                                    </tbody>
                                </table>

                                <button type="button" class="btn btn-sm btn-secondary" onclick="addEditBuktiRow()">+ Tambah Bukti</button>

                                <div id="preview_bukti_existing" class="mt-2">
                                </div>
                            </div>

                            <fieldset class="border p-3 mb-3">
                                <legend class="w-auto px-2">Kolom Persetujuan</legend>

                                <div class="mb-3">
                                    <label for="edit_hasil_pekerjaan" class="form-label">Hasil Pekerjaan</label>
                                    <input type="text" class="form-control" id="edit_hasil_pekerjaan" name="hasil_pekerjaan">
                                </div>

                                <div class="mb-3">
                                    <label for="edit_mengetahui" class="form-label">Mengetahui</label>
                                    <input type="text" class="form-control" id="edit_mengetahui" name="mengetahui">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Paraf (Upload atau Gambar)</label>

                                    <input type="file" class="form-control mb-2" id="edit_paraf" name="paraf" accept="image/*">
                                    <div id="preview_paraf" class="mb-2"></div>

                                    <canvas id="editSignatureCanvas" class="border" style="width: 100%; height: 200px;"></canvas>
                                    <input type="hidden" name="paraf_signature_edit" id="paraf_signature_edit">
                                    <button type="button" class="btn btn-sm btn-secondary mt-2" onclick="clearEditSignature()">Hapus</button>
                                </div>
                            </fieldset>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary">Update</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!-- Modal Persetujuan -->
        <div class="modal fade" id="modalApproval" tabindex="-1" aria-labelledby="modalApprovalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <form id="formApproval">
                    @csrf
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Persetujuan Laporan</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body">
                            <div class="alert alert-warning d-flex align-items-center gap-2" role="alert">
                                <i class="fas fa-exclamation-triangle text-warning fs-4"></i>
                                <div>
                                    <strong>Perhatian:</strong> Laporan bulan ini belum <u>disetujui</u>. 
                                    Harap isi nama dan tanda tangan untuk menyetujui sebelum melakukan ekspor.
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="approval_nama" class="form-label">Nama Penyetuju</label>
                                <input type="text" class="form-control" id="approval_nama" name="nama" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Tanda Tangan</label>
                                <canvas id="approvalCanvas" class="border" style="width:100%; height:200px;"></canvas>
                                <input type="hidden" id="approval_ttd_base64" name="ttd_base64">
                                <button type="button" class="btn btn-sm btn-secondary mt-2" onclick="approvalPad.clear()">Hapus</button>
                            </div>

                            <input type="hidden" name="bulan" id="approval_bulan">
                            <input type="hidden" name="tahun" id="approval_tahun">
                        </div>

                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">Simpan & Download</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="modal fade" id="imagePreviewModal" tabindex="-1" aria-labelledby="imagePreviewModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-xl">
                <div class="modal-content bg-dark">
                    <div class="modal-body p-0 position-relative text-center">
                        <img id="modalPreviewImage" src="" alt="Preview" class="img-fluid" style="max-height: 90vh; cursor: zoom-in;">
                        <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <style>
        .highlight-title {
            background-color: #f8f9fa;
            padding: 10px 20px;
            border-radius: 8px;
            display: inline-block;
            font-weight: bold;
            box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.1);
        }
        #tableLaporanHarian tbody tr:hover {
            background-color: #f2f2f2;
            cursor: pointer;
        }
        .custom-button {
            display: block;
            text-align: center;
        }

        .dataTables_wrapper .dataTable {
            border-collapse: collapse;
            width: 100%;
            font-size: 0.9rem;
            color: #333;
        }

        .dataTables_wrapper .dataTable thead th {
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
            font-weight: bold;
        }

        .dataTables_wrapper .dataTable tbody tr:nth-child(odd) {
            background-color: #f9f9f9;
        }

        .custom-button {
            font-size: 0.875rem;
            padding: 6px 12px;
            border-radius: 4px;
        }

        .custom-button:hover {
            color: #fff;
            background-color: #0056b3;
            border-color: #0056b3;
        }

        @media (max-width: 768px) {
            .dataTables_wrapper .dataTable {
                font-size: 0.8rem;
            }

            .custom-buttons-container {
                justify-content: center;
                margin-bottom: 10px;
            }

            .custom-button {
                margin-bottom: 5px;
            }
        }
        .table-responsive {
            position: relative;
            overflow: visible;
        }

        .relative .dropdown-menu {
            position: absolute !important;
            transform: translate3d(0, 38px, 0) !important;
            z-index: 1050;
            will-change: transform;
        }
        #previewImage.zoomed {
            transform: scale(2);
            cursor: zoom-out;
            transition: transform 0.3s ease;
        }

        .modal-content {
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            border: none;
        }

        .modal-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #e0e0e0;
            padding: 1rem 1.25rem;
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
        }

        .modal-title {
            font-weight: 600;
            font-size: 1.2rem;
        }

        .modal-footer {
            border-top: 1px solid #e0e0e0;
            padding: 1rem 1.25rem;
            border-bottom-left-radius: 10px;
            border-bottom-right-radius: 10px;
        }

        .modal-body label {
            font-weight: 500;
            margin-bottom: 0.3rem;
        }

        .modal-body input,
        .modal-body select {
            font-size: 0.95rem;
            padding: 0.45rem 0.75rem;
        }

        .modal-body h6 {
            margin-top: 1rem;
            font-weight: 600;
            color: #495057;
        }

        .modal-body small.text-muted {
            font-size: 0.8rem;
            display: block;
            margin-top: 0.25rem;
            margin-left: 2px;
        }

        .btn-xs {
            font-size: 0.75rem;
            padding: 4px 10px;
            line-height: 1.3;
            min-width: 90px;
            text-align: center;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .btn-xs i {
            font-size: 0.8rem;
        }

        .btn-xs:hover {
            background-color: #0d6efd;
            color: #fff;
            border-color: #0d6efd;
            transition: all 0.2s ease;
        }

        @media (max-width: 576px) {
            .opsi-buttons {
                flex-direction: column;
                align-items: stretch;
            }
        }

        fieldset {
            border: 1px dashed #999 !important;
            padding-top: 1.5rem;
            margin-top: 1rem;
            position: relative;
        }

        legend {
            font-size: 1rem;
            font-weight: 600;
            padding: 0 10px;
            width: auto;
            color: #000000;
        }

        #previewImageModal.zoomed {
            transform: scale(2);
            cursor: zoom-out;
            transition: transform 0.3s ease;
        }

        fieldset {
            border: 1px dashed #999 !important;
            padding-top: 1.5rem;
            margin-top: 1rem;
            position: relative;
        }

        legend {
            font-size: 1rem;
            font-weight: 600;
            padding: 0 10px;
            width: auto;
            color: #000000;
        }

        fieldset.border {
            border: 1px dashed #e3e3e3 !important;
            padding: 1.5rem;
            margin-top: 1rem;
            position: relative;
        }

        fieldset.border legend {
            float: unset;
            background: #fff;
            padding: 0 0.5rem;
            margin-left: 1rem;
        }
        .section-title h6 {
            font-weight: 800;
            font-size: 1rem;
        }
        .bg-light.fw-bold {
            background-color: #f0f2f5 !important;
            font-size: 1rem;
        }

        table.dataTable tbody tr.dtrg-group {
            text-align: left !important;
            padding-left: 12px;
            font-weight: bold;
            background-color: #f8f9fa !important;
            color: #000;
            text-transform: uppercase;
        }

        .pekerjaan-column {
            min-width: 250px;
            max-width: 400px;
            white-space: normal;
            word-wrap: break-word;
            text-align: left !important;
        }

        .periodic-column {
            min-width: 200px;
            max-width: 300px;
            white-space: normal;
            word-wrap: break-word;
            text-align: left !important;
        }

        .keterangan-column {
            min-width: 200px;
            max-width: 400px;
            white-space: normal;
            word-wrap: break-word;
            text-align: left !important;
        }

        .nomor-column {
            width: 40px;
            max-width: 50px;
            text-align: center !important;
            vertical-align: middle !important;
            font-weight: 500;
        }

        .hari-libur {
            background-color: #ffe5e5;
            color: #d10000 !important;
            font-weight: bold;
        }

        .img-paraf-preview {
            height: 50px;
            width: auto;
            object-fit: contain;
        }

        #modalPreviewImage {
            transition: transform 0.3s ease;
            max-width: 100%;
            height: auto;
        }

        .laporan-shell {
            border: 1px solid #e6edf5;
        }

        .hero-laporan {
            display: grid;
            grid-template-columns: minmax(0, 1.6fr) minmax(0, 1fr);
            gap: 1.5rem;
            padding: 2rem;
            border-radius: 28px;
            background:
                radial-gradient(circle at top right, rgba(249, 115, 22, 0.14), transparent 30%),
                linear-gradient(135deg, #0f172a 0%, #155e75 55%, #f8fafc 145%);
            color: #f8fafc;
            box-shadow: 0 18px 50px rgba(15, 23, 42, 0.16);
        }

        .hero-laporan__tag,
        .section-kicker {
            display: inline-block;
            margin-bottom: 0.55rem;
            font-size: 0.78rem;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .hero-laporan__tag {
            padding: 0.45rem 0.85rem;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.12);
            color: #fff;
        }

        .section-kicker {
            color: #0c7489;
        }

        .hero-laporan__title {
            margin-bottom: 0.75rem;
            color: #fff;
            font-size: clamp(2rem, 3vw, 3rem);
            font-weight: 800;
            line-height: 1.1;
        }

        .hero-laporan__subtitle {
            max-width: 760px;
            color: rgba(248, 250, 252, 0.84);
        }

        .hero-laporan__stats {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1rem;
        }

        .hero-stat-card {
            padding: 1rem 1.1rem;
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.18);
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(8px);
        }

        .hero-stat-card span {
            display: block;
            margin-bottom: 0.35rem;
            color: rgba(248, 250, 252, 0.74);
            font-size: 0.82rem;
        }

        .hero-stat-card strong {
            font-size: 1.35rem;
            font-weight: 800;
        }

        .section-copy .section-title {
            margin-bottom: 0.35rem;
            color: #122033;
            font-size: 1.4rem;
            font-weight: 800;
        }

        .schedule-card,
        .quick-guide {
            height: 100%;
            padding: 1.35rem;
            border: 1px solid #dde7f2;
            border-radius: 24px;
            background: linear-gradient(180deg, #fff 0%, #f8fbff 100%);
        }

        .schedule-card__head,
        .shift-card__title {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
        }

        .schedule-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.45rem 0.85rem;
            border-radius: 999px;
            background: #ecfeff;
            color: #0f766e;
            font-size: 0.84rem;
            font-weight: 700;
            white-space: nowrap;
        }

        .shift-card {
            height: 100%;
            padding: 1rem;
            border-radius: 18px;
            background: #f8fafc;
        }

        .shift-card__title {
            margin-bottom: 0.85rem;
            align-items: center;
        }

        .shift-card__title span {
            color: #66758b;
            font-size: 0.84rem;
            font-weight: 700;
        }

        .shift-card__list {
            display: grid;
            gap: 0.7rem;
        }

        .shift-task {
            display: flex;
            gap: 0.7rem;
            align-items: flex-start;
            padding: 0.85rem 0.9rem;
            border-radius: 14px;
            background: #eef2f7;
            color: #122033;
            font-weight: 600;
        }

        .shift-task.is-done {
            background: #ecfdf5;
            color: #166534;
        }

        .shift-card__empty {
            padding: 0.9rem 1rem;
            border-radius: 14px;
            background: #eef2f7;
            color: #66758b;
        }

        .guide-point {
            display: flex;
            gap: 0.8rem;
            align-items: flex-start;
            padding: 0.95rem 1rem;
            border-radius: 16px;
            background: #f8fafc;
            color: #475569;
        }

        .guide-point + .guide-point {
            margin-top: 0.75rem;
        }

        .guide-point span {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #0c7489;
            color: #fff;
            font-weight: 800;
            flex-shrink: 0;
        }

        @media (max-width: 1199.98px) {
            .hero-laporan {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 767.98px) {
            .hero-laporan {
                padding: 1.4rem;
            }

            .hero-laporan__stats {
                grid-template-columns: 1fr;
            }

            .schedule-card__head,
            .shift-card__title {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>

    <script src="{{ asset('assets/plugins/global/plugins.bundle.js') }}"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.5/dist/signature_pad.umd.min.js"></script>

    <script>
        const editUrlTemplate = "{{ route('laporanharian.edit', ':id') }}";
        const updateUrlTemplate = "{{ route('laporanharian.update', ':id') }}";
        const storageBaseUrl = @json(asset('storage'));
        let editSignaturePad;
        let approvalPad;

        function resizeCanvas(canvas, signaturePadInstance) {
            const ratio = Math.max(window.devicePixelRatio || 1, 1);
            canvas.width = canvas.offsetWidth * ratio;
            canvas.height = canvas.offsetHeight * ratio;
            canvas.getContext("2d").scale(ratio, ratio);
            signaturePadInstance.clear();
        }

        function loadPekerjaanList(tanggal, shift) {
            if (!tanggal || !shift) return;

            const hint = document.getElementById('jobPickerHint');
            if (hint) {
                hint.textContent = 'Memuat item pekerjaan yang tersedia...';
                hint.classList.remove('text-danger');
            }

            $.get(`{{ route('laporanharian.pekerjaan-tersedia') }}`, { tanggal, shift }, function (data) {
                let $pekerjaanSelect = $('#item_pekerjaan');
                $pekerjaanSelect.empty().append('<option value="" disabled selected>Pilih Item Pekerjaan</option>');

                if (data.length === 0) {
                    $pekerjaanSelect.append('<option value="" disabled>Tidak ada pekerjaan</option>');
                    if (hint) {
                        hint.textContent = 'Tidak ada pekerjaan terjadwal pada tanggal dan shift yang dipilih.';
                        hint.classList.add('text-danger');
                    }
                } else {
                    data.forEach(item => {
                        $pekerjaanSelect.append(`<option value="${item.id}">${item.pekerjaan}</option>`);
                    });
                    if (hint) {
                        hint.textContent = `${data.length} item pekerjaan tersedia untuk dipilih.`;
                        hint.classList.remove('text-danger');
                    }
                }

                $pekerjaanSelect.trigger('change');
            }).fail(function () {
                if (hint) {
                    hint.textContent = 'Gagal memuat item pekerjaan. Silakan coba lagi.';
                    hint.classList.add('text-danger');
                }
            });
        }

        // Trigger saat tanggal atau shift berubah
        $('#tanggal').on('change', function () {
            const tanggal = $(this).val();
            const shift = $('input[name="shift"]:checked').val();
            loadPekerjaanList(tanggal, shift);
        });

        $('input[name="shift"]').on('change', function () {
            const shift = $(this).val();
            const tanggal = $('#tanggal').val();
            loadPekerjaanList(tanggal, shift);
        });

        // Optional: saat modal dibuka, kosongkan pekerjaan
        $('#addLaporanHarian').on('show.bs.modal', function () {
            $('#item_pekerjaan').empty().append('<option value="" disabled selected>Silakan pilih tanggal dan shift terlebih dahulu</option>');
            const hint = document.getElementById('jobPickerHint');
            if (hint) {
                hint.textContent = 'Pilih tanggal dan shift untuk memuat item pekerjaan yang tersedia.';
                hint.classList.remove('text-danger');
            }
        });

        $(document).ready(function () {
            const editCanvas = document.getElementById("editSignatureCanvas");
            editSignaturePad = new SignaturePad(editCanvas);
            resizeCanvas(editCanvas, editSignaturePad);
            const canvas = document.getElementById('approvalCanvas');
            approvalPad = new SignaturePad(canvas);

            $('#filter_bulan, #filter_tahun').on('change', function () {
                $('#filterForm').trigger('submit');
            });

            $('#tableLaporanHarian').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('laporanharian.data') }}",
                    data: function (d) {
                        d.bulan = $('#filter_bulan').val();
                        d.tahun = $('#filter_tahun').val();
                    }
                },
                scrollX: true,
                paging: true,
                searching: true,
                ordering: false,
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable:false, searchable:false },
                    { data: 'tanggal', name: 'tanggal' },
                    { data: 'jam_mulai', name: 'jam_mulai' },
                    { data: 'jam_selesai', name: 'jam_selesai' },
                    { data: 'pekerjaan', name: 'checklist.pekerjaan' },
                    { data: 'area', name: 'area' },
                    { data: 'bukti', name: 'bukti', orderable:false, searchable:false },
                    { data: 'hasil_pekerjaan', name: 'hasil_pekerjaan' },
                    { data: 'mengetahui', name: 'mengetahui' },
                    { data: 'paraf', name: 'paraf', orderable:false, searchable:false },
                    @if(auth()->user()->hasRole('Admin'))
                        { data: 'opsi', name: 'opsi', orderable:false, searchable:false },
                    @endif
                ],
                dom: '<"row mb-3 align-items-center"' +
                    '<"col-md-6 d-flex align-items-center gap-2"B>' +
                    '<"col-md-6 text-end"f>>' +
                    '<"row"<"col-sm-12"t>>' +
                    '<"row mt-3"' +
                    '<"col-sm-6"l><"col-sm-6 text-end"p>>',
                buttons: [
                    {
                        text: '<i class="fas fa-plus"></i> Tambah Laporan',
                        className: 'btn custom-button btn-sm me-1',
                        action: function () {
                            $('#addLaporanHarian').modal('show');
                        }
                    },
                    {
                        extend: 'colvis',
                        text: '<i class="fas fa-columns"></i> Column Visible',
                        className: 'btn custom-button btn-sm me-1',
                    },
                    @if(auth()->user()->hasRole('Admin'))
                    {
                        text: '<i class="fas fa-file-excel"></i> Export Excel',
                        className: 'btn custom-button btn-sm me-1',
                        action: function () {
                            const bulan = $('#filter_bulan').val();
                            const tahun = $('#filter_tahun').val();

                            // Check approval status via AJAX
                            $.get("{{ route('laporanharian.exportexcel') }}", {
                                bulan,
                                tahun,
                                ajax: true
                            }).done(function (res) {
                                if (res.needs_approval) {
                                    // Tampilkan modal approval
                                    $('#approval_bulan').val(bulan);
                                    $('#approval_tahun').val(tahun);
                                    $('#modalApproval').modal('show');
                                } else {
                                    // Langsung download
                                    const url = `{{ route('laporanharian.exportexcel') }}?bulan=${bulan}&tahun=${tahun}`;
                                    window.location.href = url;
                                }
                            }).fail(function () {
                                Swal.fire('Gagal', 'Terjadi kesalahan saat mengecek approval.', 'error');
                            });
                        }
                    }
                    @endif
                ],
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Cari Laporan Harian",
                    lengthMenu: "Tampilkan _MENU_ entri",
                    info: "Menampilkan _START_ hingga _END_ dari _TOTAL_ entri",
                    paginate: {
                        previous: '<i class="fas fa-chevron-left"></i>',
                        next: '<i class="fas fa-chevron-right"></i>',
                    },
                },
                initComplete: function () {
                    const searchBox = $('.dataTables_filter input');
                    searchBox.wrap('<div class="input-group"></div>');
                    searchBox.before('<span class="input-group-text"><i class="fas fa-search"></i></span>');
                }
            });

            $(document).on('click', '.bukti-thumb', function() {
                const src = $(this).attr('src');
                $('#modalPreviewImage').attr('src', src);
                $('#imagePreviewModal').modal('show');
            });

            $('#item_pekerjaan').select2({
                dropdownParent: $('#addLaporanHarian'),
                placeholder: "Pilih Pekerjaan",
                allowClear: true,
                width: "100%"
            });

            $('#edit_item_pekerjaan').select2({
                dropdownParent: $('#editLaporanModal'),
                placeholder: "Pilih Pekerjaan",
                allowClear: true,
                width: "100%"
            });

            $('#edit_area').select2({
                dropdownParent: $('#editLaporanModal'),
                placeholder: "Pilih Area",
                allowClear: true,
                width: "100%"
            });

            $('#area').select2({
                dropdownParent: $('#addLaporanHarian'),
                placeholder: "Pilih Area",
                allowClear: true,
                width: "100%"
            });

            $(document).on('click', '.edit-btn', function () {
                let id = $(this).data('id');
                let editUrl = editUrlTemplate.replace(':id', id);
                let updateUrl = updateUrlTemplate.replace(':id', id);

                $.get(editUrl, function (data) {
                    let laporan = data.laporan;

                    $('#formEditLaporan').attr('action', updateUrl);
                    $('#edit_tanggal').val(laporan.tanggal);
                    $(`#edit_shift${laporan.shift}`).prop('checked', true);
                    $('#edit_jam_mulai').val(laporan.jam_mulai);
                    $('#edit_jam_selesai').val(laporan.jam_selesai);
                    let areaOptions = '<option value="" disabled>Pilih Area</option>';
                    data.areaList.forEach(function(area) {
                        const selected = (laporan.area === area) ? 'selected' : '';
                        areaOptions += `<option value="${area}" ${selected}>${area}</option>`;
                    });
                    $('#edit_area').html(areaOptions);
                    $('#edit_hasil_pekerjaan').val(laporan.hasil_pekerjaan ?? '');
                    $('#edit_mengetahui').val(laporan.mengetahui ?? '');

                    let pekerjaanOptions = '';
                    data.pekerjaanList.forEach(function (p) {
                        let selected = laporan.checklist_id == p.id ? 'selected' : '';
                        pekerjaanOptions += `<option value="${p.id}" ${selected}>${p.pekerjaan}</option>`;
                    });
                    $('#edit_item_pekerjaan').html(pekerjaanOptions);

                    if (laporan.paraf) {
                        $('#preview_paraf').html(`<img src="${storageBaseUrl}/${laporan.paraf}" class="img-paraf-preview" alt="Paraf">`);
                    } else {
                        $('#preview_paraf').html('');
                    }

                    $('#editBuktiBody').html('');

                    if (laporan.bukti_list && laporan.bukti_list.length) {
                        laporan.bukti_list.forEach(function (bukti, index) {
                            const ekstensi = bukti.split('.').pop().toLowerCase();
                            const url = `${storageBaseUrl}/${bukti}`;
                            const previewId = `preview_existing_${index}`;

                            let previewHTML = '';
                            if (['jpg', 'jpeg', 'png'].includes(ekstensi)) {
                                previewHTML = `<img src="${url}" alt="Preview" class="img-thumbnail mb-1 bukti-thumb" style="max-width: 150px;">`;
                            } else if (ekstensi === 'pdf') {
                                previewHTML = `<a href="${url}" target="_blank" class="badge bg-secondary d-inline-block mb-1">Lihat PDF</a>`;
                            }

                            $('#editBuktiBody').append(`
                                <tr>
                                    <td>
                                        <input type="hidden" name="bukti_lama[]" value="${bukti}">
                                        ${previewHTML}
                                        <input type="file" name="bukti_ganti[]" accept="image/*,application/pdf" class="form-control mb-1" onchange="previewFile(this, '${previewId}')">
                                        <div id="${previewId}" class="mt-1"></div>
                                    </td>
                                    <td class="text-center align-middle">
                                        <button type="button" class="btn btn-sm btn-danger" onclick="removeBuktiRow(this)">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </td>
                                </tr>
                            `);
                        });
                    } else {
                        $('#editBuktiBody').html(''); // kosongkan tabel jika tidak ada bukti
                    }
                    $('#editLaporanModal').modal('show');
                });
            });

            function clearEditSignature() {
                editSignaturePad.clear();
                $("#paraf_signature_edit").val('');
            }

            $('#formEditLaporan')
            .off('submit')
            .on('submit', function (e) {
                e.preventDefault();

                if (!editSignaturePad.isEmpty()) {
                    const dataUrl = editSignaturePad.toDataURL();
                    $('#paraf_signature_edit').val(dataUrl);
                }

                const form = this;
                const formData = new FormData(form);
                const actionUrl = $(form).attr('action');

                $.ajax({
                    url: actionUrl,
                    method: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function () {
                        $('#editLaporanModal').modal('hide');
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: 'Laporan berhasil diperbarui!',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    },
                    error: function (xhr) {
                        let message = 'Gagal memperbarui laporan.';
                        if (xhr.responseJSON?.errors) {
                            message += '\n' + Object.values(xhr.responseJSON.errors).flat().join('\n');
                        }
                        Swal.fire('Error', message, 'error');
                        console.error(xhr.responseText);
                    }
                });
            });

            $('#editLaporanModal').on('shown.bs.modal', function () {
                resizeCanvas(editCanvas, editSignaturePad);
            });

            window.clearEditSignature = function () {
                editSignaturePad.clear();
                $("#paraf_signature_edit").val('');
            }

            $('#formApproval').on('submit', function (e) {
                e.preventDefault();

                if (approvalPad.isEmpty()) {
                    return Swal.fire('Error', 'Tanda tangan belum diisi.', 'error');
                }

                $('#approval_ttd_base64').val(approvalPad.toDataURL());

                const formData = $(this).serialize();

                $.post(`{{ route('laporanharian.storeapproval') }}`, formData, function (res) {
                    $('#modalApproval').modal('hide');

                    const bulan = $('#approval_bulan').val();
                    const tahun = $('#approval_tahun').val();
                    const url = `{{ route('laporanharian.exportexcel') }}?bulan=${bulan}&tahun=${tahun}`;

                    Swal.fire({
                        icon: 'success',
                        title: 'Disetujui!',
                        text: 'Laporan berhasil disetujui dan akan diunduh...',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = url;
                    });
                }).fail(function (xhr) {
                    Swal.fire('Error', 'Gagal menyimpan persetujuan.', 'error');
                });
            });

            window.addBuktiRow = function () {
                const tbody = document.getElementById('buktiUploadBody');
                const row = document.createElement('tr');

                const uniqueId = 'preview_' + Date.now();

                row.innerHTML = `
                    <td>
                        <input type="file" name="bukti[]" accept="image/*,application/pdf" capture="environment" class="form-control mb-1" required onchange="previewFile(this, '${uniqueId}')">
                        <div id="${uniqueId}" class="mt-1"></div>
                    </td>
                    <td class="text-center align-middle">
                        <button type="button" class="btn btn-sm btn-danger" onclick="removeBuktiRow(this)">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </td>
                `;

                tbody.appendChild(row);
            };

            window.previewFile = function (input, previewId) {
                const file = input.files[0];
                const preview = document.getElementById(previewId);
                preview.innerHTML = '';

                if (file && file.type.startsWith('image/')) {
                    const objectUrl = URL.createObjectURL(file);
                    preview.innerHTML = `<img src="${objectUrl}" alt="Preview" class="img-thumbnail bukti-thumb" style="max-width: 150px;">`;
                } else if (file && file.type === 'application/pdf') {
                    preview.innerHTML = `<span class="badge bg-secondary">PDF dipilih</span>`;
                }
            };

            window.removeBuktiRow = function (button) {
                const row = button.closest('tr');
                const tbody = row.parentElement;

                if (tbody.children.length <= 1) {
                    const input = row.querySelector('input[type="file"]');
                    const preview = row.querySelector('[id^="preview_"], .proof-preview');

                    if (input) {
                        input.value = '';
                    }

                    if (preview) {
                        preview.innerHTML = '';
                    }

                    return;
                }

                row.remove();
            };

            window.addEditBuktiRow = function () {
                const tbody = document.getElementById('editBuktiBody');
                const row = document.createElement('tr');

                const uniqueId = 'preview_edit_' + Date.now();

                row.innerHTML = `
                    <td>
                        <input type="file" name="bukti[]" accept="image/*,application/pdf" capture="environment" class="form-control mb-1" required onchange="previewFile(this, '${uniqueId}')">
                        <div id="${uniqueId}" class="mt-1"></div>
                    </td>
                    <td class="text-center align-middle">
                        <button type="button" class="btn btn-sm btn-danger" onclick="removeBuktiRow(this)">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </td>
                `;

                tbody.appendChild(row);
            };

            $(document).on('click', '.img-thumbnail, .img-paraf-preview', function () {
                const src = $(this).attr('src');
                $('#modalPreviewImage').attr('src', src);
                $('#imagePreviewModal').modal('show');
            });

            let isZoomed = false;

            $('#modalPreviewImage').on('click', function () {
                if (!isZoomed) {
                    $(this).css({
                        'transform': 'scale(2)',
                        'transition': 'transform 0.3s ease',
                        'cursor': 'zoom-out'
                    });
                    isZoomed = true;
                } else {
                    $(this).css({
                        'transform': 'scale(1)',
                        'cursor': 'zoom-in'
                    });
                    isZoomed = false;
                }
            });

            $('#imagePreviewModal').on('hidden.bs.modal', function () {
                $('#modalPreviewImage').css({
                    'transform': 'scale(1)',
                    'cursor': 'zoom-in'
                });
                isZoomed = false;
            });
        });

        $(document).on('click', '.btn-delete-laporan', function () {
            const id = $(this).data('id');

            Swal.fire({
                title: 'Yakin ingin menghapus?',
                text: 'Data yang dihapus tidak bisa dikembalikan.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('laporanharian.destroy', ':id') }}".replace(':id', id),
                        method: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function (res) {
                            Swal.fire('Terhapus!', res.message, 'success').then(() => {
                                location.reload();
                            });
                        },
                        error: function () {
                            Swal.fire('Gagal', 'Terjadi kesalahan saat menghapus data.', 'error');
                        }
                    });
                }
            });
        });
    </script>
</x-default-layout>
