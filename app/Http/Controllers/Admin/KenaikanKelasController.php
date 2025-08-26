<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\TahunAjaran;
use Illuminate\Support\Facades\DB;
use App\Helpers\ActivityLogger;

class KenaikanKelasController extends Controller
{
    public function index(Request $request)
    {
        $tahunAjaranId = $request->get('tahun_ajaran_id');
        $kelasFilter = $request->get('kelas_filter'); // New filter for specific class
        $jenisFilter = $request->get('jenis_filter'); // Filter by grade level (X, XI, XII)
        
        // Get all academic years for filter (semester system)
        $tahunAjarans = TahunAjaran::orderBy('nama_tahun_ajaran', 'desc')
                                  ->orderBy('semester', 'desc')
                                  ->get();
        
        // Get active academic year if not specified
        if (!$tahunAjaranId) {
            $activeTahunAjaran = TahunAjaran::where('is_active', true)->first();
            $tahunAjaranId = $activeTahunAjaran ? $activeTahunAjaran->id : null;
        }
        
        // Get all classes for filter dropdown
        $allKelas = Kelas::orderBy('nama_kelas')->get();
        
        // Get class statistics (siswa per kelas)
        $klasifikasi = [];
        $statistik = [];
        if ($tahunAjaranId) {
            $klasifikasi = $this->getKlasifikasiSiswa($tahunAjaranId, $kelasFilter, $jenisFilter);
            $statistik = $this->getStatistikKenaikan($klasifikasi);
        }
        
        return view('admin.kenaikan_kelas.index', compact(
            'tahunAjarans', 
            'tahunAjaranId', 
            'klasifikasi', 
            'allKelas', 
            'kelasFilter', 
            'jenisFilter',
            'statistik'
        ));
    }
    
    private function getKlasifikasiSiswa($tahunAjaranId, $kelasFilter = null, $jenisFilter = null)
    {
        // Build query for classes with student counts
        $query = Kelas::withCount(['siswa' => function ($query) {
            $query->where('status', 'aktif');
        }]);
        
        // Apply class filter if specified
        if ($kelasFilter) {
            $query->where('id', $kelasFilter);
        }
        
        // Apply grade level filter if specified
        if ($jenisFilter) {
            $query->where('nama_kelas', 'like', '%' . $jenisFilter . '%');
        }
        
        $kelas = $query->orderBy('nama_kelas')->get();
            
        $klasifikasi = [
            'X' => [],   // Add grade X for completeness
            'XI' => [],
            'XII' => []
        ];
        
        foreach ($kelas as $kelasItem) {
            $gradeLevel = $this->getGradeLevel($kelasItem->nama_kelas);
            
            if ($gradeLevel === 'X') {
                // Grade X students will promote to XI
                $nextClass = $this->findNextClass($kelasItem->nama_kelas);
                $klasifikasi['X'][] = [
                    'id' => $kelasItem->id,
                    'nama_kelas' => $kelasItem->nama_kelas,
                    'jumlah_siswa' => $kelasItem->siswa_count,
                    'next_class' => $nextClass ? $nextClass->nama_kelas : 'XI ' . $this->getMajorFromClass($kelasItem->nama_kelas),
                    'next_class_id' => $nextClass ? $nextClass->id : null,
                    'can_promote' => true, // Grade X can always promote (create XI class if needed)
                    'promotion_type' => 'naik_kelas'
                ];
            } elseif ($gradeLevel === 'XI') {
                // Grade XI students will promote to XII
                $nextClass = $this->findNextClass($kelasItem->nama_kelas);
                $klasifikasi['XI'][] = [
                    'id' => $kelasItem->id,
                    'nama_kelas' => $kelasItem->nama_kelas,
                    'jumlah_siswa' => $kelasItem->siswa_count,
                    'next_class' => $nextClass ? $nextClass->nama_kelas : 'XII ' . $this->getMajorFromClass($kelasItem->nama_kelas),
                    'next_class_id' => $nextClass ? $nextClass->id : null,
                    'can_promote' => true, // Grade XI can always promote (create XII class if needed)
                    'promotion_type' => 'naik_kelas'
                ];
            } elseif ($gradeLevel === 'XII') {
                // Grade XII students will graduate
                $klasifikasi['XII'][] = [
                    'id' => $kelasItem->id,
                    'nama_kelas' => $kelasItem->nama_kelas,
                    'jumlah_siswa' => $kelasItem->siswa_count,
                    'next_class' => 'LULUS',
                    'next_class_id' => null,
                    'can_promote' => $kelasItem->siswa_count > 0, // Only if there are students
                    'promotion_type' => 'lulus'
                ];
            }
        }
        
        return $klasifikasi;
    }
    
