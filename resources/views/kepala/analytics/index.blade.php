@extends('layouts.app')

@section('title', 'Analytics Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row">
        <div class="col-12">
            <div class="page-header">
                <h2 class="page-title">
                    <i class="ti ti-chart-bar me-2"></i>Advanced Analytics Dashboard
                </h2>
                <div class="page-subtitle">Comprehensive achievement analytics and insights</div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <select class="form-select" id="academicYearFilter">
                                <option value="">All Academic Years</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" id="analysisType">
                                <option value="overview">School Overview</option>
                                <option value="multi-year">Multi-Year Comparison</option>
                                <option value="student">Individual Student</option>
                                <option value="extracurricular">Extracurricular Analysis</option>
                            </select>
                        </div>
                        <div class="col-md-3" id="studentSelectContainer" style="display: none;">
                            <select class="form-select" id="studentSelect">
                                <option value="">Select Student...</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-primary w-100" onclick="loadAnalysis()">
                                <i class="ti ti-refresh me-1"></i>Load Analysis
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Analytics Content -->
    <div id="analyticsContent">
        <!-- School Overview Section (Default) -->
        <div id="schoolOverview">
            <div class="row mb-4">
                <!-- Key Metrics Cards -->
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card card-metric">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-8">
                                    <div class="metric-value" id="totalAchievements">0</div>
                                    <div class="metric-label">Total Achievements</div>
                                </div>
                                <div class="col-4 text-end">
                                    <div class="metric-icon bg-primary">
                                        <i class="ti ti-trophy"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card card-metric">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-8">
                                    <div class="metric-value" id="totalStudents">0</div>
                                    <div class="metric-label">Active Students</div>
                                </div>
                                <div class="col-4 text-end">
                                    <div class="metric-icon bg-success">
                                        <i class="ti ti-users"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card card-metric">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-8">
                                    <div class="metric-value" id="currentYearAchievements">0</div>
                                    <div class="metric-label">This Year</div>
                                </div>
                                <div class="col-4 text-end">
                                    <div class="metric-icon bg-warning">
                                        <i class="ti ti-calendar-stats"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card card-metric">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-8">
                                    <div class="metric-value" id="averagePerStudent">0</div>
                                    <div class="metric-label">Avg per Student</div>
                                </div>
                                <div class="col-4 text-end">
                                    <div class="metric-icon bg-info">
                                        <i class="ti ti-user-star"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <!-- Monthly Trends Chart -->
                <div class="col-lg-8 mb-3">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Achievement Trends</h4>
                            <div class="card-actions">
                                <button class="btn btn-sm btn-outline-secondary" onclick="exportChart('monthlyTrends')">
                                    <i class="ti ti-download"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div id="monthlyTrendsChart" style="height: 300px;"></div>
                        </div>
                    </div>
                </div>

                <!-- Competition Level Distribution -->
                <div class="col-lg-4 mb-3">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Competition Levels</h4>
                        </div>
                        <div class="card-body">
                            <div id="competitionLevelChart" style="height: 300px;"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <!-- Category Performance -->
                <div class="col-lg-6 mb-3">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Performance by Category</h4>
                        </div>
                        <div class="card-body">
                            <div id="categoryPerformanceChart" style="height: 350px;"></div>
                        </div>
                    </div>
                </div>

                <!-- Top Performing Classes -->
                <div class="col-lg-6 mb-3">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Top Performing Classes</h4>
                        </div>
                        <div class="card-body">
                            <div id="topClassesChart" style="height: 350px;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Multi-Year Comparison Section -->
        <div id="multiYearComparison" style="display: none;">
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Multi-Year Achievement Comparison</h4>
                        </div>
                        <div class="card-body">
                            <div id="multiYearChart" style="height: 400px;"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-6 mb-3">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Competition Level Trends</h4>
                        </div>
                        <div class="card-body">
                            <div id="competitionTrendsChart" style="height: 350px;"></div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 mb-3">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Academic vs Non-Academic</h4>
                        </div>
                        <div class="card-body">
                            <div id="typeComparisonChart" style="height: 350px;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Individual Student Analysis -->
        <div id="studentAnalysis" style="display: none;">
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Student Performance Profile</h4>
                            <div id="studentInfo" class="text-muted"></div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-8">
                                    <div id="studentTimelineChart" style="height: 300px;"></div>
                                </div>
                                <div class="col-lg-4">
                                    <div id="studentCategoryChart" style="height: 300px;"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-6 mb-3">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Competition Level Distribution</h4>
                        </div>
                        <div class="card-body">
                            <div id="studentCompetitionChart" style="height: 250px;"></div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 mb-3">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Class Ranking</h4>
                        </div>
                        <div class="card-body">
                            <div id="classRankingTable"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Extracurricular Analysis -->
        <div id="extracurricularAnalysis" style="display: none;">
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Extracurricular Participation & Performance</h4>
                        </div>
                        <div class="card-body">
                            <div id="extracurricularOverviewChart" style="height: 400px;"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-6 mb-3">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Participation by Period</h4>
                        </div>
                        <div class="card-body">
                            <div id="participationPeriodChart" style="height: 300px;"></div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 mb-3">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Extracurricular Achievements</h4>
                        </div>
                        <div class="card-body">
                            <div id="extracurricularAchievementsTable"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div id="loadingOverlay" class="position-fixed top-0 start-0 w-100 h-100 align-items-center justify-content-center" style="background: rgba(0,0,0,0.5); z-index: 9999; display: none;">
        <div class="card">
            <div class="card-body text-center">
                <div class="spinner-border text-primary mb-3"></div>
                <h5>Loading Analytics...</h5>
                <p class="text-muted">Please wait while we process your data</p>
            </div>
        </div>
    </div>
</div>

<style>
.card-metric {
    border-left: 4px solid var(--bs-primary);
    transition: all 0.3s ease;
}

.card-metric:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.metric-value {
    font-size: 2rem;
    font-weight: bold;
    color: var(--bs-primary);
}

.metric-label {
    font-size: 0.875rem;
    color: var(--bs-secondary);
    font-weight: 500;
}

.metric-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
}

