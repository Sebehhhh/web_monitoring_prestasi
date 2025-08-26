<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PrestasiSiswa;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Models\KategoriPrestasi;
use App\Models\Kelas;
use App\Models\Ekstrakurikuler;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use PDF;
use Excel;

class ReportController extends Controller
{
    public function index()
    {
        $tahunAjarans = TahunAjaran::orderBy('nama_tahun_ajaran', 'desc')->get();
        $categories = KategoriPrestasi::active()->get();
        $classes = Kelas::orderBy('nama_kelas')->get();
        
        return view('admin.reports.index', compact('tahunAjarans', 'categories', 'classes'));
    }

    public function generateStudentReport(Request $request)
    {
        $request->validate([
            'siswa_id' => 'required|exists:siswa,id',
            'tahun_ajaran_id' => 'nullable|exists:tahun_ajaran,id',
            'format' => 'required|in:pdf,excel,json'
        ]);

        try {
            $siswa = Siswa::with(['kelas', 'prestasi.kategoriPrestasi', 'prestasi.tingkatPenghargaan', 'prestasi.tahunAjaran'])
                ->findOrFail($request->siswa_id);

            $query = $siswa->prestasi()->with(['kategoriPrestasi', 'tingkatPenghargaan', 'tahunAjaran']);
            
            if ($request->tahun_ajaran_id) {
                $query->where('id_tahun_ajaran', $request->tahun_ajaran_id);
            }
            
            $prestasi = $query->where('status', 'diterima')
                ->orderBy('tanggal_prestasi', 'desc')
                ->get();

            // Calculate statistics
            $stats = [
                'total_prestasi' => $prestasi->count(),
                'prestasi_akademik' => $prestasi->where('kategoriPrestasi.jenis_prestasi', 'akademik')->count(),
                'prestasi_non_akademik' => $prestasi->where('kategoriPrestasi.jenis_prestasi', 'non_akademik')->count(),
                'tingkat_sekolah' => $prestasi->where('kategoriPrestasi.tingkat_kompetisi', 'sekolah')->count(),
                'tingkat_kabupaten' => $prestasi->where('kategoriPrestasi.tingkat_kompetisi', 'kabupaten')->count(),
                'tingkat_provinsi' => $prestasi->where('kategoriPrestasi.tingkat_kompetisi', 'provinsi')->count(),
                'tingkat_nasional' => $prestasi->where('kategoriPrestasi.tingkat_kompetisi', 'nasional')->count(),
                'tingkat_internasional' => $prestasi->where('kategoriPrestasi.tingkat_kompetisi', 'internasional')->count(),
            ];

            $data = [
                'siswa' => $siswa,
                'prestasi' => $prestasi,
                'stats' => $stats,
                'periode' => $request->tahun_ajaran_id ? TahunAjaran::find($request->tahun_ajaran_id) : null,
                'generated_at' => now()
            ];

            switch ($request->format) {
                case 'pdf':
                    return $this->generateStudentPDF($data);
                case 'excel':
                    return $this->generateStudentExcel($data);
                case 'json':
                    return response()->json($data);
                default:
                    return response()->json($data);
            }

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function generateClassReport(Request $request)
    {
        $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'tahun_ajaran_id' => 'nullable|exists:tahun_ajaran,id',
            'format' => 'required|in:pdf,excel,json'
        ]);

        try {
            $kelas = Kelas::with(['siswa.prestasi.kategoriPrestasi', 'siswa.prestasi.tingkatPenghargaan'])
                ->findOrFail($request->kelas_id);

            $tahunAjaran = $request->tahun_ajaran_id ? TahunAjaran::find($request->tahun_ajaran_id) : null;

            $siswaData = $kelas->siswa->map(function($siswa) use ($request) {
                $query = $siswa->prestasi()->with(['kategoriPrestasi', 'tingkatPenghargaan']);
                
                if ($request->tahun_ajaran_id) {
                    $query->where('id_tahun_ajaran', $request->tahun_ajaran_id);
                }
                
                $prestasi = $query->where('status', 'diterima')->get();
                
                return [
                    'siswa' => $siswa,
                    'total_prestasi' => $prestasi->count(),
                    'prestasi_akademik' => $prestasi->where('kategoriPrestasi.jenis_prestasi', 'akademik')->count(),
                    'prestasi_non_akademik' => $prestasi->where('kategoriPrestasi.jenis_prestasi', 'non_akademik')->count(),
                    'prestasi' => $prestasi
                ];
            });

            $classStats = [
                'total_siswa' => $kelas->siswa->count(),
                'total_prestasi' => $siswaData->sum('total_prestasi'),
                'avg_prestasi_per_siswa' => $kelas->siswa->count() > 0 ? 
                    round($siswaData->sum('total_prestasi') / $kelas->siswa->count(), 2) : 0,
                'siswa_berprestasi' => $siswaData->where('total_prestasi', '>', 0)->count()
            ];

            $data = [
                'kelas' => $kelas,
                'siswa_data' => $siswaData,
                'class_stats' => $classStats,
                'periode' => $tahunAjaran,
                'generated_at' => now()
            ];

            switch ($request->format) {
                case 'pdf':
                    return $this->generateClassPDF($data);
                case 'excel':
                    return $this->generateClassExcel($data);
                case 'json':
                    return response()->json($data);
                default:
                    return response()->json($data);
            }

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function generateSchoolReport(Request $request)
    {
        $request->validate([
            'tahun_ajaran_id' => 'nullable|exists:tahun_ajaran,id',
            'format' => 'required|in:pdf,excel,json'
        ]);

        try {
            $tahunAjaran = $request->tahun_ajaran_id ? TahunAjaran::find($request->tahun_ajaran_id) : null;
            
            // Overall statistics
            $query = PrestasiSiswa::where('status', 'diterima');
            if ($request->tahun_ajaran_id) {
                $query->where('id_tahun_ajaran', $request->tahun_ajaran_id);
            }

            $totalPrestasi = $query->count();
            
            // Achievement by category
            $prestasiByCategory = PrestasiSiswa::select(
                    'kategori_prestasi.nama_kategori',
                    'kategori_prestasi.jenis_prestasi',
                    'kategori_prestasi.bidang_prestasi',
                    'kategori_prestasi.tingkat_kompetisi',
                    DB::raw('COUNT(*) as total')
                )
                ->join('kategori_prestasi', 'prestasi_siswa.id_kategori_prestasi', '=', 'kategori_prestasi.id')
                ->where('prestasi_siswa.status', 'diterima')
                ->when($request->tahun_ajaran_id, function($q) use ($request) {
                    return $q->where('prestasi_siswa.id_tahun_ajaran', $request->tahun_ajaran_id);
                })
                ->groupBy('kategori_prestasi.id', 'kategori_prestasi.nama_kategori', 'kategori_prestasi.jenis_prestasi', 'kategori_prestasi.bidang_prestasi', 'kategori_prestasi.tingkat_kompetisi')
                ->orderBy('total', 'desc')
                ->get();

            // Achievement by class
            $prestasiByClass = Kelas::select(
                    'kelas.nama_kelas',
                    DB::raw('COUNT(prestasi_siswa.id) as total_prestasi'),
                    DB::raw('COUNT(DISTINCT siswa.id) as total_siswa')
                )
                ->leftJoin('siswa', 'kelas.id', '=', 'siswa.id_kelas')
                ->leftJoin('prestasi_siswa', function($join) use ($request) {
                    $join->on('siswa.id', '=', 'prestasi_siswa.id_siswa')
                         ->where('prestasi_siswa.status', '=', 'diterima');
                    if ($request->tahun_ajaran_id) {
                        $join->where('prestasi_siswa.id_tahun_ajaran', '=', $request->tahun_ajaran_id);
                    }
                })
                ->groupBy('kelas.id', 'kelas.nama_kelas')
                ->orderBy('total_prestasi', 'desc')
                ->get();

            // Monthly trends
            $monthlyTrends = PrestasiSiswa::select(
                    DB::raw('DATE_FORMAT(tanggal_prestasi, "%Y-%m") as bulan'),
                    DB::raw('COUNT(*) as total')
                )
                ->where('status', 'diterima')
                ->when($request->tahun_ajaran_id, function($q) use ($request) {
                    return $q->where('id_tahun_ajaran', $request->tahun_ajaran_id);
                })
                ->groupBy('bulan')
                ->orderBy('bulan')
                ->get();

            $data = [
                'periode' => $tahunAjaran,
                'total_prestasi' => $totalPrestasi,
                'prestasi_by_category' => $prestasiByCategory,
                'prestasi_by_class' => $prestasiByClass,
                'monthly_trends' => $monthlyTrends,
                'generated_at' => now()
            ];

            switch ($request->format) {
                case 'pdf':
                    return $this->generateSchoolPDF($data);
                case 'excel':
                    return $this->generateSchoolExcel($data);
                case 'json':
                    return response()->json($data);
                default:
                    return response()->json($data);
            }

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function generateMultiYearComparison(Request $request)
    {
        $request->validate([
            'years' => 'required|array|min:2',
            'years.*' => 'exists:tahun_ajaran,id',
            'format' => 'required|in:pdf,excel,json'
        ]);

        try {
            $tahunAjarans = TahunAjaran::whereIn('id', $request->years)
                ->orderBy('nama_tahun_ajaran')
                ->get();

            $comparisonData = [];
            
            foreach ($tahunAjarans as $tahun) {
                $totalPrestasi = PrestasiSiswa::where('id_tahun_ajaran', $tahun->id)
                    ->where('status', 'diterima')
                    ->count();
                
                $prestasiAkademik = PrestasiSiswa::whereHas('kategoriPrestasi', function($q) {
                    $q->where('jenis_prestasi', 'akademik');
                })->where('id_tahun_ajaran', $tahun->id)
                  ->where('status', 'diterima')
                  ->count();
                
                $prestasiNonAkademik = PrestasiSiswa::whereHas('kategoriPrestasi', function($q) {
                    $q->where('jenis_prestasi', 'non_akademik');
                })->where('id_tahun_ajaran', $tahun->id)
                  ->where('status', 'diterima')
                  ->count();

                // Competition level breakdown
                $competitionLevels = PrestasiSiswa::select('kategori_prestasi.tingkat_kompetisi', DB::raw('count(*) as total'))
                    ->join('kategori_prestasi', 'prestasi_siswa.id_kategori_prestasi', '=', 'kategori_prestasi.id')
                    ->where('prestasi_siswa.id_tahun_ajaran', $tahun->id)
                    ->where('prestasi_siswa.status', 'diterima')
                    ->groupBy('kategori_prestasi.tingkat_kompetisi')
                    ->pluck('total', 'tingkat_kompetisi')
                    ->toArray();

                $comparisonData[] = [
                    'tahun_ajaran' => $tahun->nama_tahun_ajaran,
                    'total_prestasi' => $totalPrestasi,
                    'prestasi_akademik' => $prestasiAkademik,
                    'prestasi_non_akademik' => $prestasiNonAkademik,
                    'competition_levels' => $competitionLevels
                ];
            }

            $data = [
                'comparison_data' => $comparisonData,
                'selected_years' => $tahunAjarans,
                'generated_at' => now()
            ];

            switch ($request->format) {
                case 'pdf':
                    return $this->generateComparisonPDF($data);
                case 'excel':
                    return $this->generateComparisonExcel($data);
                case 'json':
                    return response()->json($data);
                default:
                    return response()->json($data);
            }

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    private function generateStudentPDF($data)
    {
        $pdf = PDF::loadView('admin.reports.pdf.student', $data);
        return $pdf->download('laporan-prestasi-siswa-' . $data['siswa']->nama . '.pdf');
    }

    private function generateClassPDF($data)
    {
        $pdf = PDF::loadView('admin.reports.pdf.class', $data);
        return $pdf->download('laporan-prestasi-kelas-' . $data['kelas']->nama_kelas . '.pdf');
    }

    private function generateSchoolPDF($data)
    {
        $pdf = PDF::loadView('admin.reports.pdf.school', $data);
        $filename = 'laporan-prestasi-sekolah-' . ($data['periode'] ? $data['periode']->nama_tahun_ajaran : 'semua') . '.pdf';
        return $pdf->download($filename);
    }

    private function generateComparisonPDF($data)
    {
        $pdf = PDF::loadView('admin.reports.pdf.comparison', $data);
        return $pdf->download('laporan-perbandingan-tahunan.pdf');
    }

    private function generateStudentExcel($data)
    {
        return Excel::download(new class($data) implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings, \Maatwebsite\Excel\Concerns\WithStyles, \Maatwebsite\Excel\Concerns\WithTitle {
            private $data;
            
            public function __construct($data) {
                $this->data = $data;
            }
            
            public function collection() {
                return $this->data['prestasi']->map(function($prestasi, $index) {
                    return [
                        'No' => $index + 1,
                        'Nama Prestasi' => $prestasi->nama_prestasi,
                        'Kategori' => $prestasi->kategoriPrestasi->nama_kategori ?? '-',
                        'Jenis' => ucfirst($prestasi->kategoriPrestasi->jenis_prestasi ?? '-'),
                        'Tingkat Kompetisi' => ucfirst($prestasi->kategoriPrestasi->tingkat_kompetisi ?? '-'),
                        'Tingkat Penghargaan' => $prestasi->tingkatPenghargaan->tingkat ?? '-',
                        'Penyelenggara' => $prestasi->penyelenggara,
                        'Tanggal' => $prestasi->tanggal_prestasi ? \Carbon\Carbon::parse($prestasi->tanggal_prestasi)->format('d-m-Y') : '-',
                        'Status' => ucwords(str_replace('_', ' ', $prestasi->status)),
                        'Keterangan' => $prestasi->keterangan ?? '-'
                    ];
                });
            }
            
            public function headings(): array {
                return ['No', 'Nama Prestasi', 'Kategori', 'Jenis', 'Tingkat Kompetisi', 'Tingkat Penghargaan', 'Penyelenggara', 'Tanggal', 'Status', 'Keterangan'];
            }
            
            public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet) {
                return [
                    1 => ['font' => ['bold' => true]],
                ];
            }
            
            public function title(): string {
                return 'Prestasi ' . $this->data['siswa']->nama;
            }
        }, 'prestasi-' . $data['siswa']->nama . '.xlsx');
    }

    private function generateClassExcel($data)
    {
        return Excel::download(new class($data) implements \Maatwebsite\Excel\Concerns\WithMultipleSheets {
            private $data;
            
            public function __construct($data) {
                $this->data = $data;
            }
            
            public function sheets(): array {
                return [
                    'Ringkasan Kelas' => new class($this->data) implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings, \Maatwebsite\Excel\Concerns\WithStyles {
                        private $data;
                        
                        public function __construct($data) {
                            $this->data = $data;
                        }
                        
                        public function collection() {
                            return $this->data['siswa_data']->map(function($siswaData, $index) {
                                return [
                                    'No' => $index + 1,
                                    'Nama Siswa' => $siswaData['siswa']->nama,
                                    'NISN' => $siswaData['siswa']->nisn,
                                    'Total Prestasi' => $siswaData['total_prestasi'],
                                    'Prestasi Akademik' => $siswaData['prestasi_akademik'],
                                    'Prestasi Non-Akademik' => $siswaData['prestasi_non_akademik'],
                                    'Ranking' => $index + 1
                                ];
                            })->sortByDesc('Total Prestasi')->values();
                        }
                        
                        public function headings(): array {
                            return ['No', 'Nama Siswa', 'NISN', 'Total Prestasi', 'Prestasi Akademik', 'Prestasi Non-Akademik', 'Ranking'];
                        }
                        
                        public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet) {
                            return [
                                1 => ['font' => ['bold' => true]],
                            ];
                        }
                    },
                    'Detail Prestasi' => new class($this->data) implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings, \Maatwebsite\Excel\Concerns\WithStyles {
                        private $data;
                        
                        public function __construct($data) {
                            $this->data = $data;
                        }
                        
                        public function collection() {
                            $result = collect();
                            foreach($this->data['siswa_data'] as $siswaData) {
                                foreach($siswaData['prestasi'] as $prestasi) {
                                    $result->push([
                                        'Nama Siswa' => $siswaData['siswa']->nama,
                                        'Nama Prestasi' => $prestasi->nama_prestasi,
                                        'Kategori' => $prestasi->kategoriPrestasi->nama_kategori ?? '-',
                                        'Tingkat Penghargaan' => $prestasi->tingkatPenghargaan->tingkat ?? '-',
                                        'Tanggal' => $prestasi->tanggal_prestasi ? \Carbon\Carbon::parse($prestasi->tanggal_prestasi)->format('d-m-Y') : '-'
                                    ]);
                                }
                            }
                            return $result;
                        }
                        
                        public function headings(): array {
                            return ['Nama Siswa', 'Nama Prestasi', 'Kategori', 'Tingkat Penghargaan', 'Tanggal'];
                        }
                        
                        public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet) {
                            return [
                                1 => ['font' => ['bold' => true]],
                            ];
                        }
                    }
                ];
            }
        }, 'laporan-kelas-' . $data['kelas']->nama_kelas . '.xlsx');
    }

    private function generateSchoolExcel($data)
    {
        return Excel::download(new class($data) implements \Maatwebsite\Excel\Concerns\WithMultipleSheets {
            private $data;
            
            public function __construct($data) {
                $this->data = $data;
            }
            
            public function sheets(): array {
                return [
                    'Ringkasan' => new class($this->data) implements \Maatwebsite\Excel\Concerns\FromArray, \Maatwebsite\Excel\Concerns\WithStyles {
                        private $data;
                        
                        public function __construct($data) {
                            $this->data = $data;
                        }
                        
                        public function array(): array {
                            $periode = $this->data['periode'] ? $this->data['periode']->nama_tahun_ajaran : 'Semua Periode';
                            return [
                                ['LAPORAN PRESTASI SEKOLAH'],
                                ['Periode', $periode],
                                ['Generated', $this->data['generated_at']->format('d-m-Y H:i:s')],
                                [],
                                ['STATISTIK UMUM'],
                                ['Total Prestasi', $this->data['total_prestasi']],
                                [],
                            ];
                        }
                        
                        public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet) {
                            return [
                                1 => ['font' => ['bold' => true, 'size' => 16]],
                                5 => ['font' => ['bold' => true]],
                            ];
                        }
                    },
                    'Per Kategori' => new class($this->data) implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings, \Maatwebsite\Excel\Concerns\WithStyles {
                        private $data;
                        
                        public function __construct($data) {
                            $this->data = $data;
                        }
                        
                        public function collection() {
                            return collect($this->data['prestasi_by_category'])->map(function($item, $index) {
                                return [
                                    'No' => $index + 1,
                                    'Kategori' => $item->nama_kategori,
                                    'Jenis' => ucfirst($item->jenis_prestasi),
                                    'Tingkat Kompetisi' => ucfirst($item->tingkat_kompetisi),
                                    'Total' => $item->total
                                ];
                            });
                        }
                        
                        public function headings(): array {
                            return ['No', 'Kategori', 'Jenis', 'Tingkat Kompetisi', 'Total'];
                        }
                        
                        public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet) {
                            return [
                                1 => ['font' => ['bold' => true]],
                            ];
                        }
                    },
                    'Per Kelas' => new class($this->data) implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings, \Maatwebsite\Excel\Concerns\WithStyles {
                        private $data;
                        
                        public function __construct($data) {
                            $this->data = $data;
                        }
                        
                        public function collection() {
                            return collect($this->data['prestasi_by_class'])->map(function($item, $index) {
                                return [
                                    'No' => $index + 1,
                                    'Kelas' => $item->nama_kelas,
                                    'Total Siswa' => $item->total_siswa,
                                    'Total Prestasi' => $item->total_prestasi,
                                    'Rata-rata per Siswa' => $item->total_siswa > 0 ? round($item->total_prestasi / $item->total_siswa, 2) : 0
                                ];
                            });
                        }
                        
                        public function headings(): array {
                            return ['No', 'Kelas', 'Total Siswa', 'Total Prestasi', 'Rata-rata per Siswa'];
                        }
                        
                        public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet) {
                            return [
                                1 => ['font' => ['bold' => true]],
                            ];
                        }
                    }
                ];
            }
        }, 'laporan-sekolah-' . ($data['periode'] ? $data['periode']->nama_tahun_ajaran : 'semua') . '.xlsx');
    }

