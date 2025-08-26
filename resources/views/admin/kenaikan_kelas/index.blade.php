@extends('layouts.app')
@section('title', 'Kenaikan Kelas')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title">
                        <i class="ti ti-arrow-up-circle me-2"></i>Manajemen Kenaikan Kelas
                    </h4>
                    <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#processAllModal">
                        <i class="ti ti-rocket me-1"></i>Proses Semua Kelas
                    </button>
                </div>
                <div class="card-body">
                    @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    @endif

                    @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    @endif

                    <!-- Advanced Filters -->
                    <form method="GET" class="row g-3 mb-4">
                        <div class="col-md-3">
                            <label class="form-label">Tahun Ajaran - Semester</label>
                            <select name="tahun_ajaran_id" class="form-select" onchange="this.form.submit()">
                                @foreach($tahunAjarans as $ta)
                                <option value="{{ $ta->id }}" {{ $tahunAjaranId == $ta->id ? 'selected' : '' }}>
                                    {{ $ta->nama_tahun_ajaran }} - {{ ucfirst($ta->semester) }} {{ $ta->is_active ? '(Aktif)' : '' }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Filter Tingkat</label>
                            <select name="jenis_filter" class="form-select" onchange="this.form.submit()">
                                <option value="">Semua Tingkat</option>
                                <option value="X" {{ request('jenis_filter') == 'X' ? 'selected' : '' }}>Kelas X</option>
                                <option value="XI" {{ request('jenis_filter') == 'XI' ? 'selected' : '' }}>Kelas XI</option>
                                <option value="XII" {{ request('jenis_filter') == 'XII' ? 'selected' : '' }}>Kelas XII</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Filter Kelas Spesifik</label>
                            <select name="kelas_filter" class="form-select" onchange="this.form.submit()">
                                <option value="">Semua Kelas</option>
                                @foreach($allKelas as $kelas)
                                <option value="{{ $kelas->id }}" {{ request('kelas_filter') == $kelas->id ? 'selected' : '' }}>
                                    {{ $kelas->nama_kelas }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-flex gap-2">
                                <a href="{{ route('admin.kenaikan_kelas.index') }}" class="btn btn-outline-secondary">
                                    <i class="ti ti-refresh"></i> Reset
                                </a>
                            </div>
                        </div>
                        <input type="hidden" name="tahun_ajaran_id" value="{{ $tahunAjaranId }}">
                    </form>

                    @if(!empty($klasifikasi))
                    <!-- Statistics Overview -->
                    @if(isset($statistik))
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="alert alert-info">
                                <h6 class="mb-2"><i class="ti ti-chart-bar me-2"></i>Statistik Kenaikan Kelas</h6>
                                <div class="row">
                                    <div class="col-md-2 text-center">
                                        <strong>{{ $statistik['total_siswa_x'] }}</strong><br>
                                        <small>Siswa Kelas X</small>
                                    </div>
                                    <div class="col-md-2 text-center">
                                        <strong>{{ $statistik['total_siswa_xi'] }}</strong><br>
                                        <small>Siswa Kelas XI</small>
                                    </div>
                                    <div class="col-md-2 text-center">
                                        <strong>{{ $statistik['total_siswa_xii'] }}</strong><br>
                                        <small>Siswa Kelas XII</small>
                                    </div>
                                    <div class="col-md-2 text-center">
                                        <strong class="text-primary">{{ $statistik['siap_naik_x_xi'] }}</strong><br>
                                        <small>Siap X → XI</small>
                                    </div>
                                    <div class="col-md-2 text-center">
                                        <strong class="text-primary">{{ $statistik['siap_naik_xi_xii'] }}</strong><br>
                                        <small>Siap XI → XII</small>
                                    </div>
                                    <div class="col-md-2 text-center">
                                        <strong class="text-success">{{ $statistik['siap_lulus'] }}</strong><br>
                                        <small>Siap Lulus</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    <!-- Klasifikasi Kelas X -->
                    @if(!empty($klasifikasi['X']))
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5 class="text-info">
                                <i class="ti ti-school me-2"></i>Kelas X (Akan Naik ke XI)
                            </h5>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="table-info">
                                        <tr>
                                            <th>Kelas Asal</th>
                                            <th>Jumlah Siswa</th>
                                            <th>Kelas Tujuan</th>
                                            <th>Status</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($klasifikasi['X'] as $kelas)
                                        <tr>
                                            <td><strong>{{ $kelas['nama_kelas'] }}</strong></td>
                                            <td>
                                                <span class="badge bg-info fs-6">{{ $kelas['jumlah_siswa'] }} Siswa</span>
                                            </td>
                                            <td>
                                                @if($kelas['can_promote'])
                                                <span class="text-success fw-bold">{{ $kelas['next_class'] }}</span>
                                                @else
                                                <span class="text-danger">Kelas XI tidak tersedia</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($kelas['jumlah_siswa'] > 0)
                                                <span class="badge bg-warning">Siap Naik Kelas</span>
                                                @else
                                                <span class="badge bg-secondary">Kosong</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($kelas['can_promote'] && $kelas['jumlah_siswa'] > 0)
                                                <button class="btn btn-info btn-sm" 
                                                        onclick="processKenaikan('{{ $kelas['id'] }}', '{{ $kelas['nama_kelas'] }}', '{{ $kelas['next_class'] }}', {{ $kelas['jumlah_siswa'] }})">
                                                    <i class="ti ti-arrow-up me-1"></i>Naik Kelas
                                                </button>
                                                @else
                                                <button class="btn btn-secondary btn-sm" disabled>
                                                    <i class="ti ti-ban me-1"></i>Tidak Bisa
                                                </button>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Klasifikasi Kelas XI -->
                    @if(!empty($klasifikasi['XI']))
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5 class="text-primary">
                                <i class="ti ti-school me-2"></i>Kelas XI (Akan Naik ke XII)
                            </h5>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="table-primary">
                                        <tr>
                                            <th>Kelas Asal</th>
                                            <th>Jumlah Siswa</th>
                                            <th>Kelas Tujuan</th>
                                            <th>Status</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($klasifikasi['XI'] as $kelas)
                                        <tr>
                                            <td><strong>{{ $kelas['nama_kelas'] }}</strong></td>
                                            <td>
                                                <span class="badge bg-info fs-6">{{ $kelas['jumlah_siswa'] }} Siswa</span>
                                            </td>
                                            <td>
                                                @if($kelas['can_promote'])
                                                <span class="text-success fw-bold">{{ $kelas['next_class'] }}</span>
                                                @else
                                                <span class="text-danger">Kelas XII tidak tersedia</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($kelas['jumlah_siswa'] > 0)
                                                <span class="badge bg-warning">Siap Naik Kelas</span>
                                                @else
                                                <span class="badge bg-secondary">Kosong</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($kelas['can_promote'] && $kelas['jumlah_siswa'] > 0)
                                                <button class="btn btn-primary btn-sm" 
                                                        onclick="processKenaikan('{{ $kelas['id'] }}', '{{ $kelas['nama_kelas'] }}', '{{ $kelas['next_class'] }}', {{ $kelas['jumlah_siswa'] }})">
                                                    <i class="ti ti-arrow-up me-1"></i>Naik Kelas
                                                </button>
                                                @else
                                                <button class="btn btn-secondary btn-sm" disabled>
                                                    <i class="ti ti-ban me-1"></i>Tidak Bisa
                                                </button>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Klasifikasi Kelas XII -->
                    @if(!empty($klasifikasi['XII']))
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5 class="text-success">
                                <i class="ti ti-trophy me-2"></i>Kelas XII (Akan Lulus)
                            </h5>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="table-success">
                                        <tr>
                                            <th>Kelas Asal</th>
                                            <th>Jumlah Siswa</th>
                                            <th>Status</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($klasifikasi['XII'] as $kelas)
                                        <tr>
                                            <td><strong>{{ $kelas['nama_kelas'] }}</strong></td>
                                            <td>
                                                <span class="badge bg-info fs-6">{{ $kelas['jumlah_siswa'] }} Siswa</span>
                                            </td>
                                            <td>
                                                @if($kelas['jumlah_siswa'] > 0)
                                                <span class="badge bg-warning">Siap Lulus</span>
                                                @else
                                                <span class="badge bg-secondary">Kosong</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($kelas['jumlah_siswa'] > 0)
                                                <button class="btn btn-success btn-sm" 
                                                        onclick="processKenaikan('{{ $kelas['id'] }}', '{{ $kelas['nama_kelas'] }}', 'LULUS', {{ $kelas['jumlah_siswa'] }})">
                                                    <i class="ti ti-trophy me-1"></i>Luluskan
                                                </button>
                                                @else
                                                <button class="btn btn-secondary btn-sm" disabled>
                                                    <i class="ti ti-ban me-1"></i>Tidak Bisa
                                                </button>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @endif
                    @else
                    <div class="text-center py-5">
                        <i class="ti ti-calendar-off" style="font-size: 3rem; color: #6c757d;"></i>
                        <h5 class="mt-3">Pilih Tahun Ajaran</h5>
                        <p class="text-muted">Pilih tahun ajaran untuk melihat klasifikasi siswa per kelas.</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Process Individual Class Modal -->
<div class="modal fade" id="processModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Konfirmasi Kenaikan Kelas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.kenaikan_kelas.process') }}" id="processForm">
                @csrf
                <input type="hidden" name="kelas_id" id="process_kelas_id">
                <input type="hidden" name="tahun_ajaran_from" value="{{ $tahunAjaranId }}">
                <div class="modal-body">
                    <div class="alert alert-info">
                        <strong>Konfirmasi:</strong>
                        <div id="process_message"></div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Tahun Ajaran Tujuan <span class="text-danger">*</span></label>
                        <select class="form-select" name="tahun_ajaran_to" required>
                            <option value="">Pilih Tahun Ajaran - Semester</option>
                            @foreach($tahunAjarans as $ta)
                            <option value="{{ $ta->id }}">
                                {{ $ta->nama_tahun_ajaran }} - {{ ucfirst($ta->semester) }}
                                @if($ta->is_active) (Aktif) @endif
                            </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Tahun ajaran dan semester dimana siswa akan ditempatkan setelah naik kelas</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success" id="process_submit">Proses</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Process All Classes Modal -->
<div class="modal fade" id="processAllModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Proses Semua Kelas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.kenaikan_kelas.process_all') }}">
                @csrf
                <input type="hidden" name="tahun_ajaran_from" value="{{ $tahunAjaranId }}">
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <strong>Perhatian:</strong> Proses ini akan:
                        <ul class="mb-0 mt-2">
                            <li>Memindahkan semua siswa XI ke kelas XII</li>
                            <li>Meluluskan semua siswa XII</li>
                            <li>Mengubah tahun ajaran aktif ke tahun berikutnya</li>
                        </ul>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Tahun Ajaran Tujuan <span class="text-danger">*</span></label>
                        <select class="form-select" name="tahun_ajaran_to" required>
                            <option value="">Pilih Tahun Ajaran - Semester</option>
                            @foreach($tahunAjarans as $ta)
                            <option value="{{ $ta->id }}">
                                {{ $ta->nama_tahun_ajaran }} - {{ ucfirst($ta->semester) }}
                                @if($ta->is_active) (Aktif) @endif
                            </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Tahun ajaran dan semester yang akan diaktifkan setelah proses kenaikan</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Proses Semua</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function processKenaikan(kelasId, namaKelas, kelasTujuan, jumlahSiswa) {
    document.getElementById('process_kelas_id').value = kelasId;
    
    let message = '';
    if (kelasTujuan === 'LULUS') {
        message = `Akan <strong>meluluskan</strong> ${jumlahSiswa} siswa dari kelas <strong>${namaKelas}</strong><br><small class="text-muted">Status siswa akan berubah menjadi 'lulus' dan tidak lagi aktif di kelas.</small>`;
        document.getElementById('process_submit').innerHTML = '<i class="ti ti-trophy me-1"></i>Luluskan';
        document.getElementById('process_submit').className = 'btn btn-success';
    } else {
        message = `Akan <strong>memindahkan</strong> ${jumlahSiswa} siswa dari kelas <strong>${namaKelas}</strong> ke <strong>${kelasTujuan}</strong><br><small class="text-muted">Kelas tujuan akan dibuat otomatis jika belum ada.</small>`;
        document.getElementById('process_submit').innerHTML = '<i class="ti ti-arrow-up me-1"></i>Naik Kelas';
        document.getElementById('process_submit').className = 'btn btn-primary';
    }
    
    document.getElementById('process_message').innerHTML = message;
    
    var modal = new bootstrap.Modal(document.getElementById('processModal'));
    modal.show();
}
</script>
@endsection