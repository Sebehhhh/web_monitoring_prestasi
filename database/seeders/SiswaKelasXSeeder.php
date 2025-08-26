<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Siswa;
use App\Models\Kelas;

class SiswaKelasXSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing students in grade X classes first
        $gradeXClasses = Kelas::whereIn('nama_kelas', [
            'X IPA 1', 'X IPA 2', 'X IPS 1', 'X IPS 2', 'X BAHASA 1'
        ])->get();

        foreach ($gradeXClasses as $kelas) {
            Siswa::where('id_kelas', $kelas->id)->delete();
        }

        // Sample names for variety
        $namaLaki = [
            'Ahmad Rizky Pratama', 'Budi Santoso', 'Cahyo Nugroho', 'Dian Pratama', 'Eko Saputra',
            'Fajar Ramadhan', 'Giri Wahyu', 'Hadi Purnomo', 'Indra Wijaya', 'Joko Susilo',
            'Kevin Ananda', 'Luis Fernando', 'Mario Andika', 'Nanda Arya', 'Omar Hakim',
            'Panji Nugraha', 'Qomar Rizki', 'Rahman Hakim', 'Surya Perdana', 'Taufik Hidayat',
            'Umar Faruq', 'Vincent Pratama', 'Wahyu Ramadan', 'Xavier Nugroho', 'Yoga Pratama', 'Zaki Ramadhan'
        ];

        $namaPerempuan = [
            'Ayu Lestari', 'Bella Sari', 'Citra Dewi', 'Diana Putri', 'Ella Pratiwi',
            'Fitri Handayani', 'Gita Maharani', 'Hana Safitri', 'Intan Permata', 'Julia Rahmawati',
            'Kirana Sari', 'Luna Aprilia', 'Maya Puspita', 'Nina Fadila', 'Olivia Kartika',
            'Putri Andini', 'Queen Aurelia', 'Rina Melati', 'Sari Purnama', 'Tika Wardani',
            'Una Safira', 'Vera Wulandari', 'Winda Sari', 'Xenia Pratiwi', 'Yuni Astuti', 'Zahra Kamila'
        ];

        $tempatLahir = [
            'Jakarta', 'Bandung', 'Surabaya', 'Medan', 'Semarang', 'Makassar', 'Palembang',
            'Yogyakarta', 'Malang', 'Solo', 'Bogor', 'Depok', 'Tangerang', 'Bekasi', 'Batam'
        ];

        $alamat = [
            'Jl. Merdeka No. 12, Jakarta Pusat',
            'Jl. Sudirman No. 45, Bandung',
            'Jl. Pemuda No. 78, Surabaya',
            'Jl. Gatot Subroto No. 23, Medan',
            'Jl. Diponegoro No. 56, Semarang',
            'Jl. Veteran No. 89, Makassar',
            'Jl. Ahmad Yani No. 34, Palembang',
            'Jl. Malioboro No. 67, Yogyakarta',
            'Jl. Ijen No. 90, Malang',
            'Jl. Slamet Riyadi No. 21, Solo'
        ];

        // Create students for each grade X class (5 students each)
        $gradeXData = [
            'X IPA 1' => ['major' => 'IPA', 'focus' => 'Matematika dan Sains'],
            'X IPA 2' => ['major' => 'IPA', 'focus' => 'Biologi dan Kimia'], 
            'X IPS 1' => ['major' => 'IPS', 'focus' => 'Sejarah dan Geografi'],
            'X IPS 2' => ['major' => 'IPS', 'focus' => 'Ekonomi dan Sosiologi'],
            'X BAHASA 1' => ['major' => 'BAHASA', 'focus' => 'Sastra dan Linguistik']
        ];

        $currentYear = date('Y');
        $siswaCounter = 1;

        foreach ($gradeXData as $namaKelas => $info) {
            $kelas = Kelas::where('nama_kelas', $namaKelas)->first();
            
            if (!$kelas) continue;

            // Create exactly 5 students per class
            for ($i = 1; $i <= 5; $i++) {
                // Alternate gender
                $jenisKelamin = ($i % 2 == 1) ? 'L' : 'P';
                $namaPool = ($jenisKelamin == 'L') ? $namaLaki : $namaPerempuan;
                
                // Pick names ensuring no duplicates within the class
                $nama = $namaPool[($siswaCounter - 1) % count($namaPool)];
                
                // Generate NISN (unique 10 digit number)
                $nisn = $currentYear . str_pad($siswaCounter, 6, '0', STR_PAD_LEFT);
                
                // Random birth date (age 15-16 for grade X)
                $birthYear = $currentYear - rand(15, 16);
                $birthMonth = rand(1, 12);
                $birthDay = rand(1, 28);
                $tanggalLahir = sprintf('%04d-%02d-%02d', $birthYear, $birthMonth, $birthDay);
                
                Siswa::create([
                    'nisn' => $nisn,
                    'nama' => $nama,
                    'jenis_kelamin' => $jenisKelamin,
                    'tanggal_lahir' => $tanggalLahir,
                    'tempat_lahir' => $tempatLahir[array_rand($tempatLahir)],
                    'id_kelas' => $kelas->id,
                    'status' => 'aktif',
                    'tahun_lulus' => null,
                    'alamat' => $alamat[array_rand($alamat)],
                    'tahun_masuk' => $currentYear, // Assuming they just entered this year
                    'wali_id' => null // Will be linked later if needed
                ]);

                $siswaCounter++;
            }
        }

        $this->command->info('✅ Created 25 students (5 per class) for Grade X:');
        $this->command->info('   - X IPA 1: 5 students (Matematika dan Sains)');
        $this->command->info('   - X IPA 2: 5 students (Biologi dan Kimia)');
        $this->command->info('   - X IPS 1: 5 students (Sejarah dan Geografi)'); 
        $this->command->info('   - X IPS 2: 5 students (Ekonomi dan Sosiologi)');
        $this->command->info('   - X BAHASA 1: 5 students (Sastra dan Linguistik)');
    }
}