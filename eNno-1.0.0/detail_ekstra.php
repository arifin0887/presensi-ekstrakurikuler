<?php
include 'koneksi.php';

if (!isset($_GET['id'])) {
  die("ID ekstrakurikuler tidak ditemukan.");
}

$id = $_GET['id'];

// Ambil data ekstra + pembina
$stmt = $pdo->prepare("SELECT e.*, p.nama AS nama_pembina 
                       FROM tb_ekstrakurikuler e 
                       LEFT JOIN tb_pembina p ON e.id_pembina = p.id_pembina 
                       WHERE e.id_ekstra = ?");
$stmt->execute([$id]);
$ekstra = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$ekstra) {
  die("Data ekstrakurikuler tidak tersedia.");
}

// Mapping ikon
$icon_map = [
    'Pasopati' => 'fa-campground',
    'Tonti' => 'fa-flag',
    'Basket' => 'fa-basketball-ball',
    'Futsal' => 'fa-futbol',
    'Volly' => 'fa-volleyball-ball',
    'Tari' => 'fa-mask',
    'Band' => 'fa-music',
    'OSIS' => 'fa-users',
    'Padus' => 'fa-microphone',
    'PMR' => 'fa-briefcase-medical',
    'Cospala' => 'fa-hiking',
    'Screen' => 'fa-camera',
    'Rohis' => 'fa-mosque',
    'default' => 'fa-shapes'
];
$icon_class = $icon_map[$ekstra['nama_ekstra']] ?? $icon_map['default'];

// Ambil anggota ekstrakurikuler (tampilkan nama)
$stmt2 = $pdo->prepare("SELECT s.nis, s.nama FROM tb_peserta_ekstra pe JOIN tb_siswa s ON pe.nis = s.nis WHERE pe.id_ekstra = ?");
$stmt2->execute([$id]);
$anggota = $stmt2->fetchAll(PDO::FETCH_ASSOC);

// Ambil prestasi jika ada
$prestasi = [];
if ($pdo->query("SHOW TABLES LIKE 'tb_prestasi'")->rowCount() > 0) {
    $stmt3 = $pdo->prepare("SELECT * FROM tb_prestasi WHERE id_ekstra = ? ORDER BY tahun DESC");
    $stmt3->execute([$id]);
    $prestasi = $stmt3->fetchAll(PDO::FETCH_ASSOC);
}

