<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Siswa extends Model
{
    use HasFactory;

    protected $table = 'siswa';
    protected $fillable = [
        'nisn',
        'nama',
        'jenis_kelamin',
        'tanggal_lahir',
        'tempat_lahir',
        'id_kelas',
        'status',
        'tahun_lulus',
        'alamat',
        'tahun_masuk',
        'no_hp',
        'wali_id',
    ];

    // Relasi ke kelas
    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'id_kelas');
    }

    // Relasi ke user (login siswa)
    public function user()
    {
        return $this->hasOne(User::class, 'siswa_id');
    }

    // Relasi ke wali murid (orang tua)
    public function wali()
    {
        return $this->belongsTo(User::class, 'wali_id');
    }

    // Relasi ke ekstrakurikuler (melalui siswa_ekskul)
    public function ekstrakurikuler()
    {
        return $this->belongsToMany(Ekstrakurikuler::class, 'siswa_ekskul', 'id_siswa', 'id_ekskul');
    }

    // Relasi ke prestasi
    public function prestasi()
    {
        return $this->hasMany(PrestasiSiswa::class, 'id_siswa');
    }

    // Scope untuk siswa aktif (belum lulus)
    public function scopeAktif($query)
    {
        return $query->where('status', 'aktif');
    }

    // Scope untuk siswa lulus
    public function scopeLulus($query)
    {
        return $query->where('status', 'lulus');
    }

    // Get prestasi by academic year
    public function prestasiByTahunAjaran($tahunAjaranId = null)
    {
        $query = $this->prestasi();
        if ($tahunAjaranId) {
            $query->where('id_tahun_ajaran', $tahunAjaranId);
        }
        return $query;
    }

    // Check if student is in final year (class XII)
    public function isFinalYear()
    {
        return $this->kelas && str_contains(strtoupper($this->kelas->nama_kelas), 'XII');
    }

    // Get next class for promotion
    public function getNextClass()
    {
        if (!$this->kelas) return null;
        
        $currentClass = $this->kelas->nama_kelas;
        
        // Convert XI to XII (e.g., "XI IPA 1" -> "XII IPA 1")
        if (str_contains(strtoupper($currentClass), 'XI')) {
            $nextClass = str_replace(['XI', 'xi'], ['XII', 'xii'], $currentClass);
            return Kelas::where('nama_kelas', $nextClass)->first();
        }
        
        return null; // XII students don't have next class (they graduate)
    }
}