.page-header {
    margin-bottom: 2rem;
}

.page-title {
    margin: 0;
    color: var(--bs-dark);
    font-weight: 600;
}

.page-subtitle {
    color: var(--bs-secondary);
    margin-top: 0.25rem;
}

.card-actions .btn {
    margin-left: 0.5rem;
}

#loadingOverlay {
    backdrop-filter: blur(4px);
}

.chart-container {
    position: relative;
}
</style>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize the analytics dashboard
    initializeDashboard();
    
    // Load default analysis
    loadAnalysis();
    
    // Setup event listeners
    setupEventListeners();
});

function initializeDashboard() {
    // Load academic years
    fetch('{{ route("admin.tahun_ajaran.for_select") }}')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const select = document.getElementById('academicYearFilter');
                data.data.forEach(year => {
                    const option = document.createElement('option');
                    option.value = year.value;
                    option.textContent = year.label;
                    if (year.is_active) option.selected = true;
                    select.appendChild(option);
                });
            }
        });
    
    // Load students list
    fetch('{{ route("kepala.analytics.students_list") }}')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const select = document.getElementById('studentSelect');
                data.students.forEach(student => {
                    const option = document.createElement('option');
                    option.value = student.id;
                    option.textContent = `${student.nama} - ${student.kelas}`;
                    select.appendChild(option);
                });
            }
        });
}

function setupEventListeners() {
    // Analysis type change
    document.getElementById('analysisType').addEventListener('change', function() {
        const studentContainer = document.getElementById('studentSelectContainer');
        if (this.value === 'student') {
            studentContainer.style.display = 'block';
        } else {
            studentContainer.style.display = 'none';
        }
        
        // Hide all sections
        document.querySelectorAll('#analyticsContent > div').forEach(div => {
            div.style.display = 'none';
        });
    });
}

function showLoading() {
    const overlay = document.getElementById('loadingOverlay');
    overlay.style.display = 'flex';
    overlay.classList.add('d-flex');
}

function hideLoading() {
    const overlay = document.getElementById('loadingOverlay');
    overlay.style.display = 'none';
    overlay.classList.remove('d-flex');
}

function loadAnalysis() {
    const analysisType = document.getElementById('analysisType').value;
    const academicYear = document.getElementById('academicYearFilter').value;
    const studentId = document.getElementById('studentSelect').value;
    
    showLoading();
    
    // Hide all sections first
    document.querySelectorAll('#analyticsContent > div').forEach(div => {
        div.style.display = 'none';
    });
    
    switch(analysisType) {
        case 'overview':
            loadSchoolOverview(academicYear);
            break;
        case 'multi-year':
            loadMultiYearComparison();
            break;
        case 'student':
            if (studentId) {
                loadStudentAnalysis(studentId);
            } else {
                alert('Please select a student first');
                hideLoading();
            }
            break;
        case 'extracurricular':
            loadExtracurricularAnalysis(academicYear);
            break;
    }
}

