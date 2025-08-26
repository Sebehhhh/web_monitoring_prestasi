<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\EkstrakurikulerController;
use App\Http\Controllers\Admin\KategoriPrestasiController;
use App\Http\Controllers\Admin\KelasController;
use App\Http\Controllers\Admin\LogController;
use App\Http\Controllers\Admin\PrestasiSiswaController;
use App\Http\Controllers\Admin\SiswaController;
use App\Http\Controllers\Admin\TingkatPenghargaanController;
use App\Http\Controllers\Admin\SiswaEkskulController;
use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\TahunAjaranController;
use App\Http\Controllers\Admin\KenaikanKelasController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\NotificationController;

// Guru Controllers
use App\Http\Controllers\Guru\DashboardController as GuruDashboardController;
use App\Http\Controllers\Guru\SiswaController as GuruSiswaController;
use App\Http\Controllers\Guru\PrestasiSiswaController as GuruPrestasiSiswaController;
use App\Http\Controllers\Guru\EkstrakurikulerController as GuruEkstrakurikulerController;
use App\Http\Controllers\Guru\KategoriPrestasiController as GuruKategoriPrestasiController;
use App\Http\Controllers\Guru\TingkatPenghargaanController as GuruTingkatPenghargaanController;
use App\Http\Controllers\Guru\KelasController as GuruKelasController;
use App\Http\Controllers\Guru\AnalyticsController as GuruAnalyticsController;

// Kepala Sekolah Controllers
use App\Http\Controllers\Kepala\DashboardController as KepalaDashboardController;
use App\Http\Controllers\Kepala\PrestasiSiswaController as KepalaPrestasiSiswaController;
use App\Http\Controllers\Kepala\SiswaController as KepalaSiswaController;
use App\Http\Controllers\Kepala\KelasController as KepalaKelasController;
use App\Http\Controllers\Kepala\EkstrakurikulerController as KepalaEkstrakurikulerController;
use App\Http\Controllers\Kepala\UserController as KepalaUserController;
use App\Http\Controllers\Kepala\LogController as KepalaLogController;
use App\Http\Controllers\Kepala\NotifikasiController as KepalaNotifikasiController;
use App\Http\Controllers\Kepala\AnalyticsController as KepalaAnalyticsController;
use App\Http\Controllers\Kepala\ReportController as KepalaReportController;

// Wali Controllers
use App\Http\Controllers\Wali\DashboardController as WaliDashboardController;
use App\Http\Controllers\Wali\SiswaController as WaliSiswaController;
use App\Http\Controllers\Wali\PrestasiController as WaliPrestasiController;
use App\Http\Controllers\Wali\DokumenController as WaliDokumenController;

use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

// Login & Logout
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Notification routes (for all authenticated users)
Route::middleware(['auth'])->group(function () {
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::get('/notifications/count', [NotificationController::class, 'getCount'])->name('notifications.count');
});

