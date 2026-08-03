<x-default-layout>
    @section('title', 'Master User')

    <div class="container py-4">
        <!-- Premium Header Banner -->
        <div class="card border-0 shadow-sm mb-5" style="border-radius: 12px; background: linear-gradient(135deg, #e2eafc 0%, #eef2f3 100%);">
            <div class="card-body p-4 d-flex justify-content-between align-items-center flex-column flex-md-row">
                <div class="mb-3 mb-md-0">
                    <h1 class="text-gray-900 fw-bolder fs-2 mb-1"><i class="fas fa-users text-primary me-2"></i> Master Data User</h1>
                    <p class="text-gray-600 fs-6 mb-0">Kelola akun pengguna, atur password, dan tetapkan hak akses peran mereka.</p>
                </div>
                <div>
                    <button type="button" class="btn btn-primary fw-bold btn-sm shadow-sm hover-scale" data-bs-toggle="modal" data-bs-target="#addUserModal">
                        <i class="fas fa-user-plus me-2"></i> Tambah User Baru
                    </button>
                </div>
            </div>
        </div>

        <!-- Info & Guide Alert (Balanced UX) -->
        <div class="alert alert-dismissible bg-light-success d-flex align-items-center p-4 mb-5 border-start border-4 border-success rounded-3 shadow-none">
            <i class="fas fa-info-circle text-success fs-3 me-3"></i>
            <div class="pe-10 fs-7 text-gray-700">
                <strong>Panduan:</strong> Setiap user dihubungkan dengan satu **Role**. Hak akses menu yang dimiliki user diatur langsung di dalam menu konfigurasi Role.
            </div>
            <button type="button" class="position-absolute position-sm-relative m-2 m-sm-0 top-0 end-0 btn btn-icon ms-sm-auto" data-bs-dismiss="alert">
                <i class="fas fa-times text-success fs-4"></i>
            </button>
        </div>

        <!-- Beautified Content Card -->
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-5">
                <div class="table-responsive">
                    <table id="tableUsers" class="table table-row-dashed table-hover align-middle gs-0 gy-3 mb-0">
                        <thead>
                            <tr class="fw-bold text-gray-700 fs-7 text-uppercase border-bottom bg-light-transparent">
                                <th class="text-center w-80px">No</th>
                                <th class="min-w-200px">Nama Lengkap</th>
                                <th class="min-w-150px">Username</th>
                                <th class="text-center min-w-120px">Role</th>
                                <th class="text-center min-w-150px">Tanggal Dibuat</th>
                                <th class="text-center w-180px">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($users as $user)
                                <tr class="align-middle" data-id="{{ $user->id }}">
                                    <td class="text-center fw-semibold text-gray-600 fs-7">{{ $loop->iteration }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <!-- Elegant Name Initials Avatar -->
                                            <div class="symbol symbol-35px me-3">
                                                <div class="symbol-label bg-light-primary text-primary fw-bold fs-6">
                                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                                </div>
                                            </div>
                                            <div class="d-flex flex-column">
                                                <span class="text-gray-900 fw-bold fs-6">{{ $user->name }}</span>
                                                @if($user->id == auth()->id())
                                                    <span class="text-primary fs-8 fw-semibold"><i class="fas fa-check-circle fs-9 me-1"></i> Akun Anda</span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="text-gray-700 fw-semibold fs-7">{{ $user->username }}</span>
                                    </td>
                                    <td class="text-center">
                                        @if($user->role)
                                            @php
                                                $color = [
                                                    1 => 'danger',
                                                    2 => 'warning',
                                                    3 => 'primary'
                                                ][$user->role->id] ?? 'secondary';
                                            @endphp
                                            <span class="badge badge-light-{{ $color }} fw-bold px-3 py-1 fs-7">
                                                {{ $user->role->name }}
                                            </span>
                                        @else
                                            <span class="badge badge-light-secondary fw-semibold px-2.5 py-1 fs-8 text-muted">
                                                Tidak Ada Role
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center text-gray-600 fs-7">
                                        {{ $user->created_at ? $user->created_at->translatedFormat('d M Y H:i') : '-' }}
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-2">
                                            <button type="button" class="btn btn-sm btn-light-warning hover-scale px-3 edit-user-btn" 
                                                    data-id="{{ $user->id }}">
                                                <i class="fas fa-edit me-1"></i> Edit
                                            </button>
                                            @if($user->id != auth()->id())
                                                <button type="button" class="btn btn-sm btn-light-danger hover-scale px-3 delete-user-btn" 
                                                        data-id="{{ $user->id }}" data-name="{{ $user->name }}">
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

    <!-- Modal: Add User -->
    <div class="modal fade" id="addUserModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border">
                <div class="modal-header bg-light-success p-4 border-bottom">
                    <h5 class="modal-title fw-bold text-success"><i class="fas fa-user-plus me-2"></i> Tambah User Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formAddUser">
                    @csrf
                    <div class="modal-body p-5">
                        <div class="mb-4">
                            <label for="name" class="form-label fw-bold text-gray-800">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-solid" id="name" name="name" required placeholder="Contoh: Budi Santoso">
                        </div>
                        <div class="mb-4">
                            <label for="username" class="form-label fw-bold text-gray-800">Username <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-solid" id="username" name="username" required placeholder="Contoh: budi_s">
                        </div>
                        <div class="mb-4">
                            <label for="role_id" class="form-label fw-bold text-gray-800">Role Pengguna <span class="text-danger">*</span></label>
                            <select name="role_id" id="role_id" class="form-select form-select-solid" required>
                                <option value="">Pilih Role</option>
                                @foreach ($roles as $role)
                                    <option value="{{ $role->id }}">{{ $role->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-4">
                            <label for="password" class="form-label fw-bold text-gray-800">Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control form-control-solid" id="password" name="password" required placeholder="Min. 8 Karakter">
                        </div>
                        <div class="mb-4">
                            <label for="password_confirmation" class="form-label fw-bold text-gray-800">Konfirmasi Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control form-control-solid" id="password_confirmation" name="password_confirmation" required placeholder="Ulangi Password">
                        </div>
                    </div>
                    <div class="modal-footer border-top p-4 bg-light">
                        <button type="button" class="btn btn-light btn-sm fw-bold" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success text-white btn-sm fw-bold"><i class="fas fa-save me-1"></i> Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal: Edit User -->
    <div class="modal fade" id="editUserModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border">
                <div class="modal-header bg-light-warning p-4 border-bottom">
                    <h5 class="modal-title fw-bold text-warning-dark"><i class="fas fa-user-edit me-2"></i> Edit Data User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formEditUser">
                    @csrf
                    @method('PUT')
                    <div class="modal-body p-5">
                        <div class="mb-4">
                            <label for="edit_name" class="form-label fw-bold text-gray-800">Nama Lengkap</label>
                            <input type="text" class="form-control form-control-solid" id="edit_name" name="name" required>
                        </div>
                        <div class="mb-4">
                            <label for="edit_username" class="form-label fw-bold text-gray-800">Username</label>
                            <input type="text" class="form-control form-control-solid" id="edit_username" name="username" required>
                        </div>
                        <div class="mb-4">
                            <label for="edit_role_id" class="form-label fw-bold text-gray-800">Role Pengguna</label>
                            <select name="role_id" id="edit_role_id" class="form-select form-select-solid" required>
                                <option value="">Pilih Role</option>
                                @foreach ($roles as $role)
                                    <option value="{{ $role->id }}">{{ $role->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="p-3 mb-4 bg-light border rounded fs-8 text-gray-700 border-warning-subtle">
                            <strong>Mengubah Password (Opsional):</strong> Biarkan kedua field di bawah ini kosong jika Anda tidak ingin mengganti password pengguna ini.
                        </div>
                        
                        <div class="mb-4">
                            <label for="edit_password" class="form-label fw-bold text-gray-800">Password Baru</label>
                            <input type="password" class="form-control form-control-solid" id="edit_password" name="password" placeholder="Kosongkan jika tidak diubah">
                        </div>
                        <div class="mb-4">
                            <label for="edit_password_confirmation" class="form-label fw-bold text-gray-800">Konfirmasi Password Baru</label>
                            <input type="password" class="form-control form-control-solid" id="edit_password_confirmation" name="password_confirmation" placeholder="Ulangi password baru">
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
            $('#tableUsers').DataTable({
                ordering: true,
                searching: true,
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Cari User",
                    lengthMenu: "Tampilkan _MENU_ entri",
                    info: "Menampilkan _START_ hingga _END_ dari _TOTAL_ entri",
                    paginate: {
                        previous: '<i class="fas fa-chevron-left"></i>',
                        next: '<i class="fas fa-chevron-right"></i>',
                    }
                }
            });

            // Helper function to build dynamic user row HTML
            function getUserRowHtml(user) {
                const currentUserId = {{ auth()->id() }};
                const userInitial = user.name.substring(0, 1).toUpperCase();
                const userBadge = user.id == currentUserId ? '<span class="text-primary fs-8 fw-semibold"><i class="fas fa-check-circle fs-9 me-1"></i> Akun Anda</span>' : '';
                
                let roleColor = 'secondary';
                let roleName = 'Tidak Ada Role';
                if (user.role) {
                    roleName = user.role.name;
                    roleColor = {
                        1: 'danger',
                        2: 'warning',
                        3: 'primary'
                    }[user.role.id] || 'secondary';
                }

                const deleteBtnHtml = user.id == currentUserId ? `
                    <button type="button" class="btn btn-sm btn-light" disabled title="Anda tidak dapat menghapus akun Anda sendiri">
                        <i class="fas fa-lock text-muted fs-6"></i>
                    </button>
                ` : `
                    <button type="button" class="btn btn-sm btn-light-danger hover-scale px-3 delete-user-btn" 
                            data-id="${user.id}" data-name="${user.name}">
                        <i class="fas fa-trash me-1"></i> Hapus
                    </button>
                `;

                const formattedDate = user.created_at_formatted || '-';

                return `
                    <tr class="align-middle" data-id="${user.id}">
                        <td class="text-center fw-semibold text-gray-600 fs-7">-</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="symbol symbol-35px me-3">
                                    <div class="symbol-label bg-light-primary text-primary fw-bold fs-6">
                                        ${userInitial}
                                    </div>
                                </div>
                                <div class="d-flex flex-column">
                                    <span class="text-gray-900 fw-bold fs-6">${user.name}</span>
                                    ${userBadge}
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="text-gray-700 fw-semibold fs-7">${user.username}</span>
                        </td>
                        <td class="text-center">
                            <span class="badge badge-light-${roleColor} fw-bold px-3 py-1 fs-7">
                                ${roleName}
                            </span>
                        </td>
                        <td class="text-center text-gray-600 fs-7">
                            ${formattedDate}
                        </td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-2">
                                <button type="button" class="btn btn-sm btn-light-warning hover-scale px-3 edit-user-btn" 
                                        data-id="${user.id}">
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
                const table = $('#tableUsers').DataTable();
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

            // Reset forms on modal opens
            $('[data-bs-target="#addUserModal"]').on('click', function() {
                $('#formAddUser')[0].reset();
            });

            // Add User Submit
            $('#formAddUser').on('submit', function(e) {
                e.preventDefault();
                const form = $(this);
                const btn = form.find('button[type="submit"]');
                btn.attr('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');

                $.post("{{ route('superadmin.users.store') }}", form.serialize())
                    .done(function(response) {
                        btn.removeAttr('disabled').html('<i class="fas fa-save me-1"></i> Simpan');
                        Swal.fire('Sukses!', response.message, 'success').then(() => {
                            $('#addUserModal').modal('hide');
                            
                            // Insert dynamically using Datatable API
                            const table = $('#tableUsers').DataTable();
                            const newRowHtml = getUserRowHtml(response.user);
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

            // Edit User Show Modal
            const editUserUrlTemplate = "{{ route('superadmin.users.edit', ':id') }}";
            const updateUserUrlTemplate = "{{ route('superadmin.users.update', ':id') }}";

            $(document).on('click', '.edit-user-btn', function() {
                const id = $(this).data('id');
                const editUrl = editUserUrlTemplate.replace(':id', id);

                $.get(editUrl, function(res) {
                    const u = res.user;
                    const form = $('#formEditUser');
                    form.attr('action', updateUserUrlTemplate.replace(':id', id));
                    form.find('[name=name]').val(u.name);
                    form.find('[name=username]').val(u.username);
                    form.find('[name=role_id]').val(u.role_id);
                    form.find('[name=password]').val('');
                    form.find('[name=password_confirmation]').val('');
                    $('#editUserModal').modal('show');
                }).fail(function() {
                    Swal.fire('Gagal!', 'Tidak dapat memuat detail data user.', 'error');
                });
            });

            // Update User Submit
            $('#formEditUser').on('submit', function(e) {
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
                            $('#editUserModal').modal('hide');
                            
                            // Update row dynamically using Datatable API
                            const table = $('#tableUsers').DataTable();
                            const existingRow = $(`tr[data-id="${response.user.id}"]`);
                            if (existingRow.length > 0) {
                                const newRowHtml = getUserRowHtml(response.user);
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

            // Delete User
            const deleteUserUrlTemplate = "{{ route('superadmin.users.destroy', ':id') }}";

            $(document).on('click', '.delete-user-btn', function() {
                const id = $(this).data('id');
                const name = $(this).data('name');
                const deleteUrl = deleteUserUrlTemplate.replace(':id', id);

                Swal.fire({
                    title: 'Apakah anda yakin?',
                    text: `Anda akan menghapus user "${name}". Tindakan ini tidak dapat dibatalkan!`,
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
                                    const table = $('#tableUsers').DataTable();
                                    const existingRow = $(`tr[data-id="${id}"]`);
                                    table.row(existingRow).remove().draw(false);
                                    renumberTable();
                                    reloadSidebarMenu();
                                });
                            },
                            error: function(xhr) {
                                let message = xhr.responseJSON?.message || 'Gagal menghapus user.';
                                Swal.fire('Gagal!', message, 'error');
                            }
                        });
                    }
                });
            });
        });
    </script>
</x-default-layout>
