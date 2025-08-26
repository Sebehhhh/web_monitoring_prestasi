@extends('layouts.app')
@section('title', 'Dashboard Admin')
@section('content')

<!-- Row 1 - Statistik Utama -->
<div class="row">
  <div class="col-sm-6 col-xl-3">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-start justify-content-between">
          <div class="content-left">
            <span class="fw-semibold d-block mb-1">Total Siswa</span>
            <div class="d-flex align-items-end mt-2">
              <h4 class="mb-0 me-2">{{ number_format($totalSiswa) }}</h4>
              <small class="text-success">(+100%)</small>
            </div>
            <small>Total siswa terdaftar</small>
          </div>
          <span class="badge bg-label-primary rounded p-2">
            <i class="ti ti-users ti-sm"></i>
          </span>
        </div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-xl-3">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-start justify-content-between">
          <div class="content-left">
            <span class="fw-semibold d-block mb-1">Total Prestasi</span>
            <div class="d-flex align-items-end mt-2">
              <h4 class="mb-0 me-2">{{ number_format($totalPrestasi) }}</h4>
              <small class="text-success">(+{{ $totalPrestasi > 0 ? round(($prestasiTervalidasi / $totalPrestasi) * 100) : 0 }}%)</small>
            </div>
            <small>{{ $prestasiTervalidasi }} prestasi tervalidasi</small>
          </div>
          <span class="badge bg-label-success rounded p-2">
            <i class="ti ti-trophy ti-sm"></i>
          </span>
        </div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-xl-3">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-start justify-content-between">
          <div class="content-left">
            <span class="fw-semibold d-block mb-1">Total Kelas</span>
            <div class="d-flex align-items-end mt-2">
              <h4 class="mb-0 me-2">{{ number_format($totalKelas) }}</h4>
            </div>
            <small>Kelas terdaftar</small>
          </div>
          <span class="badge bg-label-warning rounded p-2">
            <i class="ti ti-school ti-sm"></i>
          </span>
        </div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-xl-3">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-start justify-content-between">
          <div class="content-left">
            <span class="fw-semibold d-block mb-1">Total Ekskul</span>
            <div class="d-flex align-items-end mt-2">
              <h4 class="mb-0 me-2">{{ number_format($totalEkskul) }}</h4>
              <small class="text-success">({{ $totalAnggotaEkskul }} anggota)</small>
            </div>
            <small>Ekstrakurikuler aktif</small>
          </div>
          <span class="badge bg-label-info rounded p-2">
            <i class="ti ti-activity ti-sm"></i>
          </span>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Row 2 - Statistik Pengguna -->
