<x-default-layout>
    @section('title', 'Checklist Area Pembersihan')

    <div class="container py-4">
        <h1 class="text-center mb-4">
            <span class="highlight-title">Checklist Area Pembersihan</span>
        </h1>

        <div class="d-flex justify-content-end mb-3 gap-2">
            <select id="filterBulan" class="form-select w-auto">
                @for ($i = 1; $i <= 12; $i++)
                    <option value="{{ $i }}" {{ $i == $now->month ? 'selected' : '' }}>
                        {{ \Carbon\Carbon::create()->month($i)->translatedFormat('F') }}
                    </option>
                @endfor
            </select>

            <select id="filterTahun" class="form-select w-auto">
                @for ($y = now()->year - 5; $y <= now()->year + 1; $y++)
                    <option value="{{ $y }}" {{ $y == $now->year ? 'selected' : '' }}>
                        {{ $y }}
                    </option>
                @endfor
            </select>
        </div>

        <div class="p-4 rounded shadow-sm bg-white" id="checklistTableContainer">
            <div class="table-responsive">
                @php
                    $bulan = request('bulan', now()->month);
                    $tahun = request('tahun', now()->year);

                    $now = \Carbon\Carbon::createFromDate($tahun, $bulan, 1);
                    $daysInMonth = $now->daysInMonth;

                    $totalColspan = 1 + 1 + 1 + ($daysInMonth * 2) + 1;
                @endphp

                <table id="tablechecklist" class="table table-bordered table-striped" style="width:100%">
                    <thead>
                        <tr>
                            <th class="text-start" style="display:none">Area</th>
                            <th class="text-center nomor-column">No</th>
                            <th class="text-center">Pekerjaan</th>
                            <th class="text-center">Periodic Cleaning</th>
                            @for ($i = 1; $i <= $daysInMonth; $i++)
                                @php
                                    $tanggalCell = \Carbon\Carbon::create($tahun, $bulan, $i)->format('Y-m-d');
                                    $day = \Carbon\Carbon::create($tahun, $bulan, $i)->format('l');
                                    $isWeekend = in_array($day, ['Saturday', 'Sunday']);
                                    $isHoliday = in_array($tanggalCell, $holidayDates ?? []);
                                @endphp
                                <th class="text-center {{ ($isWeekend || $isHoliday) ? 'text-danger fw-bold hari-libur' : '' }}" colspan="2">
                                    {{ $i }}
                                </th>
                            @endfor
                            <th class="text-center">Keterangan</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                        <tr>
                            <th style="display:none"></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            @for ($i = 1; $i <= $daysInMonth; $i++)
                                @php
                                    $tanggalCell = \Carbon\Carbon::create($tahun, $bulan, $i)->format('Y-m-d');
                                    $day = \Carbon\Carbon::create($tahun, $bulan, $i)->format('l');
                                    $isWeekend = in_array($day, ['Saturday', 'Sunday']);
                                    $isHoliday = in_array($tanggalCell, $holidayDates ?? []);
                                @endphp
                                <th class="text-center {{ ($isWeekend || $isHoliday) ? 'text-danger hari-libur' : '' }}">P</th>
                                <th class="text-center {{ ($isWeekend || $isHoliday) ? 'text-danger hari-libur' : '' }}">S</th>
                            @endfor
                            <th></th>
                            <th></th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($checklists as $area => $items)
                            @foreach ($items as $index => $item)
                                <tr>
                                    <td style="display:none">{{ $area }}</td>
                                    <td class="text-center nomor-column">{{ $loop->iteration }}</td>
                                    <td class="text-start pekerjaan-column">{{ $item->pekerjaan }}</td>
                                    <td class="text-start periodic-column">
                                        {{ $item->frequency_count }}x
                                        @if($item->frequency_unit === 'per_x_hari')
                                            per {{ $item->frequency_interval }} hari
                                        @elseif($item->frequency_unit === 'per_hari')
                                            per hari
                                        @elseif($item->frequency_unit === 'per_minggu')
                                            per minggu
                                        @elseif($item->frequency_unit === 'per_x_minggu')
                                            per {{ $item->frequency_interval }} minggu
                                        @elseif($item->frequency_unit === 'per_bulan')
                                            per bulan
                                        @endif
                                    </td>

                                    @for ($i = 1; $i <= $daysInMonth; $i++)
                                        @php
                                            $tanggalCell = \Carbon\Carbon::create($tahun, $bulan, $i)->format('Y-m-d');
                                            $day = \Carbon\Carbon::create($tahun, $bulan, $i)->format('l');
                                            $isWeekend = in_array($day, ['Saturday', 'Sunday']);
                                            $isHoliday = in_array($tanggalCell, $holidayDates ?? []);

                                            $keyPagi = $item->id . '_' . $tanggalCell . '_Pagi';
                                            $keySiang = $item->id . '_' . $tanggalCell . '_Siang';

                                            $statusPagi = ($statusData[$keyPagi] ?? 0) && ($parafStatuses[$keyPagi] ?? 0);
                                            $statusSiang = ($statusData[$keySiang] ?? 0) && ($parafStatuses[$keySiang] ?? 0);
                                        @endphp

                                        <td class="
                                            @if ($isWeekend || $isHoliday) hari-libur @endif
                                            @if (array_key_exists($keyPagi, $statusData))
                                                {{ isset($parafStatuses[$keyPagi]) ? 'bg-success text-white' : 'bg-primary text-white' }}
                                            @endif
                                        "></td>
                                        <td class="
                                            @if ($isWeekend || $isHoliday) hari-libur @endif
                                            @if (array_key_exists($keySiang, $statusData))
                                                {{ isset($parafStatuses[$keySiang]) ? 'bg-success text-white' : 'bg-primary text-white' }}
                                            @endif
                                        "></td>
                                    @endfor

                                    <td class="text-start keterangan-column">{{ $item->keterangan }}</td>
                                    <td class="text-center">
                                        @if(auth()->user()->hasPermission('checklist_edit'))
                                            <button type="button" class="btn btn-xs btn-light border edit-checklist-btn"
                                                    data-id="{{ $item->id }}">
                                                <i class="fas fa-edit"></i> Edit
                                            </button>
                                        @else
                                            <span class="text-muted fs-9">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        @empty
                        @endforelse
                    </tbody>
                </table>
                @if (!empty($holidayDetails))
                    <div class="mt-8 border-0 shadow-sm card bg-light-danger">
                        <div class="p-5 card-body">
                            <div class="mb-4 d-flex align-items-center">
                                <i class="text-danger fas fa-calendar-day fs-3 me-3"></i>
                                <h5 class="mb-0 fw-bold text-danger">Informasi Hari Libur & Cuti Bersama ({{ $now->translatedFormat('F Y') }})</h5>
                            </div>
                            <div class="row g-4">
                                @foreach ($holidayDetails as $libur)
                                    <div class="col-md-4">
                                        <div class="p-4 bg-white border-0 shadow-sm rounded-3 h-100 border-start border-4 border-danger">
                                            <div class="mb-1 d-flex justify-content-between align-items-center">
                                                <span class="badge badge-light-danger fw-bold">{{ $libur['jenis_libur'] }}</span>
                                                <small class="text-muted fw-bold"><i class="far fa-calendar me-1"></i> {{ $libur['tanggal'] }}</small>
                                            </div>
                                            <div class="mt-2 fw-bolder text-dark fs-6">{{ $libur['keterangan'] }}</div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @else
                   <div class="p-5 mt-5 text-center bg-light rounded-3 shadow-none">
                        <i class="mb-3 fas fa-info-circle text-muted fs-1"></i>
                        <p class="mb-0 text-muted">Tidak ada hari libur nasional atau cuti bersama yang tercatat untuk bulan ini.</p>
                   </div>
                @endif
            </div>

            <!-- Enhanced Legend Section -->
            <div class="p-6 mt-8 border-0 shadow-sm card bg-white">
                <div class="mb-4 d-flex align-items-center">
                    <i class="text-primary fas fa-info-circle fs-4 me-2"></i>
                    <h6 class="mb-0 fw-bold">Panduan Warna & Status</h6>
                </div>
                <div class="row g-4">
                    <div class="col-sm-4">
                        <div class="p-3 border rounded d-flex align-items-center h-100">
                            <div class="flex-shrink-0 shadow-sm rounded-circle me-3" style="width: 20px; height: 20px; background-color: #92D050; border: 2px solid #fff; outline: 1px solid #92D050;"></div>
                            <div>
                                <div class="fw-bold fs-7">Selesai & Paraf</div>
                                <small class="text-muted">Pekerjaan telah dikerjakan dan diverifikasi.</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="p-3 border rounded d-flex align-items-center h-100">
                            <div class="flex-shrink-0 shadow-sm rounded-circle me-3" style="width: 20px; height: 20px; background-color: #00B0F0; border: 2px solid #fff; outline: 1px solid #00B0F0;"></div>
                            <div>
                                <div class="fw-bold fs-7">Dijadwalkan</div>
                                <small class="text-muted">Pekerjaan dijadwalkan namun belum diparaf.</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="p-3 border rounded d-flex align-items-center h-100">
                            <div class="flex-shrink-0 shadow-sm rounded-circle me-3" style="width: 20px; height: 20px; background-color: #FFE5E5; border: 2px solid #fff; outline: 1px solid #FFDada;"></div>
                            <div>
                                <div class="fw-bold fs-7">Hari Libur / Weekend</div>
                                <small class="text-muted">Hari libur nasional atau akhir pekan.</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Modal Tambah Jadwal OB -->
        <div class="modal fade" id="addJadwalOB" tabindex="-1" aria-labelledby="addJadwalOBLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <form id="formTambahJadwalOB" method="POST" action="{{ route('checklist.store') }}">
                        @csrf
                        <div class="modal-header border-0 pb-0">
                            <h5 class="modal-title fs-2 fw-bolder text-dark" id="addJadwalOBLabel">Tambah Jadwal Pekerjaan</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <div class="modal-body px-5 py-8">
                            <!-- Section: Primary Info -->
                            <div class="mb-8">
                                <h6 class="fs-6 fw-bold text-gray-700 mb-4 border-bottom pb-2">Informasi Pekerjaan</h6>
                                <div class="row g-5">
                                    <div class="col-md-6">
                                        <label for="area" class="form-label fw-bold small text-uppercase">Area</label>
                                        <select class="form-select select2-taggable bg-light-gray" name="area" id="area" required>
                                            <option value="" disabled selected>Pilih atau Ketik Area</option>
                                            @foreach ($areas as $area)
                                                <option value="{{ $area }}">{{ $area }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="pekerjaan" class="form-label fw-bold small text-uppercase">Tugas / Pekerjaan</label>
                                        <input type="text" class="form-control" id="pekerjaan" name="pekerjaan" placeholder="Contoh: Pembersihan Kaca" required>
                                    </div>
                                </div>
                            </div>

                            <!-- Section: Scheduling -->
                            <div class="mb-8">
                                <h6 class="fs-6 fw-bold text-gray-700 mb-4 border-bottom pb-2">Penjadwalan & Frekuensi</h6>
                                <div class="row g-5">
                                    <div class="col-md-12">
                                        <label for="start_date" class="form-label fw-bold small text-uppercase">Dimulai Tanggal</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0"><i class="fas fa-calendar-alt"></i></span>
                                            <input type="date" class="form-control border-start-0" id="start_date" name="start_date" required>
                                        </div>
                                        <small class="text-primary mt-1 d-block"><i class="fas fa-magic me-1"></i> Bulan & Tahun akan otomatis menyesuaikan tanggal pilihan Anda.</small>
                                    </div>
                                    
                                    <!-- Hidden / Background fields for Month/Year that sync automatically -->
                                    <div class="col-md-6 d-none">
                                        <select name="bulan" id="bulan" required>
                                            @for ($i = 1; $i <= 12; $i++)
                                                <option value="{{ $i }}">{{ $i }}</option>
                                            @endfor
                                        </select>
                                        <select name="tahun" id="tahun" required>
                                            @for ($y = now()->year - 2; $y <= now()->year + 2; $y++)
                                                <option value="{{ $y }}">{{ $y }}</option>
                                            @endfor
                                        </select>
                                    </div>

                                    <div class="col-md-8">
                                        <label class="form-label fw-bold small text-uppercase">Berapa Kali Dikerjakan?</label>
                                        <div class="input-group">
                                            <input type="number" class="form-control" name="frequency_count" min="1" max="5" value="1" required style="max-width: 80px;">
                                            <span class="input-group-text bg-light text-dark fw-bold">Kali</span>
                                            <select name="frequency_unit" class="form-select border-start-0" required>
                                                <option value="per_hari">Per Hari</option>
                                                <option value="per_x_hari">Per X Hari</option>
                                                <option value="per_minggu">Per Minggu</option>
                                                <option value="per_x_minggu">Per X Minggu</option>
                                                <option value="per_bulan">Per Bulan</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold small text-uppercase">Interval (X)</label>
                                        <input type="number" class="form-control" name="frequency_interval" min="1" placeholder="Cth: 2">
                                    </div>
                                </div>
                            </div>

                            <!-- Section: Options -->
                            <div class="mb-0">
                                <h6 class="fs-6 fw-bold text-gray-700 mb-4 border-bottom pb-2">Detail Tambahan</h6>
                                <div class="row g-5">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small text-uppercase">Default Shift (Jika 1x)</label>
                                        <div class="d-flex gap-4 mt-2">
                                            <div class="form-check form-check-custom form-check-solid">
                                                <input class="form-check-input" type="radio" value="Pagi" name="default_shift" checked id="shiftPagi"/>
                                                <label class="form-check-label" for="shiftPagi">Pagi</label>
                                            </div>
                                            <div class="form-check form-check-custom form-check-solid">
                                                <input class="form-check-input" type="radio" value="Siang" name="default_shift" id="shiftSiang"/>
                                                <label class="form-check-label" for="shiftSiang">Siang</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <label for="keterangan" class="form-label fw-bold small text-uppercase">Keterangan / Catatan</label>
                                        <textarea class="form-control" id="keterangan" name="keterangan" rows="2" placeholder="Catatan khusus jika ada..."></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer bg-light border-0">
                            <button type="button" class="btn btn-light-danger fw-bold" data-bs-dismiss="modal">Tutup</button>
                            <button type="submit" class="btn btn-primary fw-bold" id="btnSubmitTambah">
                                <span class="indicator-label">Simpan Jadwal</span>
                                <span class="indicator-progress d-none">
                                    Mohon tunggu... <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!-- Modal Edit Jadwal OB -->
        <div class="modal fade" id="editJadwalOB" tabindex="-1" aria-labelledby="editJadwalOBLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <form id="formEditJadwalOB" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="modal-header border-0 pb-0">
                            <h5 class="modal-title fs-2 fw-bolder text-dark" id="editJadwalOBLabel">Edit Jadwal Pekerjaan</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                        </div>
                        <div class="modal-body px-5 py-8">
                             <!-- Section: Primary Info -->
                             <div class="mb-8">
                                <h6 class="fs-6 fw-bold text-gray-700 mb-4 border-bottom pb-2">Informasi Pekerjaan</h6>
                                <div class="row g-5">
                                    <div class="col-md-6">
                                        <label for="edit_area" class="form-label fw-bold small text-uppercase">Area</label>
                                        <select class="form-select select2-taggable bg-light-gray" name="area" id="edit_area" required>
                                            <option value="" disabled>Pilih Area</option>
                                            @foreach ($areas as $area)
                                                <option value="{{ $area }}">{{ $area }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="edit_pekerjaan" class="form-label fw-bold small text-uppercase">Tugas / Pekerjaan</label>
                                        <input type="text" class="form-control" id="edit_pekerjaan" name="pekerjaan" required>
                                    </div>
                                </div>
                            </div>

                            <!-- Section: Scheduling -->
                            <div class="mb-8">
                                <h6 class="fs-6 fw-bold text-gray-700 mb-4 border-bottom pb-2">Penjadwalan & Frekuensi</h6>
                                <div class="row g-5">
                                    <div class="col-md-12">
                                        <label for="edit_start_date" class="form-label fw-bold small text-uppercase">Dimulai Tanggal</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0"><i class="fas fa-calendar-alt"></i></span>
                                            <input type="date" class="form-control border-start-0" id="edit_start_date" name="start_date" required>
                                        </div>
                                    </div>

                                    <!-- Hidden fields for Month/Year that sync automatically -->
                                    <div class="col-md-6 d-none">
                                        <select name="bulan" id="edit_bulan" required>
                                            @for ($i = 1; $i <= 12; $i++)
                                                <option value="{{ $i }}">{{ $i }}</option>
                                            @endfor
                                        </select>
                                        <select name="tahun" id="edit_tahun" required>
                                            @for ($y = now()->year - 5; $y <= now()->year + 2; $y++)
                                                <option value="{{ $y }}">{{ $y }}</option>
                                            @endfor
                                        </select>
                                    </div>

                                    <div class="col-md-8">
                                        <label class="form-label fw-bold small text-uppercase">Frekuensi</label>
                                        <div class="input-group">
                                            <input type="number" class="form-control" name="frequency_count" id="edit_frequency_count" min="1" max="5" required style="max-width: 80px;">
                                            <span class="input-group-text bg-light text-dark fw-bold">Kali</span>
                                            <select name="frequency_unit" class="form-select border-start-0" id="edit_frequency_unit" required>
                                                <option value="per_hari">Per Hari</option>
                                                <option value="per_x_hari">Per X Hari</option>
                                                <option value="per_minggu">Per Minggu</option>
                                                <option value="per_x_minggu">Per X Minggu</option>
                                                <option value="per_bulan">Per Bulan</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold small text-uppercase">Interval (X)</label>
                                        <input type="number" class="form-control" name="frequency_interval" id="edit_frequency_interval">
                                    </div>
                                </div>
                            </div>

                            <!-- Section: Options -->
                            <div class="mb-0">
                                <h6 class="fs-6 fw-bold text-gray-700 mb-4 border-bottom pb-2">Detail Tambahan</h6>
                                <div class="row g-5">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small text-uppercase">Default Shift</label>
                                        <select name="default_shift" class="form-select bg-light" id="edit_default_shift">
                                            <option value="Pagi">Pagi</option>
                                            <option value="Siang">Siang</option>
                                        </select>
                                    </div>
                                    <div class="col-md-12">
                                        <label for="edit_keterangan" class="form-label fw-bold small text-uppercase">Keterangan / Catatan</label>
                                        <textarea class="form-control" id="edit_keterangan" name="keterangan" rows="2"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer bg-light border-0">
                            <button type="button" class="btn btn-light-danger fw-bold" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary fw-bold">
                                <span class="indicator-label">Update Jadwal</span>
                                <span class="indicator-progress d-none">
                                    Menyimpan... <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
<style>
        /* Dynamic Datatable FixedColumns Freeze Panel styling */
        .DTFC_LeftWrapper table,
        .dtfc-fixed-left,
        th.dtfc-fixed-left,
        td.dtfc-fixed-left {
            background-color: #ffffff !important;
            opacity: 1 !important;
            z-index: 10 !important;
        }

        /* Ensure header of fixed columns is solid light gray */
        thead tr th.dtfc-fixed-left {
            background-color: #f1f5f9 !important;
            font-weight: 700 !important;
            border-bottom: 2px solid #cbd5e1 !important;
        }

        /* Make grid lines/borders inside the table thicker and clearer */
        #tablechecklist,
        #tablechecklist th,
        #tablechecklist td {
            border: 2px solid #94a3b8 !important; /* Thicker grid lines */
        }

        /* Add a very distinct thick border to separate frozen columns from scrollable columns */
        th.dtfc-fixed-left:nth-child(3),
        td.dtfc-fixed-left:nth-child(3) {
            border-right: 4px double #475569 !important; /* Thick dividing border */
        }

        /* Ensure alternate row coloring works on frozen columns as well */
        #tablechecklist tbody tr:nth-child(odd) td.dtfc-fixed-left {
            background-color: #f8fafc !important;
        }

        #tablechecklist tbody tr:nth-child(even) td.dtfc-fixed-left {
            background-color: #ffffff !important;
        }

        /* Fix hover state for frozen columns */
        #tablechecklist tbody tr:hover td.dtfc-fixed-left {
            background-color: #e2e8f0 !important;
        }

        .highlight-title {
            background-color: #f8f9fa;
            padding: 10px 20px;
            border-radius: 8px;
            display: inline-block;
            font-weight: bold;
            box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.1);
        }

        .hari-libur {
            background-color: #ffeaea !important;
            color: #e62e2e !important;
            font-weight: bold;
            position: relative;
        }

        .hari-libur::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(230, 46, 46, 0.05);
            pointer-events: none;
        }

        #tablechecklist tbody tr:hover {
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
            overflow: hidden;
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

        table.dataTable tbody tr.dtrg-group td {
            position: sticky;
            left: 0;
            z-index: 9 !important;
            text-align: left !important;
            padding-left: 12px !important;
            font-weight: bold;
            background-color: #e2e8f0 !important; /* Nice distinct solid light background */
            color: #0f172a !important;
            text-transform: uppercase;
            border-bottom: 2px solid #94a3b8 !important;
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
    </style>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.datatables.net/rowgroup/1.1.5/js/dataTables.rowGroup.min.js"></script>
    <script src="https://cdn.datatables.net/fixedcolumns/5.0.5/js/fixedColumns.dataTables.js"></script>
    <script src="https://cdn.datatables.net/fixedcolumns/5.0.5/js/dataTables.fixedColumns.js"></script>
    <script>
        $(document).ready(function () {
            function initChecklistDataTable() {
                if ($.fn.DataTable.isDataTable('#tablechecklist')) {
                    $('#tablechecklist').DataTable().destroy();
                }

                $('#tablechecklist').DataTable({
                    scrollX: true,
                    scrollCollapse: true,
                    paging: true,
                    searching: true,
                    scrollY: 300,
                    ordering: false,
                    rowGroup: {
                        dataSrc: 0
                    },
                    fixedColumns: {
                        left: 3
                    },
                    fixedHeader: {
                        header: true
                    },
                    rowCallback: function(row, data, index) {
                        if ($(row).hasClass('area-header-row') || $(row).hasClass('dtrg-group')) {
                            $(row).removeClass('odd even');
                        }
                    },
                    columnDefs: [
                        { targets: 0, visible: false, className: 'text-start' },
                        { targets: 1, width: "50px", className: "text-center nomor-column" },
                        { targets: 2, width: "300px", className: "text-start pekerjaan-column" },
                        { targets: 3, width: "150px", className: "periodic-column" },
                        { targets: -1, width: "250px", className: "text-start keterangan-column" },
                    ],
                    dom: '<"row mb-3 align-items-center"' +
                        '<"col-md-6 d-flex align-items-center gap-2"B>' +
                        '<"col-md-6 text-end"f>>' +
                        '<"row"<"col-sm-12"t>>' +
                        '<"row mt-3"' +
                        '<"col-sm-6"l><"col-sm-6 text-end"p>>',
                    buttons: [
                        @if(auth()->user()->hasPermission('checklist_create'))
                        {
                            text: '<i class="fas fa-plus"></i> Tambah Jadwal',
                            className: 'btn custom-button btn-sm me-1',
                            action: function () {
                                $('#addJadwalOB').modal('show');
                            }
                        },
                        @endif
                        @if(auth()->user()->hasPermission('checklist'))
                        {
                            text: '<i class="fas fa-file-excel"></i> Export Excel',
                            className: 'btn custom-button btn-sm me-1',
                            action: function () {
                                const bulan = $('#filterBulan').val();
                                const tahun = $('#filterTahun').val();
                                const url = `{{ route('checklist.exportexcel') }}?bulan=${bulan}&tahun=${tahun}`;
                                window.location.href = url;
                            }
                        }
                        @endif
                    ],
                    language: {
                        search: "_INPUT_",
                        searchPlaceholder: "Cari Pekerjaan",
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
            }

            // Initialize on page load
            initChecklistDataTable();

            // Function to reload table wrapper content via AJAX
            function reloadChecklistTable() {
                const url = new URL(window.location.href);
                url.searchParams.set('bulan', $('#filterBulan').val());
                url.searchParams.set('tahun', $('#filterTahun').val());

                $('#checklistTableContainer').load(url.toString() + ' #checklistTableContainer > *', function() {
                    // Re-initialize DataTable and other components in the reloaded DOM
                    initChecklistDataTable();
                });
            }

            $('.select2-taggable').select2({
                tags: true,
                placeholder: "Pilih atau ketik area baru",
                width: '100%',
                dropdownParent: $('#addJadwalOB')
            });

            $('#edit_area').select2({
                tags: true,
                placeholder: "Pilih atau ketik area",
                width: '100%',
                dropdownParent: $('#editJadwalOB')
            });

            // API Diagnostic Logs for Troubleshooting
            const apiStatus = @json($apiDiagnostics ?? []);
            console.group('%c 🔍 Milenia API Diagnostic Details', 'background: #222; color: #bada55; padding: 5px; font-weight: bold;');
            console.log('%c API Target URL: ', 'font-weight: bold;', apiStatus.url);
            console.log('%c Cache Status: ', 'font-weight: bold;', apiStatus.cached ? '✅ CACHED' : '❌ LIVE FETCH');
            console.log('%c Records Found: ', 'font-weight: bold;', apiStatus.count);
            console.log('%c Server Time: ', 'font-weight: bold;', apiStatus.server_time);
            
            if (apiStatus.count === 0) {
                console.warn('%c ⚠️ No holiday records found! Check if the API URL is reachable from the server.', 'color: orange; font-weight: bold;');
            }
            console.groupEnd();

            // Loading State Helper
            const setBtnLoading = (form, isLoading) => {
                const btn = form.find('button[type="submit"]');
                const label = btn.find('.indicator-label');
                const progress = btn.find('.indicator-progress');

                if (isLoading) {
                    btn.attr('disabled', true);
                    label.addClass('d-none');
                    progress.removeClass('d-none');
                } else {
                    btn.removeAttr('disabled');
                    label.removeClass('d-none');
                    progress.addClass('d-none');
                }
            };

            // Smart Sync Logic for Add Modal
            $('#start_date').on('change', function() {
                const dateVal = $(this).val();
                if (dateVal) {
                    const date = new Date(dateVal);
                    $('#bulan').val(date.getMonth() + 1);
                    $('#tahun').val(date.getFullYear());
                }
            });

            // Smart Sync Logic for Edit Modal
            $('#edit_start_date').on('change', function() {
                const dateVal = $(this).val();
                if (dateVal) {
                    const date = new Date(dateVal);
                    $('#edit_bulan').val(date.getMonth() + 1);
                    $('#edit_tahun').val(date.getFullYear());
                }
            });

            // Fix aria-hidden focus conflict
            $('.modal').on('show.bs.modal', function() {
                $('#kt_app_root').attr('aria-hidden', 'true');
            }).on('hide.bs.modal', function() {
                $('#kt_app_root').removeAttr('aria-hidden');
            });

            $('#formTambahJadwalOB').on('submit', function(e) {
                e.preventDefault();
                const form = $(this);
                setBtnLoading(form, true);

                $.post("{{ route('checklist.store') }}", form.serialize())
                    .done(function(response) {
                        setBtnLoading(form, false);
                        Swal.fire('Sukses!', 'Jadwal pembersihan berhasil ditambahkan!', 'success').then(() => {
                            $('#addJadwalOB').modal('hide');
                            reloadChecklistTable();
                        });
                    })
                    .fail(function(xhr) {
                        setBtnLoading(form, false);
                        let errors = xhr.responseJSON?.errors;
                        let message = 'Terjadi kesalahan.';

                        if (errors) {
                            message = Object.values(errors).join('<br>');
                        } else if (xhr.responseJSON?.message) {
                            message = xhr.responseJSON.message;
                        }

                        Swal.fire('Gagal!', message, 'error');
                    });
            });

            $('#filterBulan, #filterTahun').on('change', function () {
                const bulan = $('#filterBulan').val();
                const tahun = $('#filterTahun').val();

                const url = new URL(window.location.href);
                url.searchParams.set('bulan', bulan);
                url.searchParams.set('tahun', tahun);

                window.location.href = url.toString();
            });

            const editChecklistUrlTemplate = "{{ route('checklist.edit', ':id') }}";
            const updateChecklistUrlTemplate = "{{ route('checklist.update', ':id') }}";

            $(document).on('click', '.edit-checklist-btn', function () {
                const id = $(this).data('id');
                const editUrl = editChecklistUrlTemplate.replace(':id', id);

                $.get(editUrl, function (res) {
                    const c = res.checklist;

                    const form = $('#formEditJadwalOB');
                    form.attr('action', updateChecklistUrlTemplate.replace(':id', id));
                    form.find('[name=area]').val(c.area).trigger('change');
                    form.find('[name=pekerjaan]').val(c.pekerjaan);
                    form.find('[name=start_date]').val(c.start_date);
                    form.find('[name=frequency_count]').val(c.frequency_count);
                    form.find('[name=frequency_unit]').val(c.frequency_unit);
                    form.find('[name=frequency_interval]').val(c.frequency_interval);
                    form.find('[name=default_shift]').val(c.default_shift);
                    form.find('[name=bulan]').val(c.bulan);
                    form.find('[name=tahun]').val(c.tahun);
                    form.find('[name=keterangan]').val(c.keterangan);

                    $('#editJadwalOB').modal('show');
                });
            });

            $('#formEditJadwalOB').on('submit', function(e) {
                e.preventDefault();
                
                const form = $(this);
                const actionUrl = form.attr('action');
                const formData = form.serialize();
                setBtnLoading(form, true);

                $.ajax({
                    url: actionUrl,
                    type: 'POST',
                    data: formData,
                    success: function(response) {
                        setBtnLoading(form, false);
                        Swal.fire('Berhasil!', response.message, 'success').then(() => {
                            $('#editJadwalOB').modal('hide');
                            reloadChecklistTable();
                        });
                    },
                    error: function(xhr) {
                        setBtnLoading(form, false);
                        let errors = xhr.responseJSON?.errors;
                        let message = 'Terjadi kesalahan.';

                        if (errors) {
                            message = Object.values(errors).join('<br>');
                        }

                        Swal.fire('Gagal!', message, 'error');
                    }
                });
            });
        });
    </script>
</x-default-layout>
