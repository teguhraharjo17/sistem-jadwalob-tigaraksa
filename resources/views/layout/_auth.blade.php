@extends('layout.master')

@section('content')

    <!--begin::Custom Auth Styles-->
    <style>
        .auth-bg-gradient {
            background: linear-gradient(145deg, #09172d 0%, #0d2451 55%, #083b6f 100%);
            position: relative;
            overflow: hidden;
        }

        .auth-bg-gradient::before {
            content: '';
            position: absolute;
            top: -10%;
            right: -10%;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(0, 158, 247, 0.28) 0%, rgba(0, 158, 247, 0) 70%);
            border-radius: 50%;
            pointer-events: none;
            filter: blur(50px);
        }

        .auth-bg-gradient::after {
            content: '';
            position: absolute;
            bottom: -10%;
            left: -10%;
            width: 450px;
            height: 450px;
            background: radial-gradient(circle, rgba(0, 210, 255, 0.18) 0%, rgba(13, 36, 81, 0) 70%);
            border-radius: 50%;
            pointer-events: none;
            filter: blur(60px);
        }

        .auth-form-card {
            background: #ffffff;
            border-radius: 1.5rem;
            box-shadow: 0 20px 45px -10px rgba(13, 36, 81, 0.08), 0 0 1px 1px rgba(0, 158, 247, 0.08);
            border: 1px solid #eef3f7;
            transition: box-shadow 0.3s ease;
        }

        [data-bs-theme="dark"] .auth-form-card {
            background: #1e1e2d;
            border-color: #2b2b40;
            box-shadow: 0 20px 45px -10px rgba(0, 0, 0, 0.5);
        }

        .feature-pill {
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-radius: 12px;
            padding: 10px 16px;
            transition: all 0.3s ease;
        }

        .feature-pill:hover {
            background: rgba(255, 255, 255, 0.14);
            transform: translateY(-2px);
        }

        .float-animation {
            animation: floating 4s ease-in-out infinite;
        }

        @keyframes floating {
            0%, 100% {
                transform: translateY(0px);
            }
            50% {
                transform: translateY(-8px);
            }
        }

        @media (max-width: 575.98px) {
            .auth-form-card {
                padding: 1.75rem 1.25rem !important;
                border-radius: 1.25rem;
            }

            .auth-bg-gradient {
                min-height: auto !important;
            }
        }
    </style>
    <!--end::Custom Auth Styles-->

    <!--begin::App-->
    <div class="d-flex flex-column flex-root app-root" id="kt_app_root">
        <!--begin::Wrapper-->
        <div class="d-flex flex-column flex-lg-row flex-column-fluid">
            
            <!--begin::Body (Form Side)-->
            <div class="d-flex flex-column flex-lg-row-fluid w-lg-50 p-6 p-lg-12 order-2 order-lg-1 bg-body">
                <!--begin::Form Wrapper-->
                <div class="d-flex flex-center flex-column flex-lg-row-fluid">
                    <!--begin::Container Card-->
                    <div class="w-100 w-md-475px auth-form-card p-8 p-lg-12">
                        <!--begin::Page-->
                        {{ $slot }}
                        <!--end::Page-->
                    </div>
                    <!--end::Container Card-->
                </div>
                <!--end::Form Wrapper-->

                <!--begin::Footer-->
                <div class="d-flex flex-column flex-md-row flex-center justify-content-between px-5 pt-8 text-gray-500 fs-7">
                    <!--begin::Copyright-->
                    <div class="d-flex align-items-center gap-1 mb-2 mb-md-0">
                        <span>&copy; {{ date('Y') }}</span>
                        <a href="https://ccas.co.id/" target="_blank" class="text-gray-700 text-hover-primary fw-semibold">PT. Milenia Megah Mandiri</a>
                    </div>
                    <!--end::Copyright-->

                    <!--begin::Links-->
                    <div class="d-flex align-items-center text-center text-md-end">
                        <a href="mailto:it.web3@ccas.co.id" class="text-gray-600 text-hover-primary fw-semibold">
                            <i class="bi bi-envelope-at-fill me-1 text-primary"></i> Saran & Kritik: <span class="text-primary">it.web3@ccas.co.id</span>
                        </a>
                    </div>
                    <!--end::Links-->
                </div>
                <!--end::Footer-->
            </div>
            <!--end::Body-->

            <!--begin::Aside (Hero Showcase Side)-->
            <div class="d-flex flex-lg-row-fluid w-lg-50 auth-bg-gradient order-1 order-lg-2 min-h-350px min-h-lg-auto">
                <!--begin::Content-->
                <div class="d-flex flex-column flex-center justify-content-center py-10 py-lg-16 px-6 px-md-12 w-100 position-relative z-index-1">
                    
                    <!--begin::Logo Section-->
                    <div class="text-center mb-6 mb-lg-8">
                        <a href="https://ccas.co.id/" target="_blank" class="d-inline-block transition-transform hover-scale">
                            <img alt="Logo Milenia" src="{{ image('logos/logo_milenia_login.png') }}" class="h-60px h-lg-90px"/>
                        </a>
                    </div>
                    <!--end::Logo Section-->

                    <!--begin::Heading & Intro-->
                    <div class="text-center mb-6 max-w-450px">
                        <h2 class="text-white fw-bold fs-2x mb-2">
                            OB Management System
                        </h2>
                        <p class="text-white-50 fs-6 mb-0">
                            Pusat pengelolaan jadwal, checklist area kebersihan, dan pemantauan tugas operasional Tigaraksa.
                        </p>
                    </div>
                    <!--end::Heading & Intro-->

                    <!--begin::Screen Image Preview (Compact & Borderless)-->
                    <div class="text-center position-relative mb-8 d-none d-lg-block">
                        <img class="img-fluid float-animation" 
                             src="{{ image('misc/milenia_screen.png') }}" 
                             alt="Preview Sistem" 
                             style="max-width: 410px; height: auto; filter: drop-shadow(0 20px 35px rgba(0, 0, 0, 0.45));"/>
                    </div>
                    <!--end::Screen Image Preview-->

                    <!--begin::Feature Badges (Clean & Minimalist)-->
                    <div class="d-none d-lg-flex flex-wrap justify-content-center gap-4 max-w-500px">
                        <div class="feature-pill d-flex align-items-center gap-2 text-white">
                            <i class="bi bi-calendar-check-fill text-info fs-5"></i>
                            <span class="fs-7 fw-semibold">Jadwal Tugas Otomatis</span>
                        </div>
                        <div class="feature-pill d-flex align-items-center gap-2 text-white">
                            <i class="bi bi-check2-all text-success fs-5"></i>
                            <span class="fs-7 fw-semibold">Checklist Area Bersih</span>
                        </div>
                        <div class="feature-pill d-flex align-items-center gap-2 text-white">
                            <i class="bi bi-bar-chart-fill text-warning fs-5"></i>
                            <span class="fs-7 fw-semibold">Monitoring Harian</span>
                        </div>
                    </div>
                    <!--end::Feature Badges-->

                </div>
                <!--end::Content-->
            </div>
            <!--end::Aside-->

        </div>
        <!--end::Wrapper-->
    </div>
    <!--end::App-->

@endsection

