<?php
// session_start();
require_once 'koneksi.php';

// Validasi pembina login
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'pembina') {
    header('Location: login.php');
    exit;
}

$id_pembina = $_SESSION['user_id'];

// Ambil informasi ekstrakurikuler yang dibimbing
$queryEkstra = $pdo->prepare("SELECT * FROM tb_ekstrakurikuler WHERE id_pembina = :id_pembina");
$queryEkstra->execute(['id_pembina' => $id_pembina]);
$ekstra = $queryEkstra->fetch(PDO::FETCH_ASSOC);

if (!$ekstra) {
    echo "<div class='alert alert-warning'>Anda belum membimbing ekstrakurikuler manapun.</div>";
    exit;
}

// Cek apakah hari ini 
$hari_ini = date('l'); 

// Ambil semua hari latihan dari ekstrakurikuler yang dibimbing oleh pembina
$queryHari = $pdo->prepare("SELECT LOWER(hari) as hari FROM tb_ekstrakurikuler WHERE id_pembina = :id_pembina");
$queryHari->execute(['id_pembina' => $id_pembina]);
$hariEkstra = $queryHari->fetchAll(PDO::FETCH_COLUMN);

// Ambil hari ini dalam format bahasa Indonesia (lowercase)
$mapHari = [
    'Sunday'    => 'minggu',
    'Monday'    => 'senin',
    'Tuesday'   => 'selasa',
    'Wednesday' => 'rabu',
    'Thursday'  => 'kamis',
    'Friday'    => 'jumat',
    'Saturday'  => 'sabtu'
];
$hariIni = strtolower($mapHari[date('l')] ?? '');

// Cek apakah hari ini termasuk dalam salah satu jadwal ekstra
$isJadwal = in_array($hariIni, $hariEkstra);

