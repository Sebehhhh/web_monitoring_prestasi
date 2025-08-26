<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TahunAjaran extends Model
{
    use HasFactory;

    protected $table = 'tahun_ajaran';

    protected $fillable = [
        'nama_tahun_ajaran',
        'semester',
        'tanggal_mulai_tahun',
        'tanggal_selesai_tahun',
        'tanggal_mulai_semester',
        'tanggal_selesai_semester',
        'is_active',
        'keterangan'
    ];

    protected $casts = [
        'tanggal_mulai_tahun' => 'date',
        'tanggal_selesai_tahun' => 'date',
        'tanggal_mulai_semester' => 'date',
        'tanggal_selesai_semester' => 'date',
        'is_active' => 'boolean',
    ];

    public function prestasi()
    {
        return $this->hasMany(PrestasiSiswa::class, 'id_tahun_ajaran');
    }

    public function kenaikanKelas()
    {
        return $this->hasMany(KenaikanKelas::class, 'tahun_ajaran_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeCurrent($query)
    {
        return $query->where('is_active', true)->first();
    }

    public static function getActiveTahunAjaran()
    {
        return static::where('is_active', true)->first();
    }

    public function isActive()
    {
        return $this->is_active;
    }

    public function getFormatTahunAttribute()
    {
        return $this->nama_tahun_ajaran . ' - ' . ucfirst($this->semester);
    }
    
    public function getFullNameAttribute()
    {
        return $this->nama_tahun_ajaran . ' - ' . ucfirst($this->semester) . 
               ' (' . $this->tanggal_mulai_semester->format('M') . 
               ' - ' . $this->tanggal_selesai_semester->format('M Y') . ')';
    }
    
    public function scopeByYear($query, $year)
    {
        return $query->where('nama_tahun_ajaran', $year);
    }
    
    public function scopeBySemester($query, $semester)
    {
        return $query->where('semester', $semester);
    }
    
    public function scopeCurrentSemester($query)
    {
        $now = now();
        return $query->where('tanggal_mulai_semester', '<=', $now)
                    ->where('tanggal_selesai_semester', '>=', $now);
    }
}
