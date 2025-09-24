<?php
$koneksi = new mysqli("localhost", "root", "", "presensi_ekstra");

// Cek apakah user sudah login sebagai siswa
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'siswa') {
    header("Location: login.php");
    exit;
}

$nis = $_SESSION['user_id'] ?? '';

if (empty($nis)) {
    echo "NIS tidak ditemukan di session.";
    exit;
}

// Ambil data siswa
$querySiswa = $koneksi->prepare("
    SELECT s.nis, s.nama, k.nama_kelas AS kelas, k.jurusan 
    FROM tb_siswa s 
    LEFT JOIN tb_kelas k ON s.id_kelas = k.id_kelas
    WHERE s.nis = ?
");
$querySiswa->bind_param("s", $nis);
$querySiswa->execute();
$resultSiswa = $querySiswa->get_result();
$dataSiswa = $resultSiswa->fetch_assoc();

// Ambil ekstrakurikuler yang diikuti
$queryEkstra = $koneksi->prepare("
    SELECT e.id_ekstra, e.nama_ekstra, e.hari, e.jam_mulai, e.jam_selesai, p.nama 
    FROM tb_ekstrakurikuler e
    JOIN tb_peserta_ekstra se ON e.id_ekstra = se.id_ekstra
    LEFT JOIN tb_pembina p ON e.id_pembina = p.id_pembina
    WHERE se.nis = ?
");
$queryEkstra->bind_param("s", $nis);
$queryEkstra->execute();
$resultEkstra = $queryEkstra->get_result();
$totalEkstra = $resultEkstra->num_rows;

// Hitung total pertemuan & hadir
$queryPresensi = $koneksi->prepare("
    SELECT COUNT(*) AS total_pertemuan,
           SUM(CASE WHEN status = 'H' THEN 1 ELSE 0 END) AS total_hadir
    FROM tb_presensi
    WHERE nis = ?
");
$queryPresensi->bind_param("s", $nis);
$queryPresensi->execute();
$resultPresensi = $queryPresensi->get_result();
$dataPresensi = $resultPresensi->fetch_assoc();
$totalPertemuan = $dataPresensi['total_pertemuan'] ?? 0;
$totalHadir = $dataPresensi['total_hadir'] ?? 0;
?>

<section class="section dashboard">
  <!-- Welcome Banner -->
  <div class="row mb-4">
    <div class="col-12">
      <div class="alert shadow-sm text-center rounded-4 p-4" 
           style="background: linear-gradient(135deg, #4dabf7, #228be6); color: white;">
        <h4 class="mb-2">
          <i class="bi bi-person-circle me-2"></i>
          Halo, <span class="fw-bold"><?= htmlspecialchars($dataSiswa['nama']) ?></span>!
        </h4>
        <p class="mb-0 opacity-75">Berikut adalah data diri, kegiatan, dan presensimu.</p>
      </div>
    </div>
  </div>

  <!-- Statistik Ringkasan -->
  <div class="row g-4 mb-4">
    <!-- Ekstra Diikuti -->
    <div class="col-md-4">
      <div class="card h-100 shadow-sm border-0 rounded-4">
        <div class="card-body text-center p-4">
          <div class="d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary rounded-circle mb-3" style="width:60px;height:60px;">
            <i class="bi bi-star-fill fs-3"></i>
          </div>
          <h6 class="text-muted">Ekstra Diikuti</h6>
          <h2 class="fw-bold text-primary"><?= $totalEkstra ?></h2>
        </div>
      </div>
    </div>
    <!-- Total Pertemuan -->
    <div class="col-md-4">
      <div class="card h-100 shadow-sm border-0 rounded-4">
        <div class="card-body text-center p-4">
          <div class="d-inline-flex align-items-center justify-content-center bg-warning bg-opacity-10 text-warning rounded-circle mb-3" style="width:60px;height:60px;">
            <i class="bi bi-calendar-event fs-3"></i>
          </div>
          <h6 class="text-muted">Total Pertemuan</h6>
          <h2 class="fw-bold text-warning"><?= $totalPertemuan ?></h2>
        </div>
      </div>
    </div>
    <!-- Jumlah Hadir -->
    <div class="col-md-4">
      <div class="card h-100 shadow-sm border-0 rounded-4">
        <div class="card-body text-center p-4">
          <div class="d-inline-flex align-items-center justify-content-center bg-success bg-opacity-10 text-success rounded-circle mb-3" style="width:60px;height:60px;">
            <i class="bi bi-check2-circle fs-3"></i>
          </div>
          <h6 class="text-muted">Jumlah Hadir</h6>
          <h2 class="fw-bold text-success"><?= $totalHadir ?></h2>
        </div>
      </div>
    </div>
  </div>

  <!-- Data Siswa & Ekstra -->
  <div class="row mb-4">
    <!-- Data Siswa -->
    <div class="col-lg-6">
      <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body p-4">
          <h5 class="card-title mb-3 text-primary fw-bold">
            <i class="bi bi-file-person me-2"></i> Data Siswa
          </h5>
          <ul class="list-group list-group-flush">
            <li class="list-group-item">NIS: <strong><?= htmlspecialchars($dataSiswa['nis']) ?></strong></li>
            <li class="list-group-item">Nama: <strong><?= htmlspecialchars($dataSiswa['nama']) ?></strong></li>
            <li class="list-group-item">Kelas: <strong><?= htmlspecialchars($dataSiswa['kelas']) ?></strong></li>
            <li class="list-group-item">Jurusan: <strong><?= htmlspecialchars($dataSiswa['jurusan']) ?></strong></li>
          </ul>
        </div>
      </div>
    </div>

    <!-- Ekstra Diikuti -->
    <div class="col-lg-6">
      <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body p-4">
          <h5 class="card-title mb-3 text-success fw-bold">
            <i class="bi bi-star-fill me-2"></i> Ekstrakurikuler
          </h5>
          <?php if ($totalEkstra > 0): ?>
            <?php $no = 1; $resultEkstra->data_seek(0); while ($ekstra = $resultEkstra->fetch_assoc()): ?>
              <div class="mb-2 d-inline-block">
                <!-- Tombol buka modal -->
                <button type="button" 
                        class="btn btn-outline-primary btn-sm rounded-pill px-3 shadow-sm hover-shadow" 
                        data-bs-toggle="modal" 
                        data-bs-target="#modalEkstra<?= $no ?>">
                  <?= htmlspecialchars($ekstra['nama_ekstra']) ?>
                </button>

                <!-- Modal -->
                <div class="modal fade" id="modalEkstra<?= $no ?>" tabindex="-1">
                  <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content shadow-lg rounded-4 border-0 overflow-hidden">
                      
                      <!-- Header -->
                      <div class="modal-header text-white py-3" 
                          style="background: linear-gradient(135deg, #0d6efd, #4dabf7);">
                        <h5 class="modal-title fw-semibold">
                          <i class="bi bi-stars me-2"></i> Detail Ekstrakurikuler
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                      </div>
                      
                      <!-- Body -->
                      <div class="modal-body bg-light px-4 py-4">
                        <div class="row g-3">
                          <div class="col-md-6">
                            <div class="p-3 rounded-3 bg-white shadow-sm h-100">
                              <h6 class="text-primary fw-bold mb-1"><i class="bi bi-bookmark-star-fill me-2"></i> Nama Ekstra</h6>
                              <p class="mb-0"><?= htmlspecialchars($ekstra['nama_ekstra']) ?></p>
                            </div>
                          </div>
                          <div class="col-md-6">
                            <div class="p-3 rounded-3 bg-white shadow-sm h-100">
                              <h6 class="text-success fw-bold mb-1"><i class="bi bi-person-badge-fill me-2"></i> Pembina</h6>
                              <p class="mb-0"><?= htmlspecialchars($ekstra['nama']) ?></p>
                            </div>
                          </div>
                          <div class="col-md-6">
                            <div class="p-3 rounded-3 bg-white shadow-sm h-100">
                              <h6 class="text-warning fw-bold mb-1"><i class="bi bi-calendar-event-fill me-2"></i> Hari</h6>
                              <span class="badge bg-warning text-dark px-3 py-2 rounded-pill">
                                <?= htmlspecialchars($ekstra['hari']) ?>
                              </span>
                            </div>
                          </div>
                          <div class="col-md-6">
                            <div class="p-3 rounded-3 bg-white shadow-sm h-100">
                              <h6 class="text-danger fw-bold mb-1"><i class="bi bi-clock-fill me-2"></i> Jam</h6>
                              <span class="badge bg-danger-subtle text-danger px-3 py-2 rounded-pill">
                                <?= htmlspecialchars($ekstra['jam_mulai']) ?> - <?= htmlspecialchars($ekstra['jam_selesai']) ?>
                              </span>
                            </div>
                          </div>
                        </div>
                      </div>
                      
                      <!-- Footer -->
                      <div class="modal-footer bg-white py-3">
                        <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">
                          <i class="bi bi-x-circle me-1"></i> Tutup
                        </button>
                      </div>
                      
                    </div>
                  </div>
                </div>
              </div>
            <?php $no++; endwhile; ?>
          <?php else: ?>
            <p class="text-muted">Belum mengikuti ekstrakurikuler.</p>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</section>

