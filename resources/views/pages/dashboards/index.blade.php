<x-default-layout>
    @section('title', 'Dashboard')
    <!--begin::Filter Tahun-->
    <div class="mt-5 mb-5 text-start">
        <div class="form-group">
            <label for="filterYear" class="form-label fw-bold">Filter Tahun</label>
            <select id="filterYear"
                    class="form-select form-select-sm w-auto"
                    data-route="{{ route('dashboard.data', ['year' => ':year']) }}">
                @foreach(range(date('Y'), 2020) as $year)
                    <option value="{{ $year }}" {{ $year == date('Y') ? 'selected' : '' }}>{{ $year }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <!--end::Filter Tahun-->

    <!--begin::Row 3 (Custom Chart 1 & 2)-->
    <div class="row g-5 g-xl-10 mb-5 mb-xl-10">
        <div class="col-xl-6">
            @include('partials/widgets/charts/_widget-checklist-progress')
        </div>
        <div class="col-xl-6">
            @include('partials/widgets/charts/_widget-area-distribution')
        </div>
    </div>

    <!--begin::Row 4 (Custom Chart 3 & 4)-->
    <div class="row g-5 g-xl-10 mb-5 mb-xl-10">
        <div class="col-xl-6">
            @include('partials/widgets/charts/_widget-laporan-perbulan')
        </div>
        <div class="col-xl-6">
            @include('partials/widgets/charts/_widget-shift-comparison')
        </div>
    </div>

    <!--begin::Row 5 (Custom Chart 5)-->
    <div class="row g-5 g-xl-10 mb-5 mb-xl-10">
        <div class="col-xl-12">
            @include('partials/widgets/charts/_widget-top-jobs')
        </div>
    </div>
</x-default-layout>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>
<script src="{{ asset('assets/js/charts/dashboard.js') }}"></script>
