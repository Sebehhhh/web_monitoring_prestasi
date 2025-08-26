@extends('layouts.app')
@section('title', 'Dashboard Admin - Analytics Monitoring Prestasi')
@section('content')

<!-- Iconify & ApexCharts CDN -->
<script src="https://code.iconify.design/3/3.1.0/iconify.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.41.0/dist/apexcharts.min.js"></script>

<!-- Row 1 - KPI Cards with Enhanced Stats -->
<div class="row mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-start justify-content-between">
                    <div class="content-left">
                        <span class="fw-semibold d-block mb-1">Total Siswa Aktif</span>
                        <div class="d-flex align-items-end mt-2">
                            <h4 class="mb-0 me-2">{{ number_format($totalSiswa) }}</h4>
                            <small class="text-success">{{ $tahunAjaranAktif->nama_tahun_ajaran ?? '2024/2025' }}</small>
                        </div>
                        <small class="text-muted">Siswa terdaftar tahun ini</small>
                    </div>
                    <span class="badge bg-label-primary rounded p-2">
                        <span class="iconify" data-icon="mdi:account-group" data-width="24" data-height="24"></span>
                    </span>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-sm-6 col-xl-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-start justify-content-between">
                    <div class="content-left">
                        <span class="fw-semibold d-block mb-1">Total Prestasi</span>
                        <div class="d-flex align-items-end mt-2">
                            <h4 class="mb-0 me-2">{{ number_format($totalPrestasi) }}</h4>
                            <small class="text-success">+{{ $prestasiProgress }}%</small>
                        </div>
                        <small class="text-muted">{{ $prestasiTervalidasi }} prestasi tervalidasi</small>
                    </div>
                    <span class="badge bg-label-success rounded p-2">
                        <span class="iconify" data-icon="mdi:trophy-award" data-width="24" data-height="24"></span>
                    </span>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-sm-6 col-xl-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-start justify-content-between">
                    <div class="content-left">
                        <span class="fw-semibold d-block mb-1">Prestasi Nasional</span>
                        <div class="d-flex align-items-end mt-2">
                            <h4 class="mb-0 me-2">{{ number_format($prestasiNasional) }}</h4>
                            <small class="text-warning">{{ number_format($prestasiInternasional) }} Internasional</small>
                        </div>
                        <small class="text-muted">Tingkat kompetisi tinggi</small>
                    </div>
                    <span class="badge bg-label-warning rounded p-2">
                        <span class="iconify" data-icon="mdi:medal" data-width="24" data-height="24"></span>
                    </span>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-sm-6 col-xl-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-start justify-content-between">
                    <div class="content-left">
                        <span class="fw-semibold d-block mb-1">Partisipasi Ekskul</span>
                        <div class="d-flex align-items-end mt-2">
                            <h4 class="mb-0 me-2">{{ number_format($totalAnggotaEkskul) }}</h4>
                            <small class="text-info">{{ $totalEkskul }} Ekskul</small>
                        </div>
                        <small class="text-muted">Siswa aktif berpartisipasi</small>
                    </div>
                    <span class="badge bg-label-info rounded p-2">
                        <span class="iconify" data-icon="mdi:account-multiple-plus" data-width="24" data-height="24"></span>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Row 2 - Multi-Year Performance Analysis -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">
                    <span class="iconify" data-icon="mdi:chart-timeline-variant" data-width="24" data-height="24"></span>
                    Perbandingan Prestasi Multi-Tahun Ajaran
                </h4>
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <span class="iconify" data-icon="mdi:filter" data-width="16" data-height="16"></span>
                        Filter
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#" onclick="loadMultiYearData('all')">Semua Kategori</a></li>
                        <li><a class="dropdown-item" href="#" onclick="loadMultiYearData('akademik')">Akademik</a></li>
                        <li><a class="dropdown-item" href="#" onclick="loadMultiYearData('non_akademik')">Non-Akademik</a></li>
                    </ul>
                </div>
            </div>
            <div class="card-body">
                <div id="multiYearChart" style="height: 350px;"></div>
            </div>
        </div>
    </div>