    private function getGradeLevel($className)
    {
        $upperName = strtoupper($className);
        if (str_contains($upperName, ' XII ') || str_starts_with($upperName, 'XII ')) {
            return 'XII';
        } elseif (str_contains($upperName, ' XI ') || str_starts_with($upperName, 'XI ')) {
            return 'XI';
        } elseif (str_contains($upperName, ' X ') || str_starts_with($upperName, 'X ')) {
            return 'X';
        }
        return null;
    }
    
    private function getMajorFromClass($className)
    {
        // Extract major from class name (e.g., "X IPA 1" -> "IPA 1")
        $parts = explode(' ', $className);
        if (count($parts) >= 3) {
            return implode(' ', array_slice($parts, 1));
        }
        return 'UMUM 1';
    }
    
    private function findNextClass($currentClassName)
    {
        $gradeLevel = $this->getGradeLevel($currentClassName);
        
        if ($gradeLevel === 'X') {
            // X IPA 1 -> XI IPA 1
            $nextClassName = str_replace('X ', 'XI ', $currentClassName);
        } elseif ($gradeLevel === 'XI') {
            // XI IPA 1 -> XII IPA 1
            $nextClassName = str_replace('XI ', 'XII ', $currentClassName);
        } else {
            return null; // XII students graduate, no next class
        }
        
        return Kelas::where('nama_kelas', $nextClassName)->first();
    }
    
    private function getStatistikKenaikan($klasifikasi)
    {
        $statistik = [
            'total_siswa_x' => 0,
            'total_siswa_xi' => 0,
            'total_siswa_xii' => 0,
            'siap_naik_x_xi' => 0,
            'siap_naik_xi_xii' => 0,
            'siap_lulus' => 0,
            'total_kelas_x' => count($klasifikasi['X'] ?? []),
            'total_kelas_xi' => count($klasifikasi['XI'] ?? []),
            'total_kelas_xii' => count($klasifikasi['XII'] ?? [])
        ];
        
        foreach ($klasifikasi as $grade => $classes) {
            foreach ($classes as $class) {
                if ($grade === 'X') {
                    $statistik['total_siswa_x'] += $class['jumlah_siswa'];
                    if ($class['can_promote'] && $class['jumlah_siswa'] > 0) {
                        $statistik['siap_naik_x_xi'] += $class['jumlah_siswa'];
                    }
                } elseif ($grade === 'XI') {
                    $statistik['total_siswa_xi'] += $class['jumlah_siswa'];
                    if ($class['can_promote'] && $class['jumlah_siswa'] > 0) {
                        $statistik['siap_naik_xi_xii'] += $class['jumlah_siswa'];
                    }
                } elseif ($grade === 'XII') {
                    $statistik['total_siswa_xii'] += $class['jumlah_siswa'];
                    if ($class['can_promote'] && $class['jumlah_siswa'] > 0) {
                        $statistik['siap_lulus'] += $class['jumlah_siswa'];
                    }
                }
            }
        }
        
        return $statistik;
    }
    
    private function findAlumniClass($tahunLulus)
    {
        // Find Alumni class for graduation year (e.g., "Alumni - Angkatan 2026")
        $alumniClassName = "Alumni - Angkatan {$tahunLulus}";
        return Kelas::where('nama_kelas', $alumniClassName)->first();
    }
    
    private function createAlumniClassIfNotExists($tahunLulus)
    {
        $alumniClass = $this->findAlumniClass($tahunLulus);
        
        if (!$alumniClass) {
            // Create Alumni class if it doesn't exist
            $alumniClass = Kelas::create([
                'nama_kelas' => "Alumni - Angkatan {$tahunLulus}",
                'tahun_ajaran' => ($tahunLulus - 1) . '/' . $tahunLulus, // e.g., "2025/2026"
                'id_wali_kelas' => null
            ]);
            
            ActivityLogger::log(
                'create_alumni_class',
                'Kelas',
                $alumniClass->id,
                "Auto-created Alumni class: Alumni - Angkatan {$tahunLulus}"
            );
        }
        
        return $alumniClass;
    }
    
