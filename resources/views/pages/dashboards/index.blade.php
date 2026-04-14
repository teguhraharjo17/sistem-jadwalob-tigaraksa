<x-default-layout>
    @section('title', 'Dashboard')
    <div class="dashboard-page container-fluid py-4 py-lg-5">
        <section class="dashboard-hero mb-4">
            <div class="dashboard-hero__copy">
                <span class="dashboard-hero__tag">Ringkasan Kinerja</span>
                <h1 class="dashboard-hero__title">Dashboard Monitoring</h1>
                <p class="dashboard-hero__text mb-0">
                    Pantau progres checklist, distribusi area, tren laporan, aktivitas shift, dan pekerjaan teratas dalam satu halaman yang lebih jelas.
                </p>
            </div>

            <div class="dashboard-hero__panel">
                <label for="filterYear" class="form-label dashboard-filter__label">Tahun Analisis</label>
                <select id="filterYear"
                        class="form-select dashboard-filter"
                        data-route="{{ route('dashboard.data', ['year' => ':year']) }}">
                    @foreach(range(date('Y'), 2020) as $year)
                        <option value="{{ $year }}" {{ $year == date('Y') ? 'selected' : '' }}>{{ $year }}</option>
                    @endforeach
                </select>
                <small class="dashboard-filter__hint">Semua grafik akan otomatis menyesuaikan saat tahun diganti.</small>
            </div>
        </section>

        <section class="dashboard-grid">
            <div class="row g-4 g-xl-5 mb-4 mb-xl-5">
                <div class="col-xl-6">
                    @include('partials/widgets/charts/_widget-checklist-progress')
                </div>
                <div class="col-xl-6">
                    @include('partials/widgets/charts/_widget-area-distribution')
                </div>
            </div>

            <div class="row g-4 g-xl-5 mb-4 mb-xl-5">
                <div class="col-xl-6">
                    @include('partials/widgets/charts/_widget-laporan-perbulan')
                </div>
                <div class="col-xl-6">
                    @include('partials/widgets/charts/_widget-shift-comparison')
                </div>
            </div>

            <div class="row g-4 g-xl-5">
                <div class="col-12">
                    @include('partials/widgets/charts/_widget-top-jobs')
                </div>
            </div>
        </section>
    </div>
</x-default-layout>

<style>
    .dashboard-page {
        --dash-surface: #ffffff;
        --dash-line: #dbe4ef;
        --dash-text: #112031;
        --dash-muted: #66758b;
        --dash-brand: #0f766e;
    }

    .dashboard-hero {
        display: grid;
        grid-template-columns: minmax(0, 1.7fr) minmax(280px, 0.8fr);
        gap: 1.5rem;
        padding: 2rem;
        border-radius: 28px;
        background:
            radial-gradient(circle at top right, rgba(14, 165, 233, 0.16), transparent 28%),
            linear-gradient(135deg, #102a43 0%, #155e75 48%, #f8fafc 150%);
        color: #fff;
        box-shadow: 0 18px 50px rgba(15, 23, 42, 0.16);
    }

    .dashboard-hero__tag {
        display: inline-flex;
        padding: 0.45rem 0.85rem;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.12);
        font-size: 0.8rem;
        font-weight: 800;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .dashboard-hero__title {
        margin: 1rem 0 0.75rem;
        font-size: clamp(2rem, 3vw, 3rem);
        font-weight: 800;
        line-height: 1.08;
        color: #fff;
    }

    .dashboard-hero__text {
        max-width: 760px;
        color: rgba(255, 255, 255, 0.84);
        font-size: 1rem;
    }

    .dashboard-hero__panel {
        align-self: end;
        padding: 1.2rem;
        border-radius: 22px;
        border: 1px solid rgba(255, 255, 255, 0.18);
        background: rgba(255, 255, 255, 0.08);
        backdrop-filter: blur(10px);
    }

    .dashboard-filter__label {
        color: #fff;
        font-weight: 700;
    }

    .dashboard-filter {
        min-height: 50px;
        border: 1px solid rgba(255, 255, 255, 0.22);
        border-radius: 16px;
        background: rgba(255, 255, 255, 0.96);
        color: var(--dash-text);
        font-weight: 700;
    }

    .dashboard-filter__hint {
        display: block;
        margin-top: 0.6rem;
        color: rgba(255, 255, 255, 0.74);
        line-height: 1.45;
    }

    .dashboard-chart-card {
        height: 100%;
        border: 1px solid var(--dash-line);
        border-radius: 24px;
        background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
        overflow: hidden;
        opacity: 0;
        transform: translateY(22px) scale(0.985);
    }

    .dashboard-chart-card.is-ready {
        animation: dashboardCardIn 0.65s cubic-bezier(0.22, 1, 0.36, 1) forwards;
    }

    .dashboard-chart-card--delay-1.is-ready { animation-delay: 0.05s; }
    .dashboard-chart-card--delay-2.is-ready { animation-delay: 0.12s; }
    .dashboard-chart-card--delay-3.is-ready { animation-delay: 0.19s; }
    .dashboard-chart-card--delay-4.is-ready { animation-delay: 0.26s; }
    .dashboard-chart-card--delay-5.is-ready { animation-delay: 0.33s; }

    .dashboard-chart-card .card-header {
        padding: 1.5rem 1.5rem 0.5rem;
    }

    .dashboard-chart-card .card-body {
        padding: 1rem 1.5rem 1.5rem;
        min-height: 340px;
    }

    .dashboard-chart-card .card-title {
        color: var(--dash-text);
        font-size: 1.15rem;
        font-weight: 800;
    }

    .dashboard-chart-card__eyebrow {
        display: inline-block;
        margin-bottom: 0.45rem;
        color: var(--dash-brand);
        font-size: 0.78rem;
        font-weight: 800;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    @keyframes dashboardCardIn {
        0% {
            opacity: 0;
            transform: translateY(22px) scale(0.985);
        }
        100% {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    @media (max-width: 991.98px) {
        .dashboard-hero {
            grid-template-columns: 1fr;
            padding: 1.4rem;
        }
    }
</style>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>
<script src="{{ asset('assets/js/charts/dashboard.js') }}"></script>