<div class="row">
  <div class="col-lg-6">
    <div class="card">
      <div class="card-header">
        <h4 class="card-title">Statistik Pengguna</h4>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-6">
            <div class="d-flex align-items-center mb-3">
              <div class="flex-shrink-0">
                <div class="avatar avatar-sm me-3">
                  <span class="avatar-initial rounded bg-label-primary">
                    <i class="ti ti-user-check"></i>
                  </span>
                </div>
              </div>
              <div class="flex-grow-1">
                <h6 class="mb-0">Guru</h6>
                <small class="text-muted">{{ $totalGuru }} pengguna</small>
              </div>
            </div>
          </div>
          <div class="col-6">
            <div class="d-flex align-items-center mb-3">
              <div class="flex-shrink-0">
                <div class="avatar avatar-sm me-3">
                  <span class="avatar-initial rounded bg-label-success">
                    <i class="ti ti-user"></i>
                  </span>
                </div>
              </div>
              <div class="flex-grow-1">
                <h6 class="mb-0">Siswa</h6>
                <small class="text-muted">{{ $totalSiswa }} pengguna</small>
              </div>
            </div>
          </div>
          <div class="col-6">
            <div class="d-flex align-items-center mb-3">
              <div class="flex-shrink-0">
                <div class="avatar avatar-sm me-3">
                  <span class="avatar-initial rounded bg-label-warning">
                    <i class="ti ti-user-shield"></i>
                  </span>
                </div>
              </div>
              <div class="flex-grow-1">
                <h6 class="mb-0">Kepala Sekolah</h6>
                <small class="text-muted">{{ $totalKepalaSekolah }} pengguna</small>
              </div>
            </div>
          </div>
          <div class="col-6">
            <div class="d-flex align-items-center mb-3">
              <div class="flex-shrink-0">
                <div class="avatar avatar-sm me-3">
                  <span class="avatar-initial rounded bg-label-danger">
                    <i class="ti ti-user-cog"></i>
                  </span>
                </div>
              </div>
              <div class="flex-grow-1">
                <h6 class="mb-0">Admin</h6>
                <small class="text-muted">{{ $totalAdmin }} pengguna</small>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-lg-6">
    <div class="card">
      <div class="card-header">
        <h4 class="card-title">Status Prestasi</h4>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-6">
            <div class="d-flex align-items-center mb-3">
              <div class="flex-shrink-0">
                <div class="avatar avatar-sm me-3">
                  <span class="avatar-initial rounded bg-label-success">
                    <i class="ti ti-check"></i>
                  </span>
                </div>
              </div>
              <div class="flex-grow-1">
                <h6 class="mb-0">Tervalidasi</h6>
                <small class="text-muted">{{ $prestasiTervalidasi }} prestasi</small>
              </div>
            </div>
          </div>
          <div class="col-6">
            <div class="d-flex align-items-center mb-3">
              <div class="flex-shrink-0">
                <div class="avatar avatar-sm me-3">
                  <span class="avatar-initial rounded bg-label-warning">
                    <i class="ti ti-clock"></i>
                  </span>
                </div>
              </div>
              <div class="flex-grow-1">
                <h6 class="mb-0">Menunggu Validasi</h6>
                <small class="text-muted">{{ $prestasiPending }} prestasi</small>
              </div>
            </div>
          </div>
          <div class="col-6">
            <div class="d-flex align-items-center mb-3">
              <div class="flex-shrink-0">
                <div class="avatar avatar-sm me-3">
                  <span class="avatar-initial rounded bg-label-danger">
                    <i class="ti ti-x"></i>
                  </span>
                </div>
              </div>
              <div class="flex-grow-1">
                <h6 class="mb-0">Ditolak</h6>
                <small class="text-muted">{{ $prestasiDitolak }} prestasi</small>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Row 3 - Grafik Prestasi -->
<div class="row">
  <div class="col-lg-8">
    <div class="card">
      <div class="card-header">
        <h4 class="card-title">Tren Prestasi (6 Bulan Terakhir)</h4>
      </div>
      <div class="card-body">
        <div id="prestasi-chart" style="height: 300px;"></div>
      </div>
    </div>
  </div>
  <div class="col-lg-4">
    <div class="card">
      <div class="card-header">
        <h4 class="card-title">Prestasi per Kategori</h4>
      </div>
      <div class="card-body">
        <div id="kategori-chart" style="height: 300px;"></div>
      </div>
    </div>
  </div>