function loadSchoolOverview(academicYear = null) {
    fetch('{{ route("kepala.analytics.school_performance") }}' + (academicYear ? `?academic_year=${academicYear}` : ''))
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                console.error('Error:', data.error);
                return;
            }
            
            // Update metrics
            document.getElementById('totalAchievements').textContent = data.overview.total_achievements;
            document.getElementById('totalStudents').textContent = data.overview.total_students;
            document.getElementById('currentYearAchievements').textContent = data.overview.current_year_achievements;
            document.getElementById('averagePerStudent').textContent = 
                data.overview.total_students > 0 ? 
                (data.overview.current_year_achievements / data.overview.total_students).toFixed(1) : '0';
            
            // Render charts
            renderMonthlyTrends(data.monthly_trends);
            renderCompetitionLevel(data.competition_level_achievements);
            renderCategoryPerformance(data.category_performance);
            renderTopClasses(data.top_classes);
            
            document.getElementById('schoolOverview').style.display = 'block';
            hideLoading();
        })
        .catch(error => {
            console.error('Error:', error);
            hideLoading();
        });
}

function loadMultiYearComparison() {
    fetch('{{ route("kepala.analytics.multi_year_comparison") }}')
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                console.error('Error:', data.error);
                return;
            }
            
            renderMultiYearChart(data.multiYearData);
            renderCompetitionTrends(data.competitionLevelData);
            renderTypeComparison(data.multiYearData);
            
            document.getElementById('multiYearComparison').style.display = 'block';
            hideLoading();
        })
        .catch(error => {
            console.error('Error:', error);
            hideLoading();
        });
}

function loadStudentAnalysis(studentId) {
    fetch(`{{ url('kepala/analytics/student-analysis') }}/${studentId}`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                console.error('Error:', data.error);
                return;
            }
            
            // Update student info
            document.getElementById('studentInfo').innerHTML = 
                `<strong>${data.student_info.nama}</strong> (${data.student_info.nisn}) - ${data.student_info.kelas || 'No Class'} | ${data.student_info.total_prestasi} Total Achievements`;
            
            renderStudentTimeline(data.achievement_timeline);
            renderStudentCategory(data.achievements_by_category);
            renderStudentCompetition(data.competition_level_distribution);
            renderClassRanking(data.class_ranking);
            
            document.getElementById('studentAnalysis').style.display = 'block';
            hideLoading();
        })
        .catch(error => {
            console.error('Error:', error);
            hideLoading();
        });
}

function loadExtracurricularAnalysis(academicYear = null) {
    fetch('{{ route("kepala.analytics.extracurricular_analysis") }}' + (academicYear ? `?academic_year=${academicYear}` : ''))
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                console.error('Error:', data.error);
                return;
            }
            
            renderExtracurricularOverview(data.ekstrakurikuler_stats);
            renderParticipationPeriod(data.participation_by_period);
            renderExtracurricularAchievements(data.ekstrakurikuler_achievements);
            
            document.getElementById('extracurricularAnalysis').style.display = 'block';
            hideLoading();
        })
        .catch(error => {
            console.error('Error:', error);
            hideLoading();
        });
}

// Chart rendering functions
function renderMonthlyTrends(data) {
    const options = {
        series: [{
            name: 'Achievements',
            data: data.map(item => ({
                x: item.bulan,
                y: parseInt(item.total)
            }))
        }],
        chart: {
            type: 'area',
            height: 300,
            toolbar: {
                show: false
            }
        },
        colors: ['#206bc4'],
        dataLabels: {
            enabled: false
        },
        stroke: {
            curve: 'smooth',
            width: 2
        },
        fill: {
            type: 'gradient',
            gradient: {
                shadeIntensity: 1,
                inverseColors: false,
                opacityFrom: 0.7,
                opacityTo: 0.1,
                stops: [0, 100]
            }
        },
        xaxis: {
            type: 'category',
            title: {
                text: 'Month'
            }
        },
        yaxis: {
            title: {
                text: 'Number of Achievements'
            }
        },
        tooltip: {
            shared: true,
            intersect: false
        }
    };
    
    const chart = new ApexCharts(document.querySelector("#monthlyTrendsChart"), options);
    chart.render();
}

