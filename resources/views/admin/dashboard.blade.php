@extends('layouts.app')
@section('title', 'Dashboard Admin - Analytics Monitoring Prestasi')
@section('content')

<!-- Iconify & ApexCharts CDN -->
<script src="https://code.iconify.design/3/3.1.0/iconify.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.41.0/dist/apexcharts.min.js"></script>

<!-- Custom Dashboard Styles -->
<style>
.avatar {
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
}
.avatar-sm {
    width: 32px;
    height: 32px;
}
.avatar-lg {
    width: 48px;
    height: 48px;
}
.card {
    transition: all 0.2s ease-in-out;
}
.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 25px 0 rgba(0,0,0,.1);
}
.bg-gradient {
    background: linear-gradient(45deg, var(--bs-primary), var(--bs-primary-dark)) !important;
}
.bg-success.bg-gradient {
    background: linear-gradient(45deg, var(--bs-success), #198754) !important;
}
.bg-warning.bg-gradient {
    background: linear-gradient(45deg, var(--bs-warning), #e0a800) !important;
}
.bg-info.bg-gradient {
    background: linear-gradient(45deg, var(--bs-info), #0dcaf0) !important;
}
.progress {
    border-radius: 10px;
}
.progress-bar {
    border-radius: 10px;
}
</style>

<!-- Loading Overlay -->
<div id="loadingOverlay" class="position-fixed top-0 start-0 w-100 h-100 d-none" style="background: rgba(255,255,255,0.8); z-index: 1050;">
    <div class="d-flex justify-content-center align-items-center h-100">
        <div class="text-center">
            <div class="spinner-border text-primary mb-3" style="width: 3rem; height: 3rem;" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <h5>Memuat Dashboard Analytics...</h5>
        </div>
    </div>
</div>

<!-- Row 1 - KPI Cards (Clean & Organized) -->
<div class="row mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <span class="text-muted small d-block mb-1">Total Siswa Aktif</span>
                        <h3 class="mb-1 text-primary">{{ number_format($totalSiswa) }}</h3>
                        <small class="text-success">{{ $currentTahunAjaran->nama_tahun_ajaran ?? 'Tahun Aktif' }}</small>
                    </div>
                    <div class="flex-shrink-0">
                        <div class="avatar avatar-lg bg-primary bg-gradient">
                            <span class="iconify text-white" data-icon="mdi:account-group" data-width="24" data-height="24"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <span class="text-muted small d-block mb-1">Total Prestasi</span>
                        <h3 class="mb-1 text-success">{{ number_format($totalPrestasi) }}</h3>
                        <small class="text-success">{{ $prestasiTervalidasi }} tervalidasi</small>
                    </div>
                    <div class="flex-shrink-0">
                        <div class="avatar avatar-lg bg-success bg-gradient">
                            <span class="iconify text-white" data-icon="mdi:trophy-award" data-width="24" data-height="24"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <span class="text-muted small d-block mb-1">Prestasi Nasional+</span>
                        <h3 class="mb-1 text-warning">{{ number_format($prestasiNasional + $prestasiInternasional) }}</h3>
                        <small class="text-warning">{{ $prestasiInternasional }} Internasional</small>
                    </div>
                    <div class="flex-shrink-0">
                        <div class="avatar avatar-lg bg-warning bg-gradient">
                            <span class="iconify text-white" data-icon="mdi:medal" data-width="24" data-height="24"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <span class="text-muted small d-block mb-1">Avg Prestasi/Siswa</span>
                        <h3 class="mb-1 text-info">{{ round($avgPrestasiPerSiswa ?? 0, 1) }}</h3>
                        <small class="text-info">{{ $participationRate ?? 0 }}% partisipasi</small>
                    </div>
                    <div class="flex-shrink-0">
                        <div class="avatar avatar-lg bg-info bg-gradient">
                            <span class="iconify text-white" data-icon="mdi:chart-line" data-width="24" data-height="24"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Row 2 - Main Analytics Charts -->
<div class="row mb-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-0">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <span class="iconify text-primary" data-icon="mdi:chart-timeline-variant" data-width="20" data-height="20"></span>
                        Tren Prestasi Multi-Tahun
                    </h5>
                    <small class="text-muted">
                        <span class="iconify" data-icon="mdi:information" data-width="14" data-height="14"></span>
                        Semua prestasi tervalidasi
                    </small>
                </div>
            </div>
            <div class="card-body">
                <div id="multiYearChart" style="height: 300px;"></div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-0">
                <h5 class="card-title mb-0">
                    <span class="iconify text-success" data-icon="mdi:account-star" data-width="20" data-height="20"></span>
                    Top 10 Siswa Berprestasi
                </h5>
            </div>
            <div class="card-body" style="height: 300px; overflow-y: auto;">
                <div id="topStudentsList">
                    <div class="text-center py-3">
                        <div class="spinner-border spinner-border-sm text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Row 3 - Core Analytics -->
<div class="row mb-4">
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-0">
                <h5 class="card-title mb-0">
                    <span class="iconify text-primary" data-icon="mdi:chart-donut" data-width="20" data-height="20"></span>
                    Tingkat Kompetisi
                </h5>
            </div>
            <div class="card-body d-flex align-items-center justify-content-center">
                <div id="competitionLevelChart" style="height: 250px; width: 100%;"></div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-0">
                <h5 class="card-title mb-0">
                    <span class="iconify text-success" data-icon="mdi:chart-pie" data-width="20" data-height="20"></span>
                    Performance Kategori
                </h5>
            </div>
            <div class="card-body d-flex align-items-center justify-content-center">
                <div id="categoryPerformanceChart" style="height: 250px; width: 100%;"></div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-0">
                <h5 class="card-title mb-0">
                    <span class="iconify text-warning" data-icon="mdi:chart-areaspline" data-width="20" data-height="20"></span>
                    Tren 6 Bulan
                </h5>
            </div>
            <div class="card-body">
                <div id="monthlyTrendChart" style="height: 250px;"></div>
            </div>
        </div>
    </div>
</div>



<!-- Row 4 - Additional Insights -->
<div class="row mb-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-0">
                <h5 class="card-title mb-0">
                    <span class="iconify text-info" data-icon="mdi:chart-bar-stacked" data-width="20" data-height="20"></span>
                    Prestasi Akademik vs Non-Akademik
                </h5>
            </div>
            <div class="card-body">
                <div id="academicVsNonAcademicChart" style="height: 250px;"></div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-0">
                <h5 class="card-title mb-0">
                    <span class="iconify text-secondary" data-icon="mdi:school-outline" data-width="20" data-height="20"></span>
                    Ringkasan Sekolah
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-6">
                        <div class="text-center">
                            <div class="avatar avatar-sm bg-primary bg-gradient mx-auto mb-2">
                                <span class="iconify text-white" data-icon="mdi:google-classroom" data-width="16"></span>
                            </div>
                            <h6 class="mb-1">{{ $totalKelas }}</h6>
                            <small class="text-muted">Kelas</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="text-center">
                            <div class="avatar avatar-sm bg-success bg-gradient mx-auto mb-2">
                                <span class="iconify text-white" data-icon="mdi:account-tie" data-width="16"></span>
                            </div>
                            <h6 class="mb-1">{{ $totalGuru }}</h6>
                            <small class="text-muted">Guru</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="text-center">
                            <div class="avatar avatar-sm bg-warning bg-gradient mx-auto mb-2">
                                <span class="iconify text-white" data-icon="mdi:chart-line" data-width="16"></span>
                            </div>
                            <h6 class="mb-1">{{ round($avgPrestasiPerSiswa ?? 0, 1) }}</h6>
                            <small class="text-muted">Avg/Siswa</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="text-center">
                            <div class="avatar avatar-sm bg-info bg-gradient mx-auto mb-2">
                                <span class="iconify text-white" data-icon="mdi:account-check" data-width="16"></span>
                            </div>
                            <h6 class="mb-1">{{ $participationRate ?? 0 }}%</h6>
                            <small class="text-muted">Partisipasi</small>
                        </div>
                    </div>
                </div>
                <div class="mt-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <small class="text-muted">Tingkat Validasi</small>
                        <small class="fw-medium">{{ $prestasiValidationRate ?? 100 }}%</small>
                    </div>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar bg-success" style="width: {{ $prestasiValidationRate ?? 100 }}%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Dashboard Analytics Implementation with Enhanced Error Handling
document.addEventListener('DOMContentLoaded', function() {
    initializeDashboard();
});

function initializeDashboard() {
    // Show global loading overlay
    showGlobalLoading();
    
    // Load analytics data with timeout
    const fetchTimeout = setTimeout(() => {
        hideGlobalLoading();
        showOfflineMode();
    }, 10000); // 10 second timeout
    
    fetch('{{ route("admin.dashboard.analytics") }}', {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        clearTimeout(fetchTimeout);
        if (!response.ok) throw new Error(`HTTP ${response.status}`);
        return response.json();
    })
    .then(data => {
        hideGlobalLoading();
        renderAllCharts(data);
    })
    .catch(error => {
        clearTimeout(fetchTimeout);
        console.error('Dashboard analytics error:', error);
        hideGlobalLoading();
        showOfflineMode();
    });
}

function renderAllCharts(data) {
    try {
        renderMultiYearChart(data.multiYear || {});
        renderCompetitionLevelChart(data.competitionLevel || {});
        renderCategoryPerformanceChart(data.categoryPerformance || {});
        renderMonthlyTrendChart(data.monthlyTrend || {});
        renderTopStudentsList(data.topStudents || []);
        renderAcademicVsNonAcademicChart(data.academicVsNonAcademic || {});
    } catch (error) {
        console.error('Chart rendering error:', error);
    }
}

function renderMultiYearChart(data) {
    const container = document.querySelector("#multiYearChart");
    if (!container) return;
    
    if (!data.series || !data.categories || data.series.length === 0) {
        container.innerHTML = `
            <div class="d-flex flex-column align-items-center justify-content-center h-100 text-center">
                <span class="iconify text-muted mb-2" data-icon="mdi:chart-timeline-variant" data-width="48" data-height="48"></span>
                <h6 class="text-muted mb-1">Belum ada data</h6>
                <small class="text-muted">Data prestasi multi-tahun akan muncul di sini</small>
            </div>`;
        return;
    }
    
    const options = {
        series: data.series,
        chart: {
            height: 300,
            type: 'line',
            zoom: { enabled: false },
            toolbar: { show: false }
        },
        dataLabels: { 
            enabled: true,
            style: { fontSize: '11px' }
        },
        stroke: {
            width: 3,
            curve: 'smooth'
        },
        markers: {
            size: 5,
            strokeColors: '#fff',
            strokeWidth: 2
        },
        xaxis: {
            categories: data.categories,
            labels: { style: { fontSize: '11px' } }
        },
        yaxis: {
            labels: { style: { fontSize: '11px' } }
        },
        legend: {
            position: 'bottom',
            fontSize: '12px',
            height: 40
        },
        colors: ['#696cff'], // Single color for total prestasi only
        grid: {
            borderColor: '#f1f1f1',
            strokeDashArray: 3
        }
    };
    
    new ApexCharts(container, options).render();
}

function renderCompetitionLevelChart(data) {
    const container = document.querySelector("#competitionLevelChart");
    if (!container) return;
    
    if (!data.series || !data.labels || data.series.length === 0) {
        container.innerHTML = `
            <div class="d-flex flex-column align-items-center justify-content-center h-100 text-center">
                <span class="iconify text-muted mb-2" data-icon="mdi:chart-donut" data-width="48" data-height="48"></span>
                <h6 class="text-muted mb-1">Belum ada data</h6>
                <small class="text-muted">Distribusi tingkat kompetisi akan muncul di sini</small>
            </div>`;
        return;
    }
    
    const options = {
        series: data.series,
        chart: {
            type: 'donut',
            height: 250
        },
        labels: data.labels,
        colors: ['#696cff', '#71dd37', '#ffab00', '#ff6384', '#8663ff'],
        legend: {
            position: 'bottom',
            fontSize: '11px',
            height: 60
        },
        plotOptions: {
            pie: {
                donut: {
                    size: '70%',
                    labels: {
                        show: true,
                        total: {
                            show: true,
                            fontSize: '14px',
                            color: '#666'
                        }
                    }
                }
            }
        },
        dataLabels: {
            enabled: true,
            style: { fontSize: '11px' }
        }
    };
    
    new ApexCharts(container, options).render();
}


function renderMonthlyTrendChart(data) {
    const container = document.querySelector("#monthlyTrendChart");
    if (!container) return;
    
    if (!data.series || !data.categories || data.categories.length === 0) {
        container.innerHTML = `
            <div class="d-flex flex-column align-items-center justify-content-center h-100 text-center">
                <span class="iconify text-muted mb-2" data-icon="mdi:chart-areaspline" data-width="48" data-height="48"></span>
                <h6 class="text-muted mb-1">Belum ada data</h6>
                <small class="text-muted">Tren bulanan akan muncul di sini</small>
            </div>`;
        return;
    }
    
    const options = {
        series: [{
            name: 'Prestasi',
            data: data.series
        }],
        chart: {
            type: 'area',
            height: 250,
            zoom: { enabled: false },
            toolbar: { show: false }
        },
        dataLabels: { 
            enabled: true,
            style: { fontSize: '11px' }
        },
        stroke: {
            curve: 'smooth',
            width: 3
        },
        fill: {
            type: 'gradient',
            gradient: {
                shadeIntensity: 1,
                inverseColors: false,
                opacityFrom: 0.4,
                opacityTo: 0.1
            }
        },
        xaxis: {
            categories: data.categories,
            labels: { style: { fontSize: '11px' } }
        },
        yaxis: {
            labels: { style: { fontSize: '11px' } }
        },
        colors: ['#ffab00'],
        grid: {
            borderColor: '#f1f1f1',
            strokeDashArray: 3
        }
    };
    
    new ApexCharts(container, options).render();
}

function renderTopStudentsList(data) {
    const container = document.getElementById('topStudentsList');
    if (!container) return;
    
    if (!data || data.length === 0) {
        container.innerHTML = `
            <div class="d-flex flex-column align-items-center justify-content-center h-100 text-center py-4">
                <span class="iconify text-muted mb-2" data-icon="mdi:account-star" data-width="48" data-height="48"></span>
                <h6 class="text-muted mb-1">Belum ada data</h6>
                <small class="text-muted">Top siswa akan muncul di sini</small>
            </div>`;
        return;
    }
    
    let html = '';
    data.slice(0, 10).forEach((student, index) => {
        const badgeColor = index < 3 ? 'primary' : 'secondary';
        const medal = index === 0 ? '🏆' : (index === 1 ? '🥈' : (index === 2 ? '🥉' : ''));
        
        html += `
            <div class="d-flex align-items-center mb-2 p-2 rounded" style="background: rgba(105, 108, 255, 0.05);">
                <div class="flex-shrink-0 me-2">
                    <span class="badge bg-${badgeColor} rounded-pill" style="width: 24px; height: 24px; font-size: 11px;">${index + 1}</span>
                </div>
                <div class="flex-grow-1">
                    <h6 class="mb-0" style="font-size: 13px;">${medal} ${student.nama}</h6>
                    <small class="text-muted" style="font-size: 11px;">${student.kelas}</small>
                </div>
                <div class="flex-shrink-0">
                    <span class="badge bg-success" style="font-size: 10px;">${student.total_prestasi}</span>
                </div>
            </div>
        `;
    });
    
    container.innerHTML = html;
}

function renderCategoryPerformanceChart(data) {
    const container = document.querySelector("#categoryPerformanceChart");
    if (!container) return;
    
    if (!data.series || !data.labels || data.series.length === 0) {
        container.innerHTML = `
            <div class="d-flex flex-column align-items-center justify-content-center h-100 text-center">
                <span class="iconify text-muted mb-2" data-icon="mdi:chart-pie" data-width="48" data-height="48"></span>
                <h6 class="text-muted mb-1">Belum ada data</h6>
                <small class="text-muted">Performance kategori akan muncul di sini</small>
            </div>`;
        return;
    }
    
    const options = {
        series: data.series,
        chart: {
            type: 'pie',
            height: 250
        },
        labels: data.labels,
        colors: ['#71dd37', '#696cff', '#ffab00', '#ff6384', '#8663ff', '#03c3ec', '#ffc107'],
        legend: {
            position: 'bottom',
            fontSize: '11px',
            height: 60
        },
        dataLabels: {
            enabled: true,
            style: { fontSize: '10px' }
        },
        plotOptions: {
            pie: {
                expandOnClick: false
            }
        }
    };
    
    new ApexCharts(container, options).render();
}


function renderAcademicVsNonAcademicChart(data) {
    const container = document.querySelector("#academicVsNonAcademicChart");
    if (!container) return;
    
    if (!data.series || !data.categories || data.categories.length === 0) {
        container.innerHTML = `
            <div class="d-flex flex-column align-items-center justify-content-center h-100 text-center">
                <span class="iconify text-muted mb-2" data-icon="mdi:chart-bar-stacked" data-width="48" data-height="48"></span>
                <h6 class="text-muted mb-1">Belum ada data</h6>
                <small class="text-muted">Perbandingan akademik vs non-akademik akan muncul di sini</small>
            </div>`;
        return;
    }
    
    const options = {
        series: data.series,
        chart: {
            type: 'bar',
            height: 250,
            stacked: true,
            toolbar: { show: false }
        },
        plotOptions: {
            bar: {
                horizontal: false,
                columnWidth: '60%',
                borderRadius: 4
            }
        },
        xaxis: {
            categories: data.categories,
            labels: { style: { fontSize: '11px' } }
        },
        yaxis: {
            labels: { style: { fontSize: '11px' } }
        },
        dataLabels: {
            enabled: true,
            style: { fontSize: '10px' }
        },
        fill: {
            opacity: 0.9
        },
        colors: ['#696cff', '#71dd37'],
        legend: {
            position: 'bottom',
            fontSize: '12px',
            height: 40
        },
        grid: {
            borderColor: '#f1f1f1',
            strokeDashArray: 3
        }
    };
    
    new ApexCharts(container, options).render();
}

// Helper Functions
function showGlobalLoading() {
    const overlay = document.getElementById('loadingOverlay');
    if (overlay) overlay.classList.remove('d-none');
}

function hideGlobalLoading() {
    const overlay = document.getElementById('loadingOverlay');
    if (overlay) overlay.classList.add('d-none');
}

function showOfflineMode() {
    // Show offline mode message for all chart containers
    const chartIds = ['multiYearChart', 'competitionLevelChart', 'categoryPerformanceChart', 'monthlyTrendChart', 'academicVsNonAcademicChart'];
    
    chartIds.forEach(id => {
        const container = document.getElementById(id);
        if (container) {
            container.innerHTML = `
                <div class="d-flex flex-column align-items-center justify-content-center h-100 text-center">
                    <span class="iconify text-danger mb-2" data-icon="mdi:wifi-off" data-width="48" data-height="48"></span>
                    <h6 class="text-danger mb-1">Tidak dapat memuat data</h6>
                    <small class="text-muted">Periksa koneksi internet Anda</small>
                    <button class="btn btn-sm btn-outline-primary mt-2" onclick="initializeDashboard()">Coba Lagi</button>
                </div>`;
        }
    });
    
    // Show error for top students list
    const studentsList = document.getElementById('topStudentsList');
    if (studentsList) {
        studentsList.innerHTML = `
            <div class="text-center py-4">
                <span class="iconify text-danger mb-2" data-icon="mdi:account-off" data-width="32" data-height="32"></span>
                <p class="text-danger mb-0">Data tidak dapat dimuat</p>
            </div>`;
    }
}

// Auto-refresh every 5 minutes
setInterval(() => {
    console.log('Auto-refreshing dashboard data...');
    initializeDashboard();
}, 300000);
</script>

@endsection