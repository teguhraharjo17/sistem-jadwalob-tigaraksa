<x-default-layout>
    @section('title', 'Laporan Kerja Harian')

    @php
        $totalJadwalHariIni = count($jadwalHariIniPagi) + count($jadwalHariIniSiang);
        $totalSelesaiHariIni = collect($jadwalHariIniPagi)->where('status', 1)->count() + collect($jadwalHariIniSiang)->where('status', 1)->count();
        $totalJadwalBesok = count($jadwalBesokPagi) + count($jadwalBesokSiang);
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
                <div class="hero-stat-card">
                    <span>Jadwal Besok</span>
                    <strong class="text-primary">{{ $totalJadwalBesok }}</strong>
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
                        <div class="schedule-card__head flex-column flex-sm-row align-items-sm-center justify-content-between gap-3 mb-4">
                            <div>
                                <span class="section-kicker">Agenda Kerja</span>
                                <h3 class="section-title mb-0">Jadwal Pekerjaan OB</h3>
                            </div>
                            
                            <!--begin::Nav Tabs Hari Ini / Besok-->
                            <ul class="nav nav-pills schedule-tabs gap-2" id="scheduleTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active px-3 py-2 fs-7 fw-bold rounded-pill" 
                                            id="tab-today" 
                                            data-bs-toggle="pill" 
                                            data-bs-target="#content-today" 
                                            type="button" 
                                            role="tab">
                                        <i class="bi bi-calendar-check me-1"></i> Hari Ini ({{ \Carbon\Carbon::today()->translatedFormat('d M') }})
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link px-3 py-2 fs-7 fw-bold rounded-pill" 
                                            id="tab-tomorrow" 
                                            data-bs-toggle="pill" 
                                            data-bs-target="#content-tomorrow" 
                                            type="button" 
                                            role="tab">
                                        <i class="bi bi-calendar-plus me-1"></i> Besok ({{ \Carbon\Carbon::tomorrow()->translatedFormat('d M') }})
                                    </button>
                                </li>
                            </ul>
                            <!--end::Nav Tabs-->
                        </div>

                        <!--begin::Tab Content-->
                        <div class="tab-content" id="scheduleTabContent">
                            
                            <!--begin::Tab Hari Ini-->
                            <div class="tab-pane fade show active" id="content-today" role="tabpanel" aria-labelledby="tab-today">
                                <div class="d-flex justify-content-between align-items-center mb-3 px-1">
                                    <span class="text-muted fs-7"><i class="bi bi-clock me-1 text-primary"></i> {{ \Carbon\Carbon::today()->translatedFormat('l, d F Y') }}</span>
                                    <span class="schedule-badge">{{ $totalSelesaiHariIni }}/{{ $totalJadwalHariIni ?: 0 }} selesai</span>
                                </div>

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="shift-card">
                                            <div class="shift-card__title">
                                                <strong><i class="bi bi-sun-fill text-warning me-1"></i> Shift Pagi</strong>
                                                <span>{{ count($jadwalHariIniPagi) }} tugas</span>
                                            </div>
                                            @if(count($jadwalHariIniPagi))
                                                <div class="shift-card__list">
                                                    @foreach ($jadwalHariIniPagi as $item)
                                                        <div class="shift-task {{ $item['status'] == 1 ? 'is-done' : '' }}">
                                                            <i class="fas {{ $item['status'] == 1 ? 'fa-check-circle text-success' : 'fa-clock text-muted' }}"></i>
                                                            <span>{{ $item['pekerjaan'] }}</span>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @else
                                                <div class="shift-card__empty">Tidak ada jadwal untuk shift pagi hari ini.</div>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="shift-card">
                                            <div class="shift-card__title">
                                                <strong><i class="bi bi-cloud-sun-fill text-info me-1"></i> Shift Siang</strong>
                                                <span>{{ count($jadwalHariIniSiang) }} tugas</span>
                                            </div>
                                            @if(count($jadwalHariIniSiang))
                                                <div class="shift-card__list">
                                                    @foreach ($jadwalHariIniSiang as $item)
                                                        <div class="shift-task {{ $item['status'] == 1 ? 'is-done' : '' }}">
                                                            <i class="fas {{ $item['status'] == 1 ? 'fa-check-circle text-success' : 'fa-clock text-muted' }}"></i>
                                                            <span>{{ $item['pekerjaan'] }}</span>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @else
                                                <div class="shift-card__empty">Tidak ada jadwal untuk shift siang hari ini.</div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!--end::Tab Hari Ini-->

                            <!--begin::Tab Besok-->
                            <div class="tab-pane fade" id="content-tomorrow" role="tabpanel" aria-labelledby="tab-tomorrow">
                                <div class="d-flex justify-content-between align-items-center mb-3 px-1">
                                    <span class="text-muted fs-7"><i class="bi bi-calendar-event me-1 text-primary"></i> {{ \Carbon\Carbon::tomorrow()->translatedFormat('l, d F Y') }}</span>
                                    <span class="schedule-badge bg-light-primary text-primary border border-primary border-opacity-20"><i class="bi bi-stars me-1 text-primary"></i> {{ $totalJadwalBesok }} tugas dipersiapkan</span>
                                </div>

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="shift-card">
                                            <div class="shift-card__title">
                                                <strong><i class="bi bi-sun-fill text-warning me-1"></i> Shift Pagi (Besok)</strong>
                                                <span class="text-primary fw-bold">{{ count($jadwalBesokPagi) }} tugas</span>
                                            </div>
                                            @if(count($jadwalBesokPagi))
                                                <div class="shift-card__list">
                                                    @foreach ($jadwalBesokPagi as $item)
                                                        <div class="shift-task shift-task--tomorrow">
                                                            <i class="bi bi-arrow-right-circle-fill text-primary fs-6 mt-1 flex-shrink-0"></i>
                                                            <span>{{ $item['pekerjaan'] }}</span>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @else
                                                <div class="shift-card__empty">Belum ada jadwal pekerjaan untuk shift pagi besok.</div>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="shift-card">
                                            <div class="shift-card__title">
                                                <strong><i class="bi bi-cloud-sun-fill text-info me-1"></i> Shift Siang (Besok)</strong>
                                                <span class="text-info fw-bold">{{ count($jadwalBesokSiang) }} tugas</span>
                                            </div>
                                            @if(count($jadwalBesokSiang))
                                                <div class="shift-card__list">
                                                    @foreach ($jadwalBesokSiang as $item)
                                                        <div class="shift-task shift-task--tomorrow">
                                                            <i class="bi bi-arrow-right-circle-fill text-info fs-6 mt-1 flex-shrink-0"></i>
                                                            <span>{{ $item['pekerjaan'] }}</span>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @else
                                                <div class="shift-card__empty">Belum ada jadwal pekerjaan untuk shift siang besok.</div>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex align-items-center bg-light-primary p-3 mt-3 rounded-3 border border-primary border-opacity-15">
                                    <i class="bi bi-info-circle-fill fs-5 text-primary me-2 flex-shrink-0"></i>
                                    <span class="fs-8 text-gray-700">Daftar pekerjaan di atas dapat dijadikan acuan persiapan sebelum pulang kerja hari ini.</span>
                                </div>
                            </div>
                            <!--end::Tab Besok-->

                        </div>
                        <!--end::Tab Content-->
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
                            @if(auth()->user()->hasPermission('laporanharian_edit') || auth()->user()->hasPermission('laporanharian_approve'))
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

                                <button type="button" class="btn btn-sm btn-secondary" id="btnTambahBuktiRow" onclick="addEditBuktiRow()">+ Tambah Bukti</button>

                                <div id="preview_bukti_existing" class="mt-2">
                                </div>
                            </div>

                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary">Update</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal: Persetujuan Laporan Harian (Supervisor) -->
        <div class="modal fade" id="approveLaporanModal" tabindex="-1" aria-labelledby="approveLaporanModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="approveLaporanModalLabel">Persetujuan / Paraf Laporan Harian</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Readonly Info Card of Laporan Harian -->
                        <div class="card bg-light border-0 mb-4 shadow-sm">
                            <div class="card-body p-4">
                                <h6 class="fw-bold mb-3 text-primary"><i class="fas fa-file-alt text-primary me-2"></i> Rincian Pekerjaan</h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <span class="text-muted d-block fs-8">Tanggal / Shift:</span>
                                        <span class="fw-bold text-gray-900 fs-7" id="approve_info_tanggal_shift">-</span>
                                    </div>
                                    <div class="col-md-6">
                                        <span class="text-muted d-block fs-8">Jam Kerja:</span>
                                        <span class="fw-bold text-gray-900 fs-7" id="approve_info_jam">-</span>
                                    </div>
                                    <div class="col-md-6">
                                        <span class="text-muted d-block fs-8">Item Pekerjaan:</span>
                                        <span class="fw-bold text-gray-900 fs-7" id="approve_info_pekerjaan">-</span>
                                    </div>
                                    <div class="col-md-6">
                                        <span class="text-muted d-block fs-8">Area:</span>
                                        <span class="fw-bold text-gray-900 fs-7" id="approve_info_area">-</span>
                                    </div>
                                    <div class="col-12">
                                        <span class="text-muted d-block fs-8 mb-1">Bukti Kerja:</span>
                                        <div id="approve_info_bukti" class="d-flex flex-wrap gap-2"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Approval Inputs Form -->
                        <form id="formApproveLaporan" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            
                            <div class="mb-4">
                                <label for="approve_hasil_pekerjaan" class="form-label fw-bold text-gray-800">Hasil Pekerjaan <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-solid" id="approve_hasil_pekerjaan" name="hasil_pekerjaan" placeholder="Contoh: Selesai dengan rapi dan bersih" required>
                            </div>

                            <div class="mb-4">
                                <label for="approve_mengetahui" class="form-label fw-bold text-gray-800">Mengetahui (Nama Supervisor) <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-solid" id="approve_mengetahui" name="mengetahui" placeholder="Nama Supervisor / Pemberi Persetujuan" required>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold text-gray-800">Paraf / Tanda Tangan <span class="text-danger">*</span></label>
                                
                                <div id="preview_paraf_approve" class="mb-2"></div>

                                <!-- Signature Mode Tabs -->
                                <ul class="nav nav-tabs nav-line-tabs mb-3 fs-6" id="signatureTabs" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link active fw-bold py-2 px-3" id="canvas-tab" data-bs-toggle="tab" data-bs-target="#canvas-panel" type="button" role="tab" aria-controls="canvas-panel" aria-selected="true">
                                            <i class="fas fa-signature me-1"></i> Tulis Langsung
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link fw-bold py-2 px-3" id="upload-tab" data-bs-toggle="tab" data-bs-target="#upload-panel" type="button" role="tab" aria-controls="upload-panel" aria-selected="false">
                                            <i class="fas fa-upload me-1"></i> Unggah Gambar
                                        </button>
                                    </li>
                                </ul>

                                <div class="tab-content" id="signatureTabsContent">
                                    <!-- Canvas Panel -->
                                    <div class="tab-pane fade show active" id="canvas-panel" role="tabpanel" aria-labelledby="canvas-tab">
                                        <div class="border rounded bg-white p-2">
                                            <canvas id="approveSignatureCanvas" class="border rounded" style="width: 100%; height: 180px; touch-action: none; background-color: #fafafa;"></canvas>
                                        </div>
                                        <input type="hidden" name="paraf_signature" id="paraf_signature">
                                        <div class="mt-2 text-end">
                                            <button type="button" class="btn btn-sm btn-light-danger fw-bold" onclick="clearApproveSignature()"><i class="fas fa-eraser me-1"></i> Bersihkan Paraf</button>
                                        </div>
                                    </div>
                                    <!-- Upload Panel -->
                                    <div class="tab-pane fade" id="upload-panel" role="tabpanel" aria-labelledby="upload-tab">
                                        <div class="border rounded bg-white p-4 text-center">
                                            <input type="file" name="paraf" id="approve_paraf_file" class="form-control" accept="image/jpeg,image/png,image/jpg" onchange="previewParafFile(this)">
                                            <div class="text-muted mt-2 fs-9">Format: JPG, JPEG, PNG. Maks: 4MB.</div>
                                            <div id="paraf_file_preview" class="mt-3"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="modal-footer px-0 pb-0 pt-4 border-top">
                                <button type="button" class="btn btn-light btn-sm fw-bold" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-primary btn-sm fw-bold"><i class="fas fa-check-double me-1"></i> Simpan Persetujuan</button>
                            </div>
                        </form>
                    </div>
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
        <div class="modal fade" id="imagePreviewModal" tabindex="-1" aria-labelledby="imagePreviewModalLabel">
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

        .shift-task--tomorrow {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-left: 3px solid #009ef7;
        }

        .schedule-tabs .nav-link {
            background: #eef2f7;
            color: #4b566b;
            border: 1px solid transparent;
            transition: all 0.2s ease;
        }

        .schedule-tabs .nav-link:hover {
            background: #e2e8f0;
            color: #0f172a;
        }

        .schedule-tabs .nav-link.active {
            background: #009ef7;
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(0, 158, 247, 0.3);
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
                padding: 1.25rem 1rem;
                border-radius: 18px;
            }

            .hero-laporan__title {
                font-size: 1.45rem;
            }

            .hero-laporan__subtitle {
                font-size: 0.84rem;
            }

            .hero-laporan__stats {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                gap: 0.65rem;
            }

            .hero-stat-card {
                padding: 0.75rem 0.85rem;
                border-radius: 14px;
            }

            .hero-stat-card span {
                font-size: 0.74rem;
                margin-bottom: 0.2rem;
            }

            .hero-stat-card strong {
                font-size: 1.15rem;
            }

            /* 5th stat card (Jadwal Besok) spans full width for balance */
            .hero-laporan__stats .hero-stat-card:last-child {
                grid-column: span 2;
                background: rgba(0, 158, 247, 0.15);
                border-color: rgba(0, 158, 247, 0.35);
            }

            .laporan-shell {
                padding: 1rem 0.85rem !important;
                border-radius: 16px !important;
            }

            .schedule-card,
            .quick-guide {
                padding: 1rem 0.85rem;
                border-radius: 16px;
            }

            .schedule-card__head {
                flex-direction: column;
                align-items: stretch !important;
                gap: 0.85rem;
            }

            .schedule-tabs {
                display: flex;
                width: 100%;
                background: #f1f5f9;
                padding: 4px;
                border-radius: 999px;
            }

            .schedule-tabs .nav-item {
                flex: 1;
                text-align: center;
            }

            .schedule-tabs .nav-link {
                width: 100%;
                justify-content: center;
                font-size: 0.78rem;
                padding: 0.45rem 0.5rem;
                border-radius: 999px;
            }

            .shift-card {
                padding: 0.85rem;
                border-radius: 14px;
            }

            .shift-card__title {
                margin-bottom: 0.65rem;
            }

            .shift-task {
                padding: 0.65rem 0.75rem;
                font-size: 0.84rem;
                border-radius: 10px;
            }

            .guide-point {
                padding: 0.75rem 0.85rem;
                font-size: 0.82rem;
                border-radius: 12px;
            }

            .guide-point span {
                width: 26px;
                height: 26px;
                font-size: 0.75rem;
            }

            .section-copy .section-title {
                font-size: 1.15rem;
            }
        }

        @media (max-width: 480px) {
            .container {
                padding-left: 0.75rem;
                padding-right: 0.75rem;
            }

            .schedule-badge {
                font-size: 0.74rem;
                padding: 0.35rem 0.65rem;
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
        let approveSignaturePad;
        let approvalPad;

        function refreshPageSections() {
            // Reload Datatable dynamically
            $('#tableLaporanHarian').DataTable().ajax.reload(null, false);

            // Fetch current page content to update stats and schedules
            $.get(window.location.href, function (html) {
                const $parsed = $(html);

                // Update stats container
                const newStats = $parsed.find('.hero-laporan__stats').html();
                $('.hero-laporan__stats').html(newStats);

                // Update schedule card container
                const newSchedule = $parsed.find('.schedule-card').html();
                $('.schedule-card').html(newSchedule);
            });
        }

        function resizeCanvas(canvas, signaturePadInstance) {
            const ratio = Math.max(window.devicePixelRatio || 1, 1);
            canvas.width = canvas.offsetWidth * ratio;
            canvas.height = canvas.offsetHeight * ratio;
            canvas.getContext("2d").scale(ratio, ratio);
            signaturePadInstance.clear();
        }

        let addModalJobs = [];

        function populateJobsDropdown() {
            let selectedArea = $('#area').val();
            let $pekerjaanSelect = $('#item_pekerjaan');
            let currentSelectedJobId = $pekerjaanSelect.val();

            // Clear options, keep placeholder
            $pekerjaanSelect.empty().append('<option value="" disabled selected>Pilih Item Pekerjaan</option>');

            let filteredJobs = addModalJobs;
            if (selectedArea) {
                filteredJobs = addModalJobs.filter(job => job.area === selectedArea);
            }

            if (filteredJobs.length === 0) {
                $pekerjaanSelect.append('<option value="" disabled>Tidak ada pekerjaan</option>');
            } else {
                filteredJobs.forEach(item => {
                    let isSelected = item.id == currentSelectedJobId ? 'selected' : '';
                    $pekerjaanSelect.append(`<option value="${item.id}" data-area="${item.area}" ${isSelected}>${item.pekerjaan}</option>`);
                });
            }

            $pekerjaanSelect.trigger('change');
        }

        function loadPekerjaanList(tanggal, shift) {
            if (!tanggal || !shift) return;

            const hint = document.getElementById('jobPickerHint');
            if (hint) {
                hint.textContent = 'Memuat item pekerjaan yang tersedia...';
                hint.classList.remove('text-danger');
            }

            $.get(`{{ route('laporanharian.pekerjaan-tersedia') }}`, { tanggal, shift }, function (data) {
                addModalJobs = data;
                populateJobsDropdown();

                if (data.length === 0) {
                    if (hint) {
                        hint.textContent = 'Tidak ada pekerjaan terjadwal pada tanggal dan shift yang dipilih.';
                        hint.classList.add('text-danger');
                    }
                } else {
                    if (hint) {
                        hint.textContent = `${data.length} item pekerjaan tersedia untuk dipilih.`;
                        hint.classList.remove('text-danger');
                    }
                }
            }).fail(function () {
                if (hint) {
                    hint.textContent = 'Gagal memuat item pekerjaan. Silakan coba lagi.';
                    hint.classList.add('text-danger');
                }
            });
        }

        $(document).ready(function () {
            // Register event listeners for Add Modal
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

            $('#area').on('change', function () {
                let selectedArea = $(this).val();
                let selectedJobId = $('#item_pekerjaan').val();
                
                if (selectedJobId) {
                    let currentJob = addModalJobs.find(j => j.id == selectedJobId);
                    if (currentJob && currentJob.area !== selectedArea) {
                        $('#item_pekerjaan').val(null);
                    }
                }
                populateJobsDropdown();
            });

            $('#item_pekerjaan').on('change', function () {
                let selectedJobId = $(this).val();
                if (selectedJobId) {
                    let job = addModalJobs.find(j => j.id == selectedJobId);
                    if (job && job.area) {
                        if ($('#area').val() !== job.area) {
                            $('#area').val(job.area).trigger('change');
                        }
                    }
                }
            });

            $('#addLaporanHarian').on('show.bs.modal', function () {
                addModalJobs = [];
                $('#item_pekerjaan').empty().append('<option value="" disabled selected>Silakan pilih tanggal dan shift terlebih dahulu</option>');
                $('#area').val(null).trigger('change');
                const hint = document.getElementById('jobPickerHint');
                if (hint) {
                    hint.textContent = 'Pilih tanggal dan shift untuk memuat item pekerjaan yang tersedia.';
                    hint.classList.remove('text-danger');
                }
            });
            // formTambahLaporanHarian AJAX Submit
            $('#formTambahLaporanHarian').on('submit', function (e) {
                e.preventDefault();
                const form = this;
                const formData = new FormData(form);
                const btn = $(form).find('button[type="submit"]');
                btn.attr('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');

                $.ajax({
                    url: $(form).attr('action'),
                    method: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (res) {
                        btn.removeAttr('disabled').html('<i class="fas fa-save me-1"></i> Simpan');
                        $('#addLaporanHarian').modal('hide');
                        form.reset();
                        // Reset Select2 fields inside the form
                        $('#item_pekerjaan').val(null).trigger('change');
                        $('#area').val(null).trigger('change');
                        
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: res.message || 'Laporan Harian berhasil disimpan!',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            refreshPageSections();
                        });
                    },
                    error: function (xhr) {
                        btn.removeAttr('disabled').html('<i class="fas fa-save me-1"></i> Simpan');
                        let message = 'Gagal menyimpan laporan.';
                        if (xhr.responseJSON?.errors) {
                            message += '\n' + Object.values(xhr.responseJSON.errors).flat().join('\n');
                        } else if (xhr.responseJSON?.message) {
                            message = xhr.responseJSON.message;
                        }
                        Swal.fire('Error', message, 'error');
                    }
                });
            });

            const approveCanvas = document.getElementById("approveSignatureCanvas");
            approveSignaturePad = new SignaturePad(approveCanvas);
            resizeCanvas(approveCanvas, approveSignaturePad);
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
                    @if(auth()->user()->hasPermission('laporanharian_edit') || auth()->user()->hasPermission('laporanharian_approve'))
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
                    @if(auth()->user()->hasPermission('laporanharian_create'))
                    {
                        text: '<i class="fas fa-plus"></i> Tambah Laporan',
                        className: 'btn custom-button btn-sm me-1',
                        action: function () {
                            $('#addLaporanHarian').modal('show');
                        }
                    },
                    @endif
                    {
                        extend: 'colvis',
                        text: '<i class="fas fa-columns"></i> Column Visible',
                        className: 'btn custom-button btn-sm me-1',
                    },
                    @if(auth()->user()->hasPermission('laporanharian'))
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

            let editModalJobs = [];

            function populateEditJobsDropdown() {
                let selectedArea = $('#edit_area').val();
                let $pekerjaanSelect = $('#edit_item_pekerjaan');
                let currentSelectedJobId = $pekerjaanSelect.val();

                // Clear options, keep placeholder
                $pekerjaanSelect.empty().append('<option value="" disabled selected>Pilih Item Pekerjaan</option>');

                let filteredJobs = editModalJobs;
                if (selectedArea) {
                    filteredJobs = editModalJobs.filter(job => job.area === selectedArea);
                }

                if (filteredJobs.length === 0) {
                    $pekerjaanSelect.append('<option value="" disabled>Tidak ada pekerjaan</option>');
                } else {
                    filteredJobs.forEach(item => {
                        let isSelected = item.id == currentSelectedJobId ? 'selected' : '';
                        $pekerjaanSelect.append(`<option value="${item.id}" data-area="${item.area}" ${isSelected}>${item.pekerjaan}</option>`);
                    });
                }

                $pekerjaanSelect.trigger('change');
            }

            $('#edit_area').on('change', function () {
                let selectedArea = $(this).val();
                let selectedJobId = $('#edit_item_pekerjaan').val();
                
                if (selectedJobId) {
                    let currentJob = editModalJobs.find(j => j.id == selectedJobId);
                    if (currentJob && currentJob.area !== selectedArea) {
                        $('#edit_item_pekerjaan').val(null);
                    }
                }
                populateEditJobsDropdown();
            });

            $('#edit_item_pekerjaan').on('change', function () {
                let selectedJobId = $(this).val();
                if (selectedJobId) {
                    let job = editModalJobs.find(j => j.id == selectedJobId);
                    if (job && job.area) {
                        if ($('#edit_area').val() !== job.area) {
                            $('#edit_area').val(job.area).trigger('change');
                        }
                    }
                }
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
                    
                    editModalJobs = data.pekerjaanList;

                    let areaOptions = '<option value="" disabled>Pilih Area</option>';
                    data.areaList.forEach(function(area) {
                        const selected = (laporan.area === area) ? 'selected' : '';
                        areaOptions += `<option value="${area}" ${selected}>${area}</option>`;
                    });
                    $('#edit_area').html(areaOptions);
                    $('#edit_hasil_pekerjaan').val(laporan.hasil_pekerjaan ?? '');
                    $('#edit_mengetahui').val(laporan.mengetahui ?? '');

                    // Set value of edit_item_pekerjaan before populating
                    $('#edit_item_pekerjaan').val(laporan.checklist_id);
                    populateEditJobsDropdown();

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
                        $('#editBuktiBody').html('');
                    }

                    $('#editLaporanLabel').text('Edit Laporan Harian');
                    
                    // Enable details fields
                    $('#edit_tanggal').removeAttr('readonly');
                    $('input[name="edit_shift"]').removeAttr('disabled');
                    $('#edit_jam_mulai').removeAttr('readonly');
                    $('#edit_jam_selesai').removeAttr('readonly');
                    $('#edit_item_pekerjaan').removeAttr('disabled');
                    $('#edit_area').removeAttr('disabled');
                    $('#btnTambahBuktiRow').show();
                    
                    // Handle approval fields availability based on user permission laporanharian_approve
                    @if(auth()->user()->hasPermission('laporanharian_approve'))
                        $('#edit_hasil_pekerjaan').removeAttr('readonly');
                        $('#edit_mengetahui').removeAttr('readonly');
                        $('#editParafContainer').show();
                        $('#btnClearEditSignature').show();
                    @else
                        $('#edit_hasil_pekerjaan').attr('readonly', true);
                        $('#edit_mengetahui').attr('readonly', true);
                        $('#editParafContainer').hide();
                        $('#btnClearEditSignature').hide();
                    @endif

                    $('#editLaporanModal').modal('show');
                });
            });

            $(document).on('click', '.approve-btn', function () {
                let id = $(this).data('id');
                let editUrl = editUrlTemplate.replace(':id', id);
                let updateUrl = updateUrlTemplate.replace(':id', id);

                $.get(editUrl, function (data) {
                    let laporan = data.laporan;

                    $('#formApproveLaporan').attr('action', updateUrl);
                    
                    const formattedTanggal = laporan.tanggal ? new Date(laporan.tanggal).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) : '-';
                    $('#approve_info_tanggal_shift').text(`${formattedTanggal} (${laporan.shift})`);
                    $('#approve_info_jam').text(`${laporan.jam_mulai} s.d ${laporan.jam_selesai}`);
                    $('#approve_info_pekerjaan').text(laporan.rincian_pekerjaan ?? '-');
                    $('#approve_info_area').text(laporan.area ?? '-');
                    
                    $('#approve_hasil_pekerjaan').val(laporan.hasil_pekerjaan ?? '');
                    $('#approve_mengetahui').val(laporan.mengetahui ?? '');

                    $('#approve_info_bukti').html('');
                    if (laporan.bukti_list && laporan.bukti_list.length) {
                        laporan.bukti_list.forEach(function (bukti) {
                            const ekstensi = bukti.split('.').pop().toLowerCase();
                            const url = `${storageBaseUrl}/${bukti}`;
                            
                            if (['jpg', 'jpeg', 'png'].includes(ekstensi)) {
                                $('#approve_info_bukti').append(`<img src="${url}" alt="Bukti" class="img-thumbnail bukti-thumb" style="max-height: 100px; cursor: pointer;">`);
                            } else if (ekstensi === 'pdf') {
                                $('#approve_info_bukti').append(`<a href="${url}" target="_blank" class="badge bg-secondary d-flex align-items-center p-3 fs-8"><i class="fas fa-file-pdf me-1"></i> Lihat PDF</a>`);
                            }
                        });
                    } else {
                        $('#approve_info_bukti').text('Tidak ada bukti kerja.');
                    }

                    if (laporan.paraf) {
                        $('#preview_paraf_approve').html(`<div class="mb-2 text-muted fs-9">Paraf saat ini:</div><img src="${storageBaseUrl}/${laporan.paraf}" class="img-paraf-preview border rounded p-1 mb-3" style="max-height: 80px;" alt="Paraf">`);
                    } else {
                        $('#preview_paraf_approve').html('');
                    }

                    if (typeof approveSignaturePad !== 'undefined') {
                        approveSignaturePad.clear();
                    }
                    $('#paraf_signature').val('');

                    $('#approveLaporanModal').modal('show');
                });
            });

            $('#formEditLaporan')
            .off('submit')
            .on('submit', function (e) {
                e.preventDefault();

                const form = this;
                const formData = new FormData(form);
                const actionUrl = $(form).attr('action');
                const btn = $(form).find('button[type="submit"]');
                btn.attr('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');

                if (typeof editSignaturePad !== 'undefined' && !editSignaturePad.isEmpty()) {
                    const dataUrl = editSignaturePad.toDataURL();
                    formData.append('paraf_signature_edit', dataUrl);
                }

                $.ajax({
                    url: actionUrl,
                    method: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (res) {
                        btn.removeAttr('disabled').html('<i class="fas fa-save me-1"></i> Perbarui');
                        $('#editLaporanModal').modal('hide');
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: res.message || 'Laporan berhasil diperbarui!',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            refreshPageSections();
                        });
                    },
                    error: function (xhr) {
                        btn.removeAttr('disabled').html('<i class="fas fa-save me-1"></i> Perbarui');
                        let message = 'Gagal memperbarui laporan.';
                        if (xhr.responseJSON?.errors) {
                            message += '\n' + Object.values(xhr.responseJSON.errors).flat().join('\n');
                        } else if (xhr.responseJSON?.message) {
                            message = xhr.responseJSON.message;
                        }
                        Swal.fire('Error', message, 'error');
                        console.error(xhr.responseText);
                    }
                });
            });

            // Submit Handler for Persetujuan Form
            $('#formApproveLaporan')
            .off('submit')
            .on('submit', function (e) {
                e.preventDefault();

                let activeTab = $('#signatureTabs button.active').attr('id');
                let hasExistingParaf = $('#preview_paraf_approve img').length > 0;
                let hasCanvas = !approveSignaturePad.isEmpty();
                let hasFile = $('#approve_paraf_file').val() !== '';

                if (!hasExistingParaf && !hasCanvas && !hasFile) {
                    Swal.fire('Error', 'Paraf/Tanda tangan wajib diisi (silakan tulis langsung atau unggah berkas).', 'error');
                    return;
                }

                if (activeTab === 'canvas-tab' && hasCanvas) {
                    const dataUrl = approveSignaturePad.toDataURL();
                    $('#paraf_signature').val(dataUrl);
                    $('#approve_paraf_file').val('');
                } else if (activeTab === 'upload-tab' && hasFile) {
                    $('#paraf_signature').val('');
                }

                const form = this;
                const formData = new FormData(form);
                const actionUrl = $(form).attr('action');
                const btn = $(form).find('button[type="submit"]');
                btn.attr('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');

                $.ajax({
                    url: actionUrl,
                    method: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (res) {
                        btn.removeAttr('disabled').html('<i class="fas fa-check-double me-1"></i> Simpan Persetujuan');
                        $('#approveLaporanModal').modal('hide');
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: res.message || 'Persetujuan berhasil disimpan!',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            refreshPageSections();
                        });
                    },
                    error: function (xhr) {
                        btn.removeAttr('disabled').html('<i class="fas fa-check-double me-1"></i> Simpan Persetujuan');
                        let message = 'Gagal menyimpan persetujuan.';
                        if (xhr.responseJSON?.message) {
                            message = xhr.responseJSON.message;
                        }
                        Swal.fire('Error', message, 'error');
                    }
                });
            });

            $('#approveLaporanModal').on('shown.bs.modal', function () {
                const canvasEl = document.getElementById("approveSignatureCanvas");
                if (canvasEl) {
                    resizeCanvas(canvasEl, approveSignaturePad);
                }
            });

            window.clearApproveSignature = function () {
                approveSignaturePad.clear();
                $("#paraf_signature").val('');
                $("#preview_paraf_approve").html('');
                $("#approve_paraf_file").val('');
                $("#paraf_file_preview").html('');
            }

            window.previewParafFile = function (input) {
                const file = input.files[0];
                const preview = document.getElementById('paraf_file_preview');
                preview.innerHTML = '';

                if (file && file.type.startsWith('image/')) {
                    const objectUrl = URL.createObjectURL(file);
                    preview.innerHTML = `<img src="${objectUrl}" alt="Preview Paraf" class="img-thumbnail border p-1" style="max-height: 120px;">`;
                }
            };

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

            $('#imagePreviewModal').on('hide.bs.modal', function () {
                if (document.activeElement) {
                    document.activeElement.blur();
                }
            }).on('hidden.bs.modal', function () {
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
                                refreshPageSections();
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
