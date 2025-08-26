<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Kelas;

class KelasSeeder extends Seeder
{
    public function run()
    {
        echo "🏫 Creating Class data...\n";
        
        $kelasData = [
            // Kelas X (Grade 10)
            [
                'nama_kelas' => 'X IPA 1',
                'tahun_ajaran' => '2024/2025',
                'id_wali_kelas' => null // Will be updated later by ComprehensiveAllRolesSeeder
            ],
            [
                'nama_kelas' => 'X IPA 2',
                'tahun_ajaran' => '2024/2025',
                'id_wali_kelas' => null
            ],
            [
                'nama_kelas' => 'X IPS 1',
                'tahun_ajaran' => '2024/2025',
                'id_wali_kelas' => null
            ],
            [
                'nama_kelas' => 'X IPS 2',
                'tahun_ajaran' => '2024/2025',
                'id_wali_kelas' => null
            ],
            [
                'nama_kelas' => 'X BAHASA 1',
                'tahun_ajaran' => '2024/2025',
                'id_wali_kelas' => null
            ],

            // Kelas XI (Grade 11)
            [
                'nama_kelas' => 'XI IPA 1',
                'tahun_ajaran' => '2024/2025',
                'id_wali_kelas' => null
            ],
            [
                'nama_kelas' => 'XI IPA 2',
                'tahun_ajaran' => '2024/2025',
                'id_wali_kelas' => null
            ],
            [
                'nama_kelas' => 'XI IPS 1',
                'tahun_ajaran' => '2024/2025',
                'id_wali_kelas' => null
            ],
            [
                'nama_kelas' => 'XI IPS 2',
                'tahun_ajaran' => '2024/2025',
                'id_wali_kelas' => null
            ],

            // Kelas XII (Grade 12)
            [
                'nama_kelas' => 'XII IPA 1',
                'tahun_ajaran' => '2024/2025',
                'id_wali_kelas' => null
            ],
            [
                'nama_kelas' => 'XII IPA 2',
                'tahun_ajaran' => '2024/2025',
                'id_wali_kelas' => null
            ],
            [
                'nama_kelas' => 'XII IPS 1',
                'tahun_ajaran' => '2024/2025',
                'id_wali_kelas' => null
            ],
            [
                'nama_kelas' => 'XII IPS 2',
                'tahun_ajaran' => '2024/2025',
                'id_wali_kelas' => null
            ],

            // Alumni Classes (for graduated students)
            [
                'nama_kelas' => 'Alumni - Angkatan 2022',
                'tahun_ajaran' => '2021/2022',
                'id_wali_kelas' => null
            ],
            [
                'nama_kelas' => 'Alumni - Angkatan 2023',
                'tahun_ajaran' => '2022/2023',
                'id_wali_kelas' => null
            ],
            [
                'nama_kelas' => 'Alumni - Angkatan 2024',
                'tahun_ajaran' => '2023/2024',
                'id_wali_kelas' => null
            ],
            [
                'nama_kelas' => 'Alumni - Angkatan 2025',
                'tahun_ajaran' => '2024/2025',
                'id_wali_kelas' => null
            ],
            [
                'nama_kelas' => 'Alumni - Angkatan 2026',
                'tahun_ajaran' => '2025/2026',
                'id_wali_kelas' => null
            ]
        ];

        foreach ($kelasData as $kelas) {
            Kelas::create($kelas);
            echo "   ✓ Created class: {$kelas['nama_kelas']}\n";
        }
        
        echo "🏫 Class seeding completed!\n\n";
    }
}