function renderCompetitionLevel(data) {
    const options = {
        series: data.map(item => parseInt(item.total)),
        labels: data.map(item => item.tingkat_kompetisi.charAt(0).toUpperCase() + item.tingkat_kompetisi.slice(1)),
        chart: {
            type: 'donut',
            height: 300
        },
        colors: ['#206bc4', '#79a6dc', '#a4c1ec', '#d0ddf4', '#e8f2ff'],
        plotOptions: {
            pie: {
                donut: {
                    size: '70%',
                    labels: {
                        show: true,
                        total: {
                            show: true,
                            label: 'Total',
                            formatter: function (w) {
                                return w.globals.seriesTotals.reduce((a, b) => a + b, 0)
                            }
                        }
                    }
                }
            }
        },
        legend: {
            position: 'bottom'
        },
        responsive: [{
            breakpoint: 480,
            options: {
                chart: {
                    width: 200
                },
                legend: {
                    position: 'bottom'
                }
            }
        }]
    };
    
    const chart = new ApexCharts(document.querySelector("#competitionLevelChart"), options);
    chart.render();
}

function renderCategoryPerformance(data) {
    const categories = data.map(item => item.nama_kategori);
    const values = data.map(item => parseInt(item.total_prestasi));
    
    const options = {
        series: [{
            name: 'Achievements',
            data: values
        }],
        chart: {
            type: 'bar',
            height: 350,
            toolbar: {
                show: false
            }
        },
        colors: ['#206bc4'],
        plotOptions: {
            bar: {
                borderRadius: 4,
                horizontal: true,
            }
        },
        dataLabels: {
            enabled: false
        },
        xaxis: {
            categories: categories,
            title: {
                text: 'Number of Achievements'
            }
        }
    };
    
    const chart = new ApexCharts(document.querySelector("#categoryPerformanceChart"), options);
    chart.render();
}

function renderTopClasses(data) {
    const classes = data.map(item => item.nama_kelas);
    const values = data.map(item => parseInt(item.total_prestasi));
    
    const options = {
        series: [{
            name: 'Achievements',
            data: values
        }],
        chart: {
            type: 'column',
            height: 350,
            toolbar: {
                show: false
            }
        },
        colors: ['#28a745'],
        plotOptions: {
            bar: {
                borderRadius: 4,
                dataLabels: {
                    position: 'top'
                }
            }
        },
        dataLabels: {
            enabled: true,
            offsetY: -20,
            style: {
                fontSize: '12px',
                colors: ["#304758"]
            }
        },
        xaxis: {
            categories: classes,
            title: {
                text: 'Classes'
            }
        },
        yaxis: {
            title: {
                text: 'Total Achievements'
            }
        }
    };
    
    const chart = new ApexCharts(document.querySelector("#topClassesChart"), options);
    chart.render();
}

// Multi-year comparison charts
function renderMultiYearChart(data) {
    const years = data.map(item => item.tahun);
    const totalAchievements = data.map(item => parseInt(item.total));
    const academicAchievements = data.map(item => parseInt(item.akademik));
    const nonAcademicAchievements = data.map(item => parseInt(item.non_akademik));
    
    const options = {
        series: [
            {
                name: 'Total Achievements',
                data: totalAchievements
            },
            {
                name: 'Academic',
                data: academicAchievements
            },
            {
                name: 'Non-Academic',
                data: nonAcademicAchievements
            }
        ],
        chart: {
            type: 'line',
            height: 400,
            toolbar: {
                show: true
            }
        },
        colors: ['#206bc4', '#28a745', '#fd7e14'],
        stroke: {
            curve: 'smooth',
            width: 3
        },
        markers: {
            size: 6
        },
        xaxis: {
            categories: years,
            title: {
                text: 'Academic Year'
            }
        },
        yaxis: {
            title: {
                text: 'Number of Achievements'
            }
        },
        legend: {
            position: 'top'
        },
        tooltip: {
            shared: true,
            intersect: false
        }
    };
    
    const chart = new ApexCharts(document.querySelector("#multiYearChart"), options);
    chart.render();
}