</div>

<!-- Row 3 - School Performance Analytics Grid -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <span class="iconify" data-icon="mdi:chart-donut" data-width="20" data-height="20"></span>
                    Distribusi Tingkat Kompetisi
                </h5>
            </div>
            <div class="card-body">
                <div id="competitionLevelChart" style="height: 300px;"></div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <span class="iconify" data-icon="mdi:chart-bar" data-width="20" data-height="20"></span>
                    Top 10 Kelas Berprestasi
                </h5>
            </div>
            <div class="card-body">
                <div id="topClassChart" style="height: 300px;"></div>
            </div>
        </div>
    </div>
</div>

<!-- Row 4 - Category & Student Analytics -->
<div class="row mb-4">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <span class="iconify" data-icon="mdi:chart-areaspline" data-width="20" data-height="20"></span>
                    Trend Prestasi Bulanan (6 Bulan Terakhir)
                </h5>
            </div>
            <div class="card-body">
                <div id="monthlyTrendChart" style="height: 320px;"></div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <span class="iconify" data-icon="mdi:account-star" data-width="20" data-height="20"></span>
                    Top 10 Siswa Berprestasi
                </h5>
            </div>
            <div class="card-body" style="height: 320px; overflow-y: auto;">
                <div id="topStudentsList">
                    <!-- Dynamic content will be loaded here -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Row 5 - Category Performance & Extracurricular Impact -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <span class="iconify" data-icon="mdi:chart-pie" data-width="20" data-height="20"></span>
                    Performance Per Kategori
                </h5>
            </div>
            <div class="card-body">
                <div id="categoryPerformanceChart" style="height: 300px;"></div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <span class="iconify" data-icon="mdi:chart-multiline" data-width="20" data-height="20"></span>
                    Dampak Ekstrakurikuler terhadap Prestasi
                </h5>
            </div>
            <div class="card-body">
                <div id="extracurricularImpactChart" style="height: 300px;"></div>
            </div>
        </div>
    </div>
</div>

<!-- Row 6 - Academic vs Non-Academic Analysis -->
<div class="row mb-4">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <span class="iconify" data-icon="mdi:chart-box-multiple-outline" data-width="20" data-height="20"></span>
                    Perbandingan Prestasi Akademik vs Non-Akademik
                </h5>
            </div>
            <div class="card-body">
                <div id="academicVsNonAcademicChart" style="height: 300px;"></div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <span class="iconify" data-icon="mdi:school-outline" data-width="20" data-height="20"></span>
                    Statistik Sekolah
                </h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6 mb-3">
                        <h4 class="text-primary mb-1">{{ $totalKelas }}</h4>
                        <small class="text-muted">Kelas Aktif</small>
                    </div>
                    <div class="col-6 mb-3">
                        <h4 class="text-success mb-1">{{ $totalGuru }}</h4>
                        <small class="text-muted">Guru</small>
                    </div>
                    <div class="col-6 mb-3">
                        <h4 class="text-warning mb-1">{{ round($avgPrestasiPerSiswa, 1) }}</h4>
                        <small class="text-muted">Avg. Prestasi/Siswa</small>
                    </div>
                    <div class="col-6 mb-3">
                        <h4 class="text-info mb-1">{{ $participationRate }}%</h4>
                        <small class="text-muted">Tingkat Partisipasi</small>
                    </div>
                </div>
                <div class="progress mt-3" style="height: 8px;">
                    <div class="progress-bar bg-success" style="width: {{ $prestasiValidationRate }}%"></div>
                </div>
                <small class="text-muted mt-1 d-block">{{ $prestasiValidationRate }}% Prestasi Tervalidasi</small>
            </div>
        </div>
    </div>
</div>

<script>
// Dashboard Analytics Implementation
document.addEventListener('DOMContentLoaded', function() {
    loadDashboardAnalytics();
});

