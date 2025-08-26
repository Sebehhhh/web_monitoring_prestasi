<aside class="left-sidebar">
  <div>
    <div class="brand-logo d-flex align-items-center justify-content-between">
      <a href="#" class="text-nowrap logo-img d-flex align-items-center text-decoration-none">
        <img src="{{ asset('assets/images/logos/logo.png') }}" alt="Logo" style="max-height: 40px; width: auto; margin-right: 10px;" />
        <div class="brand-text">
          <h6 class="mb-0 text-dark fw-bold" style="font-size: 14px; line-height: 1.2;">SISTEM INFORMASI</h6>
          <small class="text-muted" style="font-size: 11px;">Monitoring Prestasi</small>
        </div>
      </a>
      <div class="close-btn d-xl-none d-block sidebartoggler cursor-pointer" id="sidebarCollapse">
        <i class="ti ti-x fs-6"></i>
      </div>
    </div>
    <nav class="sidebar-nav scroll-sidebar" data-simplebar="">
      <ul id="sidebarnav">
        <li class="nav-small-cap">
          <iconify-icon icon="solar:menu-dots-linear" class="nav-small-cap-icon fs-4"></iconify-icon>
          <span class="hide-menu">Menu Utama</span>
        </li>
        <!-- Hanya admin -->
        @if(auth()->user()->role === 'admin')

        <!-- ======= DASHBOARD ======= -->
        <li class="sidebar-item">
          <a class="sidebar-link" href="{{ route('admin.dashboard') }}" aria-expanded="false">
            <i class="ti ti-atom"></i>
            <span class="hide-menu">Dashboard</span>
          </a>
        </li>
        <li class="sidebar-item">
          <a class="sidebar-link" href="{{ route('admin.logs.index') }}" aria-expanded="false">
            <i class="ti ti-list-details"></i>
            <span class="hide-menu">Riwayat Aktivitas</span>
          </a>
        </li>

        <!-- ======= MASTER DATA ======= -->
        <li class="sidebar-item">
          <a class="sidebar-link has-arrow" data-bs-toggle="collapse" href="#masterDataCollapse" role="button" aria-expanded="false" aria-controls="masterDataCollapse">
            <i class="ti ti-database"></i>
            <span class="hide-menu">Master Data</span>
          </a>
          <div class="collapse" id="masterDataCollapse">
            <ul class="list-unstyled ps-3">
              <li class="sidebar-item">
                <a class="sidebar-link" href="{{ route('admin.users.index') }}">
                  <i class="ti ti-users"></i>
                  <span class="hide-menu">Manajemen User</span>
                </a>
              </li>
              <li class="sidebar-item">
                <a class="sidebar-link" href="{{ route('admin.kelas.index') }}">
                  <i class="ti ti-school"></i>
                  <span class="hide-menu">Manajemen Kelas</span>
                </a>
              </li>
              <li class="sidebar-item">
                <a class="sidebar-link" href="{{ route('admin.siswa.index') }}">
                  <i class="ti ti-user"></i>
                  <span class="hide-menu">Manajemen Siswa</span>
                </a>
              </li>
              <li class="sidebar-item">
                <a class="sidebar-link" href="{{ route('admin.kategori_prestasi.index') }}">
                  <i class="ti ti-award"></i>
                  <span class="hide-menu">Kategori Prestasi</span>
                </a>
              </li>
              <li class="sidebar-item">
                <a class="sidebar-link" href="{{ route('admin.tingkat_penghargaan.index') }}">
                  <i class="ti ti-trophy"></i>
                  <span class="hide-menu">Tingkat Penghargaan</span>
                </a>
              </li>
              <li class="sidebar-item">
                <a class="sidebar-link" href="{{ route('admin.ekstrakurikuler.index') }}">
                  <i class="ti ti-activity"></i>
                  <span class="hide-menu">Ekstrakurikuler</span>
                </a>
              </li>
              <li class="sidebar-item">
                <a class="sidebar-link" href="{{ route('admin.siswa_ekskul.index') }}">
                  <i class="ti ti-id-badge"></i>
                  <span class="hide-menu">Siswa Ekstrakurikuler</span>
                </a>
              </li>
            </ul>
          </div>
        </li>

        <!-- ======= TRANSAKSI ======= -->
        <li class="sidebar-item">
          <a class="sidebar-link" href="{{ route('admin.prestasi_siswa.index') }}" aria-expanded="false">
            <i class="ti ti-trophy"></i>
            <span class="hide-menu">Data Prestasi Siswa</span>
          </a>
        </li>

        <!-- ======= SISTEM AKADEMIK ======= -->
        <li class="sidebar-item">
          <a class="sidebar-link has-arrow" data-bs-toggle="collapse" href="#akademikCollapse" role="button" aria-expanded="false" aria-controls="akademikCollapse">
            <i class="ti ti-calendar"></i>
            <span class="hide-menu">Sistem Akademik</span>
          </a>
          <div class="collapse" id="akademikCollapse">
            <ul class="list-unstyled ps-3">
              <li class="sidebar-item">
                <a class="sidebar-link" href="{{ route('admin.tahun_ajaran.index') }}">
                  <i class="ti ti-calendar-event"></i>
                  <span class="hide-menu">Tahun Ajaran</span>
                </a>
              </li>
              <li class="sidebar-item">
                <a class="sidebar-link" href="{{ route('admin.kenaikan_kelas.index') }}">
                  <i class="ti ti-arrow-up-circle"></i>
                  <span class="hide-menu">Kenaikan Kelas</span>
                </a>
              </li>
            </ul>
          </div>
        </li>
        @endif

        <!-- Hanya guru -->
        @if(auth()->user()->role === 'guru')
        <!-- ======= DASHBOARD GURU ======= -->
        <li class="sidebar-item">
          <a class="sidebar-link" href="{{ route('guru.dashboard') }}" aria-expanded="false">
            <i class="ti ti-atom"></i>
            <span class="hide-menu">Dashboard</span>
          </a>
        </li>

        <!-- ======= DATA KELAS ======= -->
        <li class="sidebar-item">
          <a class="sidebar-link has-arrow" data-bs-toggle="collapse" href="#dataKelasCollapse" role="button" aria-expanded="false" aria-controls="dataKelasCollapse">
            <i class="ti ti-school"></i>
            <span class="hide-menu">Data Kelas</span>
          </a>
          <div class="collapse" id="dataKelasCollapse">
            <ul class="list-unstyled ps-3">
              <li class="sidebar-item">
                <a class="sidebar-link" href="{{ route('guru.siswa.index') }}">
                  <i class="ti ti-user"></i>
                  <span class="hide-menu">Daftar Siswa</span>
                </a>
              </li>
              <li class="sidebar-item">
                <a class="sidebar-link" href="{{ route('guru.kelas.index') }}">
                  <i class="ti ti-users"></i>
                  <span class="hide-menu">Kelas yang Diampu</span>
                </a>
              </li>
            </ul>
          </div>
        </li>

        <!-- ======= PRESTASI SISWA ======= -->
        <li class="sidebar-item">
          <a class="sidebar-link" href="{{ route('guru.prestasi_siswa.index') }}" aria-expanded="false">
            <i class="ti ti-trophy"></i>
            <span class="hide-menu">Data Prestasi Siswa</span>
          </a>
        </li>

        <!-- ======= REFERENSI ======= -->
        <li class="sidebar-item">
          <a class="sidebar-link has-arrow" data-bs-toggle="collapse" href="#referensiCollapse" role="button" aria-expanded="false" aria-controls="referensiCollapse">
            <i class="ti ti-book"></i>
            <span class="hide-menu">Referensi</span>
          </a>
          <div class="collapse" id="referensiCollapse">
            <ul class="list-unstyled ps-3">
              <li class="sidebar-item">
                <a class="sidebar-link" href="{{ route('guru.ekstrakurikuler.index') }}">
                  <i class="ti ti-activity"></i>
                  <span class="hide-menu">Ekstrakurikuler</span>
                </a>
              </li>
              <li class="sidebar-item">
                <a class="sidebar-link" href="{{ route('guru.kategori_prestasi.index') }}">
                  <i class="ti ti-award"></i>
                  <span class="hide-menu">Kategori Prestasi</span>
                </a>
              </li>
              <li class="sidebar-item">
                <a class="sidebar-link" href="{{ route('guru.tingkat_penghargaan.index') }}">
                  <i class="ti ti-trophy"></i>
                  <span class="hide-menu">Tingkat Penghargaan</span>
                </a>
              </li>
            </ul>
          </div>
        </li>
        @endif

        <!-- Hanya kepala sekolah -->
        @if(auth()->user()->role === 'kepala_sekolah')
        <!-- ======= DASHBOARD KEPALA SEKOLAH ======= -->
        <li class="sidebar-item">
          <a class="sidebar-link" href="{{ route('kepala_sekolah.dashboard') }}" aria-expanded="false">
            <i class="ti ti-atom"></i>
            <span class="hide-menu">Dashboard</span>
          </a>
        </li>

        <!-- ======= REKAP PRESTASI ======= -->
        <li class="sidebar-item">
          <a class="sidebar-link" href="{{ route('kepala_sekolah.prestasi_siswa.index') }}" aria-expanded="false">
            <i class="ti ti-trophy"></i>
            <span class="hide-menu">Data Prestasi Siswa</span>
          </a>
        </li>

        <!-- ======= DATA SEKOLAH ======= -->
        <li class="sidebar-item">
          <a class="sidebar-link has-arrow" data-bs-toggle="collapse" href="#dataSekolahCollapse" role="button" aria-expanded="false" aria-controls="dataSekolahCollapse">
            <i class="ti ti-building"></i>
            <span class="hide-menu">Data Sekolah</span>
          </a>
          <div class="collapse" id="dataSekolahCollapse">
            <ul class="list-unstyled ps-3">
              <li class="sidebar-item">
                <a class="sidebar-link" href="{{ route('kepala_sekolah.siswa.index') }}">
                  <i class="ti ti-user"></i>
                  <span class="hide-menu">Daftar Siswa</span>
                </a>
              </li>
              <li class="sidebar-item">
                <a class="sidebar-link" href="{{ route('kepala_sekolah.kelas.index') }}">
                  <i class="ti ti-school"></i>
                  <span class="hide-menu">Daftar Kelas</span>
                </a>
              </li>
              <li class="sidebar-item">
                <a class="sidebar-link" href="{{ route('kepala_sekolah.ekstrakurikuler.index') }}">
                  <i class="ti ti-activity"></i>
                  <span class="hide-menu">Ekstrakurikuler</span>
                </a>
              </li>
            </ul>
          </div>
        </li>

        <!-- ======= SISTEM ======= -->
        <li class="sidebar-item">
          <a class="sidebar-link has-arrow" data-bs-toggle="collapse" href="#sistemCollapse" role="button" aria-expanded="false" aria-controls="sistemCollapse">
            <i class="ti ti-settings"></i>
            <span class="hide-menu">Sistem</span>
          </a>
          <div class="collapse" id="sistemCollapse">
            <ul class="list-unstyled ps-3">
              <li class="sidebar-item">
                <a class="sidebar-link" href="{{ route('kepala_sekolah.users.index') }}">
                  <i class="ti ti-users"></i>
                  <span class="hide-menu">Daftar User</span>
                </a>
              </li>
              <li class="sidebar-item">
                <a class="sidebar-link" href="{{ route('kepala_sekolah.logs.index') }}">
                  <i class="ti ti-list-details"></i>
                  <span class="hide-menu">Log Aktivitas</span>
                </a>
              </li>
            </ul>
          </div>
        </li>
        @endif

        <!-- Hanya wali -->
        @if(auth()->user()->role === 'wali')
        <!-- ======= DASHBOARD WALI ======= -->
        <li class="sidebar-item">
          <a class="sidebar-link" href="{{ route('wali.dashboard') }}" aria-expanded="false">
            <i class="ti ti-atom"></i>
            <span class="hide-menu">Dashboard</span>
          </a>
        </li>

        <!-- ======= DATA ANAK ======= -->
        <li class="sidebar-item">
          <a class="sidebar-link" href="{{ route('wali.siswa.index') }}" aria-expanded="false">
            <i class="ti ti-user"></i>
            <span class="hide-menu">Data Anak</span>
          </a>
        </li>

        <!-- ======= PRESTASI ANAK ======= -->
        <li class="sidebar-item">
          <a class="sidebar-link has-arrow" data-bs-toggle="collapse" href="#prestasiAnakCollapse" role="button" aria-expanded="false" aria-controls="prestasiAnakCollapse">
            <i class="ti ti-trophy"></i>
            <span class="hide-menu">Prestasi Anak</span>
          </a>
          <div class="collapse" id="prestasiAnakCollapse">
            <ul class="list-unstyled ps-3">
              <li class="sidebar-item">
                <a class="sidebar-link" href="{{ route('wali.prestasi.index') }}">
                  <i class="ti ti-award"></i>
                  <span class="hide-menu">Data Prestasi</span>
                </a>
              </li>
              <li class="sidebar-item">
                <a class="sidebar-link" href="{{ route('wali.dokumen.index') }}">
                  <i class="ti ti-file-text"></i>
                  <span class="hide-menu">Dokumen Prestasi</span>
                </a>
              </li>
            </ul>
          </div>
        </li>
        @endif

        @if(auth()->user()->role === 'siswa')
        <!-- ======= DASHBOARD SISWA ======= -->
        <li class="sidebar-item">
          <a class="sidebar-link" href="{{ route('siswa.dashboard') }}" aria-expanded="false">
            <i class="ti ti-atom"></i>
            <span class="hide-menu">Dashboard</span>
          </a>
        </li>

        <!-- ======= DATA DIRI ======= -->
        <li class="sidebar-item">
          <a class="sidebar-link" href="{{ route('siswa.profil.index') }}" aria-expanded="false">
            <i class="ti ti-user"></i>
            <span class="hide-menu">Profil Saya</span>
          </a>
        </li>

        <!-- ======= PRESTASI SAYA ======= -->
        <li class="sidebar-item">
          <a class="sidebar-link" href="{{ route('siswa.prestasi.index') }}" aria-expanded="false">
            <i class="ti ti-trophy"></i>
            <span class="hide-menu">Prestasi Saya</span>
          </a>
        </li>
        @endif

      </ul>
    </nav>
  </div>