// Admin only (prefix dan middleware)
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/analytics', [AdminDashboardController::class, 'analytics'])->name('dashboard.analytics');
    Route::resource('users', UserController::class);
    Route::get('logs', [LogController::class, 'index'])->name('logs.index');
    Route::resource('kelas', KelasController::class);
    Route::resource('siswa', SiswaController::class)->except('show');
    Route::resource('kategori_prestasi', KategoriPrestasiController::class);
    Route::resource('tingkat_penghargaan', TingkatPenghargaanController::class);
    Route::resource('ekstrakurikuler', EkstrakurikulerController::class);
    Route::resource('siswa_ekskul', SiswaEkskulController::class)->only(['index', 'store', 'destroy']);
    Route::get('siswa_ekskul/cetak', [SiswaEkskulController::class, 'cetak'])->name('siswa_ekskul.cetak');
    Route::resource('prestasi_siswa', PrestasiSiswaController::class)->except('show');
    
    // Validasi prestasi yang dibuat guru
    Route::post('prestasi_siswa/{prestasi_siswa}/validasi-guru', [PrestasiSiswaController::class, 'validasiGuru'])->name('prestasi_siswa.validasi_guru');

    Route::get('prestasi_siswa/cetak', [PrestasiSiswaController::class, 'cetak'])->name('prestasi_siswa.cetak');
    
    // Analytics endpoints for prestasi siswa page  
    Route::get('prestasi_siswa/analytics-data', [PrestasiSiswaController::class, 'getAnalyticsData'])->name('prestasi_siswa.analytics_data');
    Route::get('prestasi_siswa/student-analysis/{student}', [PrestasiSiswaController::class, 'getStudentAnalysis'])->name('prestasi_siswa.student_analysis');
    Route::get('prestasi_siswa/excel-report', [PrestasiSiswaController::class, 'generateExcelReport'])->name('prestasi_siswa.excel_report');
    
    // New enhanced export routes for prestasi siswa page
    Route::get('prestasi_siswa/portfolio/{siswa}', [ReportController::class, 'generateStudentPortfolio'])->name('prestasi_siswa.portfolio');
    Route::get('prestasi_siswa/export/class', [ReportController::class, 'exportClassReport'])->name('prestasi_siswa.export_class');
    Route::get('prestasi_siswa/export/yearly', [ReportController::class, 'exportYearlyReport'])->name('prestasi_siswa.export_yearly');
    Route::get('siswa/cetak', [SiswaController::class, 'cetak'])->name('siswa.cetak');
    
    // Advanced Analytics Routes
    Route::prefix('analytics')->name('analytics.')->group(function () {
        Route::get('/', [AnalyticsController::class, 'index'])->name('index');
        Route::get('/multi-year-comparison', [AnalyticsController::class, 'multiYearComparison'])->name('multi_year_comparison');
        Route::get('/student-analysis/{siswa}', [AnalyticsController::class, 'individualStudentAnalysis'])->name('student_analysis');
        Route::get('/school-performance', [AnalyticsController::class, 'schoolPerformanceAnalysis'])->name('school_performance');
        Route::get('/extracurricular-analysis', [AnalyticsController::class, 'extracurricularAnalysis'])->name('extracurricular_analysis');
        Route::get('/students-list', [AnalyticsController::class, 'getStudentsList'])->name('students_list');
    });

    // Advanced Reporting Routes
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::post('/student', [ReportController::class, 'generateStudentReport'])->name('student');
        Route::post('/class', [ReportController::class, 'generateClassReport'])->name('class');
        Route::post('/school', [ReportController::class, 'generateSchoolReport'])->name('school');
        Route::post('/multi-year-comparison', [ReportController::class, 'generateMultiYearComparison'])->name('multi_year_comparison');
    });

    // Academic Year Management Routes
    Route::resource('tahun_ajaran', TahunAjaranController::class);
    Route::post('tahun_ajaran/{tahun_ajaran}/set-active', [TahunAjaranController::class, 'setActive'])->name('tahun_ajaran.set_active');
    Route::post('tahun_ajaran/{tahun_ajaran}/change-semester', [TahunAjaranController::class, 'changeSemester'])->name('tahun_ajaran.change_semester');
    Route::get('tahun_ajaran-active', [TahunAjaranController::class, 'getActive'])->name('tahun_ajaran.get_active');
    Route::get('tahun_ajaran-select', [TahunAjaranController::class, 'getAllForSelect'])->name('tahun_ajaran.for_select');
    Route::post('tahun_ajaran/{tahun_ajaran}/duplicate', [TahunAjaranController::class, 'duplicateToNext'])->name('tahun_ajaran.duplicate');

    // Class Progression Routes (Simplified)
    Route::get('kenaikan_kelas', [KenaikanKelasController::class, 'index'])->name('kenaikan_kelas.index');
    Route::post('kenaikan_kelas/process', [KenaikanKelasController::class, 'processKenaikan'])->name('kenaikan_kelas.process');
    Route::post('kenaikan_kelas/process-all', [KenaikanKelasController::class, 'processAllKenaikan'])->name('kenaikan_kelas.process_all');
});