    public function processKenaikan(Request $request)
    {
        $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'tahun_ajaran_from' => 'required|exists:tahun_ajaran,id',
            'tahun_ajaran_to' => 'required|exists:tahun_ajaran,id',
        ]);
        
        try {
            DB::beginTransaction();
            
            $kelas = Kelas::findOrFail($request->kelas_id);
            $tahunAjaranFrom = TahunAjaran::findOrFail($request->tahun_ajaran_from);
            $tahunAjaranTo = TahunAjaran::findOrFail($request->tahun_ajaran_to);
            
            // Get all active students in this class
            $siswaList = Siswa::where('id_kelas', $request->kelas_id)
                ->where('status', 'aktif')
                ->get();
                
            $promotedCount = 0;
            $graduatedCount = 0;
            $errors = [];
            
            foreach ($siswaList as $siswa) {
                if ($siswa->isFinalYear()) {
                    // XII -> Graduate to Alumni class
                    $tahunLulus = $tahunAjaranTo->tanggal_selesai_tahun->year;
                    $alumniClass = $this->createAlumniClassIfNotExists($tahunLulus);
                    
                    $siswa->update([
                        'status' => 'lulus',
                        'tahun_lulus' => $tahunLulus,
                        'id_kelas' => $alumniClass->id // Assign to Alumni class
                    ]);
                    $graduatedCount++;
                    
                    ActivityLogger::log(
                        'graduate',
                        'Siswa',
                        $siswa->id,
                        "Siswa {$siswa->nama} lulus dari {$kelas->nama_kelas} ke {$alumniClass->nama_kelas}"
                    );
                } else {
                    // XI -> XII
                    $nextClass = $siswa->getNextClass();
                    if ($nextClass) {
                        $siswa->update([
                            'id_kelas' => $nextClass->id,
                        ]);
                        $promotedCount++;
                        
                        ActivityLogger::log(
                            'promote',
                            'Siswa',
                            $siswa->id,
                            "Siswa {$siswa->nama} naik dari {$kelas->nama_kelas} ke {$nextClass->nama_kelas}"
                        );
                    } else {
                        $errors[] = "Tidak ditemukan kelas tujuan untuk siswa {$siswa->nama}";
                    }
                }
            }
            
            DB::commit();
            
            $message = "Kenaikan kelas berhasil! ";
            if ($promotedCount > 0) {
                $message .= "{$promotedCount} siswa naik kelas. ";
            }
            if ($graduatedCount > 0) {
                $message .= "{$graduatedCount} siswa lulus.";
            }
            if (count($errors) > 0) {
                $message .= " Errors: " . implode(', ', $errors);
            }
            
            return redirect()->route('admin.kenaikan_kelas.index', ['tahun_ajaran_id' => $request->tahun_ajaran_to])
                ->with('success', $message);
                
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
    
    public function processAllKenaikan(Request $request)
    {
        $request->validate([
            'tahun_ajaran_from' => 'required|exists:tahun_ajaran,id',
            'tahun_ajaran_to' => 'required|exists:tahun_ajaran,id',
        ]);
        
        try {
            DB::beginTransaction();
            
            $tahunAjaranFrom = TahunAjaran::findOrFail($request->tahun_ajaran_from);
            $tahunAjaranTo = TahunAjaran::findOrFail($request->tahun_ajaran_to);
            
            $promotedXtoXI = 0;
            $promotedXItoXII = 0;
            $graduatedCount = 0;
            $errors = [];
            
            // STEP 1: Process all XII students (graduate to Alumni)
            $siswaXII = Siswa::whereHas('kelas', function($query) {
                    $query->where('nama_kelas', 'like', 'XII %');
                })
                ->where('status', 'aktif')
                ->with('kelas')
                ->get();
                
            foreach ($siswaXII as $siswa) {
                // XII -> Graduate to Alumni class
                $tahunLulus = $tahunAjaranTo->tanggal_selesai_tahun->year;
                $alumniClass = $this->createAlumniClassIfNotExists($tahunLulus);
                
                $originalClass = $siswa->kelas->nama_kelas;
                
                $siswa->update([
                    'status' => 'lulus',
                    'tahun_lulus' => $tahunLulus,
                    'id_kelas' => $alumniClass->id
                ]);
                $graduatedCount++;
                
                ActivityLogger::log(
                    'graduate',
                    'Siswa',
                    $siswa->id,
                    "Siswa {$siswa->nama} lulus dari {$originalClass} ke {$alumniClass->nama_kelas}"
                );
            }
            
            // STEP 2: Process all XI students (promote to XII)
            $siswaXI = Siswa::whereHas('kelas', function($query) {
                    $query->where('nama_kelas', 'like', 'XI %');
                })
                ->where('status', 'aktif')
                ->with('kelas')
                ->get();
                
            foreach ($siswaXI as $siswa) {
                $originalClass = $siswa->kelas->nama_kelas;
                $nextClassName = str_replace('XI ', 'XII ', $originalClass);
                $nextClass = Kelas::where('nama_kelas', $nextClassName)->first();
                
                if ($nextClass) {
                    $siswa->update(['id_kelas' => $nextClass->id]);
                    $promotedXItoXII++;
                    
                    ActivityLogger::log(
                        'promote',
                        'Siswa',
                        $siswa->id,
                        "Siswa {$siswa->nama} naik dari {$originalClass} ke {$nextClass->nama_kelas}"
                    );
                } else {
                    // Create XII class if not exists
                    $newClass = Kelas::create([
                        'nama_kelas' => $nextClassName,
                        'tahun_ajaran' => $tahunAjaranTo->nama_tahun_ajaran,
                        'id_wali_kelas' => null
                    ]);
                    
                    $siswa->update(['id_kelas' => $newClass->id]);
                    $promotedXItoXII++;
                    
                    ActivityLogger::log(
                        'promote',
                        'Siswa',
                        $siswa->id,
                        "Siswa {$siswa->nama} naik dari {$originalClass} ke {$newClass->nama_kelas} (kelas baru dibuat)"
                    );
                }
            }
            
            // STEP 3: Process all X students (promote to XI)
            $siswaX = Siswa::whereHas('kelas', function($query) {
                    $query->where('nama_kelas', 'like', 'X %');
                })
                ->where('status', 'aktif')
                ->with('kelas')
                ->get();
                
            foreach ($siswaX as $siswa) {
                $originalClass = $siswa->kelas->nama_kelas;
                $nextClassName = str_replace('X ', 'XI ', $originalClass);
                $nextClass = Kelas::where('nama_kelas', $nextClassName)->first();
                
                if ($nextClass) {
                    $siswa->update(['id_kelas' => $nextClass->id]);
                    $promotedXtoXI++;
                    
                    ActivityLogger::log(
                        'promote',
                        'Siswa',
                        $siswa->id,
                        "Siswa {$siswa->nama} naik dari {$originalClass} ke {$nextClass->nama_kelas}"
                    );
                } else {
                    // Create XI class if not exists
                    $newClass = Kelas::create([
                        'nama_kelas' => $nextClassName,
                        'tahun_ajaran' => $tahunAjaranTo->nama_tahun_ajaran,
                        'id_wali_kelas' => null
                    ]);
                    
                    $siswa->update(['id_kelas' => $newClass->id]);
                    $promotedXtoXI++;
                    
                    ActivityLogger::log(
                        'promote',
                        'Siswa',
                        $siswa->id,
                        "Siswa {$siswa->nama} naik dari {$originalClass} ke {$newClass->nama_kelas} (kelas baru dibuat)"
                    );
                }
            }
            
            // Set new academic year as active
            TahunAjaran::where('is_active', true)->update(['is_active' => false]);
            $tahunAjaranTo->update(['is_active' => true]);
            
            DB::commit();
            
            $totalPromoted = $promotedXtoXI + $promotedXItoXII;
            $message = "Kenaikan kelas massal berhasil! ";
            $message .= "{$promotedXtoXI} siswa X→XI, ";
            $message .= "{$promotedXItoXII} siswa XI→XII, ";
            $message .= "{$graduatedCount} siswa XII→Alumni. ";
            $message .= "Total: {$totalPromoted} naik kelas, {$graduatedCount} lulus. ";
            $message .= "Tahun ajaran {$tahunAjaranTo->nama_tahun_ajaran} - " . ucfirst($tahunAjaranTo->semester) . " sekarang aktif.";
            
            if (count($errors) > 0) {
                $message .= " Errors: " . implode(', ', $errors);
            }
            
            return redirect()->route('admin.kenaikan_kelas.index', ['tahun_ajaran_id' => $request->tahun_ajaran_to])
                ->with('success', $message);
                
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
}