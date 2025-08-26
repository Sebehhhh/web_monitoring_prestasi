<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TahunAjaran;
use App\Models\PrestasiSiswa;
use Illuminate\Support\Facades\DB;
use App\Helpers\ActivityLogger;

class TahunAjaranController extends Controller
{
    public function index()
    {
        $tahunAjarans = TahunAjaran::withCount('prestasi')
            ->orderBy('nama_tahun_ajaran', 'desc')
            ->orderBy('semester', 'desc')
            ->get()
            ->map(function($tahun) {
                return [
                    'id' => $tahun->id,
                    'nama_tahun_ajaran' => $tahun->nama_tahun_ajaran,
                    'semester' => ucfirst($tahun->semester),
                    'tanggal_mulai_tahun' => $tahun->tanggal_mulai_tahun->format('d/m/Y'),
                    'tanggal_selesai_tahun' => $tahun->tanggal_selesai_tahun->format('d/m/Y'),
                    'tanggal_mulai_semester' => $tahun->tanggal_mulai_semester->format('d/m/Y'),
                    'tanggal_selesai_semester' => $tahun->tanggal_selesai_semester->format('d/m/Y'),
                    'is_active' => $tahun->is_active,
                    'keterangan' => $tahun->keterangan,
                    'total_prestasi' => $tahun->prestasi_count,
                    'format_tahun' => $tahun->format_tahun,
                    'full_name' => $tahun->full_name
                ];
            });

        return view('admin.tahun_ajaran.index', compact('tahunAjarans'));
    }

    public function create()
    {
        return view('admin.tahun_ajaran.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_tahun_ajaran' => 'required|string|max:10',
            'semester' => 'required|in:ganjil,genap',
            'tanggal_mulai_tahun' => 'required|date',
            'tanggal_selesai_tahun' => 'required|date|after:tanggal_mulai_tahun',
            'tanggal_mulai_semester' => 'required|date',
            'tanggal_selesai_semester' => 'required|date|after:tanggal_mulai_semester',
            'keterangan' => 'nullable|string|max:255'
        ], [
            'nama_tahun_ajaran.required' => 'Nama tahun ajaran harus diisi',
            'semester.required' => 'Semester harus dipilih',
            'tanggal_mulai_tahun.required' => 'Tanggal mulai tahun harus diisi',
            'tanggal_selesai_tahun.required' => 'Tanggal selesai tahun harus diisi',
            'tanggal_mulai_semester.required' => 'Tanggal mulai semester harus diisi',
            'tanggal_selesai_semester.required' => 'Tanggal selesai semester harus diisi'
        ]);
        
