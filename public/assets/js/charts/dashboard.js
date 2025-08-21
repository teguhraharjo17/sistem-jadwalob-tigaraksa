document.addEventListener('DOMContentLoaded', function () {
    const yearSelector = document.getElementById('filterYear');
    let charts = [];

    async function loadDashboardData(year) {
        const endpoint = yearSelector.dataset.route.replace(':year', year);
        const res = await fetch(endpoint);
        const data = await res.json();

        // Destroy all existing charts before rendering new ones
        charts.forEach(chart => chart.destroy());
        charts = [];

        const baseOptions = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    labels: { font: { size: 12 } }
                },
                tooltip: { enabled: true }
            },
            scales: {
                y: { beginAtZero: true, ticks: { precision: 0 } }
            }
        };

        // Checklist Progress
        charts.push(new Chart(document.getElementById('checklist_progress_chart'), {
            type: 'bar',
            data: {
                labels: data.checklist_progress.map(i => i.tanggal),
                datasets: [
                    { label: 'Di Approve', data: data.checklist_progress.map(i => i.selesai), backgroundColor: '#0066ffff' },
                    { label: 'Total Selesai', data: data.checklist_progress.map(i => i.total), backgroundColor: '#56c75fff' }
                ]
            },
            options: {
                ...baseOptions,
                scales: { x: { stacked: false }, y: { stacked: false } }
            }
        }));

        // Area Distribution
        charts.push(new Chart(document.getElementById('area_distribution_chart'), {
            type: 'doughnut',
            data: {
                labels: data.area_distribution.map(i => i.area),
                datasets: [{
                    data: data.area_distribution.map(i => i.jumlah),
                    backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#6366f1']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' },
                    tooltip: { enabled: true }
                },
                scales: {
                    x: { display: false },
                    y: { display: false }
                }
            }
        }));

        // Laporan per Bulan (Selalu tampil Jan–Des)
        const monthNames = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        const laporanMap = new Map(data.laporan_perbulan.map(i => [i.bulan, i.jumlah]));
        const laporanDataLengkap = Array.from({ length: 12 }, (_, i) => laporanMap.get(i + 1) || 0);

        charts.push(new Chart(document.getElementById('laporan_perbulan_chart'), {
            type: 'line',
            data: {
                labels: monthNames,
                datasets: [{
                    label: 'Jumlah Laporan',
                    data: laporanDataLengkap,
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.2)',
                    fill: true,
                    tension: 0.4,
                    pointRadius: 3
                }]
            },
            options: baseOptions
        }));

        // Shift Comparison
        const dates = [...new Set(data.shift_comparison.map(i => i.tanggal))];
        const pagiData = dates.map(t => data.shift_comparison.find(i => i.tanggal === t && i.shift === 'Pagi')?.jumlah || 0);
        const siangData = dates.map(t => data.shift_comparison.find(i => i.tanggal === t && i.shift === 'Siang')?.jumlah || 0);

        charts.push(new Chart(document.getElementById('shift_comparison_chart'), {
            type: 'bar',
            data: {
                labels: dates,
                datasets: [
                    { label: 'Shift Pagi', data: pagiData, backgroundColor: '#06b6d4' },
                    { label: 'Shift Siang', data: siangData, backgroundColor: '#f97316' }
                ]
            },
            options: {
                ...baseOptions,
                scales: {
                    x: { stacked: true },
                    y: { stacked: true }
                }
            }
        }));

        // Top Jobs Chart (Horizontal with multi-color & data labels)
        const pekerjaanLabels = data.top_jobs.map(i => i.pekerjaan);
        const pekerjaanJumlah = data.top_jobs.map(i => i.jumlah);

        // Warna acak (looping jika data > warna)
        const colors = [
            '#3b82f6', '#10b981', '#f59e0b',
            '#ef4444', '#6366f1', '#14b8a6',
            '#e11d48', '#84cc16', '#a855f7'
        ];

        // Top Jobs Chart (PolarArea Chart)
        charts.push(new Chart(document.getElementById('top_jobs_chart'), {
            type: 'polarArea',
            data: {
                labels: pekerjaanLabels,
                datasets: [{
                    label: 'Jumlah',
                    data: pekerjaanJumlah,
                    backgroundColor: pekerjaanLabels.map((_, i) => colors[i % colors.length])
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right'
                    },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => `${ctx.raw} laporan`
                        }
                    }
                }
            }
        }));
    }

    // Load default
    loadDashboardData(yearSelector.value);

    // Trigger reload when filter changes
    yearSelector.addEventListener('change', function () {
        loadDashboardData(this.value);
    });
});
