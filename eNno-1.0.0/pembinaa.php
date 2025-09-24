<?php
require_once 'koneksi.php';
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'pembina') {
  header('Location: login.php');
  exit;
}

$id_pembina = $_SESSION['user_id'];

// Ambil data ekstra
$stmt = $pdo->prepare("
  SELECT e.id_ekstra, e.nama_ekstra, e.hari, e.jam_mulai, e.jam_selesai,
         COUNT(pe.nis) AS total_siswa
  FROM tb_ekstrakurikuler e
  LEFT JOIN tb_peserta_ekstra pe ON e.id_ekstra = pe.id_ekstra
  WHERE e.id_pembina = ?
  GROUP BY e.id_ekstra
");
$stmt->execute([$id_pembina]);
$ekstrakurikuler = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Hitung total siswa
$total_siswa = array_sum(array_column($ekstrakurikuler, 'total_siswa'));

// Hitung siswa hadir
$stmtHadir = $pdo->prepare("
  SELECT COUNT(*) as total_hadir
  FROM tb_presensi p
  JOIN tb_ekstrakurikuler e ON p.id_ekstra = e.id_ekstra
  WHERE e.id_pembina = ? AND p.status = 'H'
");
$stmtHadir->execute([$id_pembina]);
$totalHadir = $stmtHadir->fetch(PDO::FETCH_ASSOC)['total_hadir'];
?>

<section class="section dashboard">
  <!-- Welcome Banner -->
  <div class="row mb-4">
    <div class="col-12">
      <div class="p-4 rounded-4 shadow-sm text-white" style="background: linear-gradient(135deg, #2980b9, #6dd5fa);">
        <h4 class="mb-1"><i class="bi bi-person-badge-fill me-2"></i>Selamat Datang, <span class="fw-bold"><?= htmlspecialchars($_SESSION['username']) ?></span> 👋</h4>
        <p class="mb-0 opacity-75">Berikut adalah ringkasan ekstrakurikuler yang Anda bimbing.</p>
      </div>
    </div>
  </div>

  <!-- Statistik Ringkasan -->
  <div class="row g-4 mb-4">
    <!-- Ekstrakurikuler -->
    <div class="col-md-4">
      <div class="card h-100 shadow-sm border-0 stat-card position-relative overflow-hidden">
        <div class="card-body text-center p-4">
          <div class="icon-circle bg-primary bg-opacity-10 text-primary mx-auto mb-3">
            <i class="bi bi-star-fill fs-2"></i>
          </div>
          <h6 class="text-muted mb-1">Ekstrakurikuler</h6>
          <h2 class="fw-bold text-primary mb-0"><?= count($ekstrakurikuler) ?></h2>
        </div>
      </div>
    </div>

    <!-- Total Siswa -->
    <div class="col-md-4">
      <div class="card h-100 shadow-sm border-0 stat-card position-relative overflow-hidden">
        <div class="card-body text-center p-4">
          <div class="icon-circle bg-success bg-opacity-10 text-success mx-auto mb-3">
            <i class="bi bi-people-fill fs-2"></i>
          </div>
          <h6 class="text-muted mb-1">Total Siswa Binaan</h6>
          <h2 class="fw-bold text-success mb-0"><?= $total_siswa ?></h2>
        </div>
      </div>
    </div>

    <!-- Jumlah Hadir -->
    <div class="col-md-4">
      <div class="card h-100 shadow-sm border-0 stat-card position-relative overflow-hidden">
        <div class="card-body text-center p-4">
          <div class="icon-circle bg-info bg-opacity-10 text-info mx-auto mb-3">
            <i class="bi bi-check2-circle fs-2"></i>
          </div>
          <h6 class="text-muted mb-1">Jumlah Siswa Hadir</h6>
          <h2 class="fw-bold text-info mb-0"><?= $totalHadir ?></h2>
        </div>
      </div>
    </div>
  </div>

  <!-- CSS tambahan -->
  <style>
    .stat-card {
      transition: transform 0.25s ease, box-shadow 0.25s ease;
      border-radius: 16px;
    }
    .stat-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 20px rgba(0,0,0,0.1);
    }
    .icon-circle {
      width: 60px;
      height: 60px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.8rem;
    }
  </style>

  <!-- Daftar Ekstrakurikuler + Agenda -->
  <div class="row">
    <!-- Agenda Kegiatan -->
    <div class="col-lg-4">
      <div class="card shadow-sm border-0">
        <div class="card-body">
          <h5 class="card-title mb-3"><i class="bi bi-calendar-check-fill me-2 text-primary"></i>Agenda Kegiatan</h5>
          <div class="timeline">
            <?php if (count($ekstrakurikuler) > 0): ?>
              <?php foreach ($ekstrakurikuler as $kegiatan): ?>
                <div class="timeline-item mb-3">
                  <div class="timeline-icon bg-primary text-white"><i class="bi bi-calendar-event"></i></div>
                  <div class="timeline-content">
                    <h6 class="mb-0 fw-bold"><?= htmlspecialchars($kegiatan['nama_ekstra']) ?></h6>
                    <small class="text-muted"><?= $kegiatan['hari'] ?>, <?= $kegiatan['jam_mulai'] ?> - <?= $kegiatan['jam_selesai'] ?></small>
                  </div>
                </div>
                
              <?php endforeach ?>
            <?php else: ?>
              <p class="text-muted text-center">Tidak ada agenda minggu ini.</p>
            <?php endif ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Tambahan CSS -->
<style>
  .hover-card {
    transition: all 0.3s ease;
    border-radius: 12px;
  }
  .hover-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 6px 15px rgba(0,0,0,0.15);
  }
  .stat-card {
    border-radius: 15px;
    background: rgba(255,255,255,0.9);
  }
  /* Timeline */
  .timeline-item {
    display: flex;
    align-items: flex-start;
    gap: 10px;
  }
  .timeline-icon {
    width: 35px;
    height: 35px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
  }
  .timeline-content {
    flex: 1;
  }
</style>
