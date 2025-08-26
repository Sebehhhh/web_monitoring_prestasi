@extends('layouts.app')
@section('title', 'Tahun Ajaran')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title">
                        <i class="ti ti-calendar-event me-2"></i>Manajemen Tahun Ajaran
                    </h4>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createModal">
                        <i class="ti ti-plus me-1"></i>Tambah Tahun Ajaran
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

                    @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    @endif

                    @if($tahunAjarans->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Tahun Ajaran</th>
                                    <th>Semester</th>
                                    <th>Periode Tahun</th>
                                    <th>Periode Semester</th>
                                    <th>Status</th>
                                    <th>Total Prestasi</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($tahunAjarans as $index => $tahun)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td><strong>{{ $tahun['nama_tahun_ajaran'] }}</strong></td>
                                    <td>
                                        <span class="badge bg-{{ $tahun['semester'] == 'Ganjil' ? 'info' : 'warning' }}">{{ $tahun['semester'] }}</span>
                                    </td>
                                    <td>
                                        <small>{{ $tahun['tanggal_mulai_tahun'] }} - {{ $tahun['tanggal_selesai_tahun'] }}</small>
                                    </td>
                                    <td>
                                        <small>{{ $tahun['tanggal_mulai_semester'] }} - {{ $tahun['tanggal_selesai_semester'] }}</small>
                                    </td>
                                    <td>
                                        @if($tahun['is_active'])
                                        <span class="badge bg-success">Aktif</span>
                                        @else
                                        <span class="badge bg-secondary">Non-Aktif</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-primary">{{ $tahun['total_prestasi'] }}</span>
                                    </td>
                                    <td>
                                        @if(!$tahun['is_active'])
                                        <form method="POST" action="{{ route('admin.tahun_ajaran.set_active', $tahun['id']) }}" style="display: inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-success btn-sm" title="Set sebagai Aktif">
                                                <i class="ti ti-check"></i>
                                            </button>
                                        </form>
                                        @endif
                                        
                                        <button class="btn btn-warning btn-sm" onclick="editTahunAjaran({{ json_encode($tahun) }})" title="Edit">
                                            <i class="ti ti-edit"></i>
                                        </button>
                                        
                                        @if($tahun['total_prestasi'] == 0)
                                        <form method="POST" action="{{ route('admin.tahun_ajaran.destroy', $tahun['id']) }}" style="display: inline;" onsubmit="return confirm('Yakin ingin menghapus tahun ajaran ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm" title="Hapus">
                                                <i class="ti ti-trash"></i>
                                            </button>
                                        </form>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-5">
                        <i class="ti ti-calendar-off" style="font-size: 3rem; color: #6c757d;"></i>
                        <h5 class="mt-3">Belum ada Tahun Ajaran</h5>
                        <p class="text-muted">Tambahkan tahun ajaran pertama untuk memulai.</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Modal -->
<div class="modal fade" id="createModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Tahun Ajaran</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.tahun_ajaran.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Tahun Ajaran <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="nama_tahun_ajaran" 
                               placeholder="2024/2025" pattern="[0-9]{4}/[0-9]{4}" required>
                        <small class="text-muted">Format: YYYY/YYYY (contoh: 2024/2025)</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Semester <span class="text-danger">*</span></label>
                        <select class="form-select" name="semester" required>
                            <option value="">Pilih Semester</option>
                            <option value="ganjil">Ganjil (Juli - Desember)</option>
                            <option value="genap">Genap (Januari - Juni)</option>
                        </select>
                    </div>
                    <h6>Periode Tahun Ajaran</h6>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tanggal Mulai Tahun <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="tanggal_mulai_tahun" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tanggal Selesai Tahun <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="tanggal_selesai_tahun" required>
                        </div>
                    </div>
                    <h6>Periode Semester</h6>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tanggal Mulai Semester <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="tanggal_mulai_semester" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tanggal Selesai Semester <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="tanggal_selesai_semester" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Keterangan</label>
                        <textarea class="form-control" name="keterangan" rows="3" 
                                  placeholder="Keterangan tambahan (opsional)"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Tahun Ajaran</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="editForm">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Tahun Ajaran <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="nama_tahun_ajaran" id="edit_nama_tahun_ajaran" 
                               pattern="[0-9]{4}/[0-9]{4}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Semester <span class="text-danger">*</span></label>
                        <select class="form-select" name="semester" id="edit_semester" required>
                            <option value="">Pilih Semester</option>
                            <option value="ganjil">Ganjil (Juli - Desember)</option>
                            <option value="genap">Genap (Januari - Juni)</option>
                        </select>
                    </div>
                    <h6>Periode Tahun Ajaran</h6>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tanggal Mulai Tahun <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="tanggal_mulai_tahun" id="edit_tanggal_mulai_tahun" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tanggal Selesai Tahun <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="tanggal_selesai_tahun" id="edit_tanggal_selesai_tahun" required>
                        </div>
                    </div>
                    <h6>Periode Semester</h6>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tanggal Mulai Semester <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="tanggal_mulai_semester" id="edit_tanggal_mulai_semester" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tanggal Selesai Semester <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="tanggal_selesai_semester" id="edit_tanggal_selesai_semester" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Keterangan</label>
                        <textarea class="form-control" name="keterangan" id="edit_keterangan" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editTahunAjaran(tahun) {
    document.getElementById('editForm').action = `/admin/tahun_ajaran/${tahun.id}`;
    document.getElementById('edit_nama_tahun_ajaran').value = tahun.nama_tahun_ajaran;
    document.getElementById('edit_semester').value = tahun.semester.toLowerCase();
    document.getElementById('edit_tanggal_mulai_tahun').value = convertDate(tahun.tanggal_mulai_tahun);
    document.getElementById('edit_tanggal_selesai_tahun').value = convertDate(tahun.tanggal_selesai_tahun);
    document.getElementById('edit_tanggal_mulai_semester').value = convertDate(tahun.tanggal_mulai_semester);
    document.getElementById('edit_tanggal_selesai_semester').value = convertDate(tahun.tanggal_selesai_semester);
    document.getElementById('edit_keterangan').value = tahun.keterangan || '';
    
    var modal = new bootstrap.Modal(document.getElementById('editModal'));
    modal.show();
}

function convertDate(dateStr) {
    // Convert from dd/mm/yyyy to yyyy-mm-dd
    if (!dateStr) return '';
    const parts = dateStr.split('/');
    if (parts.length === 3) {
        return `${parts[2]}-${parts[1].padStart(2, '0')}-${parts[0].padStart(2, '0')}`;
    }
    return dateStr;
}
</script>
@endsection