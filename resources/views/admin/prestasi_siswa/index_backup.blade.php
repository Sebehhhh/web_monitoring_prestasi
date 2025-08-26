@extends('layouts.app')
@section('title', 'Prestasi Siswa')

@section('content')
<!-- Iconify CDN -->
<script src="https://code.iconify.design/3/3.1.0/iconify.min.js"></script>
<div class="row">
    <div class="col-lg-12">
        <div class="card w-100">
            <div class="card-body">
                <!-- Filter & Action -->
                <div class="d-md-flex align-items-center justify-content-between mb-3">
                    <h4 class="card-title">Prestasi Siswa - Data Management</h4>
                    <div class="d-flex gap-2">
                        <!-- Advanced Filter Toggle -->
                        <button class="btn btn-outline-secondary" type="button" data-bs-toggle="collapse" 
                                data-bs-target="#advancedFilter" aria-expanded="false" aria-controls="advancedFilter">
                            <span class="iconify" data-icon="mdi:filter-settings" data-width="20" data-height="20"></span>
                            Filter Lanjutan
                        </button>
                        <a href="{{ route('admin.prestasi_siswa.cetak', request()->all()) }}" class="btn btn-success"
                            target="_blank" title="Cetak PDF">
                            <span class="iconify" data-icon="mdi:printer" data-width="20" data-height="20"></span>
                        </a>
                        <a href="{{ route('admin.prestasi_siswa.excel_report', request()->all()) }}" class="btn btn-info"
                            title="Export Excel">
                            <span class="iconify" data-icon="mdi:file-excel" data-width="20" data-height="20"></span>
                        </a>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createPrestasiModal">
                            <span class="iconify" data-icon="mdi:plus" data-width="20" data-height="20"></span>
                        </button>
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

                <!-- Navigation Tabs -->
                <ul class="nav nav-tabs mt-4" id="prestasiTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="data-tab" data-bs-toggle="tab" data-bs-target="#data-panel" 
                                type="button" role="tab" aria-controls="data-panel" aria-selected="true">
                            <span class="iconify" data-icon="mdi:table" data-width="20" data-height="20"></span>
                            Data Prestasi
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="analytics-tab" data-bs-toggle="tab" data-bs-target="#analytics-panel" 
                                type="button" role="tab" aria-controls="analytics-panel" aria-selected="false" onclick="loadAnalytics()">
                            <span class="iconify" data-icon="mdi:chart-line" data-width="20" data-height="20"></span>
                            Analytics & Grafik
                        </button>
                    </li>
                </ul>

                <!-- Tab Content -->
                <div class="tab-content mt-4" id="prestasiTabContent">
                    <!-- Data Panel -->
                    <div class="tab-pane fade show active" id="data-panel" role="tabpanel" aria-labelledby="data-tab">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Nama Siswa</th>
                                        <th>Nama Prestasi</th>
                                        <th>Kategori</th>
                                        <th>Tingkat</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($prestasi as $p)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                            <a href="#" onclick="showStudentAnalysis({{ $p->siswa->id }})" 
                                               class="text-decoration-none" title="Lihat Analisa Siswa">
                                                {{ $p->siswa->nama ?? '-' }}
                                                <span class="iconify" data-icon="mdi:chart-box-outline" data-width="14" data-height="14"></span>
                                            </a>
                                        </td>
                                        <td>{{ $p->nama_prestasi }}</td>
                                        <td>{{ $p->kategori->nama_kategori ?? '-' }}</td>
                                        <td>{{ $p->tingkat->tingkat ?? '-' }}</td>
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
                                            <button class="btn btn-info btn-sm" data-bs-toggle="modal"
                                                data-bs-target="#detailPrestasiModal{{ $p->id }}" title="Detail">
                                                <span class="iconify" data-icon="mdi:eye" data-width="18" data-height="18"></span>
                                            </button>
                                            <button class="btn btn-warning btn-sm" data-bs-toggle="modal"
                                                data-bs-target="#editPrestasiModal{{ $p->id }}" title="Edit">
                                                <span class="iconify" data-icon="mdi:pencil" data-width="18" data-height="18"></span>
                                            </button>
                                            @if($p->creator && $p->creator->role == 'guru' && $p->status == 'menunggu_validasi')
                                            <button class="btn btn-success btn-sm" data-bs-toggle="modal"
                                                data-bs-target="#validasiGuruModal{{ $p->id }}" title="Validasi Prestasi Guru">
                                                <span class="iconify" data-icon="mdi:check-circle" data-width="18" data-height="18"></span>
                                            </button>
                                            @endif
                                            <button class="btn btn-danger btn-sm" onclick="confirmDelete({{ $p->id }})"
                                                title="Hapus">
                                                <span class="iconify" data-icon="mdi:trash-can" data-width="18" data-height="18"></span>
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            {{ $prestasi->links("pagination::bootstrap-4") }}
                        </div>
                    </div>

                    <!-- Analytics Panel -->
                    <div class="tab-pane fade" id="analytics-panel" role="tabpanel" aria-labelledby="analytics-tab">
                        <div id="analytics-loading" class="text-center py-5" style="display: none;">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2">Memuat data analytics...</p>
                        </div>
                        
                        <div id="analytics-content" style="display: none;">
                            <!-- Multi-Year Comparison Chart -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="card-title mb-0">
                                                <span class="iconify" data-icon="mdi:chart-timeline" data-width="20" data-height="20"></span>
                                                Perbandingan Prestasi Per Tahun Ajaran
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <div id="multiYearChart"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Category & Class Performance Charts -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="card-title mb-0">
                                                <span class="iconify" data-icon="mdi:chart-pie" data-width="20" data-height="20"></span>
                                                Distribusi Kategori Prestasi
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <div id="categoryChart"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="card-title mb-0">
                                                <span class="iconify" data-icon="mdi:chart-bar" data-width="20" data-height="20"></span>
                                                Prestasi Per Kelas
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <div id="classChart"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Monthly Trends & Top Students -->
                            <div class="row mb-4">
                                <div class="col-md-8">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="card-title mb-0">
                                                <span class="iconify" data-icon="mdi:chart-areaspline" data-width="20" data-height="20"></span>
                                                Trend Prestasi Bulanan
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <div id="monthlyChart"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="card-title mb-0">
                                                <span class="iconify" data-icon="mdi:trophy" data-width="20" data-height="20"></span>
                                                Top 10 Siswa Berprestasi
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <div id="topStudentsList"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@foreach($prestasi as $p)
<!-- Modal Detail -->
<div class="modal fade" id="detailPrestasiModal{{ $p->id }}" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Prestasi Siswa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <table class="table table-borderless">
                    <tr>
                        <th>Nama Siswa</th>
                        <td>{{ $p->siswa->nama ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Nama Prestasi</th>
                        <td>{{ $p->nama_prestasi }}</td>
                    </tr>
                    <tr>
                        <th>Kategori</th>
                        <td>{{ $p->kategori->nama_kategori ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Tingkat</th>
                        <td>{{ $p->tingkat->tingkat ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Ekskul</th>
                        <td>{{ $p->ekskul->nama ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Penyelenggara</th>
                        <td>{{ $p->penyelenggara }}</td>
                    </tr>
                    <tr>
                        <th>Tanggal</th>
                        <td>{{ $p->tanggal_prestasi ? \Carbon\Carbon::parse($p->tanggal_prestasi)->format('d-m-Y') : '-'
                            }}</td>
                    </tr>
                    <tr>
                        <th>Keterangan</th>
                        <td>{{ $p->keterangan ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Dokumen</th>
                        <td>
                            @if($p->dokumen_url)
                            <a href="{{ asset($p->dokumen_url) }}" target="_blank">
                                <span class="iconify text-danger" data-icon="mdi:file-pdf-box" data-width="20" data-height="20"></span> Lihat Dokumen
                            </a>
                            @else
                            -
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td>
                            <span class="badge bg-{{ 
                                $p->status == 'draft' ? 'secondary'
                                : ($p->status == 'menunggu_validasi' ? 'warning'
                                : ($p->status == 'diterima' ? 'success'
                                : ($p->status == 'ditolak' ? 'danger' : 'secondary')))
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
                    <tr>
                        <th>Waktu Validasi</th>
                        <td>{{ $p->validated_at ? \Carbon\Carbon::parse($p->validated_at)->format('d-m-Y H:i') : '-' }}
                        </td>
                    </tr>
                    <tr>
                        <th>Alasan Tolak</th>
                        <td>{{ $p->alasan_tolak ?? '-' }}</td>
                    </tr>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endforeach

@foreach($prestasi as $p)
<!-- Modal Edit -->
<div class="modal fade" id="editPrestasiModal{{ $p->id }}" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form class="modal-content" method="POST" enctype="multipart/form-data"
            action="{{ route('admin.prestasi_siswa.update', $p->id) }}">
            @csrf
            @method('PUT')
            <div class="modal-header">
                <h5 class="modal-title">Edit Prestasi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label>Siswa</label>
                    <select name="id_siswa" class="form-control" required>
                        <option value="">- Pilih Siswa -</option>
                        @foreach($siswa as $id => $nama)
                        <option value="{{ $id }}" {{ $p->id_siswa == $id ? 'selected' : '' }}>{{ $nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label>Kategori</label>
                    <select name="id_kategori_prestasi" class="form-control" required>
                        <option value="">- Pilih Kategori -</option>
                        @foreach($kategori as $id => $nama_kategori)
                        <option value="{{ $id }}" {{ $p->id_kategori_prestasi == $id ? 'selected' : '' }}>{{
                            $nama_kategori }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label>Tingkat</label>
                    <select name="id_tingkat_penghargaan" class="form-control" required>
                        <option value="">- Pilih Tingkat -</option>
                        @foreach($tingkat as $id => $nama_tingkat)
                        <option value="{{ $id }}" {{ $p->id_tingkat_penghargaan == $id ? 'selected' : '' }}>{{
                            $nama_tingkat }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label>Ekstrakurikuler</label>
                    <select name="id_ekskul" class="form-control">
                        <option value="">- Tidak Ada -</option>
                        @foreach($ekskul as $id => $nama_ekskul)
                        <option value="{{ $id }}" {{ $p->id_ekskul == $id ? 'selected' : '' }}>{{ $nama_ekskul }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label>Nama Prestasi</label>
                    <input type="text" name="nama_prestasi" class="form-control" value="{{ $p->nama_prestasi }}"
                        required>
                </div>
                <div class="mb-3">
                    <label>Penyelenggara</label>
                    <input type="text" name="penyelenggara" class="form-control" value="{{ $p->penyelenggara }}"
                        required>
                </div>
                <div class="mb-3">
                    <label>Tanggal Prestasi</label>
                    <input type="date" name="tanggal_prestasi" class="form-control" value="{{ $p->tanggal_prestasi }}">
                </div>
                <div class="mb-3">
                    <label>Keterangan</label>
                    <input type="text" name="keterangan" class="form-control" value="{{ $p->keterangan }}">
                </div>
                <div class="mb-3">
                    <label>Dokumen Sertifikat (PDF/JPG/PNG, opsional)</label>
                    <input type="file" name="dokumen_file" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                    @if(isset($p) && $p->dokumen_url)
                    <div class="mt-2">
                        <a href="{{ asset($p->dokumen_url) }}" target="_blank">
                            <span class="iconify text-danger" data-icon="mdi:file-pdf-box" data-width="20" data-height="20"></span> Lihat Dokumen Lama
                        </a>
                    </div>
                    @endif
                </div>
                <div class="mb-3">
                    <label>Status</label>
                    <select name="status" class="form-control" required>
                        <option value="draft" {{ $p->status == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="menunggu_validasi" {{ $p->status == 'menunggu_validasi' ? 'selected' : ''
                            }}>Menunggu Validasi</option>
                        <option value="diterima" {{ $p->status == 'diterima' ? 'selected' : '' }}>Diterima</option>
                        <option value="ditolak" {{ $p->status == 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label>Nilai Rata-rata (jika akademik)</label>
                    <input type="number" name="rata_rata_nilai" step="0.01" class="form-control"
                        value="{{ $p->rata_rata_nilai }}">
                </div>
                <div class="mb-3">
                    <label>Alasan Tolak</label>
                    <input type="text" name="alasan_tolak" class="form-control" value="{{ $p->alasan_tolak }}">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-success">Update</button>
            </div>
        </form>
    </div>
</div>
@endforeach

<!-- Modal Validasi Guru untuk setiap prestasi -->
@foreach($prestasi as $p)
@if($p->creator && $p->creator->role == 'guru' && $p->status == 'menunggu_validasi')
<div class="modal fade" id="validasiGuruModal{{ $p->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Validasi Prestasi Guru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.prestasi_siswa.validasi_guru', $p->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <small><strong>Dibuat oleh:</strong> {{ $p->creator->nama ?? 'Guru' }} ({{ $p->creator->role ?? 'guru' }})</small>
                    </div>
                    
                    <div class="mb-3">
                        <strong>Prestasi:</strong> {{ $p->nama_prestasi }}<br>
                        <strong>Siswa:</strong> {{ $p->siswa->nama ?? '-' }}<br>
                        <strong>Kategori:</strong> {{ $p->kategori->nama_kategori ?? '-' }}<br>
                        <strong>Tingkat:</strong> {{ $p->tingkat->tingkat ?? '-' }}
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Status Validasi <span class="text-danger">*</span></label>
                        <select name="status" class="form-control" onchange="toggleAlasanTolakGuruValidasi{{ $p->id }}(this.value)" required>
                            <option value="">Pilih Status</option>
                            <option value="diterima">Diterima</option>
                            <option value="ditolak">Ditolak</option>
                        </select>
                    </div>
                    
                    <div class="mb-3" id="alasanTolakDivGuruValidasi{{ $p->id }}" style="display: none;">
                        <label class="form-label">Alasan Tolak</label>
                        <textarea name="alasan_tolak" class="form-control" rows="3" placeholder="Masukkan alasan penolakan..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Validasi</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endforeach

<!-- Modal Tambah -->
<div class="modal fade" id="createPrestasiModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form class="modal-content" method="POST" enctype="multipart/form-data"
            action="{{ route('admin.prestasi_siswa.store') }}">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Tambah Prestasi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label>Siswa</label>
                    <select name="id_siswa" class="form-control" required>
                        <option value="">- Pilih Siswa -</option>
                        @foreach($siswa as $id => $nama)
                        <option value="{{ $id }}">{{ $nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label>Kategori</label>
                    <select name="id_kategori_prestasi" class="form-control" required>
                        <option value="">- Pilih Kategori -</option>
                        @foreach($kategori as $id => $nama_kategori)
                        <option value="{{ $id }}">{{ $nama_kategori }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label>Tingkat</label>
                    <select name="id_tingkat_penghargaan" class="form-control" required>
                        <option value="">- Pilih Tingkat -</option>
                        @foreach($tingkat as $id => $nama_tingkat)
                        <option value="{{ $id }}">{{ $nama_tingkat }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label>Ekstrakurikuler</label>
                    <select name="id_ekskul" class="form-control">
                        <option value="">- Tidak Ada -</option>
                        @foreach($ekskul as $id => $nama_ekskul)
                        <option value="{{ $id }}">{{ $nama_ekskul }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label>Nama Prestasi</label>
                    <input type="text" name="nama_prestasi" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Penyelenggara</label>
                    <input type="text" name="penyelenggara" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Tanggal Prestasi</label>
                    <input type="date" name="tanggal_prestasi" class="form-control">
                </div>
                <div class="mb-3">
                    <label>Keterangan</label>
                    <input type="text" name="keterangan" class="form-control">
                </div>
                <div class="mb-3">
                    <label>Dokumen Sertifikat (PDF/JPG/PNG, opsional)</label>
                    <input type="file" name="dokumen_file" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                    {{-- Tidak perlu tampilkan "Lihat Dokumen Lama" di modal tambah --}}
                </div>
                <div class="mb-3">
                    <label>Status</label>
                    <select name="status" class="form-control" required>
                        <option value="draft">Draft</option>
                        <option value="menunggu_validasi">Menunggu Validasi</option>
                        <option value="diterima">Diterima</option>
                        <option value="ditolak">Ditolak</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label>Nilai Rata-rata (jika akademik)</label>
                    <input type="number" name="rata_rata_nilai" step="0.01" class="form-control">
                </div>
                <div class="mb-3">
                    <label>Alasan Tolak</label>
                    <input type="text" name="alasan_tolak" class="form-control">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Student Analysis Modal -->
<div class="modal fade" id="studentAnalysisModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <span class="iconify" data-icon="mdi:account-search" data-width="20" data-height="20"></span>
                    Analisa Prestasi Siswa
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="student-analysis-loading" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Memuat analisa siswa...</p>
                </div>
                
                <div id="student-analysis-content" style="display: none;">
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <h6 class="mb-1">Informasi Siswa</h6>
                                            <p id="student-info" class="mb-0"></p>
                                        </div>
                                        <div class="col-md-4 text-end">
                                            <h6 class="mb-1">Total Prestasi</h6>
                                            <h4 id="student-total-prestasi" class="text-primary mb-0">0</h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">Timeline Prestasi</h6>
                                </div>
                                <div class="card-body">
                                    <div id="studentTimelineChart"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">Prestasi Per Kategori</h6>
                                </div>
                                <div class="card-body">
                                    <div id="studentCategoryChart"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Analytics functionality
    let analyticsLoaded = false;
    
    function loadAnalytics() {
        if (analyticsLoaded) return;
        
        document.getElementById('analytics-loading').style.display = 'block';
        document.getElementById('analytics-content').style.display = 'none';
        
        // Get current filter values
        const filters = {
            kategori: document.querySelector('select[name="kategori"]').value,
            tingkat: document.querySelector('select[name="tingkat"]').value,
            from: document.querySelector('input[name="from"]').value,
            to: document.querySelector('input[name="to"]').value
        };
        
        // Build query string
        const queryString = Object.keys(filters)
            .filter(key => filters[key])
            .map(key => `${key}=${encodeURIComponent(filters[key])}`)
            .join('&');
            
        fetch(`{{ route('admin.prestasi_siswa.analytics_data') }}?${queryString}`)
            .then(response => response.json())
            .then(data => {
                renderAnalyticsCharts(data);
                analyticsLoaded = true;
                document.getElementById('analytics-loading').style.display = 'none';
                document.getElementById('analytics-content').style.display = 'block';
            })
            .catch(error => {
                console.error('Error loading analytics:', error);
                document.getElementById('analytics-loading').innerHTML = '<div class="alert alert-danger">Gagal memuat data analytics</div>';
            });
    }
    
    function renderAnalyticsCharts(data) {
        // Multi-Year Comparison Chart
        if (data.multiYear.length > 0) {
            const multiYearOptions = {
                series: [{
                    name: 'Total Prestasi',
                    data: data.multiYear.map(item => ({
                        x: item.nama_tahun_ajaran,
                        y: item.total_prestasi
                    }))
                }],
                chart: {
                    type: 'line',
                    height: 350,
                    toolbar: { show: true }
                },
                stroke: {
                    curve: 'smooth',
                    width: 3
                },
                markers: {
                    size: 6
                },
                xaxis: {
                    type: 'category',
                    title: { text: 'Tahun Ajaran' }
                },
                yaxis: {
                    title: { text: 'Jumlah Prestasi' }
                },
                title: {
                    text: 'Perbandingan Prestasi Antar Tahun',
                    align: 'center'
                },
                colors: ['#007bff']
            };
            
            new ApexCharts(document.querySelector("#multiYearChart"), multiYearOptions).render();
        }
        
        // Category Distribution Chart
        if (data.categories.length > 0) {
            const categoryOptions = {
                series: data.categories.map(item => item.total),
                chart: {
                    type: 'donut',
                    height: 350
                },
                labels: data.categories.map(item => item.nama_kategori),
                title: {
                    text: 'Distribusi Kategori',
                    align: 'center'
                },
                colors: ['#007bff', '#28a745', '#ffc107', '#dc3545', '#6f42c1', '#20c997']
            };
            
            new ApexCharts(document.querySelector("#categoryChart"), categoryOptions).render();
        }
        
        // Class Performance Chart
        if (data.classPerformance.length > 0) {
            const classOptions = {
                series: [{
                    name: 'Total Prestasi',
                    data: data.classPerformance.map(item => ({
                        x: item.nama_kelas,
                        y: item.total_prestasi
                    }))
                }],
                chart: {
                    type: 'bar',
                    height: 350
                },
                xaxis: {
                    type: 'category',
                    title: { text: 'Kelas' }
                },
                yaxis: {
                    title: { text: 'Jumlah Prestasi' }
                },
                title: {
                    text: 'Prestasi Per Kelas',
                    align: 'center'
                },
                colors: ['#28a745']
            };
            
            new ApexCharts(document.querySelector("#classChart"), classOptions).render();
        }
        
        // Monthly Trends Chart
        if (data.monthlyTrends.length > 0) {
            const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
            
            const monthlyOptions = {
                series: [{
                    name: 'Prestasi',
                    data: data.monthlyTrends.map(item => ({
                        x: `${monthNames[item.bulan - 1]} ${item.tahun}`,
                        y: item.total
                    }))
                }],
                chart: {
                    type: 'area',
                    height: 350
                },
                stroke: {
                    curve: 'smooth',
                    width: 2
                },
                fill: {
                    type: 'gradient',
                    gradient: {
                        shade: 'light',
                        type: 'vertical',
                        shadeIntensity: 0.5,
                        opacityFrom: 0.8,
                        opacityTo: 0.1
                    }
                },
                xaxis: {
                    type: 'category',
                    title: { text: 'Periode' }
                },
                yaxis: {
                    title: { text: 'Jumlah Prestasi' }
                },
                title: {
                    text: 'Trend Prestasi Bulanan',
                    align: 'center'
                },
                colors: ['#6f42c1']
            };
            
            new ApexCharts(document.querySelector("#monthlyChart"), monthlyOptions).render();
        }
        
        // Top Students List
        if (data.topStudents.length > 0) {
            let topStudentsHtml = '<div class="list-group">';
            data.topStudents.forEach((student, index) => {
                const badgeClass = index === 0 ? 'bg-warning' : (index === 1 ? 'bg-secondary' : (index === 2 ? 'bg-info' : 'bg-light text-dark'));
                topStudentsHtml += `
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">${student.nama}</h6>
                            <small class="text-muted">Rank ${index + 1}</small>
                        </div>
                        <span class="badge ${badgeClass} rounded-pill">${student.total_prestasi}</span>
                    </div>
                `;
            });
            topStudentsHtml += '</div>';
            document.getElementById('topStudentsList').innerHTML = topStudentsHtml;
        }
    }
    
    function showStudentAnalysis(studentId) {
        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('studentAnalysisModal'));
        modal.show();
        
        // Show loading
        document.getElementById('student-analysis-loading').style.display = 'block';
        document.getElementById('student-analysis-content').style.display = 'none';
        
        fetch(`{{ url('/admin/prestasi_siswa/student-analysis') }}/${studentId}`)
            .then(response => response.json())
            .then(data => {
                // Update student info
                document.getElementById('student-info').innerHTML = 
                    `<strong>${data.student.nama}</strong> - ${data.student.kelas.nama_kelas}`;
                document.getElementById('student-total-prestasi').textContent = data.achievements.length;
                
                // Render student charts
                renderStudentCharts(data);
                
                document.getElementById('student-analysis-loading').style.display = 'none';
                document.getElementById('student-analysis-content').style.display = 'block';
            })
            .catch(error => {
                console.error('Error loading student analysis:', error);
                document.getElementById('student-analysis-loading').innerHTML = 
                    '<div class="alert alert-danger">Gagal memuat data siswa</div>';
            });
    }
    
    function renderStudentCharts(data) {
        // Student Timeline Chart
        if (data.achievements.length > 0) {
            const timelineOptions = {
                series: [{
                    name: 'Prestasi',
                    data: data.achievements.map(achievement => ({
                        x: new Date(achievement.tanggal_prestasi).getTime(),
                        y: 1
                    }))
                }],
                chart: {
                    type: 'scatter',
                    height: 300
                },
                xaxis: {
                    type: 'datetime',
                    title: { text: 'Tanggal' }
                },
                yaxis: {
                    show: false
                },
                title: {
                    text: 'Timeline Prestasi Siswa',
                    align: 'center'
                },
                colors: ['#007bff']
            };
            
            new ApexCharts(document.querySelector("#studentTimelineChart"), timelineOptions).render();
        }
        
        // Student Category Chart
        if (data.achievementsByCategory.length > 0) {
            const studentCategoryOptions = {
                series: data.achievementsByCategory.map(item => item.total),
                chart: {
                    type: 'pie',
                    height: 300
                },
                labels: data.achievementsByCategory.map(item => item.nama_kategori),
                title: {
                    text: 'Prestasi Per Kategori',
                    align: 'center'
                }
            };
            
            new ApexCharts(document.querySelector("#studentCategoryChart"), studentCategoryOptions).render();
        }
    }
    
    // Function for guru validation modals
    @foreach($prestasi as $p)
    @if($p->creator && $p->creator->role == 'guru' && $p->status == 'menunggu_validasi')
    function toggleAlasanTolakGuruValidasi{{ $p->id }}(status) {
        const alasanDiv = document.getElementById('alasanTolakDivGuruValidasi{{ $p->id }}');
        if (status === 'ditolak') {
            alasanDiv.style.display = 'block';
        } else {
            alasanDiv.style.display = 'none';
        }
    }
    @endif
    @endforeach

    function confirmDelete(prestasiId) {
    Swal.fire({
      title: 'Yakin ingin menghapus data ini?',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#6c757d',
      confirmButtonText: 'Ya, Hapus'
    }).then((result) => {
      if (result.isConfirmed) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/prestasi_siswa/${prestasiId}`;
        form.innerHTML = '@csrf @method("DELETE")';
        document.body.appendChild(form);
        form.submit();
      }
    });
  }
</script>
@endsection