// Guru only (prefix dan middleware)
Route::middleware(['auth', 'role:guru'])->prefix('guru')->name('guru.')->group(function () {
    // Dashboard: Lihat statistik kelas/seksi yang diampu
    Route::get('/dashboard', [GuruDashboardController::class, 'index'])->name('dashboard');

    // Siswa: Lihat daftar siswa di kelasnya
    Route::get('siswa', [GuruSiswaController::class, 'index'])->name('siswa.index');
    Route::get('siswa/cetak', [GuruSiswaController::class, 'cetak'])->name('siswa.cetak');

    // Prestasi Siswa: Tambah, Edit, Lihat, Validasi, Upload Dokumen
    Route::get('prestasi_siswa', [GuruPrestasiSiswaController::class, 'index'])->name('prestasi_siswa.index');
    Route::get('prestasi_siswa/create', [GuruPrestasiSiswaController::class, 'create'])->name('prestasi_siswa.create');
    Route::post('prestasi_siswa', [GuruPrestasiSiswaController::class, 'store'])->name('prestasi_siswa.store');
    Route::get('prestasi_siswa/{prestasi_siswa}/edit', [GuruPrestasiSiswaController::class, 'edit'])->name('prestasi_siswa.edit');
    Route::put('prestasi_siswa/{prestasi_siswa}', [GuruPrestasiSiswaController::class, 'update'])->name('prestasi_siswa.update');
    // Route::get('prestasi_siswa/{prestasi_siswa}', [GuruPrestasiSiswaController::class, 'show'])->name('prestasi_siswa.show');
    // Upload dokumen bukti
    Route::post('prestasi_siswa/{prestasi_siswa}/upload', [GuruPrestasiSiswaController::class, 'uploadDokumen'])->name('prestasi_siswa.upload');
    // Validasi prestasi siswa (original)
    Route::post('prestasi_siswa/{prestasi_siswa}/validasi', [GuruPrestasiSiswaController::class, 'validasi'])->name('prestasi_siswa.validasi');
    
    // Enhanced validation workflow
    Route::get('prestasi_siswa/validation-dashboard', [GuruPrestasiSiswaController::class, 'validationDashboard'])->name('prestasi_siswa.validation_dashboard');
    Route::post('prestasi_siswa/batch-validation', [GuruPrestasiSiswaController::class, 'batchValidation'])->name('prestasi_siswa.batch_validation');
    Route::post('prestasi_siswa/{prestasi_siswa}/quick-validation', [GuruPrestasiSiswaController::class, 'quickValidation'])->name('prestasi_siswa.quick_validation');
    Route::post('prestasi_siswa/{prestasi_siswa}/enhanced-validation', [GuruPrestasiSiswaController::class, 'enhancedValidation'])->name('prestasi_siswa.enhanced_validation');
    Route::get('prestasi_siswa/{prestasi_siswa}/validation-history', [GuruPrestasiSiswaController::class, 'validationHistory'])->name('prestasi_siswa.validation_history');
    
    // Cetak rekap prestasi siswa di kelasnya
    Route::get('prestasi_siswa/cetak', [GuruPrestasiSiswaController::class, 'cetak'])->name('prestasi_siswa.cetak');

    // Ekstrakurikuler: Lihat info ekstrakurikuler, siswa peserta di kelasnya
    Route::get('ekstrakurikuler', [GuruEkstrakurikulerController::class, 'index'])->name('ekstrakurikuler.index');
    Route::get('ekstrakurikuler/{ekstrakurikuler}', [GuruEkstrakurikulerController::class, 'show'])->name('ekstrakurikuler.show');

    // Kategori Prestasi: Lihat daftar kategori
    Route::get('kategori_prestasi', [GuruKategoriPrestasiController::class, 'index'])->name('kategori_prestasi.index');

    // Tingkat Penghargaan: Lihat level penghargaan
    Route::get('tingkat_penghargaan', [GuruTingkatPenghargaanController::class, 'index'])->name('tingkat_penghargaan.index');

    // Kelas: Lihat detail kelas yang diampu
    Route::get('kelas', [GuruKelasController::class, 'index'])->name('kelas.index');
    Route::get('kelas/{kelas}', [GuruKelasController::class, 'show'])->name('kelas.show');
    
    // Analytics: Individual student analysis, class performance, progression tracking
    Route::get('analytics', [GuruAnalyticsController::class, 'index'])->name('analytics.index');
    Route::get('analytics/student/{siswa}', [GuruAnalyticsController::class, 'individualStudentAnalysis'])->name('analytics.student');
    Route::get('analytics/class-performance', [GuruAnalyticsController::class, 'classPerformanceAnalysis'])->name('analytics.class_performance');
    Route::get('analytics/student-progression/{siswa}', [GuruAnalyticsController::class, 'studentProgressionTracking'])->name('analytics.student_progression');
});