</aside>

<style>
.sidebar-link.has-arrow {
    position: relative;
}

.sidebar-link.has-arrow::after {
    /* content: "▼"; */
    position: absolute;
    right: 20px;
    font-size: 12px;
    transition: transform 0.3s ease;
    color: #6c757d;
}

.sidebar-link.has-arrow[aria-expanded="true"]::after {
    transform: rotate(180deg);
}

.sidebar-item .collapse ul .sidebar-item {
    border-left: 2px solid #e9ecef;
    margin-left: 10px;
}

.sidebar-item .collapse ul .sidebar-link {
    padding: 8px 15px;
    font-size: 0.875rem;
    color: #6c757d;
    transition: all 0.3s ease;
}

.sidebar-item .collapse ul .sidebar-link:hover {
    color: var(--bs-primary);
    background-color: rgba(var(--bs-primary-rgb), 0.1);
    border-radius: 6px;
    margin: 2px 5px;
}

.sidebar-item .collapse ul .sidebar-link i {
    width: 18px;
    height: 18px;
    font-size: 16px;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle dropdown toggles
    document.querySelectorAll('.sidebar-link.has-arrow').forEach(function(element) {
        element.addEventListener('click', function(e) {
            e.preventDefault();
            
            const targetId = this.getAttribute('href').substring(1);
            const target = document.getElementById(targetId);
            const isExpanded = this.getAttribute('aria-expanded') === 'true';
            
            // Toggle aria-expanded
            this.setAttribute('aria-expanded', !isExpanded);
            
            // Toggle the collapse manually
            if (target) {
                if (isExpanded) {
                    target.classList.remove('show');
                } else {
                    target.classList.add('show');
                }
            }
        });
    });
    
    // Auto close other dropdowns when opening a new one
    document.querySelectorAll('.collapse').forEach(function(collapse) {
        collapse.addEventListener('show.bs.collapse', function() {
            // Close other collapses
            document.querySelectorAll('.collapse.show').forEach(function(otherCollapse) {
                if (otherCollapse !== this) {
                    otherCollapse.classList.remove('show');
                    // Reset aria-expanded for the trigger
                    const trigger = document.querySelector('[href="#' + otherCollapse.id + '"]');
                    if (trigger) {
                        trigger.setAttribute('aria-expanded', 'false');
                    }
                }
            });
        });
    });
});
</script>