// Ambil foto kegiatan (misal ada kolom 'foto' di tb_kegiatan)
$galeri = [];
if ($pdo->query("SHOW TABLES LIKE 'tb_kegiatan'")->rowCount() > 0) {
    $stmt4 = $pdo->prepare("SELECT * FROM tb_kegiatan WHERE id_ekstra = ? ORDER BY tanggal DESC");
    $stmt4->execute([$id]);
    $galeri = $stmt4->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Detail Ekstrakurikuler - <?= htmlspecialchars($ekstra['nama_ekstra']); ?></title>
  <link href="assets/img/smk.png" rel="icon">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700&display=swap" rel="stylesheet">
  <style>
    body {
      background: #f4f6fa;
      font-family: 'Poppins', sans-serif;
      color: #2c384e;
      padding-top: 70px;
    }
    .navbar {
      box-shadow: 0 2px 8px rgba(65,84,241,0.07);
      background: #fff !important;
    }
    .navbar-brand, .navbar-brand-custom {
      color: #4154f1 !important;
      font-weight: 700;
      font-size: 22px;
      letter-spacing: 1px;
    }
    .nav-link {
      color: #4154f1 !important;
      font-weight: 500;
    }
    .nav-link:hover {
      color: #2c384e !important;
    }
    .hero {
      background: linear-gradient(135deg, #4154f1, #6574cd);
      color: #fff;
      padding: 60px 20px;
      text-align: center;
      border-radius: 20px;
      margin-bottom: 30px;
      position: relative;
      overflow: hidden;
    }
    .hero i {
      font-size: 80px;
      opacity: 0.2;
      position: absolute;
      right: 20px;
      bottom: 20px;
    }
    .detail-card {
      background: #fff;
      border-radius: 15px;
      box-shadow: 0 8px 20px rgba(65,84,241,0.08);
      padding: 25px;
      margin-bottom: 20px;
      transition: transform 0.3s;
      border-left: 5px solid #4154f1;
    }
    .detail-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 12px 24px rgba(65,84,241,0.13);
    }
    .info-item {
      display: flex;
      align-items: center;
      margin-bottom: 15px;
      font-size: 1rem;
    }
    .info-item i {
      width: 35px;
      height: 35px;
      background: #4154f1;
      color: #fff;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-right: 12px;
      font-size: 16px;
    }
    .gallery img {
      border-radius: 10px;
      margin-bottom: 15px;
      border: 2px solid #4154f1;
      background: #fff;
    }
    h5.section-title {
      border-bottom: 2px solid #4154f1;
      padding-bottom: 5px;
      margin-bottom: 15px;
      font-weight: 600;
      color: #4154f1;
    }
    .anggota-badge {
      font-size: 13px;
      margin-left: 6px;
      background: #4154f1 !important;
    }
    .footer {
      background: #4154f1;
      color: #fff;
      text-align: center;
      padding: 18px 0 12px 0;
      margin-top: 40px;
      border-radius: 12px 12px 0 0;
      font-size: 15px;
      letter-spacing: 0.5px;
    }
    .list-group-item {
      border: none;
      border-bottom: 1px solid #f0f0f0;
      background: transparent;
      color: #2c384e;
    }
    .list-group-item:last-child {
      border-bottom: none;
    }
    .badge.bg-primary {
      background-color: #4154f1 !important;
    }
  </style>
</head>
<body>
<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-light fixed-top">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center navbar-brand-custom" href="home.php">
      <img src="assets/img/smk.png" alt="Logo" width="36" class="me-2">
      EkstraKu
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
  </div>
</nav>

<div class="container py-4">

  <!-- Hero Section -->
  <div class="hero">
    <h1 class="fw-bold"><?= htmlspecialchars($ekstra['nama_ekstra']); ?></h1>
    <p class="lead mb-0">Detail lengkap ekstrakurikuler</p>
    <i class="fas <?= $icon_class; ?>"></i>
  </div>

  <div class="row">
    <!-- Deskripsi -->
    <div class="col-md-8">
      <div class="detail-card">
        <h4 class="fw-bold mb-3" style="color:#4154f1;">Tentang Ekstrakurikuler</h4>
        <p><?= nl2br(htmlspecialchars($ekstra['deskripsi'] ?? 'Belum ada deskripsi.')); ?></p>
      </div>

      <!-- Gallery -->
      <?php if(!empty($galeri)): ?>
      <div class="detail-card">
        <h5 class="section-title">Galeri Kegiatan</h5>
        <div class="row">
          <?php foreach($galeri as $foto): ?>
            <div class="col-6 col-md-4 mb-3">
              <img src="uploads/<?= htmlspecialchars($foto['foto']); ?>" class="img-fluid" alt="Foto Kegiatan">
            </div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>

      <!-- Prestasi -->
      <?php if(!empty($prestasi)): ?>
      <div class="detail-card">
        <h5 class="section-title">Prestasi</h5>
        <ul class="list-group list-group-flush">
          <?php foreach($prestasi as $pre): ?>
            <li class="list-group-item"><strong><?= htmlspecialchars($pre['tahun']); ?>:</strong> <?= htmlspecialchars($pre['kejuaraan']); ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
      <?php endif; ?>
    </div>

    <!-- Info Samping -->
    <div class="col-md-4">
      <div class="detail-card">
        <h5 class="fw-bold mb-3" style="color:#4154f1;">Informasi</h5>
        
        <?php if (!empty($ekstra['nama_pembina'])): ?>
          <div class="info-item">
            <i class="fas fa-user-tie"></i>
            <span><strong>Pembina:</strong> <?= htmlspecialchars($ekstra['nama_pembina']); ?></span>
          </div>
        <?php endif; ?>

        <?php if (!empty($ekstra['hari'])): ?>
          <div class="info-item">
            <i class="fas fa-calendar-alt"></i>
            <span><strong>Hari Latihan:</strong> <?= htmlspecialchars($ekstra['hari']); ?></span>
          </div>
          <div class="info-item">
            <i class="fas fa-clock"></i>
            <span><strong>Jam:</strong> <?= !empty($ekstra['jam_mulai']) ? htmlspecialchars($ekstra['jam_mulai']) : '-' ?></span>
        </div>
        <?php endif; ?>

          <div class="info-item">
            <i class="fas fa-map-marker-alt"></i>
            <span><strong>Lokasi:</strong> SMKN 2 Magelang</span>
          </div>

        <?php if($anggota): ?>
          <div class="info-item">
            <i class="fas fa-users"></i>
            <span>
              <strong>Anggota:</strong> <?= count($anggota); ?> siswa
              <span class="badge bg-primary anggota-badge"><?= count($anggota); ?></span>
            </span>
          </div>
        <?php endif; ?>

      </div>

      <!-- Daftar Anggota -->
      <?php if($anggota): ?>
      <div class="detail-card">
        <h5 class="section-title">Anggota Aktif</h5>
        <ul class="list-group list-group-flush">
          <?php foreach($anggota as $a): ?>
            <li class="list-group-item"><?= htmlspecialchars($a['nis']); ?> - <?= htmlspecialchars($a['nama']); ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
      <?php endif; ?>

    </div>
  </div>

</div>

<!-- Footer -->
<footer class="footer mt-5">
  <div class="container">
    &copy; <?= date('Y'); ?> <span style="color:#fff;font-weight:600;">EkstraKu</span> - Skanida. All Rights Reserved. |
    <a href="#" style="color:#fff;text-decoration:underline;">Privacy</a> |
    <a href="#" style="color:#fff;text-decoration:underline;">Contact</a>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>