</div>
<!-- CDN ApexCharts versi stabil dan script chart inline -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.41.0/dist/apexcharts.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Debug data
    console.log('Prestasi per Bulan Data:', @json($prestasiPerBulan));
    console.log('Prestasi per Kategori Data:', @json($prestasiPerKategori));

    // Prepare data for Area Chart Prestasi per Bulan
    var prestasiData = @json($prestasiPerBulan->pluck('total')->toArray());
    var prestasiBulan = @json($prestasiPerBulan->pluck('bulan')->toArray());
    
    // If no data, show placeholder
    if (!prestasiData || prestasiData.length === 0) {
        prestasiData = [0];
        prestasiBulan = ['-'];
    }

    var prestasiOptions = {
        series: [{
            name: 'Prestasi',
            data: prestasiData
        }],
        chart: {
            type: 'area',
            height: 300,
            toolbar: { show: false }
        },
        dataLabels: { enabled: false },
        stroke: { curve: 'smooth', width: 2 },
        colors: ['#7367F0'],
        fill: {
            type: 'gradient',
            gradient: { shadeIntensity: 1, opacityFrom: 0.7, opacityTo: 0.2, stops: [0, 90, 100] }
        },
        xaxis: {
            categories: prestasiBulan,
            labels: {
                formatter: function(value) {
                    if (!value || value === '-') return '-';
                    return new Date(value + '-01').toLocaleDateString('id-ID', { month: 'short', year: 'numeric' });
                }
            }
        },
        yaxis: {
            labels: { formatter: function(value) { return Math.round(value); } }
        },
        tooltip: { x: { format: 'MMM yyyy' } },
        noData: {
            text: 'Belum ada data prestasi',
            align: 'center',
            verticalAlign: 'middle'
        }
    };

    try {
        var prestasiChart = new ApexCharts(document.querySelector("#prestasi-chart"), prestasiOptions);
        prestasiChart.render();
        console.log('Prestasi chart rendered successfully');
    } catch(error) {
        console.error('Error rendering prestasi chart:', error);
    }

    // Prepare data for Donut Chart Prestasi per Kategori  
    var kategoriData = @json($prestasiPerKategori->pluck('total')->toArray());
    var kategoriLabels = @json($prestasiPerKategori->pluck('kategori')->toArray());

    // If no data, show placeholder
    if (!kategoriData || kategoriData.length === 0) {
        kategoriData = [1];
        kategoriLabels = ['Belum ada data'];
    }

    var kategoriOptions = {
        series: kategoriData,
        chart: { type: 'donut', height: 300 },
        labels: kategoriLabels,
        colors: ['#7367F0', '#28C76F', '#EA5455', '#FF9F43', '#1E9FF2', '#00D4AA', '#826AF9'],
        plotOptions: { 
            pie: { 
                donut: { size: '65%' },
                expandOnClick: false
            } 
        },
        legend: { position: 'bottom' },
        noData: {
            text: 'Belum ada data kategori prestasi',
            align: 'center',
            verticalAlign: 'middle'
        }
    };

    try {
        var kategoriChart = new ApexCharts(document.querySelector("#kategori-chart"), kategoriOptions);
        kategoriChart.render();
        console.log('Kategori chart rendered successfully');
    } catch(error) {
        console.error('Error rendering kategori chart:', error);
    }
});
</script>

<!-- Row 4 - Tabel dan Aktivitas -->
<div class="row">
  <div class="col-lg-6">
    <div class="card">
      <div class="card-header">
        <h4 class="card-title">Top 5 Kelas dengan Prestasi Terbanyak</h4>
      </div>
      <div class="card-body">
        @if($topKelasPrestasi->count() > 0)
          <div class="table-responsive">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>Kelas</th>
                  <th>Total Prestasi</th>
                  <th>Persentase</th>
                </tr>
              </thead>
              <tbody>
                @foreach($topKelasPrestasi as $kelas)
                <tr>
                  <td>{{ $kelas->nama_kelas }}</td>
                  <td>{{ $kelas->total_prestasi }}</td>
                  <td>
                    <div class="d-flex align-items-center">
                      <div class="progress flex-grow-1 me-2" style="height: 6px;">
                        <div class="progress-bar" style="width: {{ $totalPrestasi > 0 ? ($kelas->total_prestasi / $totalPrestasi) * 100 : 0 }}%"></div>
                      </div>
                      <span class="text-muted small">
                        {{ $totalPrestasi > 0 ? round(($kelas->total_prestasi / $totalPrestasi) * 100, 1) : 0 }}%
                        <br><small>(dari total prestasi)</small>
                      </span>
                    </div>
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        @else
          <div class="text-center py-4">
            <i class="ti ti-school ti-2x text-muted mb-2"></i>
            <p class="text-muted">Belum ada data kelas dengan prestasi</p>
          </div>
        @endif
      </div>
    </div>
  </div>
  <div class="col-lg-6">
    <div class="card">
      <div class="card-header">
        <h4 class="card-title">Top 5 Ekstrakurikuler</h4>
      </div>
      <div class="card-body">
        @if($topEkskul->count() > 0)
          <div class="table-responsive">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>Ekstrakurikuler</th>
                  <th>Total Anggota</th>
                  <th>Persentase</th>
                </tr>
              </thead>
              <tbody>
                @foreach($topEkskul as $ekskul)
                <tr>
                  <td>{{ $ekskul->nama }}</td>
                  <td>{{ $ekskul->total_anggota }}</td>
                  <td>
                    <div class="d-flex align-items-center">
                      <div class="progress flex-grow-1 me-2" style="height: 6px;">
                        <div class="progress-bar bg-info" style="width: {{ $totalSiswa > 0 ? ($ekskul->total_anggota / $totalSiswa) * 100 : 0 }}%"></div>
                      </div>
                      <span class="text-muted small">
                        {{ $totalSiswa > 0 ? round(($ekskul->total_anggota / $totalSiswa) * 100, 1) : 0 }}%
                        <br><small>(dari total siswa)</small>
                      </span>
                    </div>
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        @else
          <div class="text-center py-4">
            <i class="ti ti-activity ti-2x text-muted mb-2"></i>
            <p class="text-muted">Belum ada data ekstrakurikuler</p>
          </div>
        @endif
      </div>
    </div>
  </div>
