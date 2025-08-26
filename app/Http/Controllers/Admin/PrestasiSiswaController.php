<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ActivityLogger;
use App\Http\Controllers\Controller;
use App\Models\PrestasiSiswa;
use App\Models\Siswa;
use App\Models\KategoriPrestasi;
use App\Models\TingkatPenghargaan;
use App\Models\Ekstrakurikuler;
use App\Models\Notification;
use App\Models\TahunAjaran;
use App\Models\Kelas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PrestasiReportExport;

class PrestasiSiswaController extends Controller
{
    public function index(Request $request)
    {
        $query = PrestasiSiswa::with(['siswa.kelas', 'kategori', 'tingkat', 'ekskul', 'creator', 'validator'])
            ->orderByDesc('created_at');

        // ADVANCED FILTERS
        
        // Filter by Tahun Ajaran
        if ($request->filled('tahun_ajaran')) {
            $query->where('id_tahun_ajaran', $request->tahun_ajaran);
        }
        
        // Filter by Kelas
        if ($request->filled('kelas')) {
            $query->whereHas('siswa', function($q) use ($request) {
                $q->where('id_kelas', $request->kelas);
            });
        }
        
        // Filter by Siswa
        if ($request->filled('siswa')) {
            $query->where('id_siswa', $request->siswa);
        }
        
        // Filter by Status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by Kategori
        if ($request->filled('kategori')) {
            $query->where('id_kategori_prestasi', $request->kategori);
        }
        
        // Filter by Tingkat
        if ($request->filled('tingkat')) {
            $query->where('id_tingkat_penghargaan', $request->tingkat);
        }
        
        // Filter by Ekstrakurikuler
        if ($request->filled('ekstrakurikuler')) {
            $query->where('id_ekskul', $request->ekstrakurikuler);
        }
        
        // Filter by Date Range
        if ($request->filled('from')) {
            $query->whereDate('tanggal_prestasi', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('tanggal_prestasi', '<=', $request->to);
        }

        $prestasi = $query->paginate(15)->appends($request->except('page'));

        // Data for dropdowns
        $siswa = Siswa::with('kelas')->get();
        $kategori = KategoriPrestasi::pluck('nama_kategori', 'id');
        $tingkat = TingkatPenghargaan::pluck('tingkat', 'id');
        $ekskul = Ekstrakurikuler::pluck('nama', 'id');
        
        // Additional data for advanced filtering
        $tahunAjarans = TahunAjaran::orderBy('nama_tahun_ajaran', 'desc')
                                  ->orderBy('semester', 'desc')
                                  ->get();
        $kelasList = Kelas::orderBy('nama_kelas')->get();
        $siswaList = Siswa::with('kelas')->orderBy('nama')->get();
        $ekstrakurikulers = Ekstrakurikuler::orderBy('nama')->get();

        return view('admin.prestasi_siswa.index', compact(
            'prestasi', 'siswa', 'kategori', 'tingkat', 'ekskul',
            'tahunAjarans', 'kelasList', 'siswaList', 'ekstrakurikulers'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_siswa'                => 'required|exists:siswa,id',
            'id_kategori_prestasi'    => 'required|exists:kategori_prestasi,id',
            'id_tingkat_penghargaan'  => 'required|exists:tingkat_penghargaan,id',
            'id_ekskul'               => 'nullable|exists:ekstrakurikuler,id',
            'nama_prestasi'           => 'required|string|max:100',
            'penyelenggara'           => 'required|string|max:100',
            'tanggal_prestasi'        => 'required|date',
            'keterangan'              => 'nullable|string|max:255',
            'dokumen_file'            => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048', // max 2MB
            'status'                  => 'required|in:draft,menunggu_validasi,diterima,ditolak',
            'rata_rata_nilai'         => 'nullable|numeric',
            'alasan_tolak'            => 'nullable|string|max:255',
        ]);

        // Upload file jika ada
        if ($request->hasFile('dokumen_file')) {
            $file = $request->file('dokumen_file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('uploads/sertifikat', $filename, 'public');
            $validated['dokumen_url'] = 'storage/' . $path;
        }

        $validated['created_by'] = Auth::id() ?? 1;

        // Set validasi jika status diterima/ditolak
        if (in_array($validated['status'], ['diterima', 'ditolak'])) {
            $validated['validated_by'] = Auth::id() ?? 1;
            $validated['validated_at'] = \Illuminate\Support\Carbon::now();
        } else {
            $validated['validated_by'] = null;
            $validated['validated_at'] = null;
        }

        $prestasi = PrestasiSiswa::create($validated);

        ActivityLogger::log('create', 'prestasi_siswa', 'Tambah prestasi: ' . $prestasi->nama_prestasi . ' oleh ' . ($prestasi->siswa->nama ?? '-'));
        return redirect()->route('admin.prestasi_siswa.index')->with('success', 'Prestasi siswa berhasil ditambah.');
    }

    public function update(Request $request, PrestasiSiswa $prestasi_siswa)
    {
        $validated = $request->validate([
            'id_siswa'                => 'required|exists:siswa,id',
            'id_kategori_prestasi'    => 'required|exists:kategori_prestasi,id',
            'id_tingkat_penghargaan'  => 'required|exists:tingkat_penghargaan,id',
            'id_ekskul'               => 'nullable|exists:ekstrakurikuler,id',
            'nama_prestasi'           => 'required|string|max:100',
            'penyelenggara'           => 'required|string|max:100',
            'tanggal_prestasi'        => 'required|date',
            'keterangan'              => 'nullable|string|max:255',
            'dokumen_file'            => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'status'                  => 'required|in:draft,menunggu_validasi,diterima,ditolak',
            'rata_rata_nilai'         => 'nullable|numeric',
            'alasan_tolak'            => 'nullable|string|max:255',
        ]);

        // Upload file baru jika ada
        if ($request->hasFile('dokumen_file')) {
            // Hapus file lama (opsional, supaya storage gak numpuk)
            if ($prestasi_siswa->dokumen_url && file_exists(public_path($prestasi_siswa->dokumen_url))) {
                @unlink(public_path($prestasi_siswa->dokumen_url));
            }

            $file = $request->file('dokumen_file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('uploads/sertifikat', $filename, 'public');
            $validated['dokumen_url'] = 'storage/' . $path;
        }

        // Update validasi hanya jika status diterima/ditolak
        if (in_array($validated['status'], ['diterima', 'ditolak'])) {
            $validated['validated_by'] = Auth::id() ?? 1;
            $validated['validated_at'] = \Illuminate\Support\Carbon::now();
        } else {
            $validated['validated_by'] = null;
            $validated['validated_at'] = null;
        }

        // Store old status for comparison
        $oldStatus = $prestasi_siswa->status;
        
        $prestasi_siswa->update($validated);

        // Send notification to parent if status changed to accepted or rejected
        if ($oldStatus !== $validated['status'] && in_array($validated['status'], ['diterima', 'ditolak']) && $prestasi_siswa->siswa->wali_id) {
            $statusText = $validated['status'] === 'diterima' ? 'diterima' : 'ditolak';
            $title = $validated['status'] === 'diterima' ? 'Prestasi Diterima' : 'Prestasi Ditolak';
            $message = "Prestasi '{$prestasi_siswa->nama_prestasi}' anak Anda {$prestasi_siswa->siswa->nama} telah {$statusText}.";
            
            if ($validated['status'] === 'ditolak' && !empty($validated['alasan_tolak'])) {
                $message .= " Alasan: {$validated['alasan_tolak']}";
            }
            
            Notification::createForParent(
                $prestasi_siswa->siswa->wali_id,
                $title,
                $message,
                [
                    'prestasi_id' => $prestasi_siswa->id,
                    'siswa_id' => $prestasi_siswa->siswa->id,
                    'siswa_nama' => $prestasi_siswa->siswa->nama,
                    'prestasi_nama' => $prestasi_siswa->nama_prestasi,
                    'action' => 'validated',
                    'status' => $validated['status']
                ]
            );
        }

        // Send notification to student if status changed to accepted or rejected
        if ($oldStatus !== $validated['status'] && in_array($validated['status'], ['diterima', 'ditolak']) && $prestasi_siswa->siswa->user_id) {
            $statusText = $validated['status'] === 'diterima' ? 'diterima' : 'ditolak';
            $title = $validated['status'] === 'diterima' ? 'Prestasi Diterima' : 'Prestasi Ditolak';
            $message = "Prestasi '{$prestasi_siswa->nama_prestasi}' yang Anda ajukan telah {$statusText}.";
            
            if ($validated['status'] === 'ditolak' && !empty($validated['alasan_tolak'])) {
                $message .= " Alasan: {$validated['alasan_tolak']}";
            }
            
            Notification::create([
                'user_id' => $prestasi_siswa->siswa->user_id,
                'title' => $title,
                'message' => $message,
                'data' => json_encode([
                    'prestasi_id' => $prestasi_siswa->id,
                    'prestasi_nama' => $prestasi_siswa->nama_prestasi,
                    'action' => 'validated',
                    'status' => $validated['status']
                ]),
                'read_at' => null
            ]);
        }

        ActivityLogger::log('update', 'prestasi_siswa', 'Update prestasi: ' . $prestasi_siswa->nama_prestasi . ' oleh ' . ($prestasi_siswa->siswa->nama ?? '-'));
        return redirect()->route('admin.prestasi_siswa.index')->with('success', 'Prestasi siswa berhasil diupdate.');
    }

    /**
     * Validasi prestasi siswa yang dibuat oleh guru (untuk akses admin).
     */
    public function validasiGuru(Request $request, PrestasiSiswa $prestasi_siswa)
    {
        // Validasi bahwa prestasi ini dibuat oleh guru (bukan admin)
        $creator = $prestasi_siswa->creator;
        if (!$creator || $creator->role !== 'guru') {
            return back()->with('error', 'Prestasi ini tidak dibuat oleh guru.');
        }

        $request->validate([
            'status' => 'required|in:diterima,ditolak',
            'alasan_tolak' => 'nullable|string|max:255'
        ]);
        
        // Store old status for comparison
        $oldStatus = $prestasi_siswa->status;
        
        $prestasi_siswa->update([
            'status' => $request->status,
            'alasan_tolak' => $request->alasan_tolak,
            'validated_at' => now(),
            'validated_by' => Auth::id()
        ]);
        
        // Send notification to parent if status changed to accepted or rejected
        if ($oldStatus !== $request->status && $prestasi_siswa->siswa->wali_id) {
            $statusText = $request->status === 'diterima' ? 'diterima' : 'ditolak';
            $title = $request->status === 'diterima' ? 'Prestasi Diterima' : 'Prestasi Ditolak';
            $message = "Prestasi '{$prestasi_siswa->nama_prestasi}' anak Anda {$prestasi_siswa->siswa->nama} telah {$statusText} oleh admin.";
            
            if ($request->status === 'ditolak' && !empty($request->alasan_tolak)) {
                $message .= " Alasan: {$request->alasan_tolak}";
            }
            
            Notification::createForParent(
                $prestasi_siswa->siswa->wali_id,
                $title,
                $message,
                [
                    'prestasi_id' => $prestasi_siswa->id,
                    'siswa_id' => $prestasi_siswa->siswa->id,
                    'siswa_nama' => $prestasi_siswa->siswa->nama,
                    'prestasi_nama' => $prestasi_siswa->nama_prestasi,
                    'action' => 'validated',
                    'status' => $request->status
                ]
            );
        }

        // Send notification to student if status changed to accepted or rejected
        if ($oldStatus !== $request->status && $prestasi_siswa->siswa->user_id) {
            $statusText = $request->status === 'diterima' ? 'diterima' : 'ditolak';
            $title = $request->status === 'diterima' ? 'Prestasi Diterima' : 'Prestasi Ditolak';
            $message = "Prestasi '{$prestasi_siswa->nama_prestasi}' yang diajukan telah {$statusText} oleh admin.";
            
            if ($request->status === 'ditolak' && !empty($request->alasan_tolak)) {
                $message .= " Alasan: {$request->alasan_tolak}";
            }
            
            Notification::create([
                'user_id' => $prestasi_siswa->siswa->user_id,
                'title' => $title,
                'message' => $message,
                'data' => json_encode([
                    'prestasi_id' => $prestasi_siswa->id,
                    'prestasi_nama' => $prestasi_siswa->nama_prestasi,
                    'action' => 'validated',
                    'status' => $request->status
                ]),
                'read_at' => null
            ]);
        }

        // Send notification to guru (creator) about validation result
        if ($oldStatus !== $request->status && $creator) {
            $statusText = $request->status === 'diterima' ? 'diterima' : 'ditolak';
            $title = $request->status === 'diterima' ? 'Prestasi Diterima Admin' : 'Prestasi Ditolak Admin';
            $message = "Prestasi '{$prestasi_siswa->nama_prestasi}' yang Anda buat untuk siswa {$prestasi_siswa->siswa->nama} telah {$statusText} oleh admin.";
            
            if ($request->status === 'ditolak' && !empty($request->alasan_tolak)) {
                $message .= " Alasan: {$request->alasan_tolak}";
            }
            
            Notification::create([
                'user_id' => $creator->id,
                'title' => $title,
                'message' => $message,
                'data' => json_encode([
                    'prestasi_id' => $prestasi_siswa->id,
                    'prestasi_nama' => $prestasi_siswa->nama_prestasi,
                    'siswa_nama' => $prestasi_siswa->siswa->nama,
                    'action' => 'admin_validated',
                    'status' => $request->status
                ]),
                'read_at' => null
            ]);
        }
        
        ActivityLogger::log('update', 'prestasi_siswa', 'Admin validasi prestasi guru: ' . $prestasi_siswa->nama_prestasi . ' status: ' . $request->status);
        return redirect()->back()->with('success', 'Prestasi guru berhasil divalidasi.');
    }

    public function destroy(PrestasiSiswa $prestasi_siswa)
    {
        $desc = $prestasi_siswa->nama_prestasi . ' (' . ($prestasi_siswa->siswa->nama ?? '-') . ')';
        $prestasi_siswa->delete();
        ActivityLogger::log('delete', 'prestasi_siswa', 'Hapus prestasi: ' . $desc);
        return redirect()->route('admin.prestasi_siswa.index')->with('success', 'Prestasi siswa berhasil dihapus.');
    }

    public function cetak(Request $request)
    {
        $query = PrestasiSiswa::with(['siswa', 'kategori', 'tingkat']);

        if ($request->filled('kategori')) {
            $query->where('id_kategori_prestasi', $request->kategori);
        }
        if ($request->filled('tingkat')) {
            $query->where('id_tingkat_penghargaan', $request->tingkat);
        }
        if ($request->filled('from')) {
            $query->whereDate('tanggal_prestasi', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('tanggal_prestasi', '<=', $request->to);
        }

        $data['prestasi'] = $query->orderByDesc('tanggal_prestasi')->get();

        $pdf = Pdf::loadView('admin.prestasi_siswa.cetak', $data);
        return $pdf->stream('rekap-prestasi-' . now()->format('Ymd-His') . '.pdf');
    }

    /**
     * Get analytics data for prestasi overview
     */
    public function getAnalyticsData(Request $request)
    {
        $tahunAjaran = $request->get('tahun_ajaran', 'all');
        
        // Multi-year comparison
        $multiYearData = $this->getMultiYearComparison();
        
        // Achievement by category for current filters
        $categoryData = $this->getCategoryDistribution($request);
        
        // Top performing students
        $topStudents = $this->getTopStudents($request);
        
        // Class performance
        $classPerformance = $this->getClassPerformance($request);
        
        // Monthly trends
        $monthlyTrends = $this->getMonthlyTrends($request);
        
        return response()->json([
            'multiYear' => $multiYearData,
            'categories' => $categoryData,
            'topStudents' => $topStudents,
            'classPerformance' => $classPerformance,
            'monthlyTrends' => $monthlyTrends
        ]);
    }
    
    /**
     * Get multi-year comparison data
     */
    private function getMultiYearComparison()
    {
        return DB::table('prestasi_siswa')
            ->join('tahun_ajaran', 'prestasi_siswa.id_tahun_ajaran', '=', 'tahun_ajaran.id')
            ->where('prestasi_siswa.status', 'diterima')
            ->select(
                'tahun_ajaran.nama_tahun_ajaran',
                DB::raw('COUNT(*) as total_prestasi')
            )
            ->groupBy('tahun_ajaran.nama_tahun_ajaran')
            ->orderBy('tahun_ajaran.nama_tahun_ajaran')
            ->get();
    }
    
    /**
     * Get category distribution
     */
    private function getCategoryDistribution($request)
    {
        $query = DB::table('prestasi_siswa')
            ->join('kategori_prestasi', 'prestasi_siswa.id_kategori_prestasi', '=', 'kategori_prestasi.id')
            ->where('prestasi_siswa.status', 'diterima');
            
        // Apply filters
        if ($request->filled('kategori')) {
            $query->where('prestasi_siswa.id_kategori_prestasi', $request->kategori);
        }
        if ($request->filled('from')) {
            $query->whereDate('prestasi_siswa.tanggal_prestasi', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('prestasi_siswa.tanggal_prestasi', '<=', $request->to);
        }
            
        return $query->select(
                'kategori_prestasi.nama_kategori',
                DB::raw('COUNT(*) as total')
            )
            ->groupBy('kategori_prestasi.nama_kategori')
            ->get();
    }
    
    /**
     * Get top performing students
     */
    private function getTopStudents($request)
    {
        $query = DB::table('prestasi_siswa')
            ->join('siswa', 'prestasi_siswa.id_siswa', '=', 'siswa.id')
            ->where('prestasi_siswa.status', 'diterima');
            
        // Apply filters
        if ($request->filled('kategori')) {
            $query->where('prestasi_siswa.id_kategori_prestasi', $request->kategori);
        }
        if ($request->filled('from')) {
            $query->whereDate('prestasi_siswa.tanggal_prestasi', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('prestasi_siswa.tanggal_prestasi', '<=', $request->to);
        }
            
        return $query->select(
                'siswa.nama',
                'siswa.id',
                DB::raw('COUNT(*) as total_prestasi')
            )
            ->groupBy('siswa.id', 'siswa.nama')
            ->orderByDesc('total_prestasi')
            ->limit(10)
            ->get();
    }
    
    /**
     * Get class performance
     */
    private function getClassPerformance($request)
    {
        $query = DB::table('prestasi_siswa')
            ->join('siswa', 'prestasi_siswa.id_siswa', '=', 'siswa.id')
            ->join('kelas', 'siswa.id_kelas', '=', 'kelas.id')
            ->where('prestasi_siswa.status', 'diterima');
            
        // Apply filters
        if ($request->filled('kategori')) {
            $query->where('prestasi_siswa.id_kategori_prestasi', $request->kategori);
        }
        if ($request->filled('from')) {
            $query->whereDate('prestasi_siswa.tanggal_prestasi', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('prestasi_siswa.tanggal_prestasi', '<=', $request->to);
        }
            
        return $query->select(
                'kelas.nama_kelas',
                DB::raw('COUNT(*) as total_prestasi')
            )
            ->groupBy('kelas.nama_kelas')
            ->orderByDesc('total_prestasi')
            ->get();
    }
    
    /**
     * Get monthly trends
     */
    private function getMonthlyTrends($request)
    {
        $query = DB::table('prestasi_siswa')
            ->where('status', 'diterima');
            
        // Apply filters
        if ($request->filled('kategori')) {
            $query->where('id_kategori_prestasi', $request->kategori);
        }
        if ($request->filled('from')) {
            $query->whereDate('tanggal_prestasi', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('tanggal_prestasi', '<=', $request->to);
        }
            
        return $query->select(
                DB::raw('MONTH(tanggal_prestasi) as bulan'),
                DB::raw('YEAR(tanggal_prestasi) as tahun'),
                DB::raw('COUNT(*) as total')
            )
            ->groupBy(DB::raw('YEAR(tanggal_prestasi), MONTH(tanggal_prestasi)'))
            ->orderBy('tahun')
            ->orderBy('bulan')
            ->get();
    }
    
    /**
     * Get individual student analysis
     */
    public function getStudentAnalysis($studentId)
    {
        $student = Siswa::with(['kelas'])->findOrFail($studentId);
        
        // Student achievements timeline
        $achievements = PrestasiSiswa::with(['kategori', 'tingkat'])
            ->where('id_siswa', $studentId)
            ->where('status', 'diterima')
            ->orderBy('tanggal_prestasi')
            ->get();
            
        // Achievement by category
        $achievementsByCategory = PrestasiSiswa::join('kategori_prestasi', 'prestasi_siswa.id_kategori_prestasi', '=', 'kategori_prestasi.id')
            ->where('prestasi_siswa.id_siswa', $studentId)
            ->where('prestasi_siswa.status', 'diterima')
            ->select(
                'kategori_prestasi.nama_kategori',
                DB::raw('COUNT(*) as total')
            )
            ->groupBy('kategori_prestasi.nama_kategori')
            ->get();
            
        return response()->json([
            'student' => $student,
            'achievements' => $achievements,
            'achievementsByCategory' => $achievementsByCategory
        ]);
    }
    
    /**
     * Generate Excel report
     */
    public function generateExcelReport(Request $request)
    {
        $query = PrestasiSiswa::with(['siswa', 'kategori', 'tingkat'])
            ->where('status', 'diterima');
            
        // Apply filters
        if ($request->filled('kategori')) {
            $query->where('id_kategori_prestasi', $request->kategori);
        }
        if ($request->filled('tingkat')) {
            $query->where('id_tingkat_penghargaan', $request->tingkat);
        }
        if ($request->filled('from')) {
            $query->whereDate('tanggal_prestasi', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('tanggal_prestasi', '<=', $request->to);
        }
        
        $prestasi = $query->orderByDesc('tanggal_prestasi')->get();
        
        return Excel::download(
            new PrestasiReportExport($prestasi),
            'rekap-prestasi-' . now()->format('Ymd-His') . '.xlsx'
        );
    }
}
