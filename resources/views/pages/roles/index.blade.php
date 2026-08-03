<x-default-layout>
    @section('title', 'Master Role')

    <div class="container py-4">
        <!-- Premium Header Banner -->
        <div class="card border-0 shadow-sm mb-5" style="border-radius: 12px; background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);">
            <div class="card-body p-4 d-flex justify-content-between align-items-center flex-column flex-md-row">
                <div class="mb-3 mb-md-0">
                    <h1 class="text-gray-900 fw-bolder fs-2 mb-1"><i class="fas fa-tags text-primary me-2"></i> Master Data Role</h1>
                    <p class="text-gray-600 fs-6 mb-0">Atur peran pengguna dan konfigurasi hak akses menu sistem secara dinamis.</p>
                </div>
                <div>
                    <button type="button" class="btn btn-primary fw-bold btn-sm shadow-sm hover-scale" data-bs-toggle="modal" data-bs-target="#addRoleModal">
                        <i class="fas fa-plus me-2"></i> Tambah Role Baru
                    </button>
                </div>
            </div>
        </div>

        <!-- Info & Guide Alert (Balanced UX) -->
        <div class="alert alert-dismissible bg-light-primary d-flex align-items-center p-4 mb-5 border-start border-4 border-primary rounded-3 shadow-none">
            <i class="fas fa-info-circle text-primary fs-3 me-3"></i>
            <div class="pe-10 fs-7 text-gray-700">
                <strong>Panduan:</strong> Centang menu pada role untuk memunculkannya di sidebar pengguna dan mengizinkan akses ke rute URL halaman tersebut.
            </div>
            <button type="button" class="position-absolute position-sm-relative m-2 m-sm-0 top-0 end-0 btn btn-icon ms-sm-auto" data-bs-dismiss="alert">
                <i class="fas fa-times text-primary fs-4"></i>
            </button>
        </div>

        <!-- Beautified Content Card -->
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-5">
                <div class="table-responsive">
                    <table id="tableRoles" class="table table-row-dashed table-hover align-middle gs-0 gy-3 mb-0">
                        <thead>
                            <tr class="fw-bold text-gray-700 fs-7 text-uppercase border-bottom bg-light-transparent">
                                <th class="text-center w-80px">No</th>
                                <th class="min-w-150px">Nama Role</th>
                                <th class="text-center min-w-200px">Hak Akses Menu</th>
                                <th class="text-center w-120px">Total Pengguna</th>
                                <th class="text-center w-180px">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($roles as $role)
                                <tr class="align-middle" data-id="{{ $role->id }}">
                                    <td class="text-center fw-semibold text-gray-600 fs-7">{{ $loop->iteration }}</td>
                                    <td>
                                        <span class="badge badge-light-{{ $role->id == 1 ? 'danger' : ($role->id == 2 ? 'warning' : 'primary') }} fs-6 fw-bold">
                                            {{ $role->name }}
                                        </span>
                                        @if(in_array($role->id, [1, 2, 3]))
                                            <span class="text-muted fs-8 italic ms-2" title="Role bawaan tidak dapat dihapus"><i class="fas fa-lock fs-9 text-muted me-1"></i> Sistem</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($role->permissions && count($role->permissions) > 0)
                                            <div class="d-flex justify-content-center flex-wrap gap-1">
                                                @foreach($role->permissions as $perm)
                                                    @php
                                                        $label = [
                                                            'dashboard' => 'Dashboard',
                                                            'checklist' => 'Checklist',
                                                            'laporanharian' => 'Laporan Harian',
                                                            'master_data' => 'Master Data'
                                                        ][$perm] ?? $perm;
                                                        $color = [
                                                            'dashboard' => 'success',
                                                            'checklist' => 'info',
                                                            'laporanharian' => 'primary',
                                                            'master_data' => 'danger'
                                                        ][$perm] ?? 'secondary';
                                                    @endphp
                                                    <span class="badge badge-light-{{ $color }} fw-bold px-3 py-1 fs-8 border-{{ $color }} border-dotted border">{{ $label }}</span>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-muted fs-8 italic">Tidak ada akses</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-circle badge-secondary fw-bold">{{ $role->users_count }}</span>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-2">
                                            <button type="button" class="btn btn-sm btn-light-warning hover-scale px-3 edit-role-btnme-1" 
                                                    data-id="{{ $role->id }}">
                                                <i class="fas fa-edit me-1"></i> Edit
                                            </button>
                                            @if(!in_array($role->id, [1, 2, 3]))
                                                <button type="button" class="btn btn-sm btn-light-danger hover-scale px-3 delete-role-btn" 
                                                        data-id="{{ $role->id }}" data-name="{{ $role->name }}">
                                                    <i class="fas fa-trash me-1"></i> Hapus
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Add Role -->
    <div class="modal fade" id="addRoleModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border">
                <div class="modal-header bg-light-primary p-4 border-bottom">
                    <h5 class="modal-title fw-bold text-primary"><i class="fas fa-plus-circle me-2"></i> Tambah Role Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formAddRole">
                    @csrf
                    <div class="modal-body p-5">
                        <div class="mb-4">
                            <label for="name" class="form-label fw-bold text-gray-800">Nama Role <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-solid" id="name" name="name" required placeholder="Contoh: Supervisor, Cleaner">
                        </div>

                        <!-- Checkbox Premium Category Groups -->
                        <div class="mb-2">
                            <label class="form-label fw-bold text-gray-800 mb-3"><i class="fas fa-shield-alt text-primary me-2"></i> Konfigurasi Hak Akses Menu</label>
                            
                            <!-- Category: Dashboard -->
                            <div class="mb-4">
                                <div class="text-muted fw-bold text-uppercase fs-9 border-bottom pb-1 mb-2">Dashboard</div>
                                <div class="row">
                                    <div class="col-12">
                                        <label class="permission-card d-flex align-items-center p-3 border rounded cursor-pointer bg-light-transparent hover-shadow">
                                            <div class="form-check form-check-custom form-check-solid me-3">
                                                <input class="form-check-input" type="checkbox" name="permissions[]" value="dashboard" id="perm_dashboard" checked />
                                            </div>
                                            <div class="d-flex flex-column">
                                                <span class="fw-bold text-gray-900 fs-6">Dashboard</span>
                                                <span class="text-muted fs-8">Mengakses grafik data & ringkasan pekerjaan</span>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Category: Cleaning Services -->
                            <div class="mb-4">
                                <div class="text-muted fw-bold text-uppercase fs-9 border-bottom pb-1 mb-2">Cleaning Services</div>
                                <div class="row g-3">
                                    <!-- Checklist Card -->
                                    <div class="col-md-6">
                                        <div class="border rounded p-4 bg-light-transparent h-100 hover-shadow">
                                            <label class="permission-card d-flex align-items-center mb-3 cursor-pointer">
                                                <div class="form-check form-check-custom form-check-solid me-3">
                                                    <input class="form-check-input" type="checkbox" name="permissions[]" value="checklist" id="perm_checklist" />
                                                </div>
                                                <div class="d-flex flex-column">
                                                    <span class="fw-bold text-gray-900 fs-6">Checklist Area Pembersihan</span>
                                                    <span class="text-muted fs-8">Mengakses menu checklist kebersihan area</span>
                                                </div>
                                            </label>
                                            <div class="ms-8 border-start ps-4">
                                                <label class="d-flex align-items-center mb-2 cursor-pointer">
                                                    <div class="form-check form-check-sm form-check-custom form-check-solid me-3">
                                                        <input class="form-check-input" type="checkbox" name="permissions[]" value="checklist_create" id="perm_checklist_create" />
                                                    </div>
                                                    <div class="d-flex flex-column">
                                                        <span class="fw-semibold text-gray-800 fs-7">Tambah Jadwal Checklist</span>
                                                    </div>
                                                </label>
                                                <label class="d-flex align-items-center cursor-pointer">
                                                    <div class="form-check form-check-sm form-check-custom form-check-solid me-3">
                                                        <input class="form-check-input" type="checkbox" name="permissions[]" value="checklist_edit" id="perm_checklist_edit" />
                                                    </div>
                                                    <div class="d-flex flex-column">
                                                        <span class="fw-semibold text-gray-800 fs-7">Edit Jadwal Checklist</span>
                                                    </div>
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Laporan Harian Card -->
                                    <div class="col-md-6">
                                        <div class="border rounded p-4 bg-light-transparent h-100 hover-shadow">
                                            <label class="permission-card d-flex align-items-center mb-3 cursor-pointer">
                                                <div class="form-check form-check-custom form-check-solid me-3">
                                                    <input class="form-check-input" type="checkbox" name="permissions[]" value="laporanharian" id="perm_laporanharian" />
                                                </div>
                                                <div class="d-flex flex-column">
                                                    <span class="fw-bold text-gray-900 fs-6">Laporan Kerja Harian</span>
                                                    <span class="text-muted fs-8">Mengakses menu laporan harian kerja</span>
                                                </div>
                                            </label>
                                            <div class="ms-8 border-start ps-4">
                                                <label class="d-flex align-items-center mb-2 cursor-pointer">
                                                    <div class="form-check form-check-sm form-check-custom form-check-solid me-3">
                                                        <input class="form-check-input" type="checkbox" name="permissions[]" value="laporanharian_create" id="perm_laporanharian_create" />
                                                    </div>
                                                    <div class="d-flex flex-column">
                                                        <span class="fw-semibold text-gray-800 fs-7">Tambah Laporan Kerja</span>
                                                    </div>
                                                </label>
                                                <label class="d-flex align-items-center mb-2 cursor-pointer">
                                                    <div class="form-check form-check-sm form-check-custom form-check-solid me-3">
                                                        <input class="form-check-input" type="checkbox" name="permissions[]" value="laporanharian_edit" id="perm_laporanharian_edit" />
                                                    </div>
                                                    <div class="d-flex flex-column">
                                                        <span class="fw-semibold text-gray-800 fs-7">Edit/Hapus Laporan Kerja</span>
                                                    </div>
                                                </label>
                                                <label class="d-flex align-items-center cursor-pointer">
                                                    <div class="form-check form-check-sm form-check-custom form-check-solid me-3">
                                                        <input class="form-check-input" type="checkbox" name="permissions[]" value="laporanharian_approve" id="perm_laporanharian_approve" />
                                                    </div>
                                                    <div class="d-flex flex-column">
                                                        <span class="fw-semibold text-gray-800 fs-7">Approve/Paraf Laporan Kerja</span>
                                                    </div>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Category: Master Data -->
                            <div class="mb-2">
                                <div class="text-danger fw-bold text-uppercase fs-9 border-bottom pb-1 mb-2">Master Data</div>
                                <div class="row">
                                    <!-- Master Data Card -->
                                    <div class="col-12">
                                        <label class="permission-card d-flex align-items-center p-3 border border-danger-subtle rounded cursor-pointer bg-light-transparent hover-shadow">
                                            <div class="form-check form-check-custom form-check-solid form-check-danger me-3">
                                                <input class="form-check-input" type="checkbox" name="permissions[]" value="master_data" id="perm_master_data" />
                                            </div>
                                            <div class="d-flex flex-column">
                                                <span class="fw-bold text-danger fs-6">Master Data (User & Role)</span>
                                                <span class="text-muted fs-8">Mengelola database Pengguna & Hak Akses Peran</span>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-top p-4 bg-light">
                        <button type="button" class="btn btn-light btn-sm fw-bold" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary btn-sm fw-bold"><i class="fas fa-save me-1"></i> Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal: Edit Role -->
    <div class="modal fade" id="editRoleModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border">
                <div class="modal-header bg-light-warning p-4 border-bottom">
                    <h5 class="modal-title fw-bold text-warning-dark"><i class="fas fa-edit me-2"></i> Edit Role & Hak Akses</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formEditRole">
                    @csrf
                    @method('PUT')
                    <div class="modal-body p-5">
                        <div class="mb-4">
                            <label for="edit_name" class="form-label fw-bold text-gray-800">Nama Role</label>
                            <input type="text" class="form-control form-control-solid" id="edit_name" name="name" required>
                            <div class="form-text text-muted d-none fs-8 mt-1" id="nameLockedAlert">
                                <i class="fas fa-lock text-warning me-1"></i> Peran bawaan sistem. Nama tidak dapat diubah, namun hak akses dapat diedit.
                            </div>
                        </div>

                        <!-- Checkbox Premium Category Groups -->
                        <div class="mb-2">
                            <label class="form-label fw-bold text-gray-800 mb-3"><i class="fas fa-shield-alt text-warning-dark me-2"></i> Atur Hak Akses Menu</label>
                            
                            <!-- Category: Dashboard -->
                            <div class="mb-4">
                                <div class="text-muted fw-bold text-uppercase fs-9 border-bottom pb-1 mb-2">Dashboard</div>
                                <div class="row">
                                    <div class="col-12">
                                        <label class="permission-card d-flex align-items-center p-3 border rounded cursor-pointer bg-light-transparent hover-shadow">
                                            <div class="form-check form-check-custom form-check-solid me-3">
                                                <input class="form-check-input" type="checkbox" name="permissions[]" value="dashboard" id="edit_perm_dashboard" />
                                            </div>
                                            <div class="d-flex flex-column">
                                                <span class="fw-bold text-gray-900 fs-6">Dashboard</span>
                                                <span class="text-muted fs-8">Mengakses grafik data & ringkasan pekerjaan</span>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Category: Cleaning Services -->
                            <div class="mb-4">
                                <div class="text-muted fw-bold text-uppercase fs-9 border-bottom pb-1 mb-2">Cleaning Services</div>
                                <div class="row g-3">
                                    <!-- Checklist Card -->
                                    <div class="col-md-6">
                                        <div class="border rounded p-4 bg-light-transparent h-100 hover-shadow">
                                            <label class="permission-card d-flex align-items-center mb-3 cursor-pointer">
                                                <div class="form-check form-check-custom form-check-solid me-3">
                                                    <input class="form-check-input" type="checkbox" name="permissions[]" value="checklist" id="edit_perm_checklist" />
                                                </div>
                                                <div class="d-flex flex-column">
                                                    <span class="fw-bold text-gray-900 fs-6">Checklist Area Pembersihan</span>
                                                    <span class="text-muted fs-8">Mengakses menu checklist kebersihan area</span>
                                                </div>
                                            </label>
                                            <div class="ms-8 border-start ps-4">
                                                <label class="d-flex align-items-center mb-2 cursor-pointer">
                                                    <div class="form-check form-check-sm form-check-custom form-check-solid me-3">
                                                        <input class="form-check-input" type="checkbox" name="permissions[]" value="checklist_create" id="edit_perm_checklist_create" />
                                                    </div>
                                                    <div class="d-flex flex-column">
                                                        <span class="fw-semibold text-gray-800 fs-7">Tambah Jadwal Checklist</span>
                                                    </div>
                                                </label>
                                                <label class="d-flex align-items-center cursor-pointer">
                                                    <div class="form-check form-check-sm form-check-custom form-check-solid me-3">
                                                        <input class="form-check-input" type="checkbox" name="permissions[]" value="checklist_edit" id="edit_perm_checklist_edit" />
                                                    </div>
                                                    <div class="d-flex flex-column">
                                                        <span class="fw-semibold text-gray-800 fs-7">Edit Jadwal Checklist</span>
                                                    </div>
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Laporan Harian Card -->
                                    <div class="col-md-6">
                                        <div class="border rounded p-4 bg-light-transparent h-100 hover-shadow">
                                            <label class="permission-card d-flex align-items-center mb-3 cursor-pointer">
                                                <div class="form-check form-check-custom form-check-solid me-3">
                                                    <input class="form-check-input" type="checkbox" name="permissions[]" value="laporanharian" id="edit_perm_laporanharian" />
                                                </div>
                                                <div class="d-flex flex-column">
                                                    <span class="fw-bold text-gray-900 fs-6">Laporan Kerja Harian</span>
                                                    <span class="text-muted fs-8">Mengakses menu laporan harian kerja</span>
                                                </div>
                                            </label>
                                            <div class="ms-8 border-start ps-4">
                                                <label class="d-flex align-items-center mb-2 cursor-pointer">
                                                    <div class="form-check form-check-sm form-check-custom form-check-solid me-3">
                                                        <input class="form-check-input" type="checkbox" name="permissions[]" value="laporanharian_create" id="edit_perm_laporanharian_create" />
                                                    </div>
                                                    <div class="d-flex flex-column">
                                                        <span class="fw-semibold text-gray-800 fs-7">Tambah Laporan Kerja</span>
                                                    </div>
                                                </label>
                                                <label class="d-flex align-items-center mb-2 cursor-pointer">
                                                    <div class="form-check form-check-sm form-check-custom form-check-solid me-3">
                                                        <input class="form-check-input" type="checkbox" name="permissions[]" value="laporanharian_edit" id="edit_perm_laporanharian_edit" />
                                                    </div>
                                                    <div class="d-flex flex-column">
                                                        <span class="fw-semibold text-gray-800 fs-7">Edit/Hapus Laporan Kerja</span>
                                                    </div>
                                                </label>
                                                <label class="d-flex align-items-center cursor-pointer">
                                                    <div class="form-check form-check-sm form-check-custom form-check-solid me-3">
                                                        <input class="form-check-input" type="checkbox" name="permissions[]" value="laporanharian_approve" id="edit_perm_laporanharian_approve" />
                                                    </div>
                                                    <div class="d-flex flex-column">
                                                        <span class="fw-semibold text-gray-800 fs-7">Approve/Paraf Laporan Kerja</span>
                                                    </div>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Category: Master Data -->
                            <div class="mb-2">
                                <div class="text-danger fw-bold text-uppercase fs-9 border-bottom pb-1 mb-2">Master Data</div>
                                <div class="row">
                                    <!-- Master Data Card -->
                                    <div class="col-12">
                                        <label class="permission-card d-flex align-items-center p-3 border border-danger-subtle rounded cursor-pointer bg-light-transparent hover-shadow">
                                            <div class="form-check form-check-custom form-check-solid form-check-danger me-3">
                                                <input class="form-check-input" type="checkbox" name="permissions[]" value="master_data" id="edit_perm_master_data" />
                                            </div>
                                            <div class="d-flex flex-column">
                                                <span class="fw-bold text-danger fs-6">Master Data (User & Role)</span>
                                                <span class="text-muted fs-8">Mengelola database Pengguna & Hak Akses Peran</span>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-top p-4 bg-light">
                        <button type="button" class="btn btn-light btn-sm fw-bold" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-warning btn-sm fw-bold text-dark"><i class="fas fa-save me-1"></i> Perbarui</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        .text-warning-dark {
            color: #8a6d3b !important;
        }
        .bg-light-warning {
            background-color: #fcf8e3 !important;
        }
        .bg-light-transparent {
            background-color: #fafafa;
        }
        .permission-card {
            transition: all 0.2s ease;
        }
        .permission-card:hover {
            border-color: #0d6efd !important;
            background-color: #f8fafd !important;
            transform: translateY(-1px);
        }
        .permission-card.active {
            border-color: #0d6efd !important;
            background-color: #f0f7ff !important;
        }
        .form-check-input:checked {
            background-color: #0d6efd;
            border-color: #0d6efd;
        }
        .form-check-danger .form-check-input:checked {
            background-color: #dc3545;
            border-color: #dc3545;
        }
        .modal-content {
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
            border: none;
            overflow: hidden;
        }
        .table-hover tbody tr:hover {
            background-color: #fcfdfe !important;
        }
    </style>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            $('#tableRoles').DataTable({
                ordering: true,
                searching: true,
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Cari Role",
                    lengthMenu: "Tampilkan _MENU_ entri",
                    info: "Menampilkan _START_ hingga _END_ dari _TOTAL_ entri",
                    paginate: {
                        previous: '<i class="fas fa-chevron-left"></i>',
                        next: '<i class="fas fa-chevron-right"></i>',
                    }
                }
            });

            // Toggle active visual class on permission card clicks
            $(document).on('change', '.form-check-input', function() {
                const card = $(this).closest('.permission-card');
                if (this.checked) {
                    card.addClass('active');
                } else {
                    card.removeClass('active');
                }
            });

            // Sync visual active class on load for add/edit cards
            const syncCardClasses = (form) => {
                form.find('.form-check-input').each(function() {
                    const card = $(this).closest('.permission-card');
                    if (this.checked) {
                        card.addClass('active');
                    } else {
                        card.removeClass('active');
                    }
                });
            };

            // Open Add Role Modal
            $('[data-bs-target="#addRoleModal"]').on('click', function() {
                const form = $('#formAddRole');
                form[0].reset();
                syncCardClasses(form);
            });

            // Helper function to build dynamic role row HTML
            function getRoleRowHtml(role) {
                let badgesHtml = '';
                if (role.permissions && role.permissions.length > 0) {
                    role.permissions.forEach(function(perm) {
                        let label = {
                            'dashboard': 'Dashboard',
                            'checklist': 'Checklist',
                            'laporanharian': 'Laporan Harian',
                            'master_data': 'Master Data'
                        }[perm] || perm;
                        let color = {
                            'dashboard': 'success',
                            'checklist': 'info',
                            'laporanharian': 'primary',
                            'master_data': 'danger'
                        }[perm] || 'secondary';
                        badgesHtml += `<span class="badge badge-light-${color} fw-bold px-3 py-1 fs-8 border-${color} border-dotted border">${label}</span> `;
                    });
                } else {
                    badgesHtml = '<span class="text-muted fs-8 italic">Tidak ada akses</span>';
                }

                const isSystemRole = [1, 2, 3].includes(parseInt(role.id));
                const systemLabel = isSystemRole ? ' <span class="text-muted fs-8 italic ms-2" title="Role bawaan tidak dapat dihapus"><i class="fas fa-lock fs-9 text-muted me-1"></i> Sistem</span>' : '';
                const deleteBtnHtml = isSystemRole ? '' : `
                    <button type="button" class="btn btn-sm btn-light-danger hover-scale px-3 delete-role-btn" 
                            data-id="${role.id}" data-name="${role.name}">
                        <i class="fas fa-trash me-1"></i> Hapus
                    </button>
                `;

                return `
                    <tr class="align-middle" data-id="${role.id}">
                        <td class="text-center fw-semibold text-gray-600 fs-7">-</td>
                        <td>
                            <span class="badge badge-light-${role.id == 1 ? 'danger' : (role.id == 2 ? 'warning' : 'primary')} fs-6 fw-bold">
                                ${role.name}
                            </span>
                            ${systemLabel}
                        </td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center flex-wrap gap-1">
                                ${badgesHtml}
                            </div>
                        </td>
                        <td class="text-center">
                            <span class="badge badge-circle badge-secondary fw-bold">${role.users_count || 0}</span>
                        </td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-2">
                                <button type="button" class="btn btn-sm btn-light-warning hover-scale px-3 edit-role-btnme-1" 
                                        data-id="${role.id}">
                                    <i class="fas fa-edit me-1"></i> Edit
                                </button>
                                ${deleteBtnHtml}
                            </div>
                        </td>
                    </tr>
                `;
            }

            // Function to update table sequential index
            function renumberTable() {
                const table = $('#tableRoles').DataTable();
                let index = 1;
                table.rows().every(function() {
                    const rowNode = this.node();
                    $(rowNode).find('td:first-child').text(index++);
                });
            }

            // Function to reload sidebar menu dynamically
            function reloadSidebarMenu() {
                $.get("{{ route('superadmin.sidebar-menu') }}", function(html) {
                    const newWrapper = $(html).find('#kt_app_sidebar_menu_wrapper');
                    if (newWrapper.length > 0) {
                        $('#kt_app_sidebar_menu_wrapper').replaceWith(newWrapper);
                    } else {
                        const newMenu = $(html).filter('.app-sidebar-menu');
                        if (newMenu.length > 0) {
                            $('.app-sidebar-menu').replaceWith(newMenu);
                        }
                    }
                    if (typeof KTMenu !== 'undefined' && typeof KTMenu.createInstances === 'function') {
                        KTMenu.createInstances();
                    }
                });
            }

            // Add Role
            $('#formAddRole').on('submit', function(e) {
                e.preventDefault();
                const form = $(this);
                const btn = form.find('button[type="submit"]');
                btn.attr('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');

                $.post("{{ route('superadmin.roles.store') }}", form.serialize())
                    .done(function(response) {
                        btn.removeAttr('disabled').html('<i class="fas fa-save me-1"></i> Simpan');
                        Swal.fire('Sukses!', response.message, 'success').then(() => {
                            $('#addRoleModal').modal('hide');
                            
                            // Insert dynamically using Datatable API
                            const table = $('#tableRoles').DataTable();
                            const newRowHtml = getRoleRowHtml(response.role);
                            const rowNode = table.row.add($(newRowHtml)).draw(false).node();
                            
                            // Re-apply sequential numbers
                            renumberTable();
                            reloadSidebarMenu();
                        });
                    })
                    .fail(function(xhr) {
                        btn.removeAttr('disabled').html('<i class="fas fa-save me-1"></i> Simpan');
                        let errors = xhr.responseJSON?.errors;
                        let message = 'Terjadi kesalahan.';
                        if (errors) {
                            message = Object.values(errors).join('<br>');
                        }
                        Swal.fire('Gagal!', message, 'error');
                    });
            });

            // Edit Role Show Modal
            const editRoleUrlTemplate = "{{ route('superadmin.roles.edit', ':id') }}";
            const updateRoleUrlTemplate = "{{ route('superadmin.roles.update', ':id') }}";

            $(document).on('click', '.edit-role-btnme-1', function() {
                const id = $(this).data('id');
                const editUrl = editRoleUrlTemplate.replace(':id', id);

                $.get(editUrl, function(res) {
                    const r = res.role;
                    const form = $('#formEditRole');
                    form.attr('action', updateRoleUrlTemplate.replace(':id', id));
                    form.find('[name=name]').val(r.name);
                    
                    // Prevent editing default role names
                    if (id == 1 || id == 2 || id == 3) {
                        form.find('[name=name]').attr('readonly', true);
                        $('#nameLockedAlert').removeClass('d-none');
                    } else {
                        form.find('[name=name]').removeAttr('readonly');
                        $('#nameLockedAlert').addClass('d-none');
                    }

                    // Reset and select checkboxes
                    form.find('[name="permissions[]"]').prop('checked', false);
                    if (r.permissions && Array.isArray(r.permissions)) {
                        r.permissions.forEach(function(perm) {
                            form.find('[name="permissions[]"][value="' + perm + '"]').prop('checked', true);
                        });
                    }

                    syncCardClasses(form);
                    $('#editRoleModal').modal('show');
                }).fail(function() {
                    Swal.fire('Gagal!', 'Tidak dapat memuat detail data role.', 'error');
                });
            });

            // Update Role Submit
            $('#formEditRole').on('submit', function(e) {
                e.preventDefault();
                const form = $(this);
                const actionUrl = form.attr('action');
                const btn = form.find('button[type="submit"]');
                btn.attr('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');

                $.ajax({
                    url: actionUrl,
                    type: 'POST',
                    data: form.serialize(),
                    success: function(response) {
                        btn.removeAttr('disabled').html('<i class="fas fa-save me-1"></i> Perbarui');
                        Swal.fire('Sukses!', response.message, 'success').then(() => {
                            $('#editRoleModal').modal('hide');
                            
                            // Update row dynamically using Datatable API
                            const table = $('#tableRoles').DataTable();
                            const existingRow = $(`tr[data-id="${response.role.id}"]`);
                            if (existingRow.length > 0) {
                                const newRowHtml = getRoleRowHtml(response.role);
                                existingRow.html($(newRowHtml).html());
                                table.row(existingRow).invalidate().draw(false);
                                renumberTable();
                                reloadSidebarMenu();
                            }
                        });
                    },
                    error: function(xhr) {
                        btn.removeAttr('disabled').html('<i class="fas fa-save me-1"></i> Perbarui');
                        let errors = xhr.responseJSON?.errors;
                        let message = 'Terjadi kesalahan.';
                        if (errors) {
                            message = Object.values(errors).join('<br>');
                        } else if (xhr.responseJSON?.message) {
                            message = xhr.responseJSON.message;
                        }
                        Swal.fire('Gagal!', message, 'error');
                    }
                });
            });

            // Automatically select/deselect sub-permissions when parent is clicked
            $(document).on('change', 'input[value="checklist"]', function() {
                const parent = $(this);
                const container = parent.closest('div.border');
                container.find('input[value^="checklist_"]').prop('checked', parent.is(':checked'));
            });
            $(document).on('change', 'input[value="laporanharian"]', function() {
                const parent = $(this);
                const container = parent.closest('div.border');
                container.find('input[value^="laporanharian_"]').prop('checked', parent.is(':checked'));
            });
            
            // Conversely, if a sub-permission is checked, make sure parent is checked
            $(document).on('change', 'input[value^="checklist_"]', function() {
                if ($(this).is(':checked')) {
                    $(this).closest('div.border').find('input[value="checklist"]').prop('checked', true);
                }
            });
            $(document).on('change', 'input[value^="laporanharian_"]', function() {
                if ($(this).is(':checked')) {
                    $(this).closest('div.border').find('input[value="laporanharian"]').prop('checked', true);
                }
            });

            // Delete Role
            const deleteRoleUrlTemplate = "{{ route('superadmin.roles.destroy', ':id') }}";

            $(document).on('click', '.delete-role-btn', function() {
                const id = $(this).data('id');
                const name = $(this).data('name');
                const deleteUrl = deleteRoleUrlTemplate.replace(':id', id);

                Swal.fire({
                    title: 'Apakah anda yakin?',
                    text: `Anda akan menghapus role "${name}". Tindakan ini tidak dapat dibatalkan!`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: deleteUrl,
                            type: 'DELETE',
                            data: {
                                _token: "{{ csrf_token() }}"
                            },
                            success: function(response) {
                                Swal.fire('Terhapus!', response.message, 'success').then(() => {
                                    // Remove dynamically using Datatables API
                                    const table = $('#tableRoles').DataTable();
                                    const existingRow = $(`tr[data-id="${id}"]`);
                                    table.row(existingRow).remove().draw(false);
                                    renumberTable();
                                    reloadSidebarMenu();
                                });
                            },
                            error: function(xhr) {
                                let message = xhr.responseJSON?.message || 'Gagal menghapus role.';
                                Swal.fire('Gagal!', message, 'error');
                            }
                        });
                    }
                });
            });
        });
    </script>
</x-default-layout>