        // Custom validation: Check if same year and semester already exists
        $existing = TahunAjaran::where('nama_tahun_ajaran', $request->nama_tahun_ajaran)
                              ->where('semester', $request->semester)
                              ->first();
        if ($existing) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Tahun ajaran ' . $request->nama_tahun_ajaran . ' semester ' . $request->semester . ' sudah ada');
        }

        try {
            DB::beginTransaction();

            $tahunAjaran = TahunAjaran::create([
                'nama_tahun_ajaran' => $request->nama_tahun_ajaran,
                'semester' => $request->semester,
                'tanggal_mulai_tahun' => $request->tanggal_mulai_tahun,
                'tanggal_selesai_tahun' => $request->tanggal_selesai_tahun,
                'tanggal_mulai_semester' => $request->tanggal_mulai_semester,
                'tanggal_selesai_semester' => $request->tanggal_selesai_semester,
                'is_active' => false,
                'keterangan' => $request->keterangan
            ]);

            ActivityLogger::log(
                'create', 
                'TahunAjaran', 
                $tahunAjaran->id, 
                "Menambahkan tahun ajaran baru: {$tahunAjaran->nama_tahun_ajaran} - {$tahunAjaran->semester}"
            );

            DB::commit();

            return redirect()->route('admin.tahun_ajaran.index')
                ->with('success', 'Tahun ajaran berhasil ditambahkan');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $tahunAjaran = TahunAjaran::with(['prestasi.siswa', 'prestasi.kategoriPrestasi'])
            ->findOrFail($id);

        $statistics = [
            'total_prestasi' => $tahunAjaran->prestasi->where('status', 'diterima')->count(),
            'prestasi_pending' => $tahunAjaran->prestasi->where('status', 'menunggu_validasi')->count(),
            'prestasi_ditolak' => $tahunAjaran->prestasi->where('status', 'ditolak')->count(),
            'prestasi_akademik' => $tahunAjaran->prestasi
                ->where('status', 'diterima')
                ->where('kategoriPrestasi.jenis_prestasi', 'akademik')->count(),
            'prestasi_non_akademik' => $tahunAjaran->prestasi
                ->where('status', 'diterima')
                ->where('kategoriPrestasi.jenis_prestasi', 'non_akademik')->count(),
        ];

        $monthlyDistribution = $tahunAjaran->prestasi()
            ->select(
                DB::raw('DATE_FORMAT(tanggal_prestasi, "%Y-%m") as bulan'),
                DB::raw('COUNT(*) as total')
            )
            ->where('status', 'diterima')
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->get();

        return view('admin.tahun_ajaran.show', compact('tahunAjaran', 'statistics', 'monthlyDistribution'));
    }

    public function edit($id)
    {
        $tahunAjaran = TahunAjaran::findOrFail($id);
        return view('admin.tahun_ajaran.edit', compact('tahunAjaran'));
    }

    public function update(Request $request, $id)
    {
        $tahunAjaran = TahunAjaran::findOrFail($id);

        $request->validate([
            'nama_tahun_ajaran' => 'required|string|max:10',
            'semester' => 'required|in:ganjil,genap',
            'tanggal_mulai_tahun' => 'required|date',
            'tanggal_selesai_tahun' => 'required|date|after:tanggal_mulai_tahun',
            'tanggal_mulai_semester' => 'required|date',
            'tanggal_selesai_semester' => 'required|date|after:tanggal_mulai_semester',
            'keterangan' => 'nullable|string|max:255'
        ]);
        
        // Custom validation: Check if same year and semester already exists (except current)
        $existing = TahunAjaran::where('nama_tahun_ajaran', $request->nama_tahun_ajaran)
                              ->where('semester', $request->semester)
                              ->where('id', '!=', $id)
                              ->first();
        if ($existing) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Tahun ajaran ' . $request->nama_tahun_ajaran . ' semester ' . $request->semester . ' sudah ada');
        }

        try {
            DB::beginTransaction();

            $oldData = $tahunAjaran->toArray();

            $tahunAjaran->update([
                'nama_tahun_ajaran' => $request->nama_tahun_ajaran,
                'semester' => $request->semester,
                'tanggal_mulai_tahun' => $request->tanggal_mulai_tahun,
                'tanggal_selesai_tahun' => $request->tanggal_selesai_tahun,
                'tanggal_mulai_semester' => $request->tanggal_mulai_semester,
                'tanggal_selesai_semester' => $request->tanggal_selesai_semester,
                'keterangan' => $request->keterangan
            ]);

            ActivityLogger::log(
                'update', 
                'TahunAjaran', 
                $tahunAjaran->id, 
                "Mengubah tahun ajaran: {$tahunAjaran->nama_tahun_ajaran} - {$tahunAjaran->semester}",
                $oldData
            );

            DB::commit();

            return redirect()->route('admin.tahun_ajaran.index')
                ->with('success', 'Tahun ajaran berhasil diupdate');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $tahunAjaran = TahunAjaran::findOrFail($id);

        // Check if this academic year has any achievements
        $hasAchievements = $tahunAjaran->prestasi()->count() > 0;
        
        if ($hasAchievements) {
            return redirect()->back()
                ->with('error', 'Tidak dapat menghapus tahun ajaran yang sudah memiliki data prestasi');
        }

        // Don't allow deleting active academic year
        if ($tahunAjaran->is_active) {
            return redirect()->back()
                ->with('error', 'Tidak dapat menghapus tahun ajaran yang sedang aktif');
        }

        try {
            DB::beginTransaction();

            ActivityLogger::log(
                'delete', 
                'TahunAjaran', 
                $tahunAjaran->id, 
                "Menghapus tahun ajaran: {$tahunAjaran->nama_tahun_ajaran} - {$tahunAjaran->semester}"
            );

            $tahunAjaran->delete();

            DB::commit();

            return redirect()->route('admin.tahun_ajaran.index')
                ->with('success', 'Tahun ajaran berhasil dihapus');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function setActive(Request $request, $id)
    {
        $tahunAjaran = TahunAjaran::findOrFail($id);

        try {
            DB::beginTransaction();

            // Deactivate all other academic years
            TahunAjaran::where('is_active', true)->update(['is_active' => false]);

            // Activate selected academic year
            $tahunAjaran->update(['is_active' => true]);

            ActivityLogger::log(
                'activate', 
                'TahunAjaran', 
                $tahunAjaran->id, 
                "Mengaktifkan tahun ajaran: {$tahunAjaran->nama_tahun_ajaran} - {$tahunAjaran->semester}"
            );

            DB::commit();

            return redirect()->route('admin.tahun_ajaran.index')
                ->with('success', "Tahun ajaran {$tahunAjaran->nama_tahun_ajaran} - {$tahunAjaran->semester} berhasil diaktifkan");

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function changeSemester(Request $request, $id)
    {
        // This method is now deprecated since semester is fixed per record
        // Instead, we activate different semester records
        return $this->setActive($request, $id);
    }

    public function getActive()
    {
        $activeTahunAjaran = TahunAjaran::getActiveTahunAjaran();
        
        if (!$activeTahunAjaran) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada tahun ajaran yang aktif'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $activeTahunAjaran->id,
                'nama_tahun_ajaran' => $activeTahunAjaran->nama_tahun_ajaran,
                'semester' => $activeTahunAjaran->semester,
                'format_tahun' => $activeTahunAjaran->format_tahun,
                'full_name' => $activeTahunAjaran->full_name,
                'tanggal_mulai_tahun' => $activeTahunAjaran->tanggal_mulai_tahun->format('Y-m-d'),
                'tanggal_selesai_tahun' => $activeTahunAjaran->tanggal_selesai_tahun->format('Y-m-d'),
                'tanggal_mulai_semester' => $activeTahunAjaran->tanggal_mulai_semester->format('Y-m-d'),
                'tanggal_selesai_semester' => $activeTahunAjaran->tanggal_selesai_semester->format('Y-m-d')
            ]
        ]);
    }

    public function getAllForSelect()
    {
        $tahunAjarans = TahunAjaran::select('id', 'nama_tahun_ajaran', 'semester', 'is_active')
            ->orderBy('nama_tahun_ajaran', 'desc')
            ->orderBy('semester', 'desc')
            ->get()
            ->map(function($tahun) {
                return [
                    'value' => $tahun->id,
                    'label' => $tahun->nama_tahun_ajaran . ' - ' . ucfirst($tahun->semester) . ($tahun->is_active ? ' (Aktif)' : ''),
                    'is_active' => $tahun->is_active
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $tahunAjarans
        ]);
    }

    public function duplicateToNext(Request $request, $id)
    {
        $currentTahunAjaran = TahunAjaran::findOrFail($id);

        $request->validate([
            'nama_tahun_ajaran' => 'required|string|max:10|unique:tahun_ajaran,nama_tahun_ajaran'
        ]);

        try {
            DB::beginTransaction();

            // Calculate next academic year dates
            $nextStartDateYear = $currentTahunAjaran->tanggal_mulai_tahun->addYear();
            $nextEndDateYear = $currentTahunAjaran->tanggal_selesai_tahun->addYear();
            $nextStartDateSemester = $currentTahunAjaran->tanggal_mulai_semester->addYear();
            $nextEndDateSemester = $currentTahunAjaran->tanggal_selesai_semester->addYear();

            $nextTahunAjaran = TahunAjaran::create([
                'nama_tahun_ajaran' => $request->nama_tahun_ajaran,
                'semester' => $currentTahunAjaran->semester,
                'tanggal_mulai_tahun' => $nextStartDateYear,
                'tanggal_selesai_tahun' => $nextEndDateYear,
                'tanggal_mulai_semester' => $nextStartDateSemester,
                'tanggal_selesai_semester' => $nextEndDateSemester,
                'is_active' => false,
                'keterangan' => "Duplikasi dari tahun ajaran {$currentTahunAjaran->nama_tahun_ajaran} - {$currentTahunAjaran->semester}"
            ]);

            ActivityLogger::log(
                'duplicate', 
                'TahunAjaran', 
                $nextTahunAjaran->id, 
                "Menduplikasi tahun ajaran dari {$currentTahunAjaran->nama_tahun_ajaran} - {$currentTahunAjaran->semester} ke {$nextTahunAjaran->nama_tahun_ajaran} - {$nextTahunAjaran->semester}"
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Tahun ajaran berhasil diduplikasi',
                'data' => $nextTahunAjaran
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}