function loadDashboardAnalytics() {
    // Show loading state
    showLoadingState();
    
    // Load analytics data
    fetch('{{ route("admin.dashboard.analytics") }}')
        .then(response => response.json())
        .then(data => {
            renderMultiYearChart(data.multiYear);
            renderCompetitionLevelChart(data.competitionLevel);
            renderTopClassChart(data.topClasses);
            renderMonthlyTrendChart(data.monthlyTrend);
            renderTopStudentsList(data.topStudents);
            renderCategoryPerformanceChart(data.categoryPerformance);
            renderExtracurricularImpactChart(data.extracurricularImpact);
            renderAcademicVsNonAcademicChart(data.academicVsNonAcademic);
        })
        .catch(error => {
            console.error('Error loading dashboard analytics:', error);
            showErrorState();
        });
}

function renderMultiYearChart(data) {
    const options = {
        series: data.series,
        chart: {
            height: 350,
            type: 'line',
            zoom: { enabled: false },
            toolbar: { show: true }
        },
        dataLabels: { enabled: true },
        stroke: {
            width: 3,
            curve: 'smooth'
        },
        title: {
            text: 'Tren Prestasi Multi-Tahun',
            align: 'left'
        },
        subtitle: {
            text: 'Perbandingan prestasi antar tahun ajaran',
            align: 'left'
        },
        markers: {
            size: 6,
            strokeColors: '#fff',
            strokeWidth: 2,
            hover: { size: 8 }
        },
        xaxis: {
            categories: data.categories,
            title: { text: 'Tahun Ajaran' }
        },
        yaxis: {
            title: { text: 'Jumlah Prestasi' }
        },
        legend: {
            position: 'top',
            horizontalAlign: 'right'
        },
        colors: ['#007bff', '#28a745', '#ffc107', '#dc3545']
    };
    
    new ApexCharts(document.querySelector("#multiYearChart"), options).render();
}

function renderCompetitionLevelChart(data) {
    const options = {
        series: data.series,
        chart: {
            type: 'donut',
            height: 300
        },
        labels: data.labels,
        colors: ['#007bff', '#28a745', '#ffc107', '#fd7e14', '#dc3545'],
        legend: {
            position: 'bottom'
        },
        plotOptions: {
            pie: {
                donut: {
                    size: '70%',
                    labels: {
                        show: true,
                        total: {
                            show: true,
                            label: 'Total',
                            fontSize: '16px',
                            color: '#374151'
                        }
                    }
                }
            }
        },
        dataLabels: {
            enabled: true,
            formatter: function(val) {
                return Math.round(val) + '%';
            }
        }
    };
    
    new ApexCharts(document.querySelector("#competitionLevelChart"), options).render();
}

function renderTopClassChart(data) {
    const options = {
        series: [{
            name: 'Jumlah Prestasi',
            data: data.series
        }],
        chart: {
            type: 'bar',
            height: 300,
            toolbar: { show: false }
        },
        plotOptions: {
            bar: {
                borderRadius: 4,
                horizontal: true,
                dataLabels: { position: 'top' }
            }
        },
        dataLabels: {
            enabled: true,
            offsetX: -6,
            style: {
                fontSize: '12px',
                colors: ['#fff']
            }
        },
        xaxis: {
            categories: data.categories,
            title: { text: 'Jumlah Prestasi' }
        },
        yaxis: {
            title: { text: 'Kelas' }
        },
        colors: ['#007bff'],
        fill: {
            type: 'gradient',
            gradient: {
                shade: 'light',
                type: 'horizontal',
                shadeIntensity: 0.25,
                gradientToColors: ['#0056b3'],
                inverseColors: false,
                opacityFrom: 0.85,
                opacityTo: 0.85
            }
        }
    };
    
    new ApexCharts(document.querySelector("#topClassChart"), options).render();
}

function renderMonthlyTrendChart(data) {
    const options = {
        series: [{
            name: 'Prestasi',
            data: data.series
        }],
        chart: {
            type: 'area',
            height: 320,
            zoom: { enabled: false }
        },
        dataLabels: { enabled: true },
        stroke: {
            curve: 'smooth',
            width: 2
        },
        fill: {
            type: 'gradient',
            gradient: {
                shadeIntensity: 1,
                inverseColors: false,
                opacityFrom: 0.5,
                opacityTo: 0,
                stops: [0, 90, 100]
            }
        },
        xaxis: {
            categories: data.categories,
            title: { text: 'Bulan' }
        },
        yaxis: {
            title: { text: 'Jumlah Prestasi' }
        },
        colors: ['#28a745']
    };
    
    new ApexCharts(document.querySelector("#monthlyTrendChart"), options).render();
}