    private function generateComparisonExcel($data)
    {
        return Excel::download(new class($data) implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings, \Maatwebsite\Excel\Concerns\WithStyles, \Maatwebsite\Excel\Concerns\WithTitle {
            private $data;
            
            public function __construct($data) {
                $this->data = $data;
            }
            
            public function collection() {
                return collect($this->data['comparison_data'])->map(function($item, $index) {
                    return [
                        'No' => $index + 1,
                        'Tahun Ajaran' => $item['tahun_ajaran'],
                        'Total Prestasi' => $item['total_prestasi'],
                        'Akademik' => $item['prestasi_akademik'],
                        'Non-Akademik' => $item['prestasi_non_akademik'],
                        'Sekolah' => $item['competition_levels']['sekolah'] ?? 0,
                        'Kabupaten' => $item['competition_levels']['kabupaten'] ?? 0,
                        'Provinsi' => $item['competition_levels']['provinsi'] ?? 0,
                        'Nasional' => $item['competition_levels']['nasional'] ?? 0,
                        'Internasional' => $item['competition_levels']['internasional'] ?? 0
                    ];
                });
            }
            
            public function headings(): array {
                return ['No', 'Tahun Ajaran', 'Total Prestasi', 'Akademik', 'Non-Akademik', 'Sekolah', 'Kabupaten', 'Provinsi', 'Nasional', 'Internasional'];
            }
            
            public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet) {
                return [
                    1 => ['font' => ['bold' => true]],
                ];
            }
            
            public function title(): string {
                return 'Perbandingan Multi-Tahun';
            }
        }, 'laporan-perbandingan-tahunan.xlsx');
    }

    // Methods for prestasi siswa page exports
    public function generateStudentPortfolio(Siswa $siswa, Request $request)
    {
        $request->merge([
            'siswa_id' => $siswa->id,
            'format' => $request->get('format', 'pdf'),
            'tahun_ajaran_id' => $request->get('tahun_ajaran_id')
        ]);

        return $this->generateStudentReport($request);
    }

    public function exportClassReport(Request $request)
    {
        $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'format' => 'required|in:pdf,excel'
        ]);

        $request->merge([
            'tahun_ajaran_id' => $request->get('tahun_ajaran_id')
        ]);

        return $this->generateClassReport($request);
    }

    public function exportYearlyReport(Request $request)
    {
        $request->validate([
            'tahun_ajaran_id' => 'required|exists:tahun_ajaran,id',
            'format' => 'required|in:pdf,excel'
        ]);

        return $this->generateSchoolReport($request);
    }
}