// Kepala Sekolah only (prefix dan middleware)
Route::middleware(['auth', 'role:kepala_sekolah'])->prefix('kepala')->name('kepala_sekolah.')->group(function () {
    // Dashboard: Lihat statistik global seluruh sekolah
    Route::get('/dashboard', [KepalaDashboardController::class, 'index'])->name('dashboard');

    // Rekap Prestasi: Lihat seluruh data prestasi siswa, filter berdasarkan kelas/kategori/tingkat/periode
    Route::get('prestasi_siswa', [KepalaPrestasiSiswaController::class, 'index'])->name('prestasi_siswa.index');
    Route::get('prestasi_siswa/cetak', [KepalaPrestasiSiswaController::class, 'cetak'])->name('prestasi_siswa.cetak');
    Route::post('prestasi_siswa/{prestasi_siswa}/validasi', [KepalaPrestasiSiswaController::class, 'validasi'])->name('prestasi_siswa.validasi');

    // Siswa: Lihat daftar seluruh siswa, detail prestasi, progres
    Route::get('siswa', [KepalaSiswaController::class, 'index'])->name('siswa.index');
    Route::get('siswa/cetak', [KepalaSiswaController::class, 'cetak'])->name('siswa.cetak');

    // Kelas: Lihat daftar kelas, wali kelas, dan rekap prestasi per kelas
    Route::get('kelas', [KepalaKelasController::class, 'index'])->name('kelas.index');
    Route::get('kelas/{kelas}', [KepalaKelasController::class, 'show'])->name('kelas.show');
    Route::get('kelas/{kelas}/prestasi', [KepalaKelasController::class, 'prestasiKelas'])->name('kelas.prestasi');

    // Ekstrakurikuler: Lihat daftar ekskul dan anggota, rekap prestasi ekskul
    Route::get('ekstrakurikuler', [KepalaEkstrakurikulerController::class, 'index'])->name('ekstrakurikuler.index');
    Route::get('ekstrakurikuler/{ekstrakurikuler}/prestasi', [KepalaEkstrakurikulerController::class, 'prestasiEkskul'])->name('ekstrakurikuler.prestasi');

    // Users: Melihat daftar user (admin, guru, siswa, wali), tanpa akses edit/delete
    Route::get('users', [KepalaUserController::class, 'index'])->name('users.index');

    // Log Aktivitas: Lihat history aktivitas penting terkait validasi/prestasi
    Route::get('logs', [KepalaLogController::class, 'index'])->name('logs.index');
    
    // Advanced Analytics: School-wide performance analytics and strategic insights
    Route::get('analytics', [KepalaAnalyticsController::class, 'index'])->name('analytics.index');
    Route::get('analytics/school-performance', [KepalaAnalyticsController::class, 'schoolPerformance'])->name('analytics.school_performance');
    Route::get('analytics/teacher-analysis', [KepalaAnalyticsController::class, 'teacherAnalysis'])->name('analytics.teacher_analysis');
    Route::get('analytics/department-analysis', [KepalaAnalyticsController::class, 'departmentAnalysis'])->name('analytics.department_analysis');
    Route::get('analytics/comparative-analysis', [KepalaAnalyticsController::class, 'comparativeAnalysis'])->name('analytics.comparative_analysis');
    Route::get('analytics/strategic-forecasting', [KepalaAnalyticsController::class, 'strategicForecasting'])->name('analytics.strategic_forecasting');
    
    // Strategic Planning Reports: Comprehensive reporting system for executive decision making
    Route::get('reports', [KepalaReportController::class, 'index'])->name('reports.index');
    Route::post('reports/annual-performance', [KepalaReportController::class, 'annualPerformanceReport'])->name('reports.annual_performance');
    Route::post('reports/strategic-planning', [KepalaReportController::class, 'strategicPlanningReport'])->name('reports.strategic_planning');
    Route::post('reports/resource-allocation', [KepalaReportController::class, 'resourceAllocationReport'])->name('reports.resource_allocation');
    Route::post('reports/teacher-performance', [KepalaReportController::class, 'teacherPerformanceReport'])->name('reports.teacher_performance');
    Route::post('reports/comparative-analysis', [KepalaReportController::class, 'comparativeAnalysisReport'])->name('reports.comparative_analysis');
    Route::post('reports/goal-tracking', [KepalaReportController::class, 'goalTrackingReport'])->name('reports.goal_tracking');
    Route::post('reports/executive-summary', [KepalaReportController::class, 'executiveSummaryReport'])->name('reports.executive_summary');
});

