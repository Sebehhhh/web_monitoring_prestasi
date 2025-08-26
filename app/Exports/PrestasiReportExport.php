<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithTitle;

class PrestasiReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected $prestasi;

    public function __construct($prestasi)
    {
        $this->prestasi = $prestasi;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return $this->prestasi;
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'No',
            'Nama Siswa',
            'Kelas',
            'Nama Prestasi',
            'Kategori',
            'Tingkat Penghargaan',
            'Penyelenggara',
            'Tanggal Prestasi',
            'Status',
            'Keterangan'
        ];
    }

    /**
     * @param mixed $prestasi
     * @return array
     */
    public function map($prestasi): array
    {
        static $no = 1;
        
        return [
            $no++,
            $prestasi->siswa->nama ?? '-',
            $prestasi->siswa->kelas->nama_kelas ?? '-',
            $prestasi->nama_prestasi,
            $prestasi->kategori->nama_kategori ?? '-',
            $prestasi->tingkat->tingkat ?? '-',
            $prestasi->penyelenggara,
            $prestasi->tanggal_prestasi ? \Carbon\Carbon::parse($prestasi->tanggal_prestasi)->format('d-m-Y') : '-',
            ucfirst(str_replace('_', ' ', $prestasi->status)),
            $prestasi->keterangan ?? '-'
        ];
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text.
            1    => ['font' => ['bold' => true]],
        ];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Rekap Prestasi Siswa';
    }
}