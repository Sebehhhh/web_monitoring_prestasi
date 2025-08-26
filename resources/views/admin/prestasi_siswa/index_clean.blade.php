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

<!-- Continue with modals from original file... -->
<!-- (I'll add the modals in the next part to keep the response manageable) -->

<script>
// Export Functions
function exportStudentPortfolio() {
    // Get selected student or show selection modal
    const studentId = document.querySelector('select[name="siswa"]').value;
    if (!studentId) {
        alert('Pilih siswa terlebih dahulu untuk export portfolio');
        return;
    }
    
    // Redirect to portfolio export route
    window.open(`{{ route('admin.prestasi_siswa.index') }}/portfolio/${studentId}`, '_blank');
}

function exportClassSummary() {
    // Get current filters
    const formData = new FormData(document.getElementById('filterForm'));
    const params = new URLSearchParams(formData);
    
    // Redirect to class summary export
    window.open(`{{ route('admin.prestasi_siswa.excel_report') }}?type=class&${params.toString()}`, '_blank');
}

function exportYearlyReport() {
    // Get current filters
    const formData = new FormData(document.getElementById('filterForm'));
    const params = new URLSearchParams(formData);
    
    // Redirect to yearly report export
    window.open(`{{ route('admin.prestasi_siswa.cetak') }}?type=yearly&${params.toString()}`, '_blank');
}

function confirmDelete(id) {
    if (confirm('Apakah Anda yakin ingin menghapus prestasi ini?')) {
        fetch(`{{ route('admin.prestasi_siswa.index') }}/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
            },
        }).then(response => {
            if (response.ok) {
                location.reload();
            } else {
                alert('Gagal menghapus data');
            }
        });
    }
}
</script>

@endsection