@extends('layouts.app')
@section('title', 'Prestasi Siswa')

@section('content')
<!-- Iconify CDN -->
<script src="https://code.iconify.design/3/3.1.0/iconify.min.js"></script>

<div class="row">
    <div class="col-lg-12">
        <div class="card w-100">
            <div class="card-body">
                <!-- Header & Actions -->
                <div class="d-md-flex align-items-center justify-content-between mb-3">
                    <h4 class="card-title">Prestasi Siswa - Data Management</h4>
                    <div class="d-flex gap-2">
                        <!-- Advanced Filter Toggle -->
                        <button class="btn btn-outline-secondary" type="button" data-bs-toggle="collapse" 
                                data-bs-target="#advancedFilter" aria-expanded="false" aria-controls="advancedFilter">
                            <span class="iconify" data-icon="mdi:filter-settings" data-width="20" data-height="20"></span>
                            Filter Lanjutan
                        </button>
                        
                        <!-- Export Dropdown -->
                        <div class="dropdown">
                            <button class="btn btn-success dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <span class="iconify" data-icon="mdi:download" data-width="20" data-height="20"></span>
                                Export
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('admin.prestasi_siswa.cetak', request()->all()) }}" target="_blank">
                                    <span class="iconify" data-icon="mdi:file-pdf-box" data-width="16" data-height="16"></span> PDF Report
                                </a></li>
                                <li><a class="dropdown-item" href="{{ route('admin.prestasi_siswa.excel_report', request()->all()) }}">
                                    <span class="iconify" data-icon="mdi:file-excel" data-width="16" data-height="16"></span> Excel Export
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="#" onclick="exportStudentPortfolio()">
                                    <span class="iconify" data-icon="mdi:account-box" data-width="16" data-height="16"></span> Portfolio Siswa
                                </a></li>
                                <li><a class="dropdown-item" href="#" onclick="exportClassSummary()">
                                    <span class="iconify" data-icon="mdi:google-classroom" data-width="16" data-height="16"></span> Rekap Per Kelas
                                </a></li>
                                <li><a class="dropdown-item" href="#" onclick="exportYearlyReport()">
                                    <span class="iconify" data-icon="mdi:calendar-account" data-width="16" data-height="16"></span> Laporan Tahunan
                                </a></li>
                            </ul>
                        </div>
                        
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createPrestasiModal">
                            <span class="iconify" data-icon="mdi:plus" data-width="20" data-height="20"></span>
                            Tambah Prestasi
                        </button>
                    </div>
                </div>
                
                <!-- Advanced Filter Collapse -->
                <div class="collapse mb-3" id="advancedFilter">
                    <div class="card card-body bg-light">
                        <form method="GET" id="filterForm">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label">Tahun Ajaran</label>
                                    <select name="tahun_ajaran" class="form-select">
                                        <option value="">Semua Tahun Ajaran</option>
                                        @if(isset($tahunAjarans))
                                            @foreach($tahunAjarans as $ta)
                                            <option value="{{ $ta->id }}" {{ request('tahun_ajaran')==$ta->id ? 'selected' : '' }}>
                                                {{ $ta->nama_tahun_ajaran }} - {{ ucfirst($ta->semester) }}
                                            </option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Kelas</label>
                                    <select name="kelas" class="form-select">
                                        <option value="">Semua Kelas</option>
                                        @if(isset($kelasList))
                                            @foreach($kelasList as $kelas)
                                            <option value="{{ $kelas->id }}" {{ request('kelas')==$kelas->id ? 'selected' : '' }}>
                                                {{ $kelas->nama_kelas }}
                                            </option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Siswa</label>
                                    <select name="siswa" class="form-select">
                                        <option value="">Semua Siswa</option>
                                        @if(isset($siswaList))
                                            @foreach($siswaList as $siswa)
                                            <option value="{{ $siswa->id }}" {{ request('siswa')==$siswa->id ? 'selected' : '' }}>
                                                {{ $siswa->nama }} ({{ $siswa->kelas->nama_kelas ?? 'No Class' }})
                                            </option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Status</label>
                                    <select name="status" class="form-select">
                                        <option value="">Semua Status</option>
                                        <option value="draft" {{ request('status')=='draft' ? 'selected' : '' }}>Draft</option>
                                        <option value="menunggu_validasi" {{ request('status')=='menunggu_validasi' ? 'selected' : '' }}>Menunggu Validasi</option>
                                        <option value="diterima" {{ request('status')=='diterima' ? 'selected' : '' }}>Diterima</option>
                                        <option value="ditolak" {{ request('status')=='ditolak' ? 'selected' : '' }}>Ditolak</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Kategori</label>
                                    <select name="kategori" class="form-select">
                                        <option value="">Semua Kategori</option>
                                        @foreach($kategori as $id => $nama_kategori)
                                        <option value="{{ $id }}" {{ request('kategori')==$id ? 'selected' : '' }}>
                                            {{ $nama_kategori }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Tingkat Penghargaan</label>
                                    <select name="tingkat" class="form-select">
                                        <option value="">Semua Tingkat</option>
                                        @foreach($tingkat as $id => $nama_tingkat)
                                        <option value="{{ $id }}" {{ request('tingkat')==$id ? 'selected' : '' }}>
                                            {{ $nama_tingkat }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Ekstrakurikuler</label>
                                    <select name="ekstrakurikuler" class="form-select">
                                        <option value="">Semua Ekstrakurikuler</option>
                                        @if(isset($ekstrakurikulers))
                                            @foreach($ekstrakurikulers as $ekskul)
                                            <option value="{{ $ekskul->id }}" {{ request('ekstrakurikuler')==$ekskul->id ? 'selected' : '' }}>
                                                {{ $ekskul->nama }}
                                            </option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Rentang Tanggal</label>
                                    <div class="input-group">
                                        <input type="date" name="from" class="form-control" value="{{ request('from') }}" placeholder="Dari">
                                        <span class="input-group-text">s/d</span>
                                        <input type="date" name="to" class="form-control" value="{{ request('to') }}" placeholder="Sampai">
                                    </div>
                                </div>
                                <div class="col-md-6 d-flex align-items-end">
                                    <div class="btn-group w-100">
                                        <button type="submit" class="btn btn-primary">
                                            <span class="iconify" data-icon="mdi:magnify" data-width="20" data-height="20"></span>
                                            Terapkan Filter
                                        </button>
                                        <a href="{{ route('admin.prestasi_siswa.index') }}" class="btn btn-outline-secondary">
                                            <span class="iconify" data-icon="mdi:refresh" data-width="20" data-height="20"></span>
                                            Reset
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                @if ($errors->any())
                <div class="alert alert-danger mt-3">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <!-- Data Table -->
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-primary">
                            <tr>
                                <th width="5%">No</th>
                                <th>Nama Siswa</th>
                                <th>Kelas</th>
                                <th>Nama Prestasi</th>
                                <th>Kategori</th>
                                <th>Tingkat</th>
                                <th>Tanggal</th>
                                <th>Status</th>
                                <th width="15%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($prestasi as $p)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div>
                                            <strong>{{ $p->siswa->nama ?? '-' }}</strong><br>
                                            <small class="text-muted">{{ $p->siswa->nisn ?? '-' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-info">{{ $p->siswa->kelas->nama_kelas ?? 'No Class' }}</span>
                                </td>
                                <td>
                                    <strong>{{ $p->nama_prestasi }}</strong><br>
                                    <small class="text-muted">{{ $p->penyelenggara ?? '-' }}</small>
                                </td>
                                <td>{{ $p->kategori->nama_kategori ?? '-' }}</td>
                                <td>
                                    <span class="badge bg-warning">{{ $p->tingkat->tingkat ?? '-' }}</span>
                                </td>
                                <td>{{ $p->tanggal_prestasi ? \Carbon\Carbon::parse($p->tanggal_prestasi)->format('d-m-Y') : '-' }}</td>
                                <td>
                                    <span class="badge bg-{{ 
                                        $p->status == 'draft' ? 'secondary' :
                                        ($p->status == 'menunggu_validasi' ? 'warning' :
                                        ($p->status == 'diterima' ? 'success' :
                                        ($p->status == 'ditolak' ? 'danger' : 'secondary')))
                                    }}">
                                        {{ ucwords(str_replace('_', ' ', $p->status)) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button class="btn btn-info btn-sm" data-bs-toggle="modal"
                                            data-bs-target="#detailPrestasiModal{{ $p->id }}" title="Detail">
                                            <span class="iconify" data-icon="mdi:eye" data-width="16" data-height="16"></span>
                                        </button>
                                        <button class="btn btn-warning btn-sm" data-bs-toggle="modal"
                                            data-bs-target="#editPrestasiModal{{ $p->id }}" title="Edit">
                                            <span class="iconify" data-icon="mdi:pencil" data-width="16" data-height="16"></span>
                                        </button>
                                        @if($p->creator && $p->creator->role == 'guru' && $p->status == 'menunggu_validasi')
                                        <button class="btn btn-success btn-sm" data-bs-toggle="modal"
                                            data-bs-target="#validasiGuruModal{{ $p->id }}" title="Validasi Prestasi Guru">
                                            <span class="iconify" data-icon="mdi:check-circle" data-width="16" data-height="16"></span>
                                        </button>
                                        @endif
                                        <button class="btn btn-danger btn-sm" onclick="confirmDelete({{ $p->id }})" title="Hapus">
                                            <span class="iconify" data-icon="mdi:trash-can" data-width="16" data-height="16"></span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center py-4">
                                    <span class="iconify" data-icon="mdi:database-search-outline" data-width="48" data-height="48"></span>
                                    <br>Tidak ada data prestasi yang ditemukan
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                    
                    <!-- Pagination -->
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div>
                            Menampilkan {{ $prestasi->firstItem() ?? 0 }} - {{ $prestasi->lastItem() ?? 0 }} dari {{ $prestasi->total() }} data
                        </div>
                        <div>
                            {{ $prestasi->links("pagination::bootstrap-4") }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah -->
<div class="modal fade" id="createPrestasiModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form class="modal-content" method="POST" enctype="multipart/form-data" action="{{ route('admin.prestasi_siswa.store') }}">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">
                    <span class="iconify" data-icon="mdi:plus-circle" data-width="20" data-height="20"></span>
                    Tambah Prestasi Baru
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Siswa <span class="text-danger">*</span></label>
                            <select name="id_siswa" class="form-select" required>
                                <option value="">- Pilih Siswa -</option>
                                @if(isset($siswaList))
                                    @foreach($siswaList as $s)
                                    <option value="{{ $s->id }}">{{ $s->nama }} ({{ $s->kelas->nama_kelas ?? 'No Class' }})</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Tahun Ajaran</label>
                            <select name="id_tahun_ajaran" class="form-select">
                                <option value="">- Pilih Tahun Ajaran -</option>
                                @if(isset($tahunAjarans))
                                    @foreach($tahunAjarans as $ta)
                                    <option value="{{ $ta->id }}" {{ $ta->is_active ? 'selected' : '' }}>
                                        {{ $ta->nama_tahun_ajaran }} - {{ ucfirst($ta->semester) }}
                                    </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Kategori <span class="text-danger">*</span></label>
                            <select name="id_kategori_prestasi" class="form-select" required>
                                <option value="">- Pilih Kategori -</option>
                                @foreach($kategori as $id => $nama_kategori)
                                <option value="{{ $id }}">{{ $nama_kategori }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Tingkat Penghargaan <span class="text-danger">*</span></label>
                            <select name="id_tingkat_penghargaan" class="form-select" required>
                                <option value="">- Pilih Tingkat -</option>
                                @foreach($tingkat as $id => $nama_tingkat)
                                <option value="{{ $id }}">{{ $nama_tingkat }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Ekstrakurikuler</label>
                            <select name="id_ekskul" class="form-select">
                                <option value="">- Tidak Ada -</option>
                                @if(isset($ekstrakurikulers))
                                    @foreach($ekstrakurikulers as $ekskul)
                                    <option value="{{ $ekskul->id }}">{{ $ekskul->nama }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Tanggal Prestasi <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_prestasi" class="form-control" required>
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Nama Prestasi <span class="text-danger">*</span></label>
                    <input type="text" name="nama_prestasi" class="form-control" placeholder="Masukkan nama prestasi..." required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Penyelenggara <span class="text-danger">*</span></label>
                    <input type="text" name="penyelenggara" class="form-control" placeholder="Masukkan nama penyelenggara..." required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Keterangan</label>
                    <textarea name="keterangan" class="form-control" rows="2" placeholder="Keterangan tambahan (opsional)"></textarea>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select" required>
                                <option value="draft">Draft</option>
                                <option value="menunggu_validasi" selected>Menunggu Validasi</option>
                                <option value="diterima">Diterima</option>
                                <option value="ditolak">Ditolak</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Nilai Rata-rata</label>
                            <input type="number" name="rata_rata_nilai" step="0.01" min="0" max="100" class="form-control" placeholder="Nilai (jika akademik)">
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Dokumen Sertifikat</label>
                    <input type="file" name="dokumen_file" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                    <small class="text-muted">Format: PDF, JPG, PNG. Maksimal 2MB</small>
                </div>
                <div class="mb-3" id="alasanTolakDiv" style="display: none;">
                    <label class="form-label">Alasan Tolak</label>
                    <textarea name="alasan_tolak" class="form-control" rows="2" placeholder="Masukkan alasan penolakan..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <span class="iconify" data-icon="mdi:close" data-width="16" data-height="16"></span> Batal
                </button>
                <button type="submit" class="btn btn-primary">
                    <span class="iconify" data-icon="mdi:content-save" data-width="16" data-height="16"></span> Simpan
                </button>
            </div>
        </form>
    </div>
</div>

@foreach($prestasi as $p)
<!-- Modal Detail -->
<div class="modal fade" id="detailPrestasiModal{{ $p->id }}" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <span class="iconify" data-icon="mdi:information-outline" data-width="20" data-height="20"></span>
                    Detail Prestasi Siswa
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <th width="40%">Nama Siswa</th>
                                <td>{{ $p->siswa->nama ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>NISN</th>
                                <td>{{ $p->siswa->nisn ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Kelas</th>
                                <td><span class="badge bg-info">{{ $p->siswa->kelas->nama_kelas ?? 'No Class' }}</span></td>
                            </tr>
                            <tr>
                                <th>Nama Prestasi</th>
                                <td><strong>{{ $p->nama_prestasi }}</strong></td>
                            </tr>
                            <tr>
                                <th>Penyelenggara</th>
                                <td>{{ $p->penyelenggara }}</td>
                            </tr>
                            <tr>
                                <th>Tanggal</th>
                                <td>{{ $p->tanggal_prestasi ? \Carbon\Carbon::parse($p->tanggal_prestasi)->format('d M Y') : '-' }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <th width="40%">Kategori</th>
                                <td>{{ $p->kategori->nama_kategori ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Tingkat</th>
                                <td><span class="badge bg-warning text-dark">{{ $p->tingkat->tingkat ?? '-' }}</span></td>
                            </tr>
                            <tr>
                                <th>Ekstrakurikuler</th>
                                <td>{{ $p->ekskul->nama ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td>
                                    <span class="badge bg-{{ 
                                        $p->status == 'draft' ? 'secondary' :
                                        ($p->status == 'menunggu_validasi' ? 'warning' :
                                        ($p->status == 'diterima' ? 'success' :
                                        ($p->status == 'ditolak' ? 'danger' : 'secondary')))
                                    }}">
                                        {{ ucwords(str_replace('_', ' ', $p->status)) }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>Nilai Rata-rata</th>
                                <td>{{ $p->rata_rata_nilai ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Validator</th>
                                <td>{{ $p->validator->name ?? '-' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
                @if($p->keterangan)
                <div class="row mt-3">
                    <div class="col-12">
                        <h6>Keterangan:</h6>
                        <p class="text-muted">{{ $p->keterangan }}</p>
                    </div>
                </div>
                @endif
                @if($p->alasan_tolak)
                <div class="row mt-3">
                    <div class="col-12">
                        <h6 class="text-danger">Alasan Tolak:</h6>
                        <div class="alert alert-danger">{{ $p->alasan_tolak }}</div>
                    </div>
                </div>
                @endif
                @if($p->dokumen_url)
                <div class="row mt-3">
                    <div class="col-12">
                        <h6>Dokumen:</h6>
                        <a href="{{ asset($p->dokumen_url) }}" target="_blank" class="btn btn-outline-danger btn-sm">
                            <span class="iconify" data-icon="mdi:file-pdf-box" data-width="16" data-height="16"></span>
                            Lihat Dokumen
                        </a>
                    </div>
                </div>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Edit -->
<div class="modal fade" id="editPrestasiModal{{ $p->id }}" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form class="modal-content" method="POST" enctype="multipart/form-data" action="{{ route('admin.prestasi_siswa.update', $p->id) }}">
            @csrf
            @method('PUT')
            <div class="modal-header">
                <h5 class="modal-title">
                    <span class="iconify" data-icon="mdi:pencil" data-width="20" data-height="20"></span>
                    Edit Prestasi
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Siswa <span class="text-danger">*</span></label>
                            <select name="id_siswa" class="form-select" required>
                                <option value="">- Pilih Siswa -</option>
                                @if(isset($siswaList))
                                    @foreach($siswaList as $s)
                                    <option value="{{ $s->id }}" {{ $p->id_siswa == $s->id ? 'selected' : '' }}>
                                        {{ $s->nama }} ({{ $s->kelas->nama_kelas ?? 'No Class' }})
                                    </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Tahun Ajaran</label>
                            <select name="id_tahun_ajaran" class="form-select">
                                <option value="">- Pilih Tahun Ajaran -</option>
                                @if(isset($tahunAjarans))
                                    @foreach($tahunAjarans as $ta)
                                    <option value="{{ $ta->id }}" {{ $p->id_tahun_ajaran == $ta->id ? 'selected' : '' }}>
                                        {{ $ta->nama_tahun_ajaran }} - {{ ucfirst($ta->semester) }}
                                    </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Kategori <span class="text-danger">*</span></label>
                            <select name="id_kategori_prestasi" class="form-select" required>
                                <option value="">- Pilih Kategori -</option>
                                @foreach($kategori as $id => $nama_kategori)
                                <option value="{{ $id }}" {{ $p->id_kategori_prestasi == $id ? 'selected' : '' }}>{{ $nama_kategori }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Tingkat Penghargaan <span class="text-danger">*</span></label>
                            <select name="id_tingkat_penghargaan" class="form-select" required>
                                <option value="">- Pilih Tingkat -</option>
                                @foreach($tingkat as $id => $nama_tingkat)
                                <option value="{{ $id }}" {{ $p->id_tingkat_penghargaan == $id ? 'selected' : '' }}>{{ $nama_tingkat }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Ekstrakurikuler</label>
                            <select name="id_ekskul" class="form-select">
                                <option value="">- Tidak Ada -</option>
                                @if(isset($ekstrakurikulers))
                                    @foreach($ekstrakurikulers as $ekskul)
                                    <option value="{{ $ekskul->id }}" {{ $p->id_ekskul == $ekskul->id ? 'selected' : '' }}>{{ $ekskul->nama }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Tanggal Prestasi <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_prestasi" class="form-control" value="{{ $p->tanggal_prestasi }}" required>
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Nama Prestasi <span class="text-danger">*</span></label>
                    <input type="text" name="nama_prestasi" class="form-control" value="{{ $p->nama_prestasi }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Penyelenggara <span class="text-danger">*</span></label>
                    <input type="text" name="penyelenggara" class="form-control" value="{{ $p->penyelenggara }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Keterangan</label>
                    <textarea name="keterangan" class="form-control" rows="2">{{ $p->keterangan }}</textarea>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select" onchange="toggleAlasanTolakEdit{{ $p->id }}(this.value)" required>
                                <option value="draft" {{ $p->status == 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="menunggu_validasi" {{ $p->status == 'menunggu_validasi' ? 'selected' : '' }}>Menunggu Validasi</option>
                                <option value="diterima" {{ $p->status == 'diterima' ? 'selected' : '' }}>Diterima</option>
                                <option value="ditolak" {{ $p->status == 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Nilai Rata-rata</label>
                            <input type="number" name="rata_rata_nilai" step="0.01" min="0" max="100" class="form-control" value="{{ $p->rata_rata_nilai }}">
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Dokumen Sertifikat</label>
                    <input type="file" name="dokumen_file" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                    @if($p->dokumen_url)
                    <div class="mt-2">
                        <a href="{{ asset($p->dokumen_url) }}" target="_blank" class="btn btn-outline-danger btn-sm">
                            <span class="iconify" data-icon="mdi:file-pdf-box" data-width="16" data-height="16"></span>
                            Lihat Dokumen Lama
                        </a>
                    </div>
                    @endif
                    <small class="text-muted">Format: PDF, JPG, PNG. Maksimal 2MB</small>
                </div>
                <div class="mb-3" id="alasanTolakDivEdit{{ $p->id }}" style="display: {{ $p->status == 'ditolak' ? 'block' : 'none' }};">
                    <label class="form-label">Alasan Tolak</label>
                    <textarea name="alasan_tolak" class="form-control" rows="2">{{ $p->alasan_tolak }}</textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-success">
                    <span class="iconify" data-icon="mdi:content-save" data-width="16" data-height="16"></span>
                    Update
                </button>
            </div>
        </form>
    </div>
</div>
@endforeach

@foreach($prestasi as $p)
@if($p->creator && $p->creator->role == 'guru' && $p->status == 'menunggu_validasi')
<!-- Modal Validasi Guru -->
<div class="modal fade" id="validasiGuruModal{{ $p->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <span class="iconify" data-icon="mdi:check-circle" data-width="20" data-height="20"></span>
                    Validasi Prestasi Guru
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.prestasi_siswa.validasi_guru', $p->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <small><strong>Dibuat oleh:</strong> {{ $p->creator->name ?? 'Guru' }} ({{ ucfirst($p->creator->role ?? 'guru') }})</small>
                    </div>
                    
                    <div class="mb-3">
                        <strong>Prestasi:</strong> {{ $p->nama_prestasi }}<br>
                        <strong>Siswa:</strong> {{ $p->siswa->nama ?? '-' }}<br>
                        <strong>Kategori:</strong> {{ $p->kategori->nama_kategori ?? '-' }}<br>
                        <strong>Tingkat:</strong> {{ $p->tingkat->tingkat ?? '-' }}
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Status Validasi <span class="text-danger">*</span></label>
                        <select name="status" class="form-select" onchange="toggleAlasanTolakValidasi{{ $p->id }}(this.value)" required>
                            <option value="">Pilih Status</option>
                            <option value="diterima">Diterima</option>
                            <option value="ditolak">Ditolak</option>
                        </select>
                    </div>
                    
                    <div class="mb-3" id="alasanTolakDivValidasi{{ $p->id }}" style="display: none;">
                        <label class="form-label">Alasan Tolak</label>
                        <textarea name="alasan_tolak" class="form-control" rows="3" placeholder="Masukkan alasan penolakan..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <span class="iconify" data-icon="mdi:check" data-width="16" data-height="16"></span>
                        Validasi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endforeach

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// Export Functions
function exportStudentPortfolio() {
    const studentId = document.querySelector('select[name="siswa"]').value;
    if (!studentId) {
        Swal.fire({
            icon: 'warning',
            title: 'Pilih Siswa',
            text: 'Pilih siswa terlebih dahulu untuk export portfolio',
            confirmButtonText: 'OK'
        });
        return;
    }
    
    window.open(`{{ route('admin.prestasi_siswa.index') }}/portfolio/${studentId}`, '_blank');
}

function exportClassSummary() {
    const formData = new FormData(document.getElementById('filterForm'));
    const params = new URLSearchParams(formData);
    
    window.open(`{{ route('admin.prestasi_siswa.excel_report') }}?type=class&${params.toString()}`, '_blank');
}

function exportYearlyReport() {
    const formData = new FormData(document.getElementById('filterForm'));
    const params = new URLSearchParams(formData);
    
    window.open(`{{ route('admin.prestasi_siswa.cetak') }}?type=yearly&${params.toString()}`, '_blank');
}

// SweetAlert Delete Confirmation
function confirmDelete(prestasiId) {
    Swal.fire({
        title: 'Hapus Data Prestasi?',
        text: 'Data yang sudah dihapus tidak dapat dikembalikan!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `{{ route('admin.prestasi_siswa.index') }}/${prestasiId}`;
            form.innerHTML = `
                @csrf
                @method('DELETE')
            `;
            document.body.appendChild(form);
            form.submit();
        }
    });
}

// Toggle functions for status-dependent fields
@foreach($prestasi as $p)
function toggleAlasanTolakEdit{{ $p->id }}(status) {
    const alasanDiv = document.getElementById('alasanTolakDivEdit{{ $p->id }}');
    if (status === 'ditolak') {
        alasanDiv.style.display = 'block';
    } else {
        alasanDiv.style.display = 'none';
    }
}

@if($p->creator && $p->creator->role == 'guru' && $p->status == 'menunggu_validasi')
function toggleAlasanTolakValidasi{{ $p->id }}(status) {
    const alasanDiv = document.getElementById('alasanTolakDivValidasi{{ $p->id }}');
    if (status === 'ditolak') {
        alasanDiv.style.display = 'block';
    } else {
        alasanDiv.style.display = 'none';
    }
}
@endif
@endforeach

// Toggle for create modal
document.querySelector('select[name="status"]').addEventListener('change', function() {
    const alasanDiv = document.getElementById('alasanTolakDiv');
    if (this.value === 'ditolak') {
        alasanDiv.style.display = 'block';
    } else {
        alasanDiv.style.display = 'none';
    }
});

// Show success message if exists
@if(session('success'))
Swal.fire({
    icon: 'success',
    title: 'Berhasil!',
    text: '{{ session('success') }}',
    timer: 3000,
    showConfirmButton: false
});
@endif

// Show error message if exists
@if(session('error'))
Swal.fire({
    icon: 'error',
    title: 'Error!',
    text: '{{ session('error') }}',
    confirmButtonText: 'OK'
});
@endif
</script>

@endsection