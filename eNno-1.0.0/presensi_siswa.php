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

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Presensi Ekstrakurikuler</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Google Fonts - Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f4f7f6;
        }
        .container {
            max-width: 960px;
        }
        .text-primary {
            color: #4a6ee0 !important; /* Contoh warna primary yang lebih menarik */
        }
        .bg-primary {
            background-color: #4a6ee0 !important;
        }
        .fw-bold {
            font-weight: 700 !important;
        }
        .card-statistik {
            background-color: #ffffff;
            border-radius: 1rem; /* Sudut lebih membulat */
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }
        .card-statistik:hover {
            transform: translateY(-8px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }
        .icon-circle {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 70px; /* Ukuran ikon lebih besar */
            height: 70px;
            border-radius: 50%;
            margin-bottom: 15px; /* Jarak bawah ikon */
            font-size: 1.8rem; /* Ukuran font ikon */
        }
        .icon-circle.bg-success { background-color: rgba(40, 167, 69, 0.15) !important; color: #28a745 !important; }
        .icon-circle.bg-warning { background-color: rgba(255, 193, 7, 0.15) !important; color: #ffc107 !important; }
        .icon-circle.bg-info { background-color: rgba(23, 162, 184, 0.15) !important; color: #17a2b8 !important; }
        .icon-circle.bg-danger { background-color: rgba(220, 53, 69, 0.15) !important; color: #dc3545 !important; }

        .card-body h3 {
            font-size: 2.2rem; /* Ukuran angka statistik lebih besar */
            margin-bottom: 0;
        }
        .card-body h6 {
            font-weight: 500;
            color: #6c757d;
            margin-bottom: 5px;
        }

        .table-responsive {
            border-radius: 0 0 1rem 1rem; /* Sudut bawah tabel */
            overflow: hidden; /* Memastikan sudut tabel ikut terpotong */
        }
        .table thead th {
            background-color: #e9ecef; /* Warna header tabel */
            color: #495057;
            font-weight: 600;
            position: sticky;
            top: 0;
            z-index: 1;
            padding: 1rem 0.75rem;
        }
        .table tbody tr:hover {
            background-color: #f0f2f5;
        }
        .table td {
            padding: 0.85rem 0.75rem;
            vertical-align: middle;
        }
        .badge {
            font-size: 0.85em;
            padding: 0.6em 0.9em;
            border-radius: 0.5rem;
            font-weight: 600;
        }
        .sticky-top {
            position: sticky;
            top: 0;
            z-index: 10;
        }
        .card-header.bg-primary {
            border-bottom: none; /* Hilangkan border bawah header */
            border-radius: 1rem 1rem 0 0 !important; /* Sudut atas lebih membulat */
            padding: 1.25rem;
        }
        .card-header h5 {
            font-weight: 600;
        }
    </style>
</head>
<body>

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
            <div class="card card-statistik border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="icon-circle bg-<?= $data['warna'] ?> text-<?= $data['warna'] ?>">
                        <i class="bi <?= $data['icon'] ?>"></i>
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
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="bi bi-list-check me-2"></i> Riwayat Presensi</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive" style="max-height:400px; overflow-y:auto;">
                <table class="table table-hover align-middle mb-0">
                    <thead class="sticky-top">
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
                                <span class="badge bg-<?= $warna ?>">
                                    <?= $labelStatus ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($row['catatan'] ?? '-') ?></td>
                        </tr>
                    <?php endwhile; ?>
                    <?php if ($dataPresensi->num_rows == 0): ?>
                        <tr>
                            <td colspan="4" class="text-center p-4 text-muted">Belum ada riwayat presensi untuk ekstrakurikuler ini.</td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS Bundle (popper.js included) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>