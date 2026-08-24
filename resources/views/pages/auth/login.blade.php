<x-auth-layout>
    @section('title', 'Login')
    
    <!--begin::Form Custom Styles-->
    <style>
        .login-input-group .form-control {
            border: 1px solid #e1e7ec;
            background-color: #f8fafc;
            border-radius: 0.85rem;
            padding-top: 0.85rem;
            padding-bottom: 0.85rem;
            font-size: 0.95rem;
            transition: all 0.25s ease;
        }

        .login-input-group .form-control:focus {
            background-color: #ffffff;
            border-color: #009ef7;
            box-shadow: 0 0 0 4px rgba(0, 158, 247, 0.12);
        }

        [data-bs-theme="dark"] .login-input-group .form-control {
            background-color: #1b1b29;
            border-color: #323248;
            color: #ffffff;
        }

        [data-bs-theme="dark"] .login-input-group .form-control:focus {
            background-color: #151521;
            border-color: #009ef7;
            box-shadow: 0 0 0 4px rgba(0, 158, 247, 0.2);
        }

        .btn-login-gradient {
            background: linear-gradient(135deg, #009ef7 0%, #0072b5 100%);
            border: none;
            border-radius: 0.85rem;
            padding-top: 0.9rem;
            padding-bottom: 0.9rem;
            font-weight: 600;
            letter-spacing: 0.3px;
            box-shadow: 0 10px 20px -5px rgba(0, 158, 247, 0.4);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .btn-login-gradient:hover {
            background: linear-gradient(135deg, #0090e0 0%, #00649e 100%);
            transform: translateY(-2px);
            box-shadow: 0 14px 25px -5px rgba(0, 158, 247, 0.5);
        }

        .btn-login-gradient:active {
            transform: translateY(0);
        }
    </style>
    <!--end::Form Custom Styles-->

    <!--begin::Form-->
    <form method="POST" action="{{ route('login') }}" class="form w-100" novalidate="novalidate" id="kt_sign_in_form" data-kt-redirect-url="{{ url('/') }}">
        @csrf
        
        <!--begin::Heading-->
        <div class="text-center mb-8">
            <!--begin::Badge-->
            <div class="mb-3">
                <span class="badge badge-light-primary fw-bold fs-7 px-3 py-2 rounded-pill">
                    <i class="bi bi-shield-lock-fill text-primary me-1 fs-7"></i> Portal Autentikasi
                </span>
            </div>
            <!--end::Badge-->

            <!--begin::Title-->
            <h1 class="text-gray-900 fw-bolder mb-2 fs-2x">
                Selamat Datang
            </h1>
            <!--end::Title-->

            <!--begin::Subtitle-->
            <div class="text-gray-500 fw-semibold fs-6">
                Sistem Informasi Penjadwalan OB Tigaraksa
            </div>
            <!--end::Subtitle--->
        </div>
        <!--begin::Heading-->

        <!--begin::Alert Status & Errors-->
        @if (session('status'))
            <div class="alert alert-success d-flex align-items-center p-4 mb-6 rounded-3">
                <i class="bi bi-check-circle-fill text-success fs-3 me-3"></i>
                <div class="fs-7 fw-semibold text-success">{{ session('status') }}</div>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger d-flex align-items-center p-4 mb-6 rounded-3">
                <i class="bi bi-exclamation-triangle-fill text-danger fs-3 me-3 flex-shrink-0"></i>
                <div class="fs-7 fw-semibold text-danger">
                    {{ $errors->first() }}
                </div>
            </div>
        @endif
        <!--end::Alert Status & Errors-->

        <!--begin::Input group Username-->
        <div class="fv-row mb-6 login-input-group position-relative">
            <label class="form-label fs-7 fw-bold text-gray-700 mb-2">Username</label>
            <div class="position-relative">
                <i class="bi bi-person-fill position-absolute top-50 start-0 translate-middle-y ms-4 text-primary opacity-60 fs-4"></i>
                <input type="text" 
                       placeholder="Masukkan username Anda" 
                       name="username" 
                       value="{{ old('username') }}"
                       autocomplete="off" 
                       class="form-control ps-12"/>
            </div>
        </div>
        <!--end::Input group Username-->

        <!--begin::Input group Password-->
        <div class="fv-row mb-8 login-input-group position-relative">
            <div class="d-flex flex-stack mb-2">
                <label class="form-label fs-7 fw-bold text-gray-700 mb-0">Password</label>
            </div>
            
            <div class="position-relative">
                <i class="bi bi-key-fill position-absolute top-50 start-0 translate-middle-y ms-4 text-primary opacity-60 fs-4"></i>
                <input type="password" 
                       placeholder="Masukkan password Anda" 
                       name="password" 
                       autocomplete="off" 
                       class="form-control ps-12 pe-12"/>

                <span class="btn btn-sm btn-icon position-absolute top-50 end-0 translate-middle-y me-2 text-gray-500 text-hover-primary cursor-pointer" data-kt-password-meter-control="visibility" title="Tampilkan/Sembunyikan password">
                    <i class="bi bi-eye-slash fs-3"></i>
                    <i class="bi bi-eye fs-3 d-none"></i>
                </span>
            </div>
        </div>
        <!--end::Input group Password-->

        <!--begin::Submit button-->
        <div class="d-grid mb-8">
            <button type="submit" id="kt_sign_in_submit" class="btn btn-primary btn-login-gradient text-white">
                @include('partials/general/_button-indicator', ['label' => 'Masuk ke Sistem'])
            </button>
        </div>
        <!--end::Submit button-->

        <!--begin::Notice Info Box-->
        <div class="d-flex align-items-center bg-light-primary rounded-3 p-4 border border-primary border-opacity-15">
            <i class="bi bi-info-circle-fill text-primary fs-3 me-3 flex-shrink-0"></i>
            <div class="text-gray-700 fs-8 lh-base">
                Gunakan akun resmi yang terdaftar untuk mengakses jadwal dan laporan harian area Tigaraksa.
            </div>
        </div>
        <!--end::Notice Info Box-->

    </form>
    <!--end::Form-->

</x-auth-layout>


