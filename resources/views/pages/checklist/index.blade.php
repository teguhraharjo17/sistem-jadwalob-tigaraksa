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

        <div class="p-4 rounded shadow-sm bg-white">
            <div class="table-responsive">
                @php
                    $bulan = request('bulan', now()->month);
                    $tahun = request('tahun', now()->year);

                    $now = \Carbon\Carbon::createFromDate($tahun, $bulan, 1);
                    $daysInMonth = $now->daysInMonth;

                    $totalColspan = 1 + 1 + 1 + ($daysInMonth * 2) + 1;
                @endphp

                <table id="tablechecklist" class="table table-bordered table-striped">
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
                                        <button type="button" class="btn btn-xs btn-light border edit-checklist-btn"
                                                data-id="{{ $item->id }}">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        @empty
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <!-- Modal Tambah Jadwal OB -->
        <div class="modal fade" id="addJadwalOB" tabindex="-1" aria-labelledby="addJadwalOBLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <form id="formTambahJadwalOB" method="POST" action="{{ route('checklist.store') }}">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title" id="addJadwalOBLabel">Tambah Jadwal OB</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="area" class="form-label">Area</label>
                                <select class="form-select select2-taggable" name="area" id="area" required>
                                    <option value="" disabled selected>Pilih Area</option>
                                    @foreach ($areas as $area)
                                        <option value="{{ $area }}">{{ $area }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Anda dapat mengetik area baru jika tidak tersedia.</small>
                            </div>

                            <div class="mb-3">
                                <label for="pekerjaan" class="form-label">Pekerjaan</label>
                                <input type="text" class="form-control" id="pekerjaan" name="pekerjaan" required>
                            </div>

                            <div class="mb-3">
                                <label for="start_date" class="form-label">Dimulai Tanggal</label>
                                <input type="date" class="form-control" id="start_date" name="start_date" required>
                                <small class="text-muted">Checklist akan mulai dihitung sejak tanggal ini.</small>
                            </div>

                            <div class="mb-3">
                                <label for="frequency_count" class="form-label">Periode Pekerjaan</label>
                                <div class="row g-2">
                                    <div class="col-md-4">
                                        <input type="number" class="form-control" name="frequency_count" min="1" max="2" placeholder="Contoh: 1" required>
                                    </div>
                                    <div class="col-md-4">
                                        <select name="frequency_unit" class="form-select" required>
                                            <option value="" disabled selected>Pilih Satuan</option>
                                            <option value="per_hari">x per Hari</option>
                                            <option value="per_x_hari">x per X Hari</option>
                                            <option value="per_minggu">x per Minggu</option>
                                            <option value="per_bulan">x per Bulan</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <input type="number" class="form-control" name="frequency_interval" min="1" placeholder="Isi jika X Hari (cth: 2)">
                                    </div>
                                </div>
                                <small class="text-muted d-block mt-1">Contoh: 2x per hari = Isi <b>2</b> dan pilih <b>per Hari</b>.</small>
                            </div>

                            <div class="mb-3">
                                <label for="default_shift" class="form-label">Default Shift (Jika hanya 1x per Hari)</label>
                                <select name="default_shift" class="form-select">
                                    <option value="Pagi">Pagi</option>
                                    <option value="Siang">Siang</option>
                                </select>
                                <small class="text-muted">Digunakan saat frekuensi = 1x per hari untuk menentukan shift mana yang dijadwalkan.</small>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="bulan" class="form-label">Bulan</label>
                                    <select class="form-select" name="bulan" id="bulan" required>
                                        @for ($i = 1; $i <= 12; $i++)
                                            <option value="{{ $i }}">{{ \Carbon\Carbon::create()->month($i)->translatedFormat('F') }}</option>
                                        @endfor
                                    </select>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="tahun" class="form-label">Tahun</label>
                                    <select class="form-select" name="tahun" id="tahun" required>
                                        @for ($y = now()->year - 5; $y <= now()->year + 1; $y++)
                                            <option value="{{ $y }}" {{ $y == now()->year ? 'selected' : '' }}>{{ $y }}</option>
                                        @endfor
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="keterangan" class="form-label">Keterangan</label>
                                <textarea class="form-control" id="keterangan" name="keterangan" rows="3"></textarea>
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
        <!-- Modal Edit Jadwal OB -->
        <div class="modal fade" id="editJadwalOB" tabindex="-1" aria-labelledby="editJadwalOBLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <form id="formEditJadwalOB" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="modal-header">
                            <h5 class="modal-title" id="editJadwalOBLabel">Edit Jadwal OB</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                        </div>
                        <div class="modal-body">
                            {{-- Form isian sama seperti form tambah --}}
                            <div class="mb-3">
                                <label for="edit_area" class="form-label">Area</label>
                                <select class="form-select select2-taggable" name="area" id="edit_area" required>
                                    <option value="" disabled selected>Pilih Area</option>
                                    @foreach ($areas as $area)
                                        <option value="{{ $area }}">{{ $area }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="edit_pekerjaan" class="form-label">Pekerjaan</label>
                                <input type="text" class="form-control" id="edit_pekerjaan" name="pekerjaan" required>
                            </div>

                            <div class="mb-3">
                                <label for="edit_start_date" class="form-label">Dimulai Tanggal</label>
                                <input type="date" class="form-control" id="edit_start_date" name="start_date" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Periode Pekerjaan</label>
                                <div class="row g-2">
                                    <div class="col-md-4">
                                        <input type="number" class="form-control" name="frequency_count" id="edit_frequency_count" min="1" max="2" required>
                                    </div>
                                    <div class="col-md-4">
                                        <select name="frequency_unit" class="form-select" id="edit_frequency_unit" required>
                                            <option value="" disabled>Pilih Satuan</option>
                                            <option value="per_hari">x per Hari</option>
                                            <option value="per_x_hari">x per X Hari</option>
                                            <option value="per_minggu">x per Minggu</option>
                                            <option value="per_bulan">x per Bulan</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <input type="number" class="form-control" name="frequency_interval" id="edit_frequency_interval">
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="edit_default_shift" class="form-label">Default Shift</label>
                                <select name="default_shift" class="form-select" id="edit_default_shift">
                                    <option value="Pagi">Pagi</option>
                                    <option value="Siang">Siang</option>
                                </select>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="edit_bulan" class="form-label">Bulan</label>
                                    <select class="form-select" name="bulan" id="edit_bulan" required>
                                        @for ($i = 1; $i <= 12; $i++)
                                            <option value="{{ $i }}">{{ \Carbon\Carbon::create()->month($i)->translatedFormat('F') }}</option>
                                        @endfor
                                    </select>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="edit_tahun" class="form-label">Tahun</label>
                                    <select class="form-select" name="tahun" id="edit_tahun" required>
                                        @for ($y = now()->year - 5; $y <= now()->year + 1; $y++)
                                            <option value="{{ $y }}" {{ $y == now()->year ? 'selected' : '' }}>{{ $y }}</option>
                                        @endfor
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="edit_keterangan" class="form-label">Keterangan</label>
                                <textarea class="form-control" id="edit_keterangan" name="keterangan" rows="3"></textarea>
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
        
        .hari-libur {
            background-color: #ffe5e5 !important;
            color: #d10000 !important;
            font-weight: bold;
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
    </style>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.datatables.net/rowgroup/1.1.5/js/dataTables.rowGroup.min.js"></script>
    <script>
        $(document).ready(function () {
            $('#tablechecklist').DataTable({
                scrollX: true,
                paging: false,
                searching: true,
                ordering: false,
                rowGroup: {
                    dataSrc: 0
                },
                rowCallback: function(row, data, index) {
                    if ($(row).hasClass('area-header-row')) {
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
                    '<"row"<"col-sm-12 table-responsive"t>>' +
                    '<"row mt-3"' +
                    '<"col-sm-6"l><"col-sm-6 text-end"p>>',
                buttons: [
                    @if(auth()->user()->hasRole('Admin'))
                    {
                        text: '<i class="fas fa-plus"></i> Tambah Jadwal',
                        className: 'btn custom-button btn-sm me-1',
                        action: function () {
                            $('#addJadwalOB').modal('show');
                        }
                    },
                    @endif
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

            $('#formTambahJadwalOB').on('submit', function(e) {
                e.preventDefault();

                const formData = $(this).serialize();

                $.post("{{ route('checklist.store') }}", formData)
                    .done(function(response) {
                        Swal.fire('Sukses!', response.message, 'success');
                        $('#addJadwalOB').modal('hide');
                        setTimeout(() => window.location.reload(), 1000);
                    })
                    .fail(function(xhr) {
                        let errors = xhr.responseJSON?.errors;
                        let message = 'Terjadi kesalahan.';

                        if (errors) {
                            message = Object.values(errors).join('<br>');
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

                $.ajax({
                    url: actionUrl,
                    type: 'POST',
                    data: formData,
                    success: function(response) {
                        Swal.fire('Berhasil!', response.message, 'success');
                        $('#editJadwalOB').modal('hide');
                        setTimeout(() => window.location.reload(), 1000);
                    },
                    error: function(xhr) {
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
