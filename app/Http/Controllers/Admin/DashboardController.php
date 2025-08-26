<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Siswa;
use App\Models\PrestasiSiswa;
use App\Models\Kelas;
use App\Models\Ekstrakurikuler;
use App\Models\ActivityLog;
use App\Models\TahunAjaran;
use App\Models\KategoriPrestasi;
use App\Models\KenaikanKelas;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function index()
    {
        try {
            // Get current academic year
            $currentTahunAjaran = TahunAjaran::getActiveTahunAjaran();
            
            // Statistik Pengguna (Fixed - show actual data)
            $totalSiswa = Siswa::where('status', 'aktif')->count();
            $totalGuru = User::where('role', 'guru')->count();
            $totalWaliKelas = User::where('role', 'wali_kelas')->count();
            $totalKepalaSekolah = User::where('role', 'kepala_sekolah')->count();
            $totalAdmin = User::where('role', 'admin')->count();

            // Enhanced Statistik Prestasi (Fixed - show actual data without strict year filtering)
            $totalPrestasi = PrestasiSiswa::count(); // All time
            $prestasiTervalidasi = PrestasiSiswa::where('status', 'diterima')->count();
            $prestasiPending = PrestasiSiswa::where('status', 'menunggu_validasi')->count();
            $prestasiDitolak = PrestasiSiswa::where('status', 'ditolak')->count();
            
            // Get current year prestasi with fallback to latest year with data
            $totalPrestasiCurrentYear = 0;
            if ($currentTahunAjaran) {
                $totalPrestasiCurrentYear = PrestasiSiswa::where('id_tahun_ajaran', $currentTahunAjaran->id)->count();
                
                // If current year has no data, get latest year with data
                if ($totalPrestasiCurrentYear == 0) {
                    $latestYearWithData = PrestasiSiswa::select('id_tahun_ajaran')
                        ->whereNotNull('id_tahun_ajaran')
                        ->groupBy('id_tahun_ajaran')
                        ->orderBy('id_tahun_ajaran', 'desc')
                        ->first();
                    
                    if ($latestYearWithData) {
                        $totalPrestasiCurrentYear = PrestasiSiswa::where('id_tahun_ajaran', $latestYearWithData->id_tahun_ajaran)->count();
                    }
                }
            }

            // Enhanced Prestasi by Type (Academic/Non-Academic) - Fixed
            $prestasiAkademikCount = PrestasiSiswa::whereHas('kategoriPrestasi', function($q) {
                $q->where('jenis_prestasi', 'akademik');
            })->where('status', 'diterima')->count();

            $prestasiNonAkademikCount = PrestasiSiswa::whereHas('kategoriPrestasi', function($q) {
                $q->where('jenis_prestasi', 'non_akademik');
            })->where('status', 'diterima')->count();

            // Enhanced KPI data for dashboard (Fixed)
            $prestasiNasional = PrestasiSiswa::whereHas('kategoriPrestasi', function($q) {
                    $q->where('tingkat_kompetisi', 'nasional');
                })
                ->where('status', 'diterima')
                ->count();
                
            $prestasiInternasional = PrestasiSiswa::whereHas('kategoriPrestasi', function($q) {
                    $q->where('tingkat_kompetisi', 'internasional');
                })
                ->where('status', 'diterima')
                ->count();
                
            $prestasiProgress = $totalPrestasi > 0 ? round(($prestasiTervalidasi / $totalPrestasi) * 100, 1) : 0;
            $avgPrestasiPerSiswa = $totalSiswa > 0 ? round($prestasiTervalidasi / $totalSiswa, 1) : 0;
            $siswaWithPrestasi = Siswa::whereHas('prestasi', function($q) {
                $q->where('status', 'diterima');
            })->count();
            $participationRate = $totalSiswa > 0 ? round(($siswaWithPrestasi / $totalSiswa) * 100, 1) : 0;
            $prestasiValidationRate = $totalPrestasi > 0 ? round(($prestasiTervalidasi / $totalPrestasi) * 100, 1) : 0;

            // Statistik Kelas with Class Progression Info
            $totalKelas = Kelas::count();
            $rataRataSiswaPerKelas = $totalKelas > 0 ? round($totalSiswa / $totalKelas, 1) : 0;
            
            // Class Progression Statistics
            $kelasXICount = Siswa::whereHas('kelas', function($q) {
                $q->where('nama_kelas', 'like', '%XI%');
            })->count();
            
            $kelasXIICount = Siswa::whereHas('kelas', function($q) {
                $q->where('nama_kelas', 'like', '%XII%');
            })->count();

            $kenaikanKelasStats = [];
            if ($currentTahunAjaran) {
                $kenaikanKelasStats = [
                    'pending' => KenaikanKelas::where('tahun_ajaran_id', $currentTahunAjaran->id)->where('status', 'pending')->count(),
                    'naik' => KenaikanKelas::where('tahun_ajaran_id', $currentTahunAjaran->id)->where('status', 'naik')->count(),
                    'tidak_naik' => KenaikanKelas::where('tahun_ajaran_id', $currentTahunAjaran->id)->where('status', 'tidak_naik')->count(),
                ];
            }

            // Enhanced Ekstrakurikuler Statistics (Fixed)
            $totalEkskul = Ekstrakurikuler::count();
            $totalAnggotaEkskul = DB::table('siswa_ekskul')
                ->where('status_keaktifan', 'aktif')
                ->count();

            // Enhanced Prestasi per Kategori with Competition Level
            $prestasiPerKategori = PrestasiSiswa::select(
                    'kategori_prestasi.nama_kategori as kategori', 
                    'kategori_prestasi.jenis_prestasi',
                    'kategori_prestasi.tingkat_kompetisi',
                    DB::raw('count(*) as total')
                )
                ->join('kategori_prestasi', 'prestasi_siswa.id_kategori_prestasi', '=', 'kategori_prestasi.id')
                ->where('prestasi_siswa.status', 'diterima')
                // Simplified: show all data for now
                ->groupBy('kategori_prestasi.id', 'kategori_prestasi.nama_kategori', 'kategori_prestasi.jenis_prestasi', 'kategori_prestasi.tingkat_kompetisi')
                ->orderBy('total', 'desc')
                ->get();

            // Competition Level Distribution (Fixed - show all data)
            $prestasiPerTingkatKompetisi = PrestasiSiswa::select(
                    'kategori_prestasi.tingkat_kompetisi as tingkat', 
                    DB::raw('count(*) as total')
                )
                ->join('kategori_prestasi', 'prestasi_siswa.id_kategori_prestasi', '=', 'kategori_prestasi.id')
                ->where('prestasi_siswa.status', 'diterima')
                ->groupBy('kategori_prestasi.tingkat_kompetisi')
                ->orderByRaw("FIELD(kategori_prestasi.tingkat_kompetisi, 'internasional', 'nasional', 'provinsi', 'kabupaten', 'sekolah')")
                ->get();

            // Traditional Prestasi per Tingkat (Award Level) - keeping for compatibility
            $prestasiPerTingkat = PrestasiSiswa::select('tingkat_penghargaan.tingkat as tingkat', DB::raw('count(*) as total'))
                ->join('tingkat_penghargaan', 'prestasi_siswa.id_tingkat_penghargaan', '=', 'tingkat_penghargaan.id')
                ->where('prestasi_siswa.status', 'diterima')
                // Simplified: show all data
                ->groupBy('tingkat_penghargaan.id', 'tingkat_penghargaan.tingkat')
                ->get();

            // Enhanced Prestasi per Bulan with Current Academic Year focus
            $prestasiPerBulan = PrestasiSiswa::select(
                    DB::raw('DATE_FORMAT(tanggal_prestasi, "%Y-%m") as bulan'),
                    DB::raw('count(*) as total')
                )
                ->where('prestasi_siswa.status', 'diterima')
                ->where('tanggal_prestasi', '>=', now()->subMonths(6)) // Simplified: last 6 months
                ->groupBy('bulan')
                ->orderBy('bulan')
                ->get();

            // Multi-year comparison data (last 3 years)
            $multiYearComparison = TahunAjaran::select('nama_tahun_ajaran')
                ->withCount(['prestasi as total_prestasi' => function($query) {
                    $query->where('status', 'diterima');
                }])
                ->orderBy('nama_tahun_ajaran', 'desc')
                ->limit(3)
                ->get();

            // Aktivitas Terbaru
            $aktivitasTerbaru = ActivityLog::with('user')
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

            // Prestasi Terbaru
            $prestasiTerbaru = PrestasiSiswa::with(['siswa', 'kategoriPrestasi', 'tingkatPenghargaan'])
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();

            // Top 5 Kelas dengan Prestasi Terbanyak
            $topKelasPrestasi = Kelas::select('kelas.nama_kelas', DB::raw('count(prestasi_siswa.id) as total_prestasi'))
                ->leftJoin('siswa', 'kelas.id', '=', 'siswa.id_kelas')
                ->leftJoin('prestasi_siswa', 'siswa.id', '=', 'prestasi_siswa.id_siswa')
                ->groupBy('kelas.id', 'kelas.nama_kelas')
                ->orderBy('total_prestasi', 'desc')
                ->limit(5)
                ->get();

            // Top 5 Ekstrakurikuler dengan Anggota Terbanyak
            $topEkskul = Ekstrakurikuler::select('ekstrakurikuler.nama', DB::raw('count(siswa_ekskul.id_siswa) as total_anggota'))
                ->leftJoin('siswa_ekskul', 'ekstrakurikuler.id', '=', 'siswa_ekskul.id_ekskul')
                ->groupBy('ekstrakurikuler.id', 'ekstrakurikuler.nama')
                ->orderBy('total_anggota', 'desc')
                ->limit(5)
                ->get();

            return view('admin.dashboard', compact(
                // User Statistics
                'totalSiswa',
                'totalGuru',
                'totalWaliKelas',
                'totalKepalaSekolah',
                'totalAdmin',
                
                // Enhanced Achievement Statistics
                'totalPrestasi',
                'totalPrestasiCurrentYear',
                'prestasiTervalidasi',
                'prestasiPending',
                'prestasiDitolak',
                'prestasiAkademikCount',
                'prestasiNonAkademikCount',
                'prestasiNasional',
                'prestasiInternasional',
                'prestasiProgress',
                'avgPrestasiPerSiswa',
                'participationRate',
                'prestasiValidationRate',
                
                // Class and Progression Statistics
                'totalKelas',
                'rataRataSiswaPerKelas',
                'kelasXICount',
                'kelasXIICount',
                'kenaikanKelasStats',
                
                // Extracurricular Statistics
                'totalEkskul',
                'totalAnggotaEkskul',
                
                // Enhanced Analytics Data
                'prestasiPerKategori',
                'prestasiPerTingkat',
                'prestasiPerTingkatKompetisi',
                'prestasiPerBulan',
                'multiYearComparison',
                
                // Activity and Recent Data
                'aktivitasTerbaru',
                'prestasiTerbaru',
                'topKelasPrestasi',
                'topEkskul',
                
                // Academic Year Context
                'currentTahunAjaran'
            ));

        } catch (\Exception $e) {
            Log::error('Dashboard Admin Error: ' . $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine());
            return view('admin.dashboard', [
                // User Statistics
                'totalSiswa' => 0,
                'totalGuru' => 0,
                'totalWaliKelas' => 0,
                'totalKepalaSekolah' => 0,
                'totalAdmin' => 0,
                
                // Enhanced Achievement Statistics
                'totalPrestasi' => 0,
                'totalPrestasiCurrentYear' => 0,
                'prestasiTervalidasi' => 0,
                'prestasiPending' => 0,
                'prestasiDitolak' => 0,
                'prestasiAkademikCount' => 0,
                'prestasiNonAkademikCount' => 0,
                'prestasiNasional' => 0,
                'prestasiInternasional' => 0,
                'prestasiProgress' => 0,
                'avgPrestasiPerSiswa' => 0,
                'participationRate' => 0,
                'prestasiValidationRate' => 0,
                
                // Class and Progression Statistics
                'totalKelas' => 0,
                'rataRataSiswaPerKelas' => 0,
                'kelasXICount' => 0,
                'kelasXIICount' => 0,
                'kenaikanKelasStats' => ['pending' => 0, 'naik' => 0, 'tidak_naik' => 0],
                
                // Extracurricular Statistics
                'totalEkskul' => 0,
                'totalAnggotaEkskul' => 0,
                
                // Enhanced Analytics Data
                'prestasiPerKategori' => collect(),
                'prestasiPerTingkat' => collect(),
                'prestasiPerTingkatKompetisi' => collect(),
                'prestasiPerBulan' => collect(),
                'multiYearComparison' => collect(),
                
                // Activity and Recent Data
                'aktivitasTerbaru' => collect(),
                'prestasiTerbaru' => collect(),
                'topKelasPrestasi' => collect(),
                'topEkskul' => collect(),
                
                // Academic Year Context
                'currentTahunAjaran' => null,
                'tahunAjaranAktif' => null
            ]);
        }
    }

    /**
     * Get comprehensive analytics data for enhanced dashboard
     */
    public function analytics()
    {
        try {
            $currentTahunAjaran = TahunAjaran::getActiveTahunAjaran();
            
            return response()->json([
                'multiYear' => $this->getMultiYearAnalytics(),
                'competitionLevel' => $this->getCompetitionLevelAnalytics($currentTahunAjaran),
                'topClasses' => $this->getTopClassAnalytics($currentTahunAjaran),
                'monthlyTrend' => $this->getMonthlyTrendAnalytics($currentTahunAjaran),
                'topStudents' => $this->getTopStudentAnalytics($currentTahunAjaran),
                'categoryPerformance' => $this->getCategoryPerformanceAnalytics($currentTahunAjaran),
                'extracurricularImpact' => $this->getExtracurricularImpactAnalytics($currentTahunAjaran),
                'academicVsNonAcademic' => $this->getAcademicVsNonAcademicAnalytics($currentTahunAjaran)
            ]);
        } catch (\Exception $e) {
            Log::error('Dashboard Analytics Error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to load analytics'], 500);
        }
    }

    private function getMultiYearAnalytics()
    {
        $years = TahunAjaran::select('nama_tahun_ajaran')
            ->distinct()
            ->orderBy('nama_tahun_ajaran', 'desc')
            ->limit(4)
            ->pluck('nama_tahun_ajaran');
            
        $categories = [];
        $totalSeries = [];
        
        foreach ($years as $year) {
            $categories[] = $year;
            
            // Simplified: Only get total prestasi per year
            $totalCount = PrestasiSiswa::whereHas('tahunAjaran', function($q) use ($year) {
                    $q->where('nama_tahun_ajaran', $year);
                })
                ->where('status', 'diterima')
                ->count();
            
            $totalSeries[] = $totalCount;
        }
        
        return [
            'categories' => array_reverse($categories),
            'series' => [
                ['name' => 'Total Prestasi', 'data' => array_reverse($totalSeries)]
            ]
        ];
    }

    private function getCompetitionLevelAnalytics($currentTahunAjaran)
    {
        // Fixed: Show all competition level data without strict year filtering
        $data = PrestasiSiswa::select(
                'kategori_prestasi.tingkat_kompetisi as tingkat', 
                DB::raw('count(*) as total')
            )
            ->join('kategori_prestasi', 'prestasi_siswa.id_kategori_prestasi', '=', 'kategori_prestasi.id')
            ->where('prestasi_siswa.status', 'diterima')
            ->whereNotNull('kategori_prestasi.tingkat_kompetisi')
            ->groupBy('kategori_prestasi.tingkat_kompetisi')
            ->orderByRaw("FIELD(kategori_prestasi.tingkat_kompetisi, 'internasional', 'nasional', 'provinsi', 'kabupaten', 'sekolah')")
            ->get();
            
        return [
            'labels' => $data->pluck('tingkat')->map(function($tingkat) {
                return ucfirst($tingkat ?: 'Lainnya');
            })->toArray(),
            'series' => $data->pluck('total')->toArray()
        ];
    }

    private function getTopClassAnalytics($currentTahunAjaran)
    {
        // Fixed: Show all class data without strict year filtering
        $data = Kelas::select('kelas.nama_kelas', DB::raw('count(prestasi_siswa.id) as total_prestasi'))
            ->leftJoin('siswa', 'kelas.id', '=', 'siswa.id_kelas')
            ->leftJoin('prestasi_siswa', function($join) {
                $join->on('siswa.id', '=', 'prestasi_siswa.id_siswa')
                     ->where('prestasi_siswa.status', '=', 'diterima');
            })
            ->groupBy('kelas.id', 'kelas.nama_kelas')
            ->orderBy('total_prestasi', 'desc')
            ->limit(10)
            ->get();
            
        return [
            'categories' => $data->pluck('nama_kelas')->toArray(),
            'series' => $data->pluck('total_prestasi')->toArray()
        ];
    }

    private function getMonthlyTrendAnalytics($currentTahunAjaran)
    {
        // Fixed: Show last 6 months data without strict year filtering
        $data = PrestasiSiswa::select(
                DB::raw('DATE_FORMAT(tanggal_prestasi, "%Y-%m") as bulan'),
                DB::raw('count(*) as total')
            )
            ->where('status', 'diterima')
            ->where('tanggal_prestasi', '>=', now()->subMonths(6))
            ->groupBy('bulan')
            ->orderBy('bulan', 'asc')
            ->get();
        
        return [
            'categories' => $data->pluck('bulan')->map(function($bulan) {
                return date('M Y', strtotime($bulan . '-01'));
            })->toArray(),
            'series' => $data->pluck('total')->toArray()
        ];
    }

    private function getTopStudentAnalytics($currentTahunAjaran)
    {
        // Fixed: Show all student data without strict year filtering
        $data = Siswa::select('siswa.nama', 'kelas.nama_kelas as kelas', DB::raw('count(prestasi_siswa.id) as total_prestasi'))
            ->leftJoin('kelas', 'siswa.id_kelas', '=', 'kelas.id')
            ->leftJoin('prestasi_siswa', function($join) {
                $join->on('siswa.id', '=', 'prestasi_siswa.id_siswa')
                     ->where('prestasi_siswa.status', '=', 'diterima');
            })
            ->groupBy('siswa.id', 'siswa.nama', 'kelas.nama_kelas')
            ->orderBy('total_prestasi', 'desc')
            ->limit(10)
            ->get();
            
        return $data->toArray();
    }

    private function getCategoryPerformanceAnalytics($currentTahunAjaran)
    {
        // Fixed: Show all category data without strict year filtering
        $data = KategoriPrestasi::select('kategori_prestasi.nama_kategori', DB::raw('count(prestasi_siswa.id) as total'))
            ->leftJoin('prestasi_siswa', function($join) {
                $join->on('kategori_prestasi.id', '=', 'prestasi_siswa.id_kategori_prestasi')
                     ->where('prestasi_siswa.status', '=', 'diterima');
            })
            ->groupBy('kategori_prestasi.id', 'kategori_prestasi.nama_kategori')
            ->orderBy('total', 'desc')
            ->limit(8)
            ->get();
            
        return [
            'labels' => $data->pluck('nama_kategori')->toArray(),
            'series' => $data->pluck('total')->toArray()
        ];
    }

    private function getExtracurricularImpactAnalytics($currentTahunAjaran)
    {
        $data = Ekstrakurikuler::select(
                'ekstrakurikuler.nama',
                DB::raw('count(distinct siswa_ekskul.id_siswa) as total_anggota'),
                DB::raw('count(prestasi_siswa.id) as total_prestasi')
            )
            ->leftJoin('siswa_ekskul', 'ekstrakurikuler.id', '=', 'siswa_ekskul.id_ekskul')
            ->leftJoin('siswa', 'siswa_ekskul.id_siswa', '=', 'siswa.id')
            ->leftJoin('prestasi_siswa', function($join) {
                $join->on('siswa.id', '=', 'prestasi_siswa.id_siswa')
                     ->where('prestasi_siswa.status', '=', 'diterima');
            })
            ->groupBy('ekstrakurikuler.id', 'ekstrakurikuler.nama')
            ->orderBy('total_prestasi', 'desc')
            ->limit(8)
            ->get();
            
        return [
            'categories' => $data->pluck('nama')->toArray(),
            'series' => [
                ['name' => 'Anggota', 'data' => $data->pluck('total_anggota')->toArray()],
                ['name' => 'Prestasi', 'data' => $data->pluck('total_prestasi')->toArray()]
            ]
        ];
    }

    private function getAcademicVsNonAcademicAnalytics($currentTahunAjaran)
    {
        $months = collect(range(0, 5))->map(function($i) {
            return now()->subMonths($i)->format('Y-m');
        })->reverse();
        
        $categories = [];
        $akademikData = [];
        $nonAkademikData = [];
        
        foreach ($months as $month) {
            $categories[] = date('M Y', strtotime($month . '-01'));
            
            // Fixed: Remove strict academic year filtering  
            $akademikCount = PrestasiSiswa::whereHas('kategoriPrestasi', function($q) {
                    $q->where('jenis_prestasi', 'akademik');
                })
                ->where('status', 'diterima')
                ->whereRaw('DATE_FORMAT(tanggal_prestasi, "%Y-%m") = ?', [$month])
                ->count();
            
            $nonAkademikCount = PrestasiSiswa::whereHas('kategoriPrestasi', function($q) {
                    $q->where('jenis_prestasi', 'non_akademik');
                })
                ->where('status', 'diterima')
                ->whereRaw('DATE_FORMAT(tanggal_prestasi, "%Y-%m") = ?', [$month])
                ->count();
            
            $akademikData[] = $akademikCount;
            $nonAkademikData[] = $nonAkademikCount;
        }
        
        return [
            'categories' => $categories,
            'series' => [
                ['name' => 'Akademik', 'data' => $akademikData],
                ['name' => 'Non-Akademik', 'data' => $nonAkademikData]
            ]
        ];
    }
}