// Ambil daftar siswa yang mengikuti ekstra tersebut
$querySiswa = $pdo->prepare("
    SELECT s.nis, s.nama, k.jenjang, k.jurusan, k.nama_kelas,
           COALESCE(p.catatan, '') AS catatan
    FROM tb_siswa s
    JOIN tb_kelas k ON s.id_kelas = k.id_kelas
    JOIN tb_peserta_ekstra se ON s.nis = se.nis
    LEFT JOIN tb_presensi p 
           ON p.nis = s.nis 
          AND p.id_ekstra = se.id_ekstra
          AND p.tanggal = (
              SELECT MAX(pp.tanggal) 
              FROM tb_presensi pp 
              WHERE pp.nis = s.nis AND pp.id_ekstra = se.id_ekstra
          )
    WHERE se.id_ekstra = :id_ekstra
");
$querySiswa->execute(['id_ekstra' => $ekstra['id_ekstra']]);
$siswaList = $querySiswa->fetchAll(PDO::FETCH_ASSOC);

// Ambil data presensi siswa
$presensiData = [];
$stmtPresensi = $pdo->prepare("SELECT nis, status, COUNT(*) as total FROM tb_presensi WHERE id_ekstra = :id_ekstra GROUP BY nis, status");
$stmtPresensi->execute(['id_ekstra' => $ekstra['id_ekstra']]);
foreach ($stmtPresensi->fetchAll(PDO::FETCH_ASSOC) as $pres) {
    $presensiData[$pres['nis']][$pres['status']] = $pres['total'];
}
?>

<section class="section">
  <div class="card">
    <div class="card-body">
        <div class="mb-3">
          <h4 class="card-title mb-1">
            Ekstrakurikuler <strong><?= htmlspecialchars($ekstra['nama_ekstra']) ?></strong>
          </h4>
          <p class="mb-1 text-muted">
            <i class="bi bi-calendar-event me-1"></i>
            <?= date('l, d M Y') ?>
          </p>
          <p class="mb-2">
            Jadwal Ekstrakurikuler <span class="fw-bold text-info"><?= ucwords($ekstra['hari']) ?></span>
          </p>

          <?php if ($isJadwal): ?>
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalPresensi">
              <i class="bi bi-calendar-check"></i> Input Presensi Hari Ini
            </button>
          <?php else: ?>
            <div class="alert alert-info mt-3">
              Hari ini bukan jadwal Ekstrakurikuler Anda. Tombol presensi akan muncul di hari yang sesuai.
            </div>
          <?php endif; ?>
        </div>

        <div class="card shadow">
          <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Daftar Siswa Ekstra</h5>
          </div>
          <div class="card-body">
            <table class="table table-bordered table-hover">
              <thead class="table-light">
                <tr>
                  <th>No</th>
                  <th>NIS</th>
                  <th>Nama</th>
                  <th>Kelas</th>
                  <th>Hadir</th>
                  <th>Alfa</th>
                  <th>Sakit</th>
                  <th>Izin</th>
                </tr>
              </thead>
              <tbody>
                <?php
                $no = 1;
                foreach ($siswaList as $siswa):
                    $nis = $siswa['nis'];
                    $hadir = $presensiData[$nis]['H'] ?? 0;
                    $alfa  = $presensiData[$nis]['A'] ?? 0;
                    $sakit = $presensiData[$nis]['S'] ?? 0;
                    $izin  = $presensiData[$nis]['I'] ?? 0;
                ?>
                <tr>
                  <td><?= $no++ ?></td>
                  <td><?= htmlspecialchars($nis) ?></td>
                  <td><?= htmlspecialchars($siswa['nama']) ?></td>
                  <td><?= "{$siswa['jenjang']} {$siswa['nama_kelas']}" ?></td>
                  <td><?= $hadir ?></td>
                  <td><?= $alfa ?></td>
                  <td><?= $sakit ?></td>
                  <td><?= $izin ?></td>
                </tr>
                <?php endforeach ?>
              </tbody>
            </table>
          </div>
        </div>
    </div>
</section>

<!-- Modal Input Presensi -->
<div class="modal fade" id="modalPresensi" tabindex="-1" aria-labelledby="modalPresensiLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <form action="" method="post">
      <div class="modal-content">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title" id="modalPresensiLabel">Input Presensi Ekstrakurikuler</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
          <input type="hidden" name="id_ekstra" value="<?= htmlspecialchars($ekstra['id_ekstra']) ?>">
          <div class="table-responsive">
            <table class="table table-bordered align-middle">
              <thead>
                <tr>
                  <th>No</th>
                  <th>Nama</th>
                  <th>Kelas</th>
                  <th>Presensi</th>
                  <th>Catatan</th>
                </tr>
              </thead>
              <tbody>
                <?php $no = 1; foreach ($siswaList as $siswa): ?>
                  <tr>
                    <td><?= $no++ ?></td>
                    <td><?= htmlspecialchars($siswa['nama']) ?></td>
                    <td><?= "{$siswa['jenjang']} {$siswa['jurusan']} {$siswa['nama_kelas']}" ?></td>
                    <td>
                      <input type="hidden" name="nis[]" value="<?= htmlspecialchars($siswa['nis']) ?>">
                      <select name="status[]" class="form-select" required>
                        <option value="">-- Pilih --</option>
                        <option value="H">Hadir</option>
                        <option value="S">Sakit</option>
                        <option value="I">Izin</option>
                        <option value="A">Alfa</option>
                      </select>
                    </td>
                    <td>
                      <textarea name="catatan[]" class="form-control" rows="2"><?= htmlspecialchars($siswa['catatan'] ?? '') ?></textarea>
                    </td>
                  </tr>
                <?php endforeach ?>
              </tbody>
            </table>
          </div>
        </div>

        <div class="modal-footer">
          <button type="submit" class="btn btn-success">
            <i class="bi bi-save"></i> Simpan Presensi
          </button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        </div>
      </div>
    </form>
  </div>
</div>


<!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script> -->
