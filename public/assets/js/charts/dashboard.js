document.addEventListener('DOMContentLoaded', function () {
    const yearSelector = document.getElementById('filterYear');
    let charts = [];

    function revealCards() {
        document.querySelectorAll('.dashboard-chart-card').forEach(card => {
            card.classList.remove('is-ready');
            void card.offsetWidth;
            card.classList.add('is-ready');
        });
    }

    function makeVerticalGradient(ctx, colorTop, colorBottom) {
        const gradient = ctx.createLinearGradient(0, 0, 0, 320);
        gradient.addColorStop(0, colorTop);
        gradient.addColorStop(1, colorBottom);
        return gradient;
    }

    async function loadDashboardData(year) {
        const endpoint = yearSelector.dataset.route.replace(':year', year);
        const res = await fetch(endpoint);
        const data = await res.json();

        // Destroy all existing charts before rendering new ones
        charts.forEach(chart => chart.destroy());
        charts = [];

        revealCards();

        const checklistCtx = document.getElementById('checklist_progress_chart').getContext('2d');
        const laporanCtx = document.getElementById('laporan_perbulan_chart').getContext('2d');
        const shiftCtx = document.getElementById('shift_comparison_chart').getContext('2d');

        const baseOptions = {
            responsive: true,
            maintainAspectRatio: false,
            animation: {
                duration: 1100,
                easing: 'easeOutCubic'
            },
            plugins: {
                legend: {
                    display: true,
                    labels: {
                        font: { size: 12, weight: '600' },
                        color: '#334155',
                        boxWidth: 14,
                        usePointStyle: true,
                        pointStyle: 'circle'
                    }
                },
                tooltip: { enabled: true }
            },
            scales: {
                x: {
                    ticks: { color: '#64748b' },
                    grid: { display: false }
                },
                y: {
                    beginAtZero: true,
                    ticks: { precision: 0, color: '#64748b' },
                    grid: { color: 'rgba(148, 163, 184, 0.18)' }
                }
            }
        };

        // Checklist Progress
        charts.push(new Chart(document.getElementById('checklist_progress_chart'), {
            type: 'bar',
            data: {
                labels: data.checklist_progress.map(i => i.tanggal),
                datasets: [
                    {
                        label: 'Di Approve (Selesai)',
                        data: data.checklist_progress.map(i => i.selesai),
                        backgroundColor: makeVerticalGradient(checklistCtx, 'rgba(14, 165, 233, 0.95)', 'rgba(2, 132, 199, 0.72)'),
                        borderRadius: 10,
                        maxBarThickness: 26
                    },
                    {
                        label: 'Total Pekerjaan',
                        data: data.checklist_progress.map(i => i.total),
                        backgroundColor: makeVerticalGradient(checklistCtx, 'rgba(34, 197, 94, 0.92)', 'rgba(21, 128, 61, 0.68)'),
                        borderRadius: 10,
                        maxBarThickness: 26
                    }
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
                    backgroundColor: ['#0ea5e9', '#10b981', '#f59e0b', '#f97316', '#8b5cf6', '#ef4444'],
                    borderColor: '#ffffff',
                    borderWidth: 3,
                    hoverOffset: 14
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
                    borderColor: '#0ea5e9',
                    backgroundColor: makeVerticalGradient(laporanCtx, 'rgba(14, 165, 233, 0.28)', 'rgba(14, 165, 233, 0.03)'),
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    pointBackgroundColor: '#0ea5e9',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2
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
                    {
                        label: 'Shift Pagi',
                        data: pagiData,
                        backgroundColor: makeVerticalGradient(shiftCtx, 'rgba(6, 182, 212, 0.94)', 'rgba(8, 145, 178, 0.74)'),
                        borderRadius: 10,
                        maxBarThickness: 28
                    },
                    {
                        label: 'Shift Siang',
                        data: siangData,
                        backgroundColor: makeVerticalGradient(shiftCtx, 'rgba(249, 115, 22, 0.92)', 'rgba(194, 65, 12, 0.74)'),
                        borderRadius: 10,
                        maxBarThickness: 28
                    }
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
                    backgroundColor: pekerjaanLabels.map((_, i) => colors[i % colors.length] + 'CC'),
                    borderColor: '#ffffff',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: {
                    duration: 1300,
                    easing: 'easeOutQuart'
                },
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            color: '#334155',
                            font: { size: 12, weight: '600' }
                        }
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