// Wali only (prefix dan middleware)
Route::middleware(['auth', 'role:wali'])->prefix('wali')->name('wali.')->group(function () {
    // Dashboard: Ringkasan prestasi anak-anak
    Route::get('/dashboard', [WaliDashboardController::class, 'index'])->name('dashboard');

    // Data Anak: Lihat biodata anak-anak
    Route::get('siswa', [WaliSiswaController::class, 'index'])->name('siswa.index');

    // Prestasi Anak: Lihat daftar prestasi anak-anak
    Route::get('prestasi', [WaliPrestasiController::class, 'index'])->name('prestasi.index');
    Route::get('prestasi/cetak', [WaliPrestasiController::class, 'cetak'])->name('prestasi.cetak');

    // Dokumen Prestasi: Lihat dan download dokumen
    Route::get('dokumen', [WaliDokumenController::class, 'index'])->name('dokumen.index');
    Route::get('dokumen/{id}/download', [WaliDokumenController::class, 'download'])->name('dokumen.download');
    
    // Child Progress Detailed Tracking: Comprehensive child analytics and monitoring
    Route::get('analytics/child/{childId}', [\App\Http\Controllers\Wali\AnalyticsController::class, 'childAnalysis'])->name('analytics.child_analysis');
    Route::get('analytics/engagement', [\App\Http\Controllers\Wali\AnalyticsController::class, 'engagementAnalytics'])->name('analytics.engagement');
    Route::get('analytics/family', [\App\Http\Controllers\Wali\AnalyticsController::class, 'familyAnalytics'])->name('analytics.family');
    Route::get('analytics/notifications', [\App\Http\Controllers\Wali\AnalyticsController::class, 'notificationAnalytics'])->name('analytics.notifications');
    Route::get('analytics/communication', [\App\Http\Controllers\Wali\AnalyticsController::class, 'communicationAnalytics'])->name('analytics.communication');
    
    // Parent-Teacher Communication: Comprehensive communication management system
    Route::get('communication', [\App\Http\Controllers\Wali\CommunicationController::class, 'index'])->name('communication.index');
    Route::post('communication/send-message', [\App\Http\Controllers\Wali\CommunicationController::class, 'sendMessage'])->name('communication.send_message');
    Route::post('communication/schedule-meeting', [\App\Http\Controllers\Wali\CommunicationController::class, 'scheduleMeeting'])->name('communication.schedule_meeting');
    Route::get('communication/conversation/{teacherId}', [\App\Http\Controllers\Wali\CommunicationController::class, 'getConversation'])->name('communication.conversation');
    Route::put('communication/notification-preferences', [\App\Http\Controllers\Wali\CommunicationController::class, 'updateNotificationPreferences'])->name('communication.notification_preferences');
    Route::get('communication/analytics', [\App\Http\Controllers\Wali\CommunicationController::class, 'analytics'])->name('communication.analytics');
    Route::post('communication/export-report', [\App\Http\Controllers\Wali\CommunicationController::class, 'exportReport'])->name('communication.export_report');
});

