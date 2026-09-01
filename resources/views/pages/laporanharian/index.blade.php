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
                                    @if(!empty($isTodayHoliday))
                                        <span class="schedule-badge bg-light-danger text-danger border border-danger border-opacity-20"><i class="bi bi-calendar-x me-1 text-danger"></i> Hari Libur</span>
                                    @else
                                        <span class="schedule-badge">{{ $totalSelesaiHariIni }}/{{ $totalJadwalHariIni ?: 0 }} selesai</span>
                                    @endif
                                </div>

                                @if(!empty($isTodayHoliday))
                                    <div class="alert bg-light-danger border border-danger border-opacity-25 rounded-3 d-flex align-items-center p-3 mb-3">
                                        <i class="bi bi-calendar-x-fill text-danger fs-2 me-3"></i>
                                        <div>
                                            <div class="fw-bold text-danger fs-7">Hari ini adalah Tanggal Merah / Hari Libur</div>
                                            <div class="text-gray-700 fs-8">{{ $todayHolidayName ?? 'Hari Libur / Akhir Pekan' }} &bull; Tidak ada agenda pekerjaan OB hari ini.</div>
                                        </div>
                                    </div>
                                @endif

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
                                    @if(!empty($isTomorrowHoliday))
                                        <span class="schedule-badge bg-light-danger text-danger border border-danger border-opacity-20"><i class="bi bi-calendar-x me-1 text-danger"></i> Tanggal Merah (Libur)</span>
                                    @else
                                        <span class="schedule-badge bg-light-primary text-primary border border-primary border-opacity-20"><i class="bi bi-stars me-1 text-primary"></i> {{ $totalJadwalBesok }} tugas dipersiapkan</span>
                                    @endif
                                </div>

                                @if(!empty($isTomorrowHoliday))
                                    <div class="alert bg-light-danger border border-danger border-opacity-25 rounded-3 d-flex align-items-center p-3 mb-3">
                                        <i class="bi bi-calendar-x-fill text-danger fs-2 me-3"></i>
                                        <div>
                                            <div class="fw-bold text-danger fs-7">Besok adalah Tanggal Merah / Hari Libur</div>
                                            <div class="text-gray-700 fs-8">{{ $tomorrowHolidayName ?? 'Hari Libur / Akhir Pekan' }} &bull; Tidak ada agenda pekerjaan OB yang dipersiapkan untuk besok.</div>
                                        </div>
                                    </div>
                                @endif

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

                            <!-- Jam Kerja 24 Jam Indonesia (WIB) -->
                            <div class="time-picker-card p-3 rounded-3 mb-3 border bg-light-primary" id="add_time_picker_container">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <label class="form-label fw-bold text-gray-800 mb-0 fs-7 d-flex align-items-center">
                                        <i class="bi bi-clock-history text-primary me-2 fs-5"></i>Jam Kerja <span class="text-muted fw-normal ms-1 fs-8">(24 Jam WIB)</span>
                                    </label>
                                    <span class="badge bg-primary text-white fs-8 px-2 py-1" id="add_time_duration_badge">
                                        Durasi: 1 jam
                                    </span>
                                </div>

                                <div class="d-flex align-items-center text-muted fs-8 mb-2">
                                    <i class="bi bi-info-circle text-primary me-1"></i>
                                    <span>Format waktu 24 jam: <strong>00:00</strong> s.d. <strong>23:59</strong></span>
                                </div>

                                <div class="row g-2">
                                    <!-- Jam Mulai -->
                                    <div class="col-6">
                                        <div class="time-stepper-group">
                                            <label class="time-stepper-label"><i class="bi bi-play-circle text-success me-1"></i>Mulai</label>
                                            <div class="time-stepper-row">
                                                <div class="time-stepper-unit">
                                                    <button type="button" class="ts-btn ts-up" onclick="stepTime('add','mulai','hour',1)"><i class="bi bi-chevron-up"></i></button>
                                                    <input type="text" inputmode="numeric" maxlength="2" class="ts-input" id="add_jam_mulai_hour" value="07" onfocus="this.select()" onblur="clampTimeInput(this,'hour','add')" onkeydown="handleStepKey(event,'add','mulai','hour')">
                                                    <button type="button" class="ts-btn ts-down" onclick="stepTime('add','mulai','hour',-1)"><i class="bi bi-chevron-down"></i></button>
                                                    <span class="ts-unit-label">Jam</span>
                                                </div>
                                                <span class="ts-colon">:</span>
                                                <div class="time-stepper-unit">
                                                    <button type="button" class="ts-btn ts-up" onclick="stepTime('add','mulai','min',5)"><i class="bi bi-chevron-up"></i></button>
                                                    <input type="text" inputmode="numeric" maxlength="2" class="ts-input" id="add_jam_mulai_minute" value="00" onfocus="this.select()" onblur="clampTimeInput(this,'min','add')" onkeydown="handleStepKey(event,'add','mulai','min')">
                                                    <button type="button" class="ts-btn ts-down" onclick="stepTime('add','mulai','min',-5)"><i class="bi bi-chevron-down"></i></button>
                                                    <span class="ts-unit-label">Menit</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Jam Selesai -->
                                    <div class="col-6">
                                        <div class="time-stepper-group">
                                            <label class="time-stepper-label"><i class="bi bi-stop-circle text-danger me-1"></i>Selesai</label>
                                            <div class="time-stepper-row">
                                                <div class="time-stepper-unit">
                                                    <button type="button" class="ts-btn ts-up" onclick="stepTime('add','selesai','hour',1)"><i class="bi bi-chevron-up"></i></button>
                                                    <input type="text" inputmode="numeric" maxlength="2" class="ts-input" id="add_jam_selesai_hour" value="08" onfocus="this.select()" onblur="clampTimeInput(this,'hour','add')" onkeydown="handleStepKey(event,'add','selesai','hour')">
                                                    <button type="button" class="ts-btn ts-down" onclick="stepTime('add','selesai','hour',-1)"><i class="bi bi-chevron-down"></i></button>
                                                    <span class="ts-unit-label">Jam</span>
                                                </div>
                                                <span class="ts-colon">:</span>
                                                <div class="time-stepper-unit">
                                                    <button type="button" class="ts-btn ts-up" onclick="stepTime('add','selesai','min',5)"><i class="bi bi-chevron-up"></i></button>
                                                    <input type="text" inputmode="numeric" maxlength="2" class="ts-input" id="add_jam_selesai_minute" value="00" onfocus="this.select()" onblur="clampTimeInput(this,'min','add')" onkeydown="handleStepKey(event,'add','selesai','min')">
                                                    <button type="button" class="ts-btn ts-down" onclick="stepTime('add','selesai','min',-5)"><i class="bi bi-chevron-down"></i></button>
                                                    <span class="ts-unit-label">Menit</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Hidden Inputs untuk backend -->
                                <input type="hidden" id="jam_mulai" name="jam_mulai" value="07:00" required>
                                <input type="hidden" id="jam_selesai" name="jam_selesai" value="08:00" required>

                                <!-- Visual feedback -->
                                <div id="add_time_feedback" class="mt-2 text-muted fs-8 d-flex align-items-center">
                                    <i class="bi bi-info-circle-fill text-primary me-1"></i>
                                    <span id="add_time_feedback_text">Waktu kerja: 07:00 s.d. 08:00 WIB</span>
                                </div>
                            </div>

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

                            <!-- Jam Kerja 24 Jam Indonesia (WIB) Edit -->
                            <div class="time-picker-card p-3 rounded-3 mb-3 border bg-light-primary" id="edit_time_picker_container">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <label class="form-label fw-bold text-gray-800 mb-0 fs-7 d-flex align-items-center">
                                        <i class="bi bi-clock-history text-primary me-2 fs-5"></i>Jam Kerja <span class="text-muted fw-normal ms-1 fs-8">(24 Jam WIB)</span>
                                    </label>
                                    <span class="badge bg-primary text-white fs-8 px-2 py-1" id="edit_time_duration_badge">
                                        Durasi: 1 jam
                                    </span>
                                </div>

                                <div class="d-flex align-items-center text-muted fs-8 mb-2">
                                    <i class="bi bi-info-circle text-primary me-1"></i>
                                    <span>Format waktu 24 jam: <strong>00:00</strong> s.d. <strong>23:59</strong></span>
                                </div>

                                <div class="row g-2">
                                    <!-- Jam Mulai -->
                                    <div class="col-6">
                                        <div class="time-stepper-group">
                                            <label class="time-stepper-label"><i class="bi bi-play-circle text-success me-1"></i>Mulai</label>
                                            <div class="time-stepper-row">
                                                <div class="time-stepper-unit">
                                                    <button type="button" class="ts-btn ts-up" onclick="stepTime('edit','mulai','hour',1)"><i class="bi bi-chevron-up"></i></button>
                                                    <input type="text" inputmode="numeric" maxlength="2" class="ts-input" id="edit_jam_mulai_hour" value="07" onfocus="this.select()" onblur="clampTimeInput(this,'hour','edit')" onkeydown="handleStepKey(event,'edit','mulai','hour')">
                                                    <button type="button" class="ts-btn ts-down" onclick="stepTime('edit','mulai','hour',-1)"><i class="bi bi-chevron-down"></i></button>
                                                    <span class="ts-unit-label">Jam</span>
                                                </div>
                                                <span class="ts-colon">:</span>
                                                <div class="time-stepper-unit">
                                                    <button type="button" class="ts-btn ts-up" onclick="stepTime('edit','mulai','min',5)"><i class="bi bi-chevron-up"></i></button>
                                                    <input type="text" inputmode="numeric" maxlength="2" class="ts-input" id="edit_jam_mulai_minute" value="00" onfocus="this.select()" onblur="clampTimeInput(this,'min','edit')" onkeydown="handleStepKey(event,'edit','mulai','min')">
                                                    <button type="button" class="ts-btn ts-down" onclick="stepTime('edit','mulai','min',-5)"><i class="bi bi-chevron-down"></i></button>
                                                    <span class="ts-unit-label">Menit</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Jam Selesai -->
                                    <div class="col-6">
                                        <div class="time-stepper-group">
                                            <label class="time-stepper-label"><i class="bi bi-stop-circle text-danger me-1"></i>Selesai</label>
                                            <div class="time-stepper-row">
                                                <div class="time-stepper-unit">
                                                    <button type="button" class="ts-btn ts-up" onclick="stepTime('edit','selesai','hour',1)"><i class="bi bi-chevron-up"></i></button>
                                                    <input type="text" inputmode="numeric" maxlength="2" class="ts-input" id="edit_jam_selesai_hour" value="08" onfocus="this.select()" onblur="clampTimeInput(this,'hour','edit')" onkeydown="handleStepKey(event,'edit','selesai','hour')">
                                                    <button type="button" class="ts-btn ts-down" onclick="stepTime('edit','selesai','hour',-1)"><i class="bi bi-chevron-down"></i></button>
                                                    <span class="ts-unit-label">Jam</span>
                                                </div>
                                                <span class="ts-colon">:</span>
                                                <div class="time-stepper-unit">
                                                    <button type="button" class="ts-btn ts-up" onclick="stepTime('edit','selesai','min',5)"><i class="bi bi-chevron-up"></i></button>
                                                    <input type="text" inputmode="numeric" maxlength="2" class="ts-input" id="edit_jam_selesai_minute" value="00" onfocus="this.select()" onblur="clampTimeInput(this,'min','edit')" onkeydown="handleStepKey(event,'edit','selesai','min')">
                                                    <button type="button" class="ts-btn ts-down" onclick="stepTime('edit','selesai','min',-5)"><i class="bi bi-chevron-down"></i></button>
                                                    <span class="ts-unit-label">Menit</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Hidden Inputs untuk backend -->
                                <input type="hidden" id="edit_jam_mulai" name="jam_mulai" value="07:00" required>
                                <input type="hidden" id="edit_jam_selesai" name="jam_selesai" value="08:00" required>

                                <!-- Visual feedback -->
                                <div id="edit_time_feedback" class="mt-2 text-muted fs-8 d-flex align-items-center">
                                    <i class="bi bi-info-circle-fill text-primary me-1"></i>
                                    <span id="edit_time_feedback_text">Waktu kerja: 07:00 s.d. 08:00 WIB</span>
                                </div>
                            </div>

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
                                <label for="approve_mengetahui" class="form-label fw-bold text-gray-800">Mengetahui (Nama Staff Support Operasional) <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-solid" id="approve_mengetahui" name="mengetahui" placeholder="Nama Supervisor / Pemberi Persetujuan" required>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold text-gray-800">Paraf / Tanda Tangan <span class="text-danger">*</span></label>
                                
                                <div id="preview_paraf_approve" class="mb-2"></div>

                                <!-- Signature Mode Tabs -->
                                <ul class="nav nav-pills nav-fill mb-3 p-1 bg-light rounded-3" id="signatureTabs" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link active fw-bold py-2 fs-7" id="canvas-tab" data-bs-toggle="pill" data-bs-target="#canvas-panel" type="button" role="tab" aria-controls="canvas-panel" aria-selected="true">
                                            <i class="fas fa-pen-nib me-1"></i> Tulis Langsung
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link fw-bold py-2 fs-7" id="upload-tab" data-bs-toggle="pill" data-bs-target="#upload-panel" type="button" role="tab" aria-controls="upload-panel" aria-selected="false">
                                            <i class="fas fa-cloud-upload-alt me-1"></i> Unggah Gambar
                                        </button>
                                    </li>
                                </ul>

                                <div class="tab-content" id="signatureTabsContent">
                                    <!-- Canvas Panel -->
                                    <div class="tab-pane fade show active" id="canvas-panel" role="tabpanel" aria-labelledby="canvas-tab">
                                        <div class="border rounded-3 bg-white p-2 signature-pad-container shadow-xs">
                                            <canvas id="approveSignatureCanvas" class="w-100 border rounded-2" style="height: 180px; touch-action: none; background-color: #fafafa; cursor: crosshair;"></canvas>
                                            <div class="signature-line-guide text-muted text-center fs-9 py-1">Tanda tangani di atas area kanvas</div>
                                        </div>
                                        <input type="hidden" name="paraf_signature" id="paraf_signature">
                                        <div class="d-flex justify-content-between align-items-center mt-2">
                                            <span class="text-muted fs-9"><i class="fas fa-info-circle me-1"></i>Gunakan mouse atau sentuhan jari</span>
                                            <button type="button" class="btn btn-sm btn-outline-danger fw-bold" onclick="clearApproveSignature()"><i class="fas fa-eraser me-1"></i> Bersihkan Paraf</button>
                                        </div>
                                    </div>
                                    <!-- Upload Panel -->
                                    <div class="tab-pane fade" id="upload-panel" role="tabpanel" aria-labelledby="upload-tab">
                                        <div class="border border-dashed rounded-3 bg-light p-3 text-center upload-dropzone">
                                            <input type="file" name="paraf" id="approve_paraf_file" class="form-control" accept="image/jpeg,image/png,image/jpg" onchange="previewParafFile(this)">
                                            <div class="text-muted mt-2 fs-9"><i class="fas fa-file-image me-1"></i>Format: JPG, JPEG, PNG. Maks: 4MB.</div>
                                            <div id="paraf_file_preview" class="mt-3"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="modal-footer px-0 pb-0 pt-4 border-top">
                                <button type="button" class="btn btn-light btn-sm fw-bold" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-primary btn-sm fw-bold" id="btnSubmitApproveLaporan"><i class="fas fa-check-double me-1"></i> Simpan Persetujuan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- Modal Persetujuan (Export) -->
        <div class="modal fade" id="modalApproval" tabindex="-1" aria-labelledby="modalApprovalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-approval-dialog">
                <form id="formApproval" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                        <!-- Modal Header with icon badge & period subtitle -->
                        <div class="modal-header border-0 bg-light-subtle pb-2 pt-4 px-4 align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-3">
                                <div class="modal-approval-icon-box">
                                    <i class="fas fa-file-signature text-primary fs-4"></i>
                                </div>
                                <div>
                                    <h5 class="modal-title fw-bolder text-gray-900 mb-0" id="modalApprovalLabel">Persetujuan Ekspor Laporan</h5>
                                    <span class="text-muted fs-8">Periode: <strong class="text-primary fw-bold" id="modal_period_text">-</strong></span>
                                </div>
                            </div>
                            <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <div class="modal-body p-4 pt-3">
                            <!-- Notice Banner -->
                            <div class="approval-notice-banner mb-4">
                                <div class="notice-icon flex-shrink-0">
                                    <i class="fas fa-info-circle"></i>
                                </div>
                                <div class="notice-text">
                                    <div class="fw-bold text-gray-900 mb-1">Pengesahan Diperlukan</div>
                                    <div class="text-muted fs-8">
                                        Laporan periode ini belum disahkan. Lengkapi nama penyetuju dan paraf digital di bawah untuk mengekspor berkas Excel.
                                    </div>
                                </div>
                            </div>

                            <!-- Input Nama Penyetuju -->
                            <div class="mb-4">
                                <label for="approval_nama" class="form-label fw-bold text-gray-800 fs-7 mb-2">
                                    Nama Penyetuju / Supervisor <span class="text-danger">*</span>
                                </label>
                                <div class="input-group approval-input-group">
                                    <span class="input-group-text bg-light border-end-0 text-muted">
                                        <i class="fas fa-user-check"></i>
                                    </span>
                                    <input type="text" class="form-control bg-light border-start-0 ps-0" id="approval_nama" name="nama" placeholder="Masukkan nama lengkap penyetuju" required>
                                </div>
                            </div>

                            <!-- Signature Section -->
                            <div class="mb-3">
                                <label class="form-label fw-bold text-gray-800 fs-7 mb-2">
                                    Tanda Tangan / Paraf <span class="text-danger">*</span>
                                </label>

                                <!-- Modern Segmented Nav Pills -->
                                <div class="signature-nav-wrapper mb-3">
                                    <ul class="nav nav-pills approval-segmented-pills" id="approvalSignatureTabs" role="tablist">
                                        <li class="nav-item flex-fill" role="presentation">
                                            <button class="nav-link active w-100 fw-bold fs-7 py-2" id="approval-canvas-tab" data-bs-toggle="pill" data-bs-target="#approval-canvas-panel" type="button" role="tab" aria-controls="approval-canvas-panel" aria-selected="true">
                                                <i class="fas fa-pen-nib me-2"></i> Tulis Langsung
                                            </button>
                                        </li>
                                        <li class="nav-item flex-fill" role="presentation">
                                            <button class="nav-link w-100 fw-bold fs-7 py-2" id="approval-upload-tab" data-bs-toggle="pill" data-bs-target="#approval-upload-panel" type="button" role="tab" aria-controls="approval-upload-panel" aria-selected="false">
                                                <i class="fas fa-cloud-arrow-up me-2"></i> Unggah Berkas TTD
                                            </button>
                                        </li>
                                    </ul>
                                </div>

                                <div class="tab-content" id="approvalSignatureTabsContent">
                                    <!-- Canvas Tab -->
                                    <div class="tab-pane fade show active" id="approval-canvas-panel" role="tabpanel" aria-labelledby="approval-canvas-tab">
                                        <div class="signature-canvas-card">
                                            <div class="signature-canvas-header">
                                                <span class="fs-8 text-muted"><i class="fas fa-pencil-alt me-1 text-primary"></i> Area Goresan Tanda Tangan</span>
                                                <button type="button" class="btn btn-xs btn-outline-danger px-2 py-1 rounded-2 fw-semibold" onclick="clearExportApprovalSignature()" title="Bersihkan kanvas">
                                                    <i class="fas fa-eraser me-1"></i> Bersihkan
                                                </button>
                                            </div>
                                            <div class="signature-canvas-stage">
                                                <canvas id="approvalCanvas" class="w-100"></canvas>
                                                <div class="signature-watermark">
                                                    <span class="watermark-line"></span>
                                                    <span class="watermark-text">Tanda tangani di area ini</span>
                                                </div>
                                            </div>
                                        </div>
                                        <input type="hidden" id="approval_ttd_base64" name="ttd_base64">
                                        <div class="d-flex align-items-center justify-content-between text-muted fs-8 mt-2 px-1">
                                            <span><i class="fas fa-hand-pointer me-1 text-primary"></i> Gunakan mouse, stylus, atau jari di layar sentuh</span>
                                        </div>
                                    </div>

                                    <!-- Upload Tab -->
                                    <div class="tab-pane fade" id="approval-upload-panel" role="tabpanel" aria-labelledby="approval-upload-tab">
                                        <div class="signature-dropzone" onclick="document.getElementById('approval_ttd_file').click()">
                                            <input type="file" name="ttd_file" id="approval_ttd_file" class="d-none" accept="image/jpeg,image/png,image/jpg" onchange="previewExportApprovalFile(this)">
                                            <div id="approval_dropzone_empty">
                                                <div class="dropzone-icon-circle mb-2">
                                                    <i class="fas fa-cloud-arrow-up text-primary fs-3"></i>
                                                </div>
                                                <div class="fw-bold text-gray-800 fs-7 mb-1">Klik untuk Memilih Berkas Gambar</div>
                                                <div class="text-muted fs-8">Mendukung format JPG, JPEG, PNG (Maksimal 4MB)</div>
                                            </div>
                                            <div id="approval_file_preview" class="d-none" onclick="event.stopPropagation()"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <input type="hidden" name="bulan" id="approval_bulan">
                            <input type="hidden" name="tahun" id="approval_tahun">
                        </div>

                        <div class="modal-footer border-0 pt-0 pb-4 px-4 d-flex justify-content-end gap-2 bg-transparent">
                            <button type="button" class="btn btn-light px-4 py-2 fw-semibold fs-7 rounded-3" data-bs-dismiss="modal">
                                Batal
                            </button>
                            <button type="submit" class="btn btn-primary px-4 py-2 fw-bold fs-7 rounded-3 d-inline-flex align-items-center gap-2 shadow-sm" id="btnSubmitApproval">
                                <i class="fas fa-file-download"></i>
                                <span>Simpan & Ekspor Excel</span>
                            </button>
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

        <!-- Fullpage Excel Export Loader Overlay -->
        <div id="exportLoadingOverlay" class="export-loading-overlay d-none">
            <div class="export-loading-backdrop"></div>
            <div class="export-loading-card">
                <!-- Clean Symmetrical Loader Container (Full 62px height for both halves) -->
                <div class="export-loader-container">
                    <div class="loader"></div>
                </div>

                <!-- Text Info -->
                <h4 class="export-loading-title">Sedang Menyiapkan Rekap Excel...</h4>
                <p class="export-loading-desc">
                    Mohon tunggu, sistem sedang memproses data rekap bulanan, menyusun lampiran bukti, dan paraf...
                </p>

                <!-- Sleek Hairline Progress Bar -->
                <div class="shimmer-progress-track">
                    <div class="shimmer-progress-bar"></div>
                </div>

                <!-- Clean Status Indicator -->
                <div class="export-loading-subtext">
                    <span class="pulse-dot"></span>
                    <span class="export-status-label">Sedang memproses unduhan...</span>
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

        /* Signature Pad & Upload Styling */
        .signature-pad-container {
            background-color: #fafafa;
            position: relative;
            overflow: hidden;
        }

        .signature-pad-container canvas {
            display: block;
            touch-action: none;
            cursor: crosshair;
            background-color: #ffffff;
        }

        .signature-line-guide {
            border-top: 1px dashed #cbd5e1;
            margin-top: 4px;
            color: #94a3b8;
            font-size: 0.75rem;
            letter-spacing: 0.02em;
        }

        .upload-dropzone {
            background-color: #f8fafc;
            border: 2px dashed #cbd5e1 !important;
            transition: all 0.2s ease-in-out;
        }

        .upload-dropzone:hover {
            border-color: #3b82f6 !important;
            background-color: #eff6ff;
        }

        .preview-thumb-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }

        /* 24-Hour Time Stepper Component Styles */
        .time-picker-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            transition: all 0.25s ease-in-out;
        }
        .time-picker-card.is-invalid-time {
            background-color: #fef2f2 !important;
            border-color: #fca5a5 !important;
        }
        .time-presets-wrapper {
            background-color: #ffffff;
        }

        /* Stepper Group */
        .time-stepper-group {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 10px 8px 8px;
            text-align: center;
        }
        .time-stepper-label {
            display: block;
            font-size: 0.72rem;
            font-weight: 700;
            color: #64748b;
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .time-stepper-row {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
        }
        .time-stepper-unit {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 3px;
            flex: 1;
            min-width: 0;
            max-width: 56px;
        }

        /* Stepper Buttons — base (desktop / tablet) */
        .ts-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            max-width: 48px;
            height: 32px;
            border: 1px solid #e2e8f0;
            background: #f1f5f9;
            border-radius: 6px;
            color: #475569;
            cursor: pointer;
            font-size: 0.8rem;
            transition: all 0.15s ease;
            padding: 0;
            -webkit-tap-highlight-color: transparent;
            user-select: none;
            touch-action: manipulation;
        }
        .ts-btn:hover {
            background: #e0e7ff;
            border-color: #818cf8;
            color: #4338ca;
        }
        .ts-btn:active {
            background: #c7d2fe;
            transform: scale(0.92);
        }

        /* Stepper Input — base */
        .ts-input {
            width: 100%;
            max-width: 48px;
            height: 42px;
            text-align: center;
            font-size: 1.3rem;
            font-weight: 800;
            color: #1e293b;
            border: 2px solid #cbd5e1;
            border-radius: 8px;
            background: #ffffff;
            outline: none;
            padding: 0;
            -moz-appearance: textfield;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }
        .ts-input::-webkit-outer-spin-button,
        .ts-input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        .ts-input:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.18);
        }

        /* Colon separator */
        .ts-colon {
            font-size: 1.5rem;
            font-weight: 900;
            color: #94a3b8;
            padding: 0 2px;
            line-height: 1;
            margin-top: -14px;
            flex-shrink: 0;
        }

        /* Unit label */
        .ts-unit-label {
            font-size: 0.6rem;
            color: #94a3b8;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            margin-top: -1px;
        }

        .btn-quick-time {
            font-size: 0.725rem;
            padding: 3px 8px;
            font-weight: 500;
        }

        /* ==========================================
           RESPONSIVE BREAKPOINTS — all device sizes
           ========================================== */

        /* HP besar / phablet (max 480px) */
        @media (max-width: 480px) {
            .time-stepper-group {
                padding: 8px 6px 6px;
            }
            .ts-input {
                max-width: 46px;
                height: 40px;
                font-size: 1.2rem;
            }
            .ts-btn {
                max-width: 44px;
                height: 30px;
            }
        }

        /* HP biasa / standar (max 420px) */
        @media (max-width: 420px) {
            .time-stepper-group {
                padding: 7px 5px 5px;
                border-radius: 8px;
            }
            .ts-input {
                max-width: 42px;
                height: 38px;
                font-size: 1.15rem;
            }
            .ts-btn {
                max-width: 40px;
                height: 28px;
                font-size: 0.75rem;
            }
            .ts-colon {
                font-size: 1.3rem;
                padding: 0 1px;
            }
            .time-stepper-row {
                gap: 3px;
            }
        }

        /* HP kecil — iPhone SE, Galaxy S series kecil (max 375px) */
        @media (max-width: 375px) {
            .time-picker-card {
                padding: 10px 8px !important;
            }
            .time-stepper-group {
                padding: 6px 4px 4px;
            }
            .ts-input {
                max-width: 38px;
                height: 36px;
                font-size: 1.05rem;
                border-width: 1.5px;
                border-radius: 6px;
            }
            .ts-btn {
                max-width: 36px;
                height: 26px;
                font-size: 0.7rem;
                border-radius: 5px;
            }
            .ts-colon {
                font-size: 1.15rem;
                padding: 0;
            }
            .ts-unit-label {
                font-size: 0.5rem;
            }
            .time-stepper-label {
                font-size: 0.65rem;
                margin-bottom: 3px;
            }
            .time-stepper-row {
                gap: 2px;
            }
        }

        /* HP sangat kecil — layar 320px (iPhone 5/SE lama) */
        @media (max-width: 320px) {
            .time-picker-card {
                padding: 8px 6px !important;
            }
            .time-stepper-group {
                padding: 5px 3px 3px;
            }
            .ts-input {
                max-width: 34px;
                height: 32px;
                font-size: 0.95rem;
            }
            .ts-btn {
                max-width: 32px;
                height: 24px;
                font-size: 0.65rem;
            }
            .ts-colon {
                font-size: 1rem;
            }
            .time-stepper-label {
                font-size: 0.6rem;
            }
            .ts-unit-label {
                font-size: 0.45rem;
            }
        }

        /* =========================================================
           Fullpage Loading Overlay for Excel Export (Clean & Sleek)
           ========================================================= */
        .export-loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            z-index: 999999;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.25s ease, visibility 0.25s ease;
        }

        .export-loading-overlay.is-active {
            opacity: 1;
            visibility: visible;
        }

        .export-loading-backdrop {
            position: absolute;
            inset: 0;
            background: rgba(15, 23, 42, 0.65);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
        }

        .export-loading-card {
            position: relative;
            z-index: 1;
            background: #ffffff;
            border-radius: 24px;
            padding: 2.25rem 2rem 1.75rem;
            max-width: 400px;
            width: 90%;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.28), 0 0 0 1px rgba(0, 0, 0, 0.04);
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            animation: zoomInExportCard 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes zoomInExportCard {
            from {
                opacity: 0;
                transform: scale(0.92) translateY(8px);
            }
            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        /* Container with exact height (62px) to accommodate the full top & rotated bottom hexagon */
        .export-loader-container {
            width: 74px;
            height: 62px;
            margin: 0.5rem auto 1.5rem auto;
            position: relative;
            display: flex;
            align-items: flex-start;
            justify-content: center;
        }

        /* Custom User Loader CSS */
        .loader {
            width: 74px;
            height: calc(27px * 0.866 + 6px);
            display: grid;
            position: relative;
        }
        .loader:before,
        .loader:after {
            content: "";
            grid-area: 1/1;
            background: #FD135A;
            --c1: conic-gradient(from 150deg at top, #000 60deg, #0000 0);
            --c2: conic-gradient(from -30deg at bottom, #000 60deg, #0000 0);
            --s: 27px calc(27px * 0.866);
            clip-path: polygon(17px 0, calc(100% - 17px) 0, 100% 100%, 0 100%);
            -webkit-mask:
                var(--c1) left 6px top 4px,
                var(--c2) center,
                var(--c1) right 6px top 4px,
                linear-gradient(#000 0 0);
            mask:
                var(--c1) left 6px top 4px,
                var(--c2) center,
                var(--c1) right 6px top 4px,
                linear-gradient(#000 0 0);
            -webkit-mask-composite: xor;
            mask-composite: exclude;
            -webkit-mask-repeat: no-repeat;
            mask-repeat: no-repeat;
            animation: l6 2s infinite both;
        }
        .loader:after {
            transform: rotate(180deg);
            transform-origin: 50% 99%;
            animation-delay: 0.5s;
        }
        @keyframes l6 {
            0%    { -webkit-mask-size: 0 0, 0 0, 0 0, auto; mask-size: 0 0, 0 0, 0 0, auto; }
            8.33% { -webkit-mask-size: var(--s), 0 0, 0 0, auto; mask-size: var(--s), 0 0, 0 0, auto; }
            16.67%{ -webkit-mask-size: var(--s), var(--s), 0 0, auto; mask-size: var(--s), var(--s), 0 0, auto; }
            25%,
            50%   { -webkit-mask-size: var(--s), var(--s), var(--s), auto; mask-size: var(--s), var(--s), var(--s), auto; }
            58.33%{ -webkit-mask-size: 0 0, var(--s), var(--s), auto; mask-size: 0 0, var(--s), var(--s), auto; }
            66.67%{ -webkit-mask-size: 0 0, 0 0, var(--s), auto; mask-size: 0 0, 0 0, var(--s), auto; }
            75%,
            100%  { -webkit-mask-size: 0 0, 0 0, 0 0, auto; mask-size: 0 0, 0 0, 0 0, auto; }
        }

        .export-loading-title {
            color: #0f172a;
            font-size: 1.15rem;
            font-weight: 700;
            margin-bottom: 0.35rem;
        }

        .export-loading-desc {
            color: #64748b;
            line-height: 1.5;
            font-size: 0.84rem;
            max-width: 320px;
            margin: 0 auto 1.25rem auto;
        }

        /* Slim Shimmer Progress Bar */
        .shimmer-progress-track {
            width: 100%;
            height: 4px;
            background: #f1f5f9;
            border-radius: 99px;
            overflow: hidden;
            position: relative;
            margin-bottom: 1rem;
        }

        .shimmer-progress-bar {
            position: absolute;
            top: 0;
            left: 0;
            bottom: 0;
            width: 40%;
            background: linear-gradient(90deg, #FD135A, #ff6b8b);
            border-radius: 99px;
            animation: shimmerSweep 1.6s cubic-bezier(0.65, 0, 0.35, 1) infinite;
        }

        @keyframes shimmerSweep {
            0% { left: -40%; }
            100% { left: 100%; }
        }

        .export-loading-subtext {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            color: #FD135A;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .pulse-dot {
            width: 7px;
            height: 7px;
            background-color: #FD135A;
            border-radius: 50%;
            display: inline-block;
            animation: pulseDotGlow 1.4s ease-out infinite;
        }

        @keyframes pulseDotGlow {
            0% {
                transform: scale(0.9);
                opacity: 1;
            }
            50% {
                transform: scale(1.4);
                opacity: 0.5;
            }
            100% {
                transform: scale(0.9);
                opacity: 1;
            }
        }

        /* =========================================================
           Modal Approval Styling (High Polish)
           ========================================================= */
        .modal-approval-dialog {
            max-width: 520px;
        }

        .modal-approval-icon-box {
            width: 46px;
            height: 46px;
            border-radius: 14px;
            background: #eff6ff;
            border: 1px solid #dbeafe;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 6px rgba(59, 130, 246, 0.1);
        }

        .approval-notice-banner {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 12px 14px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-left: 4px solid #3b82f6;
            border-radius: 12px;
        }

        .approval-notice-banner .notice-icon {
            color: #3b82f6;
            font-size: 1.15rem;
            margin-top: 1px;
        }

        .approval-input-group .input-group-text {
            border-radius: 10px 0 0 10px;
            background-color: #f8fafc;
            border-color: #e2e8f0;
        }

        .approval-input-group input.form-control {
            border-radius: 0 10px 10px 0;
            border-color: #e2e8f0;
            font-size: 0.9rem;
        }

        .approval-input-group input.form-control:focus {
            background-color: #ffffff;
            border-color: #3b82f6;
            box-shadow: none;
        }

        .signature-nav-wrapper {
            background: #f1f5f9;
            padding: 4px;
            border-radius: 12px;
        }

        .approval-segmented-pills .nav-link {
            border-radius: 9px;
            color: #64748b;
            transition: all 0.2s ease;
            background: transparent;
        }

        .approval-segmented-pills .nav-link.active {
            background: #ffffff;
            color: #0f172a;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
        }

        .signature-canvas-card {
            border: 1.5px solid #e2e8f0;
            border-radius: 14px;
            overflow: hidden;
            background: #ffffff;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
            transition: border-color 0.2s ease;
        }

        .signature-canvas-card:hover,
        .signature-canvas-card:focus-within {
            border-color: #3b82f6;
        }

        .signature-canvas-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 8px 12px;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
        }

        .signature-canvas-stage {
            position: relative;
            height: 180px;
            background: #ffffff;
        }

        .signature-canvas-stage canvas {
            display: block;
            width: 100% !important;
            height: 100% !important;
            touch-action: none;
            cursor: crosshair;
            position: relative;
            z-index: 2;
        }

        .signature-watermark {
            position: absolute;
            bottom: 30px;
            left: 20px;
            right: 20px;
            z-index: 1;
            pointer-events: none;
            display: flex;
            flex-direction: column;
            align-items: center;
            opacity: 0.45;
        }

        .watermark-line {
            width: 100%;
            border-bottom: 1.5px dashed #94a3b8;
            margin-bottom: 4px;
        }

        .watermark-text {
            font-size: 0.72rem;
            color: #64748b;
            font-weight: 500;
        }

        .signature-dropzone {
            border: 2px dashed #cbd5e1;
            border-radius: 14px;
            background: #f8fafc;
            padding: 24px 16px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .signature-dropzone:hover {
            border-color: #3b82f6;
            background: #eff6ff;
        }

        .dropzone-icon-circle {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: #eff6ff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
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

        function showExportLoader(titleText, descText) {
            const $overlay = $('#exportLoadingOverlay');
            if (titleText) {
                $overlay.find('.export-loading-title').text(titleText);
            } else {
                $overlay.find('.export-loading-title').text('Menyiapkan Rekap Data Excel...');
            }
            if (descText) {
                $overlay.find('.export-loading-desc').text(descText);
            } else {
                $overlay.find('.export-loading-desc').text('Mohon tunggu beberapa saat. Sistem sedang mengumpulkan data harian, menyusun tabel kerja, serta memproses lampiran bukti dan paraf.');
            }
            $overlay.removeClass('d-none');
            // trigger reflow
            void $overlay[0].offsetWidth;
            $overlay.addClass('is-active');
        }

        function hideExportLoader() {
            const $overlay = $('#exportLoadingOverlay');
            $overlay.removeClass('is-active');
            setTimeout(() => {
                $overlay.addClass('d-none');
            }, 300);
        }

        function downloadExcelFile(url, defaultFilename) {
            showExportLoader('Sedang Menyiapkan Rekap Excel...', 'Mohon tunggu, sistem sedang memproses data rekap bulanan, menyusun lampiran bukti, dan paraf...');

            fetch(url, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(async (response) => {
                if (!response.ok) {
                    let errMsg = 'Terjadi kesalahan saat memproses data Excel.';
                    try {
                        const errData = await response.json();
                        if (errData && errData.message) errMsg = errData.message;
                    } catch (e) {}
                    throw new Error(errMsg);
                }

                let filename = defaultFilename || 'LaporanHarian.xlsx';
                const disposition = response.headers.get('Content-Disposition');
                if (disposition && disposition.indexOf('filename=') !== -1) {
                    const matches = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/.exec(disposition);
                    if (matches != null && matches[1]) {
                        filename = matches[1].replace(/['"]/g, '').trim();
                    }
                }

                return response.blob().then((blob) => ({ blob, filename }));
            })
            .then(({ blob, filename }) => {
                hideExportLoader();
                const blobUrl = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.style.display = 'none';
                a.href = blobUrl;
                a.download = filename;
                document.body.appendChild(a);
                a.click();
                setTimeout(() => {
                    window.URL.revokeObjectURL(blobUrl);
                    $(a).remove();
                }, 1000);

                Swal.fire({
                    icon: 'success',
                    title: 'Ekspor Berhasil!',
                    text: 'File Excel laporan harian berhasil diunduh.',
                    timer: 3000,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end'
                });
            })
            .catch((error) => {
                hideExportLoader();
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Mengekspor',
                    text: error.message || 'Gagal mengunduh file Excel.',
                    confirmButtonText: 'Tutup',
                    confirmButtonColor: '#d33'
                });
            });
        }

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
            if (!canvas || !signaturePadInstance) return;
            const rect = canvas.getBoundingClientRect();
            if (rect.width === 0 || rect.height === 0) return;

            const ratio = Math.max(window.devicePixelRatio || 1, 1);
            const data = !signaturePadInstance.isEmpty() ? signaturePadInstance.toData() : null;

            canvas.width = rect.width * ratio;
            canvas.height = rect.height * ratio;

            const ctx = canvas.getContext("2d");
            ctx.scale(ratio, ratio);

            signaturePadInstance.clear();
            if (data) {
                signaturePadInstance.fromData(data);
            }
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

        // ==========================================
        // 24-HOUR INDONESIAN TIME PICKER HELPERS
        // ==========================================
        function renderShiftPresets(prefix, shift) {
            const container = $(`#${prefix}_shift_presets`);
            if (!container.length) return;

            let html = '';
            if (shift === 'Siang') {
                html = `
                    <button type="button" class="btn btn-xs btn-outline-info rounded-pill" onclick="setQuickTime('${prefix}', '13', '00', '14', '00')">13:00 - 14:00</button>
                    <button type="button" class="btn btn-xs btn-outline-info rounded-pill" onclick="setQuickTime('${prefix}', '14', '00', '15', '00')">14:00 - 15:00</button>
                    <button type="button" class="btn btn-xs btn-outline-info rounded-pill" onclick="setQuickTime('${prefix}', '14', '00', '22', '00')">14:00 - 22:00 (Full)</button>
                `;
            } else {
                // Default / Pagi
                html = `
                    <button type="button" class="btn btn-xs btn-outline-primary rounded-pill" onclick="setQuickTime('${prefix}', '07', '00', '08', '00')">07:00 - 08:00</button>
                    <button type="button" class="btn btn-xs btn-outline-primary rounded-pill" onclick="setQuickTime('${prefix}', '08', '00', '09', '00')">08:00 - 09:00</button>
                    <button type="button" class="btn btn-xs btn-outline-primary rounded-pill" onclick="setQuickTime('${prefix}', '07', '00', '15', '00')">07:00 - 15:00 (Full)</button>
                `;
            }
            container.html(html);
        }

        // Helper: pad value to 2 digits
        function pad2(v) {
            return String(v).padStart(2, '0');
        }

        // Step hour or minute via ▲▼ buttons
        function stepTime(prefix, field, type, delta) {
            const el = document.getElementById(`${prefix}_jam_${field}_${type === 'hour' ? 'hour' : 'minute'}`);
            if (!el) return;
            let val = parseInt(el.value, 10) || 0;
            if (type === 'hour') {
                val = ((val + delta) % 24 + 24) % 24;
            } else {
                val = ((val + delta) % 60 + 60) % 60;
            }
            el.value = pad2(val);
            syncTimePicker(prefix);
        }

        // Clamp and format input after manual typing (onblur)
        function clampTimeInput(el, type, prefix) {
            let val = parseInt(el.value, 10);
            if (isNaN(val) || val < 0) val = 0;
            if (type === 'hour' && val > 23) val = 23;
            if (type === 'min' && val > 59) val = 59;
            el.value = pad2(val);
            syncTimePicker(prefix);
        }

        // Keyboard support: Arrow Up/Down on input
        function handleStepKey(event, prefix, field, type) {
            if (event.key === 'ArrowUp') {
                event.preventDefault();
                stepTime(prefix, field, type, type === 'hour' ? 1 : 5);
            } else if (event.key === 'ArrowDown') {
                event.preventDefault();
                stepTime(prefix, field, type, type === 'hour' ? -1 : -5);
            } else if (event.key === 'Enter') {
                event.preventDefault();
                event.target.blur();
            }
        }

        function syncTimePicker(prefix) {
            const startHour = $(`#${prefix}_jam_mulai_hour`).val() || '07';
            const startMin = $(`#${prefix}_jam_mulai_minute`).val() || '00';
            const endHour = $(`#${prefix}_jam_selesai_hour`).val() || '08';
            const endMin = $(`#${prefix}_jam_selesai_minute`).val() || '00';

            const startTime = `${pad2(startHour)}:${pad2(startMin)}`;
            const endTime = `${pad2(endHour)}:${pad2(endMin)}`;

            // Sync hidden inputs for form submit
            if (prefix === 'add') {
                $('#jam_mulai').val(startTime);
                $('#jam_selesai').val(endTime);
            } else {
                $('#edit_jam_mulai').val(startTime);
                $('#edit_jam_selesai').val(endTime);
            }

            // Calculate duration & validation
            const startTotal = parseInt(startHour, 10) * 60 + parseInt(startMin, 10);
            const endTotal = parseInt(endHour, 10) * 60 + parseInt(endMin, 10);
            const diff = endTotal - startTotal;

            const card = $(`#${prefix}_time_picker_container`);
            const badge = $(`#${prefix}_time_duration_badge`);
            const feedback = $(`#${prefix}_time_feedback`);
            const feedbackText = $(`#${prefix}_time_feedback_text`);

            if (diff < 0) {
                card.addClass('is-invalid-time').removeClass('bg-light-primary').addClass('bg-light-danger');
                badge.removeClass('bg-primary').addClass('bg-danger').text('Waktu Tidak Valid');
                feedback.removeClass('text-muted').addClass('text-danger');
                feedbackText.html(`<strong>Perhatian:</strong> Jam selesai (${endTime}) tidak boleh lebih awal dari jam mulai (${startTime})`);
            } else if (diff === 0) {
                card.removeClass('is-invalid-time bg-light-danger').addClass('bg-light-primary');
                badge.removeClass('bg-danger').addClass('bg-warning text-dark').text('Durasi: 0 menit');
                feedback.removeClass('text-danger').addClass('text-muted');
                feedbackText.text(`Waktu kerja: ${startTime} s.d ${endTime} WIB (Durasi sama / 0 menit)`);
            } else {
                const diffHours = Math.floor(diff / 60);
                const diffMins = diff % 60;
                let durText = '';
                if (diffHours > 0 && diffMins > 0) {
                    durText = `${diffHours} jam ${diffMins} mnt`;
                } else if (diffHours > 0) {
                    durText = `${diffHours} jam`;
                } else {
                    durText = `${diffMins} mnt`;
                }

                card.removeClass('is-invalid-time bg-light-danger').addClass('bg-light-primary');
                badge.removeClass('bg-danger bg-warning text-dark').addClass('bg-primary text-white').text(`Durasi: ${durText}`);
                feedback.removeClass('text-danger').addClass('text-muted');
                feedbackText.text(`Waktu kerja: ${startTime} s.d ${endTime} WIB (Durasi: ${durText})`);
            }
        }

        function setTimePickerValues(prefix, jamMulai, jamSelesai) {
            if (!jamMulai) jamMulai = '07:00';
            if (!jamSelesai) jamSelesai = '08:00';

            const startParts = jamMulai.split(':');
            const endParts = jamSelesai.split(':');

            $(`#${prefix}_jam_mulai_hour`).val(pad2(startParts[0] || '07'));
            $(`#${prefix}_jam_mulai_minute`).val(pad2(startParts[1] || '00'));
            $(`#${prefix}_jam_selesai_hour`).val(pad2(endParts[0] || '08'));
            $(`#${prefix}_jam_selesai_minute`).val(pad2(endParts[1] || '00'));

            syncTimePicker(prefix);
        }

        function setQuickTime(prefix, startH, startM, endH, endM) {
            $(`#${prefix}_jam_mulai_hour`).val(startH);
            $(`#${prefix}_jam_mulai_minute`).val(startM);
            $(`#${prefix}_jam_selesai_hour`).val(endH);
            $(`#${prefix}_jam_selesai_minute`).val(endM);

            syncTimePicker(prefix);
        }

        function setQuickNow(prefix) {
            const now = new Date();
            const currentHour = now.getHours();
            const currentMinute = now.getMinutes();

            // Bulatkan ke kelipatan 5 terdekat
            const roundedMinute = Math.round(currentMinute / 5) * 5;
            let startH = currentHour;
            let startM = roundedMinute;
            if (startM >= 60) {
                startH = (startH + 1) % 24;
                startM = 0;
            }

            let endH = (startH + 1) % 24;
            let endM = startM;

            setQuickTime(prefix, pad2(startH), pad2(startM), pad2(endH), pad2(endM));
        }

        function addDurationMinutes(prefix, minutesToAdd) {
            const startH = parseInt($(`#${prefix}_jam_mulai_hour`).val() || '0', 10);
            const startM = parseInt($(`#${prefix}_jam_mulai_minute`).val() || '0', 10);

            let totalMinutes = (startH * 60 + startM + minutesToAdd) % (24 * 60);
            let endH = Math.floor(totalMinutes / 60);
            let endM = totalMinutes % 60;

            $(`#${prefix}_jam_selesai_hour`).val(pad2(endH));
            $(`#${prefix}_jam_selesai_minute`).val(pad2(endM));

            syncTimePicker(prefix);
        }

        $(document).ready(function () {
            // Inisialisasi Time Picker default
            renderShiftPresets('add', $('#formTambahLaporanHarian input[name="shift"]:checked').val() || 'Pagi');
            syncTimePicker('add');

            // Prevent accessibility warning when hiding modal while descendant retains focus
            $(document).on('hide.bs.modal', '.modal', function () {
                if (document.activeElement && this.contains(document.activeElement)) {
                    document.activeElement.blur();
                }
            });

            // Register event listeners for Add Modal
            $('#tanggal').on('change', function () {
                const tanggal = $(this).val();
                const shift = $('#formTambahLaporanHarian input[name="shift"]:checked').val();
                loadPekerjaanList(tanggal, shift);
            });

            $('#formTambahLaporanHarian input[name="shift"]').on('change', function () {
                const shift = $(this).val();
                const tanggal = $('#tanggal').val();
                loadPekerjaanList(tanggal, shift);
                renderShiftPresets('add', shift);
                if (shift === 'Siang') {
                    setTimePickerValues('add', '14:00', '15:00');
                } else {
                    setTimePickerValues('add', '07:00', '08:00');
                }
            });

            $('#formEditLaporan input[name="shift"]').on('change', function () {
                const shift = $(this).val();
                renderShiftPresets('edit', shift);
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
                const currentShift = $('#formTambahLaporanHarian input[name="shift"]:checked').val() || 'Pagi';
                renderShiftPresets('add', currentShift);
                if (currentShift === 'Siang') {
                    setTimePickerValues('add', '14:00', '15:00');
                } else {
                    setTimePickerValues('add', '07:00', '08:00');
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
                        setTimePickerValues('add', '07:00', '08:00');
                        renderShiftPresets('add', 'Pagi');
                        
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
            if (approveCanvas) {
                approveSignaturePad = new SignaturePad(approveCanvas, {
                    minWidth: 1.5,
                    maxWidth: 3.5,
                    penColor: "#0f172a"
                });
            }
            const canvas = document.getElementById('approvalCanvas');
            if (canvas) {
                approvalPad = new SignaturePad(canvas, {
                    minWidth: 1.5,
                    maxWidth: 3.5,
                    penColor: "#0f172a"
                });
            }

            $('#filter_bulan, #filter_tahun').on('change', function () {
                $('#filterForm').trigger('submit');
            });

            $('#tableLaporanHarian').DataTable({
                processing: true,
                serverSide: true,
                deferRender: true,
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

                            // Check approval status via AJAX with quick loader
                            showExportLoader('Memeriksa Persetujuan...', 'Memeriksa status persetujuan laporan periode ini...');

                            $.get("{{ route('laporanharian.exportexcel') }}", {
                                bulan,
                                tahun,
                                ajax: true
                            }).done(function (res) {
                                hideExportLoader();
                                if (res.needs_approval) {
                                    // Tampilkan modal approval dengan teks periode
                                    $('#approval_bulan').val(bulan);
                                    $('#approval_tahun').val(tahun);
                                    const namaBulan = $(`#filter_bulan option[value="${bulan}"]`).text().trim() || 'Bulan';
                                    $('#modal_period_text').text(`${namaBulan} ${tahun}`);
                                    $('#modalApproval').modal('show');
                                } else {
                                    // Langsung download dengan loader satu halaman
                                    const url = `{{ route('laporanharian.exportexcel') }}?bulan=${bulan}&tahun=${tahun}`;
                                    const namaBulan = $('#filter_bulan option:selected').text().trim();
                                    const defaultFilename = `LaporanHarian_${namaBulan}_${tahun}.xlsx`;
                                    downloadExcelFile(url, defaultFilename);
                                }
                            }).fail(function () {
                                hideExportLoader();
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
                    setTimePickerValues('edit', laporan.jam_mulai, laporan.jam_selesai);
                    renderShiftPresets('edit', laporan.shift);
                    
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
                    $('#edit_jam_mulai_hour, #edit_jam_mulai_minute, #edit_jam_selesai_hour, #edit_jam_selesai_minute').removeAttr('disabled');
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
                let hasCanvas = approveSignaturePad && !approveSignaturePad.isEmpty();
                let fileInput = document.getElementById('approve_paraf_file');
                let hasFile = fileInput && fileInput.files && fileInput.files.length > 0;

                if (!hasExistingParaf && !hasCanvas && !hasFile) {
                    Swal.fire('Error', 'Paraf/Tanda tangan wajib diisi (silakan tulis langsung atau unggah berkas).', 'error');
                    return;
                }

                if (activeTab === 'canvas-tab' && hasCanvas) {
                    const dataUrl = approveSignaturePad.toDataURL();
                    $('#paraf_signature').val(dataUrl);
                    if (fileInput) fileInput.value = '';
                } else if (activeTab === 'upload-tab' && hasFile) {
                    $('#paraf_signature').val('');
                }

                const form = this;
                const formData = new FormData(form);
                const actionUrl = $(form).attr('action');
                const btn = $('#btnSubmitApproveLaporan');
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

            // Modal shown handlers for responsive canvas
            $('#approveLaporanModal').on('shown.bs.modal', function () {
                const canvasEl = document.getElementById("approveSignatureCanvas");
                if (canvasEl && approveSignaturePad) {
                    resizeCanvas(canvasEl, approveSignaturePad);
                }
            });

            $('#modalApproval').on('shown.bs.modal', function () {
                const canvasEl = document.getElementById("approvalCanvas");
                if (canvasEl && approvalPad) {
                    resizeCanvas(canvasEl, approvalPad);
                }
            });

            // Resize canvas when tab changes to canvas panel
            $('button[data-bs-target="#canvas-panel"]').on('shown.bs.tab', function () {
                const canvasEl = document.getElementById("approveSignatureCanvas");
                if (canvasEl && approveSignaturePad) {
                    resizeCanvas(canvasEl, approveSignaturePad);
                }
            });

            $('button[data-bs-target="#approval-canvas-panel"]').on('shown.bs.tab', function () {
                const canvasEl = document.getElementById("approvalCanvas");
                if (canvasEl && approvalPad) {
                    resizeCanvas(canvasEl, approvalPad);
                }
            });

            // Window resize debounced canvas update
            let signatureResizeDebounce;
            $(window).on('resize', function () {
                clearTimeout(signatureResizeDebounce);
                signatureResizeDebounce = setTimeout(function () {
                    if ($('#approveLaporanModal').hasClass('show')) {
                        const canvasEl = document.getElementById("approveSignatureCanvas");
                        if (canvasEl && approveSignaturePad) {
                            resizeCanvas(canvasEl, approveSignaturePad);
                        }
                    }
                    if ($('#modalApproval').hasClass('show')) {
                        const canvasEl = document.getElementById("approvalCanvas");
                        if (canvasEl && approvalPad) {
                            resizeCanvas(canvasEl, approvalPad);
                        }
                    }
                }, 150);
            });

            window.clearApproveSignature = function () {
                if (approveSignaturePad) approveSignaturePad.clear();
                $("#paraf_signature").val('');
                $("#preview_paraf_approve").html('');
                clearParafFileInput();
            };

            window.clearParafFileInput = function () {
                const input = document.getElementById('approve_paraf_file');
                if (input) input.value = '';
                const preview = document.getElementById('paraf_file_preview');
                if (preview) preview.innerHTML = '';
            };

            window.previewParafFile = function (input) {
                const file = input.files[0];
                const preview = document.getElementById('paraf_file_preview');
                if (!preview) return;
                preview.innerHTML = '';

                if (file && file.type.startsWith('image/')) {
                    const objectUrl = URL.createObjectURL(file);
                    const fileSizeKb = (file.size / 1024).toFixed(1);
                    preview.innerHTML = `
                        <div class="card border border-primary border-dashed p-3 bg-light-primary text-center mt-2">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="fs-8 fw-bold text-gray-700 text-truncate me-2"><i class="fas fa-image text-primary me-1"></i>${file.name} (${fileSizeKb} KB)</span>
                                <button type="button" class="btn btn-xs btn-icon btn-light-danger" onclick="clearParafFileInput()" title="Hapus Gambar"><i class="fas fa-times"></i></button>
                            </div>
                            <div class="position-relative d-inline-block mx-auto">
                                <img src="${objectUrl}" alt="Preview Paraf" class="img-thumbnail border p-1 shadow-sm" style="max-height: 120px; object-fit: contain; cursor: pointer;" onclick="$('#modalPreviewImage').attr('src', '${objectUrl}'); $('#imagePreviewModal').modal('show');">
                            </div>
                        </div>
                    `;
                }
            };

            window.clearExportApprovalFileInput = function () {
                const input = document.getElementById('approval_ttd_file');
                if (input) input.value = '';
                const preview = document.getElementById('approval_file_preview');
                if (preview) {
                    preview.innerHTML = '';
                    preview.classList.add('d-none');
                }
                const empty = document.getElementById('approval_dropzone_empty');
                if (empty) empty.classList.remove('d-none');
            };

            window.clearExportApprovalSignature = function () {
                if (approvalPad) approvalPad.clear();
                $('#approval_ttd_base64').val('');
                clearExportApprovalFileInput();
            };

            window.previewExportApprovalFile = function (input) {
                const file = input.files[0];
                const preview = document.getElementById('approval_file_preview');
                const empty = document.getElementById('approval_dropzone_empty');
                if (!preview) return;
                preview.innerHTML = '';

                if (file && file.type.startsWith('image/')) {
                    const objectUrl = URL.createObjectURL(file);
                    const fileSizeKb = (file.size / 1024).toFixed(1);
                    preview.innerHTML = `
                        <div class="p-3 bg-white border rounded-3 shadow-xs d-inline-block text-center position-relative">
                            <button type="button" class="btn btn-xs btn-icon btn-danger position-absolute top-0 end-0 m-1 rounded-circle" onclick="event.stopPropagation(); clearExportApprovalFileInput();" title="Hapus Gambar">
                                <i class="fas fa-times"></i>
                            </button>
                            <img src="${objectUrl}" alt="Preview TTD" class="img-fluid rounded mb-2" style="max-height: 100px; object-fit: contain;">
                            <div class="fs-8 fw-bold text-gray-800 text-truncate" style="max-width: 200px;">${file.name}</div>
                            <div class="fs-9 text-muted">${fileSizeKb} KB</div>
                        </div>
                    `;
                    preview.classList.remove('d-none');
                    if (empty) empty.classList.add('d-none');
                } else {
                    preview.classList.add('d-none');
                    if (empty) empty.classList.remove('d-none');
                }
            };

            $('#formApproval').on('submit', function (e) {
                e.preventDefault();

                let activeTab = $('#approvalSignatureTabs button.active').attr('id');
                let hasCanvas = approvalPad && !approvalPad.isEmpty();
                let fileInput = document.getElementById('approval_ttd_file');
                let hasFile = fileInput && fileInput.files && fileInput.files.length > 0;

                if (!hasCanvas && !hasFile) {
                    return Swal.fire('Error', 'Tanda tangan atau unggahan berkas paraf wajib diisi.', 'error');
                }

                if (activeTab === 'approval-canvas-tab' && hasCanvas) {
                    $('#approval_ttd_base64').val(approvalPad.toDataURL());
                    if (fileInput) fileInput.value = '';
                } else if (activeTab === 'approval-upload-tab' && hasFile) {
                    $('#approval_ttd_base64').val('');
                }

                const form = this;
                const formData = new FormData(form);
                const btn = $('#btnSubmitApproval');
                btn.attr('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');

                $.ajax({
                    url: `{{ route('laporanharian.storeapproval') }}`,
                    method: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (res) {
                        btn.removeAttr('disabled').html('<i class="fas fa-file-download me-1"></i> Simpan & Unduh');
                        $('#modalApproval').modal('hide');

                        const bulan = $('#approval_bulan').val();
                        const tahun = $('#approval_tahun').val();
                        const url = `{{ route('laporanharian.exportexcel') }}?bulan=${bulan}&tahun=${tahun}`;
                        const namaBulan = $(`#filter_bulan option[value="${bulan}"]`).text().trim() || 'Bulan';
                        const defaultFilename = `LaporanHarian_${namaBulan}_${tahun}.xlsx`;

                        // Langsung unduh dengan loader satu halaman
                        downloadExcelFile(url, defaultFilename);
                    },
                    error: function (xhr) {
                        btn.removeAttr('disabled').html('<i class="fas fa-file-download me-1"></i> Simpan & Unduh');
                        let message = 'Gagal menyimpan persetujuan.';
                        if (xhr.responseJSON?.message) {
                            message = xhr.responseJSON.message;
                        }
                        Swal.fire('Error', message, 'error');
                    }
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
                const src = $(this).attr('data-full') || $(this).attr('src');
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