function renderCompetitionTrends(data) {
    const years = [...new Set(data.map(item => item.tahun))];
    const levels = [...new Set(data.map(item => item.tingkat_kompetisi))];
    
    const series = levels.map(level => ({
        name: level.charAt(0).toUpperCase() + level.slice(1),
        data: years.map(year => {
            const item = data.find(d => d.tahun === year && d.tingkat_kompetisi === level);
            return item ? parseInt(item.total) : 0;
        })
    }));
    
    const options = {
        series: series,
        chart: {
            type: 'line',
            height: 350,
            toolbar: {
                show: true
            }
        },
        colors: ['#206bc4', '#28a745', '#fd7e14', '#dc3545', '#6f42c1'],
        stroke: {
            curve: 'smooth',
            width: 2
        },
        markers: {
            size: 4
        },
        xaxis: {
            categories: years,
            title: {
                text: 'Academic Year'
            }
        },
        yaxis: {
            title: {
                text: 'Number of Achievements'
            }
        },
        legend: {
            position: 'bottom'
        },
        tooltip: {
            shared: true,
            intersect: false
        }
    };
    
    const chart = new ApexCharts(document.querySelector("#competitionTrendsChart"), options);
    chart.render();
}

function renderTypeComparison(data) {
    const years = data.map(item => item.tahun);
    const academicData = data.map(item => parseInt(item.akademik));
    const nonAcademicData = data.map(item => parseInt(item.non_akademik));
    
    const options = {
        series: [
            {
                name: 'Academic',
                data: academicData
            },
            {
                name: 'Non-Academic',
                data: nonAcademicData
            }
        ],
        chart: {
            type: 'bar',
            height: 350,
            stacked: true,
            toolbar: {
                show: false
            }
        },
        colors: ['#28a745', '#fd7e14'],
        plotOptions: {
            bar: {
                horizontal: false,
                columnWidth: '55%',
            }
        },
        dataLabels: {
            enabled: false
        },
        xaxis: {
            categories: years,
            title: {
                text: 'Academic Year'
            }
        },
        yaxis: {
            title: {
                text: 'Number of Achievements'
            }
        },
        legend: {
            position: 'top'
        },
        fill: {
            opacity: 1
        }
    };
    
    const chart = new ApexCharts(document.querySelector("#typeComparisonChart"), options);
    chart.render();
}

function renderStudentTimeline(data) {
    const options = {
        series: [{
            name: 'Achievements',
            data: data.map(item => ({
                x: item.bulan,
                y: parseInt(item.total)
            }))
        }],
        chart: {
            type: 'area',
            height: 300,
            toolbar: {
                show: false
            },
            sparkline: {
                enabled: false
            }
        },
        colors: ['#206bc4'],
        dataLabels: {
            enabled: false
        },
        stroke: {
            curve: 'smooth',
            width: 2
        },
        fill: {
            type: 'gradient',
            gradient: {
                shadeIntensity: 1,
                inverseColors: false,
                opacityFrom: 0.6,
                opacityTo: 0.1,
                stops: [0, 100]
            }
        },
        xaxis: {
            type: 'category',
            title: {
                text: 'Timeline'
            }
        },
        yaxis: {
            title: {
                text: 'Achievements'
            }
        },
        tooltip: {
            shared: true,
            intersect: false
        }
    };
    
    const chart = new ApexCharts(document.querySelector("#studentTimelineChart"), options);
    chart.render();
}

function renderStudentCategory(data) {
    const options = {
        series: data.map(item => parseInt(item.total)),
        labels: data.map(item => item.nama_kategori),
        chart: {
            type: 'pie',
            height: 300
        },
        colors: ['#206bc4', '#28a745', '#fd7e14', '#dc3545', '#6f42c1', '#20c997'],
        plotOptions: {
            pie: {
                size: 100
            }
        },
        legend: {
            position: 'bottom',
            fontSize: '12px'
        },
        responsive: [{
            breakpoint: 480,
            options: {
                chart: {
                    width: 200
                },
                legend: {
                    position: 'bottom'
                }
            }
        }]
    };
    
    const chart = new ApexCharts(document.querySelector("#studentCategoryChart"), options);
    chart.render();
}

function renderStudentCompetition(data) {
    const options = {
        series: data.map(item => parseInt(item.total)),
        labels: data.map(item => item.tingkat_kompetisi.charAt(0).toUpperCase() + item.tingkat_kompetisi.slice(1)),
        chart: {
            type: 'donut',
            height: 250
        },
        colors: ['#206bc4', '#79a6dc', '#a4c1ec', '#d0ddf4', '#e8f2ff'],
        plotOptions: {
            pie: {
                donut: {
                    size: '60%'
                }
            }
        },
        legend: {
            position: 'bottom',
            fontSize: '11px'
        }
    };
    
    const chart = new ApexCharts(document.querySelector("#studentCompetitionChart"), options);
    chart.render();
}

