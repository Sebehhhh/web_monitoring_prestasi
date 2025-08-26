<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TahunAjaran;

class TahunAjaranSeeder extends Seeder
{
    public function run()
    {
        echo "📅 Creating Academic Years with Semester System...\n";
        
        // Clear existing data first
        TahunAjaran::query()->delete();
        
        $years = ['2022/2023', '2023/2024', '2024/2025', '2025/2026'];
        
        foreach ($years as $year) {
            $startYear = (int) substr($year, 0, 4);
            $endYear = (int) substr($year, 5, 4);
            
            // Semester Ganjil (July - December)
            TahunAjaran::create([
                'nama_tahun_ajaran' => $year,
                'semester' => 'ganjil',
                'tanggal_mulai_tahun' => "{$startYear}-07-15",
                'tanggal_selesai_tahun' => "{$endYear}-06-30",
                'tanggal_mulai_semester' => "{$startYear}-07-15",
                'tanggal_selesai_semester' => "{$startYear}-12-31",
                'is_active' => false,
                'keterangan' => "Semester Ganjil {$year}"
            ]);
            
            // Semester Genap (January - June)
            TahunAjaran::create([
                'nama_tahun_ajaran' => $year,
                'semester' => 'genap',
                'tanggal_mulai_tahun' => "{$startYear}-07-15",
                'tanggal_selesai_tahun' => "{$endYear}-06-30",
                'tanggal_mulai_semester' => "{$endYear}-01-01",
                'tanggal_selesai_semester' => "{$endYear}-06-30",
                'is_active' => false,
                'keterangan' => "Semester Genap {$year}"
            ]);
            
            echo "   ✓ Created academic year: {$year} (Ganjil & Genap)\n";
        }
        
        // Set 2025/2026 Ganjil as active
        TahunAjaran::where('nama_tahun_ajaran', '2025/2026')
                   ->where('semester', 'ganjil')
                   ->update(['is_active' => true]);
                   
        echo "   ✓ Set 2025/2026 - Ganjil as ACTIVE\n";
        echo "📅 Academic Years seeding completed!\n\n";
    }
}