</div>

<!-- Row 5 - Aktivitas Terbaru dan Prestasi Terbaru -->
<div class="row">
  <div class="col-lg-6">
    <div class="card">
      <div class="card-header">
        <h4 class="card-title">Aktivitas Terbaru</h4>
      </div>
      <div class="card-body">
        @if($aktivitasTerbaru->count() > 0)
          <div class="timeline">
            @foreach($aktivitasTerbaru as $aktivitas)
            <div class="timeline-item">
              <div class="timeline-marker"></div>
              <div class="timeline-content">
                <h6 class="mb-1">{{ $aktivitas->action }}</h6>
                <p class="text-muted mb-0">{{ $aktivitas->description }}</p>
                <small class="text-muted">{{ $aktivitas->user->nama ?? 'System' }} • {{ $aktivitas->created_at->diffForHumans() }}</small>
              </div>
            </div>
            @endforeach
          </div>
        @else
          <div class="text-center py-4">
            <i class="ti ti-activity ti-2x text-muted mb-2"></i>
            <p class="text-muted">Belum ada aktivitas terbaru</p>
          </div>
        @endif
      </div>
    </div>
  </div>
  <div class="col-lg-6">
    <div class="card">
      <div class="card-header">
        <h4 class="card-title">Prestasi Terbaru</h4>
      </div>
      <div class="card-body">
        @if($prestasiTerbaru->count() > 0)
          @foreach($prestasiTerbaru as $prestasi)
          <div class="d-flex align-items-start mb-3">
            <div class="flex-shrink-0">
              <div class="avatar avatar-sm me-3">
                <span class="avatar-initial rounded bg-label-success">
                  <i class="ti ti-trophy"></i>
                </span>
              </div>
            </div>
            <div class="flex-grow-1">
              <h6 class="mb-1">{{ $prestasi->nama_prestasi }}</h6>
              <p class="text-muted mb-0">{{ $prestasi->siswa->nama ?? 'N/A' }} • {{ $prestasi->kategoriPrestasi->nama_kategori ?? 'N/A' }}</p>
              <small class="text-muted">{{ $prestasi->tingkatPenghargaan->tingkat ?? 'N/A' }} • {{ $prestasi->created_at->diffForHumans() }}</small>
            </div>
          </div>
          @endforeach
        @else
          <div class="text-center py-4">
            <i class="ti ti-trophy ti-2x text-muted mb-2"></i>
            <p class="text-muted">Belum ada prestasi terbaru</p>
          </div>
        @endif
      </div>
    </div>
  </div>
</div>

@endsection

@push('styles')
<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline-item {
    position: relative;
    padding-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: -35px;
    top: 0;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: #7367F0;
    border: 2px solid #fff;
    box-shadow: 0 0 0 3px #7367F0;
}

.timeline-item:not(:last-child)::before {
    content: '';
    position: absolute;
    left: -29px;
    top: 12px;
    width: 2px;
    height: calc(100% - 12px);
    background: #E7E7FF;
}

.progress {
    background-color: #F5F5F5;
}

.progress-bar {
    background-color: #7367F0;
}

.avatar-initial {
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.875rem;
    font-weight: 600;
    color: #fff;
    width: 2.5rem;
    height: 2.5rem;
}

.avatar-sm {
    width: 2rem;
    height: 2rem;
    font-size: 0.75rem;
}
</style>
@endpush