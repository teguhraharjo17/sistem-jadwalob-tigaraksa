<x-default-layout>
    @section('title', 'Laporan Kerja Harian')

    <div class="container py-4">
        <h1 class="text-center mb-4">
            <span class="highlight-title">Laporan Kerja Harian</span>
        </h1>

        <div class="p-4 rounded shadow-sm bg-white">
            <form method="GET" action="{{ route('laporanharian.index') }}" id="filterForm" class="row g-3 mb-4">
                <div class="col-md-3">
                    <label for="filter_bulan" class="form-label">Bulan</label>
                    <select id="filter_bulan" name="bulan" class="form-select" onchange="document.getElementById('filterForm').submit()">
                        @for ($i = 1; $i <= 12; $i++)
                            <option value="{{ $i }}" {{ $now->month == $i ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create()->month($i)->translatedFormat('F') }}
                            </option>
                        @endfor
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="filter_tahun" class="form-label">Tahun</label>
                    <select id="filter_tahun" name="tahun" class="form-select" onchange="document.getElementById('filterForm').submit()">
                        @for ($i = now()->year; $i >= now()->year - 5; $i--)
                            <option value="{{ $i }}" {{ $now->year == $i ? 'selected' : '' }}>
                                {{ $i }}
                            </option>
                        @endfor
                    </select>
                </div>
            </form>
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
                        @forelse ($laporanList as $index => $laporan)
                            <tr>
                                <td class="text-center">{{ $index + 1 }}</td>
                                <td class="text-center">{{ \Carbon\Carbon::parse($laporan->tanggal)->format('d-m-Y') }}</td>
                                <td class="text-center">{{ $laporan->jam_mulai }}</td>
                                <td class="text-center">{{ $laporan->jam_selesai }}</td>
                                <td class="text-start">{{ $laporan->checklist->pekerjaan ?? '-' }}</td>
                                <td class="text-start">{{ $laporan->area ?? '-' }}</td>
                                <td class="text-center">
                                    @if ($laporan->bukti)
                                        <a href="{{ asset('storage/'.$laporan->bukti) }}" target="_blank">Lihat</a>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="text-start">{{ $laporan->hasil_pekerjaan ?? '-' }}</td>
                                <td class="text-start">{{ $laporan->mengetahui ?? '-' }}</td>
                                <td class="text-center">
                                    @if ($laporan->paraf)
                                        <img src="{{ asset('storage/'.$laporan->paraf) }}" alt="Paraf" width="40" height="40">
                                    @else
                                        -
                                    @endif
                                </td>
                                @if(auth()->user()->hasRole('Admin'))
                                    <td class="text-center">
                                        <button type="button" class="btn btn-xs btn-light border edit-btn"
                                            data-id="{{ $laporan->id }}"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editLaporanModal">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                    </td>
                                @endif
                            </tr>
                        @empty
                        @endforelse
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
                                <input type="text" class="form-control" id="area" name="area" required>
                            </div>

                            <div class="mb-3">
                                <label for="bukti" class="form-label">Upload Bukti</label>
                                <input type="file" class="form-control" id="bukti" name="bukti" accept="image/*,application/pdf">
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
                                <input type="text" class="form-control" id="edit_area" name="area" required>
                            </div>

                            <div class="mb-3">
                                <label for="edit_bukti" class="form-label">Upload Bukti</label>
                                <input type="file" class="form-control" id="edit_bukti" name="bukti" accept="image/*,application/pdf">
                                <div id="preview_bukti" class="mt-2"></div>
                            </div>

                            <div class="mb-3">
                                <label for="edit_hasil_pekerjaan" class="form-label">Hasil Pekerjaan</label>
                                <input type="text" class="form-control" id="edit_hasil_pekerjaan" name="hasil_pekerjaan">
                            </div>

                            <div class="mb-3">
                                <label for="edit_mengetahui" class="form-label">Mengetahui</label>
                                <input type="text" class="form-control" id="edit_mengetahui" name="mengetahui">
                            </div>

                            <div class="mb-3">
                                <label for="edit_paraf" class="form-label">Paraf</label>
                                <input type="file" class="form-control" id="edit_paraf" name="paraf">
                                <div id="preview_paraf" class="mt-2"></div>
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
    </style>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.datatables.net/rowgroup/1.1.5/js/dataTables.rowGroup.min.js"></script>

    <script>
        const editUrlTemplate = "{{ route('laporanharian.edit', ':id') }}";
        const updateUrlTemplate = "{{ route('laporanharian.update', ':id') }}";

        $(document).ready(function () {
            $('#tableLaporanHarian').DataTable({
                scrollX: true,
                paging: false,
                searching: true,
                ordering: false,
                dom: '<"row mb-3 align-items-center"' +
                    '<"col-md-6 d-flex align-items-center gap-2"B>' +
                    '<"col-md-6 text-end"f>>' +
                    '<"row"<"col-sm-12 table-responsive"t>>' +
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
                    }
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
                    $('#edit_area').val(laporan.area);
                    $('#edit_hasil_pekerjaan').val(laporan.hasil_pekerjaan ?? '');
                    $('#edit_mengetahui').val(laporan.mengetahui ?? '');

                    let pekerjaanOptions = '';
                    data.pekerjaanList.forEach(function (p) {
                        let selected = laporan.checklist_id == p.id ? 'selected' : '';
                        pekerjaanOptions += `<option value="${p.id}" ${selected}>${p.pekerjaan}</option>`;
                    });
                    $('#edit_item_pekerjaan').html(pekerjaanOptions);

                    if (laporan.paraf) {
                        $('#preview_paraf').html(`<img src="/storage/${laporan.paraf}" width="60">`);
                    } else {
                        $('#preview_paraf').html('');
                    }

                    if (laporan.bukti) {
                        const ekstensi = laporan.bukti.split('.').pop().toLowerCase();
                        const url = `/storage/${laporan.bukti}`;

                        if (['jpg', 'jpeg', 'png'].includes(ekstensi)) {
                            $('#preview_bukti').html(`<img src="${url}" width="60">`);
                        } else if (ekstensi === 'pdf') {
                            $('#preview_bukti').html(`<a href="${url}" target="_blank">Lihat Bukti (PDF)</a>`);
                        } else {
                            $('#preview_bukti').html(`<a href="${url}" target="_blank">Lihat Bukti</a>`);
                        }
                    } else {
                        $('#preview_bukti').html('');
                    }

                    $('#editLaporanModal').modal('show');
                });
            });

            $('#formEditLaporan').on('submit', function (e) {
                e.preventDefault();

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
        });
    </script>
</x-default-layout>
