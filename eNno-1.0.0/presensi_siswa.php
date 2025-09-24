<?php
// session_start();
$koneksi = new mysqli("localhost", "root", "", "presensi_ekstra");

// Validasi login siswa
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'siswa') {
    header("Location: login.php");
    exit;
}

$nis = $_SESSION['user_id'];
$id_ekstra = $_GET['id_ekstra'] ?? '';

if (empty($nis) || empty($id_ekstra)) {
    die("Data tidak valid. Silakan kembali.");
}

// Ambil data siswa
$stmtSiswa = $koneksi->prepare("
    SELECT s.nama, k.nama_kelas 
    FROM tb_siswa s 
    LEFT JOIN tb_kelas k ON s.id_kelas = k.id_kelas 
    WHERE s.nis = ?
");
$stmtSiswa->bind_param("s", $nis);
$stmtSiswa->execute();
$resultSiswa = $stmtSiswa->get_result();
$siswa = $resultSiswa->fetch_assoc();

if (!$siswa) {
    die("Data siswa tidak ditemukan.");
}

// Ambil nama ekstrakurikuler
$stmtEkstra = $koneksi->prepare("SELECT nama_ekstra FROM tb_ekstrakurikuler WHERE id_ekstra = ?");
$stmtEkstra->bind_param("s", $id_ekstra);
$stmtEkstra->execute();
$resultEkstra = $stmtEkstra->get_result();
$namaEkstra = $resultEkstra->fetch_assoc()['nama_ekstra'] ?? '';

// Statistik presensi
$queryStat = $koneksi->prepare("
    SELECT status, COUNT(*) AS jumlah 
    FROM tb_presensi 
    WHERE nis = ? AND id_ekstra = ?
    GROUP BY status
");
$queryStat->bind_param("ss", $nis, $id_ekstra);
$queryStat->execute();
$resultStat = $queryStat->get_result();

$stat = ['H' => 0, 'I' => 0, 'S' => 0, 'A' => 0];
while ($row = $resultStat->fetch_assoc()) {
    $kode = $row['status'];
    if (isset($stat[$kode])) {
        $stat[$kode] = $row['jumlah'];
    }
}

// Riwayat presensi
$queryPresensi = $koneksi->prepare("
    SELECT tanggal, status, catatan 
    FROM tb_presensi 
    WHERE nis = ? AND id_ekstra = ?
    ORDER BY tanggal DESC
");
$queryPresensi->bind_param("ss", $nis, $id_ekstra);
$queryPresensi->execute();
$dataPresensi = $queryPresensi->get_result();
?>

    <div class="container mt-5">
    <!-- Judul -->
    <div class="mb-4 text-center">
        <h2 class="fw-bold text-primary mb-2">📌 Presensi Ekstrakurikuler <?= htmlspecialchars($namaEkstra) ?></h2>
        <small class="text-muted d-block">
            <strong>👤 Nama:</strong> <?= htmlspecialchars($siswa['nama']) ?> |
            <strong>🏫 Kelas:</strong> <?= htmlspecialchars($siswa['nama_kelas']) ?>
        </small>
    </div>

    <!-- Statistik Presensi -->
    <div class="row mb-5">
        <?php
        $listStat = [
            'Hadir' => ['kode' => 'H', 'warna' => 'success', 'icon'=>'bi-check-circle-fill'],
            'Izin'  => ['kode' => 'I', 'warna' => 'warning', 'icon'=>'bi-envelope-paper-fill'],
            'Sakit' => ['kode' => 'S', 'warna' => 'info',    'icon'=>'bi-thermometer-half'],
            'Alfa'  => ['kode' => 'A', 'warna' => 'danger',  'icon'=>'bi-x-circle-fill']
        ];
        foreach ($listStat as $label => $data):
        ?>
        <div class="col-md-3 col-6 mb-3">
            <div class="card card-statistik border-0 shadow-sm rounded-4 h-100 hover-card">
                <div class="card-body text-center">
                    <div class="icon-circle bg-<?= $data['warna'] ?> bg-opacity-10 text-<?= $data['warna'] ?> mb-2">
                        <i class="bi <?= $data['icon'] ?> fs-3"></i>
                    </div>
                    <h6 class="text-muted"><?= $label ?></h6>
                    <h3 class="fw-bold text-<?= $data['warna'] ?>"><?= $stat[$data['kode']] ?? 0 ?></h3>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Tabel Riwayat Presensi -->
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-header bg-primary text-white rounded-top-4">
            <h5 class="mb-0"><i class="bi bi-list-check me-2"></i> Riwayat Presensi</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive" style="max-height:400px; overflow-y:auto;">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light sticky-top">
                        <tr>
                            <th class="text-center">No</th>
                            <th>Tanggal</th>
                            <th>Status</th>
                            <th>Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php $no = 1; while ($row = $dataPresensi->fetch_assoc()):
                        $warna = match($row['status']) {
                            'H' => 'success',
                            'I' => 'warning',
                            'S' => 'info',
                            'A' => 'danger',
                            default => 'secondary'
                        };
                        $labelStatus = match($row['status']) {
                            'H' => 'Hadir',
                            'I' => 'Izin',
                            'S' => 'Sakit',
                            'A' => 'Alfa',
                            default => 'Tidak Diketahui'
                        };
                    ?>
                        <tr>
                            <td class="text-center"><?= $no++ ?></td>
                            <td><?= date('d/m/Y', strtotime($row['tanggal'])) ?></td>
                            <td>
                                <span class="badge bg-<?= $warna ?> px-3 py-2">
                                    <?= $labelStatus ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($row['catatan'] ?? '-') ?></td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Tambahan CSS -->
<style>
    .icon-circle {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 60px;
        height: 60px;
        border-radius: 50%;
    }
    .hover-card {
        transition: all 0.3s ease;
    }
    .hover-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 6px 15px rgba(0,0,0,0.15);
    }
</style>