function renderClassRanking(data) {
    let tableHTML = '<div class="table-responsive">';
    tableHTML += '<table class="table table-striped table-hover">';
    tableHTML += '<thead><tr><th>Rank</th><th>Student</th><th>Class</th><th>Total</th></tr></thead>';
    tableHTML += '<tbody>';
    
    data.forEach((student, index) => {
        const rankClass = index < 3 ? 'text-success fw-bold' : '';
        tableHTML += `<tr>
            <td><span class="${rankClass}">${index + 1}</span></td>
            <td>${student.nama}</td>
            <td>${student.kelas || 'N/A'}</td>
            <td><span class="badge bg-primary">${student.total_prestasi}</span></td>
        </tr>`;
    });
    
    tableHTML += '</tbody></table></div>';
    
    document.getElementById('classRankingTable').innerHTML = tableHTML;
}

function renderExtracurricularOverview(data) {
    const ekstrakurikulerNames = data.map(item => item.nama_ekstrakurikuler);
    const participantCounts = data.map(item => parseInt(item.total_participants));
    const achievementCounts = data.map(item => parseInt(item.total_achievements));
    
    const options = {
        series: [
            {
                name: 'Participants',
                type: 'column',
                data: participantCounts
            },
            {
                name: 'Achievements',
                type: 'line',
                data: achievementCounts
            }
        ],
        chart: {
            type: 'line',
            height: 400,
            toolbar: {
                show: true
            }
        },
        colors: ['#206bc4', '#28a745'],
        stroke: {
            width: [0, 4]
        },
        plotOptions: {
            bar: {
                columnWidth: '50%'
            }
        },
        dataLabels: {
            enabled: true,
            enabledOnSeries: [1]
        },
        xaxis: {
            categories: ekstrakurikulerNames,
            title: {
                text: 'Extracurricular Activities'
            },
            labels: {
                rotate: -45
            }
        },
        yaxis: [
            {
                title: {
                    text: 'Number of Participants'
                }
            },
            {
                opposite: true,
                title: {
                    text: 'Number of Achievements'
                }
            }
        ],
        legend: {
            position: 'top'
        }
    };
    
    const chart = new ApexCharts(document.querySelector("#extracurricularOverviewChart"), options);
    chart.render();
}

function renderParticipationPeriod(data) {
    const periods = [...new Set(data.map(item => item.periode))];
    const ekstrakurikulerNames = [...new Set(data.map(item => item.nama_ekstrakurikuler))];
    
    const series = ekstrakurikulerNames.slice(0, 5).map(name => ({
        name: name,
        data: periods.map(period => {
            const item = data.find(d => d.periode === period && d.nama_ekstrakurikuler === name);
            return item ? parseInt(item.total_participants) : 0;
        })
    }));
    
    const options = {
        series: series,
        chart: {
            type: 'line',
            height: 300,
            toolbar: {
                show: false
            }
        },
        colors: ['#206bc4', '#28a745', '#fd7e14', '#dc3545', '#6f42c1'],
        stroke: {
            curve: 'smooth',
            width: 2
        },
        markers: {
            size: 4
        },
        xaxis: {
            categories: periods,
            title: {
                text: 'Academic Period'
            }
        },
        yaxis: {
            title: {
                text: 'Number of Participants'
            }
        },
        legend: {
            position: 'bottom'
        }
    };
    
    const chart = new ApexCharts(document.querySelector("#participationPeriodChart"), options);
    chart.render();
}

function renderExtracurricularAchievements(data) {
    let tableHTML = '<div class="table-responsive">';
    tableHTML += '<table class="table table-striped">';
    tableHTML += '<thead><tr><th>Extracurricular</th><th>Academic</th><th>Non-Academic</th><th>Total</th></tr></thead>';
    tableHTML += '<tbody>';
    
    data.forEach(item => {
        tableHTML += `<tr>
            <td><strong>${item.nama_ekstrakurikuler}</strong></td>
            <td><span class="badge bg-success">${item.akademik}</span></td>
            <td><span class="badge bg-warning">${item.non_akademik}</span></td>
            <td><span class="badge bg-primary">${item.total_achievements}</span></td>
        </tr>`;
    });
    
    tableHTML += '</tbody></table></div>';
    
    document.getElementById('extracurricularAchievementsTable').innerHTML = tableHTML;
}

function exportChart(chartId) {
    // Export functionality
    const chart = ApexCharts.getChartByID(chartId);
    if (chart) {
        chart.exportChart({
            type: 'png'
        });
    }
}
</script>
@endpush
@endsection