function renderTopStudentsList(data) {
    let html = '';
    data.forEach((student, index) => {
        const badgeColor = index < 3 ? 'warning' : 'primary';
        const medal = index === 0 ? '🥇' : (index === 1 ? '🥈' : (index === 2 ? '🥉' : ''));
        
        html += `
            <div class="d-flex align-items-center mb-3">
                <div class="flex-shrink-0 me-3">
                    <span class="badge bg-${badgeColor} rounded-pill">${index + 1}</span>
                </div>
                <div class="flex-grow-1">
                    <h6 class="mb-0">${medal} ${student.nama}</h6>
                    <small class="text-muted">${student.kelas} - ${student.total_prestasi} prestasi</small>
                </div>
                <div class="flex-shrink-0">
                    <span class="badge bg-success">${student.total_prestasi}</span>
                </div>
            </div>
        `;
    });
    
    document.getElementById('topStudentsList').innerHTML = html;
}

function renderCategoryPerformanceChart(data) {
    const options = {
        series: data.series,
        chart: {
            type: 'pie',
            height: 300
        },
        labels: data.labels,
        colors: ['#007bff', '#28a745', '#ffc107', '#fd7e14', '#dc3545', '#6f42c1', '#20c997'],
        legend: {
            position: 'bottom'
        },
        dataLabels: {
            enabled: true,
            formatter: function(val, opts) {
                return opts.w.config.series[opts.seriesIndex] + ' prestasi';
            }
        }
    };
    
    new ApexCharts(document.querySelector("#categoryPerformanceChart"), options).render();
}

function renderExtracurricularImpactChart(data) {
    const options = {
        series: data.series,
        chart: {
            type: 'line',
            height: 300
        },
        stroke: {
            width: 3,
            curve: 'smooth'
        },
        xaxis: {
            categories: data.categories
        },
        yaxis: [{
            title: {
                text: 'Jumlah Anggota'
            }
        }, {
            opposite: true,
            title: {
                text: 'Jumlah Prestasi'
            }
        }],
        colors: ['#007bff', '#28a745'],
        legend: {
            position: 'top'
        }
    };
    
    new ApexCharts(document.querySelector("#extracurricularImpactChart"), options).render();
}

function renderAcademicVsNonAcademicChart(data) {
    const options = {
        series: data.series,
        chart: {
            type: 'bar',
            height: 300,
            stacked: true,
            toolbar: { show: true }
        },
        plotOptions: {
            bar: {
                horizontal: false,
                columnWidth: '55%'
            }
        },
        xaxis: {
            categories: data.categories
        },
        yaxis: {
            title: {
                text: 'Jumlah Prestasi'
            }
        },
        fill: {
            opacity: 1
        },
        colors: ['#007bff', '#28a745'],
        legend: {
            position: 'top'
        }
    };
    
    new ApexCharts(document.querySelector("#academicVsNonAcademicChart"), options).render();
}

function loadMultiYearData(filter) {
    // Implement filter functionality for multi-year chart
    console.log('Loading multi-year data with filter:', filter);
}

function showLoadingState() {
    // Add loading spinners to chart containers
    const chartContainers = ['multiYearChart', 'competitionLevelChart', 'topClassChart', 'monthlyTrendChart', 'categoryPerformanceChart', 'extracurricularImpactChart', 'academicVsNonAcademicChart'];
    
    chartContainers.forEach(containerId => {
        const container = document.getElementById(containerId);
        if (container) {
            container.innerHTML = `
                <div class="d-flex justify-content-center align-items-center" style="height: 200px;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            `;
        }
    });
}

function showErrorState() {
    console.error('Failed to load dashboard analytics');
    // Show error message to user
}
</script>

@endsection