// Siswa only (prefix dan middleware)
Route::middleware(['auth', 'role:siswa'])->prefix('siswa')->name('siswa.')->group(function () {
    // Dashboard: Ringkasan prestasi & data diri
    Route::get('/dashboard', [\App\Http\Controllers\Siswa\DashboardController::class, 'index'])->name('dashboard');

    // Data Diri: Lihat & update biodata
    Route::get('profil', [\App\Http\Controllers\Siswa\ProfilController::class, 'index'])->name('profil.index');
    Route::put('profil', [\App\Http\Controllers\Siswa\ProfilController::class, 'update'])->name('profil.update');

    // Prestasi Siswa: Lihat, tambah, edit, hapus (semua modal, tidak ada show/create/edit route)
    Route::get('prestasi', [\App\Http\Controllers\Siswa\PrestasiController::class, 'index'])->name('prestasi.index');
    Route::post('prestasi', [\App\Http\Controllers\Siswa\PrestasiController::class, 'store'])->name('prestasi.store');
    Route::put('prestasi/{prestasi}', [\App\Http\Controllers\Siswa\PrestasiController::class, 'update'])->name('prestasi.update');
    Route::delete('prestasi/{prestasi}', [\App\Http\Controllers\Siswa\PrestasiController::class, 'destroy'])->name('prestasi.destroy');
    Route::get('prestasi/{prestasi}/cetak', [\App\Http\Controllers\Siswa\PrestasiController::class, 'cetakSurat'])->name('prestasi.cetakSurat');
    Route::post('prestasi/{prestasi}/submit', [\App\Http\Controllers\Siswa\PrestasiController::class, 'submit'])->name('prestasi.submit');

    // Dokumen Prestasi: Upload, hapus
    // Route::post('prestasi/{prestasi}/dokumen', [\App\Http\Controllers\Siswa\DokumenPrestasiController::class, 'store'])->name('prestasi.dokumen.store');
    // Route::delete('prestasi/{prestasi}/dokumen/{dokumen}', [\App\Http\Controllers\Siswa\DokumenPrestasiController::class, 'destroy'])->name('prestasi.dokumen.destroy');

    // Cetak rekap prestasi (filter di index, tidak ada menu terpisah)
    Route::get('prestasi/cetak', [\App\Http\Controllers\Siswa\PrestasiController::class, 'cetak'])->name('prestasi.cetak');
    
    // Personal Achievement Portfolio: Comprehensive portfolio management system
    Route::get('portfolio', [\App\Http\Controllers\Siswa\PortfolioController::class, 'index'])->name('portfolio.index');
    Route::get('portfolio/analytics', [\App\Http\Controllers\Siswa\PortfolioController::class, 'analytics'])->name('portfolio.analytics');
    Route::get('portfolio/timeline', [\App\Http\Controllers\Siswa\PortfolioController::class, 'timeline'])->name('portfolio.timeline');
    Route::get('portfolio/gallery', [\App\Http\Controllers\Siswa\PortfolioController::class, 'gallery'])->name('portfolio.gallery');
    Route::get('portfolio/skills-matrix', [\App\Http\Controllers\Siswa\PortfolioController::class, 'skillsMatrix'])->name('portfolio.skills_matrix');
    Route::get('portfolio/export', [\App\Http\Controllers\Siswa\PortfolioController::class, 'exportPortfolio'])->name('portfolio.export');
    Route::post('portfolio/share', [\App\Http\Controllers\Siswa\PortfolioController::class, 'sharePortfolio'])->name('portfolio.share');
    
    // Goals Setting and Monitoring: Personal goals management with progress tracking
    Route::get('goals', [\App\Http\Controllers\Siswa\GoalsController::class, 'index'])->name('goals.index');
    Route::post('goals', [\App\Http\Controllers\Siswa\GoalsController::class, 'store'])->name('goals.store');
    Route::put('goals/{goalId}/progress', [\App\Http\Controllers\Siswa\GoalsController::class, 'updateProgress'])->name('goals.update_progress');
    Route::post('goals/{goalId}/complete', [\App\Http\Controllers\Siswa\GoalsController::class, 'complete'])->name('goals.complete');
    Route::get('goals/analytics', [\App\Http\Controllers\Siswa\GoalsController::class, 'analytics'])->name('goals.analytics');
    Route::get('goals/suggestions', [\App\Http\Controllers\Siswa\GoalsController::class, 'suggestions'])->name('goals.suggestions');
    Route::post('goals/share', [\App\Http\Controllers\Siswa\GoalsController::class, 'share'])->name('goals.share');
    Route::get('goals/public/{token}', [\App\Http\Controllers\Siswa\GoalsController::class, 'publicView'])->name('goals.public');
});

// Jika nanti role 'wali', 'siswa', dll tinggal tambahkan group serupa:
# Route::middleware(['auth', 'role:wali'])->prefix('wali')->name('wali.')->group(function () {
#     Route::get('/dashboard', [WaliDashboardController::class, 'index'])->name('dashboard');
#     // dst...
# });
