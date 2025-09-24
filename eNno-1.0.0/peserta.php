<?php
include 'koneksi.php';

// Ambil semua data ekstrakurikuler
$stmt = $pdo->query("
  SELECT e.id_ekstra, e.nama_ekstra
  FROM tb_ekstrakurikuler e
  ORDER BY e.nama_ekstra ASC
");
$dataEkstra = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Simpan semua card ekstra
$cardsEkstra = [];

foreach ($dataEkstra as $ekstra) {
    $id_ekstra   = $ekstra['id_ekstra'];
    $nama_ekstra = $ekstra['nama_ekstra'];

    // Hitung total peserta
    $qTotal = $pdo->prepare("SELECT COUNT(*) FROM tb_peserta_ekstra WHERE id_ekstra = ?");
    $qTotal->execute([$id_ekstra]);
    $jumlahPeserta = $qTotal->fetchColumn();

    // Hitung laki-laki
    $qLaki = $pdo->prepare("
        SELECT COUNT(*) 
        FROM tb_peserta_ekstra p
        JOIN tb_siswa s ON p.nis = s.nis
        WHERE p.id_ekstra = ? AND s.jk = 'L'
    ");
    $qLaki->execute([$id_ekstra]);
    $laki = $qLaki->fetchColumn();

    // Hitung perempuan
    $qPerempuan = $pdo->prepare("
        SELECT COUNT(*) 
        FROM tb_peserta_ekstra p
        JOIN tb_siswa s ON p.nis = s.nis
        WHERE p.id_ekstra = ? AND s.jk = 'P'
    ");
    $qPerempuan->execute([$id_ekstra]);
    $perempuan = $qPerempuan->fetchColumn();

    $cardsEkstra[] = [
        'id' => $id_ekstra,
        'nama' => $nama_ekstra,
        'jumlah' => $jumlahPeserta,
        'laki' => $laki,
        'perempuan' => $perempuan
    ];
}
?>

<!-- ======= Main Content ======= -->
<section class="section">
  <div class="card">
    <div class="card-body">
      <h5 class="card-title">Data Ekstrakurikuler</h5>
      <p>Kelola data peserta ekstrakurikuler sekolah anda</p>

      <div class="row">
        <?php if (empty($cardsEkstra)): ?>
          <div class="alert alert-warning">Belum ada data ekstrakurikuler.</div>
        <?php else: ?>
          <?php foreach ($cardsEkstra as $ekstra): ?>
            <div class="col-md-4 mb-4">
              <div class="card border-0 shadow-lg h-100 rounded-3 hover-shadow">
                <!-- Header Card -->
                <div class="card-header bg-primary text-white text-center py-4">
                  <i class="bi bi-star fs-1"></i>
                  <h5 class="mt-2 mb-0">
                    Ekstrakurikuler <?= htmlspecialchars($ekstra['nama']) ?>
                  </h5>
                </div>
                
                <!-- Body Card -->
                <div class="card-body d-flex flex-column justify-content-between">
                  <div class="text-center mb-3">
                    <p class="text-muted mb-2">Jumlah Peserta</p>
                    <h4 class="fw-bold"><?= $ekstra['jumlah'] ?></h4>
                  </div>
                  
                  <!-- Statistik Mini -->
                  <div class="row text-center mb-3">
                    <div class="col-6">
                      <div class="p-2 bg-light rounded-3">
                        <i class="bi bi-gender-male text-primary"></i>
                        <div class="fw-semibold"><?= $ekstra['laki'] ?></div>
                        <small class="text-muted">Laki-laki</small>
                      </div>
                    </div>
                    <div class="col-6">
                      <div class="p-2 bg-light rounded-3">
                        <i class="bi bi-gender-female text-danger"></i>
                        <div class="fw-semibold"><?= $ekstra['perempuan'] ?></div>
                        <small class="text-muted">Perempuan</small>
                      </div>
                    </div>
                  </div>
                  
                  <!-- Tombol -->
                  <div class="text-center mt-auto">
                    <a href="index.php?page=daftar_peserta&id_ekstra=<?= $ekstra['id'] ?>" 
                      class="btn btn-outline-primary rounded-pill px-4">
                      <i class="bi bi-people"></i> Lihat Peserta
                    </a>
                  </div>
                </div>
              </div>
            </div>
          <?php endforeach ?>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

<style>
.hover-shadow {
  transition: all 0.3s ease;
}
.hover-shadow:hover {
  transform: translateY(-6px);
  box-shadow: 0 12px 24px rgba(0,0,0,0.15);
}
</style>
