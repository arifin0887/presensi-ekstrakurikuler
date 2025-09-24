<?php
// session_start();
$koneksi = new mysqli("localhost", "root", "", "presensi_ekstra");

// Cek login sebagai pembina
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'pembina') {
    header("Location: login.php");
    exit;
}

$id_pembina = $_SESSION['user_id'] ?? '';

if (empty($id_pembina)) {
    echo "ID Pembina tidak ditemukan.";
    exit;
}

// Ambil daftar id_ekstra yang dibina oleh pembina ini
$queryEkstra = $koneksi->prepare("
    SELECT pe.id_ekstra, pe.nama_ekstra 
    FROM tb_ekstrakurikuler pe
    JOIN tb_pembina e ON pe.id_pembina = e.id_pembina
    WHERE pe.id_pembina = ?
");
$queryEkstra->bind_param("s", $id_pembina);
$queryEkstra->execute();
$resultEkstra = $queryEkstra->get_result();

$idEkstraList = [];
$namaEkstraMap = [];

while ($row = $resultEkstra->fetch_assoc()) {
    $idEkstraList[] = $row['id_ekstra'];
    $namaEkstraMap[$row['id_ekstra']] = $row['nama_ekstra'];
}

if (empty($idEkstraList)) {
    echo "<div class='alert alert-warning m-4'>Anda belum membina ekstrakurikuler apa pun.</div>";
    exit;
}

// Buat string untuk IN clause
$inClause = implode(',', array_fill(0, count($idEkstraList), '?'));

// Query presensi untuk semua id_ekstra tersebut
$queryPresensi = $koneksi->prepare("
    SELECT p.nis, s.nama, k.nama_kelas, p.tanggal, p.status, p.id_ekstra
    FROM tb_presensi p
    JOIN tb_siswa s ON p.nis = s.nis
    LEFT JOIN tb_kelas k ON s.id_kelas = k.id_kelas
    WHERE p.id_ekstra IN ($inClause)
    ORDER BY p.tanggal DESC, s.nama ASC
");

$queryPresensi->bind_param(str_repeat('s', count($idEkstraList)), ...$idEkstraList);
$queryPresensi->execute();
$dataPresensi = $queryPresensi->get_result();
?>

<!-- Konten Utama -->
<style>
    .badge {
        font-size: 0.85rem;
        padding: 0.4em 0.75em;
        border-radius: 10px;
    }

    .table th, .table td {
        vertical-align: middle;
        text-align: center;
    }

    .table thead th {
        background-color: #2e4ead;
        color: white;
        border: none;
    }

    .table tbody tr:hover {
        background-color: #f1f4f9;
    }

    .card-header {
        background-color: #4154f1;
        color: white;
        border-bottom: 1px solid #dee2e6;
        font-weight: 600;
        padding: 1rem;
        border-radius: 0.5rem 0.5rem 0 0;
    }

    .card {
        border-radius: 0.5rem;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.05);
        border: none;
    }

    .pagetitle h1 {
        font-size: 1.75rem;
        font-weight: 600;
        color: #012970;
        margin-bottom: 0.5rem;
    }

    .section.dashboard {
        padding: 20px 10px;
    }

    @media (max-width: 768px) {
        .table th, .table td {
            font-size: 0.85rem;
        }
    }
</style>

<section class="section dashboard">
  <div class="container mt-4">

        <!-- Judul -->
        <div class="pagetitle mb-4">
            <h1>Rekap Presensi Peserta Ekstrakurikuler</h1>
            <p class="text-muted">Berikut adalah riwayat presensi seluruh siswa dari ekstrakurikuler yang Anda bina.</p>
        </div>

        <!-- Tabel Presensi Detail -->
    
        <div class="card-body">
            <div class="table-responsive">
            <table class="table table-hover table-bordered align-middle">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Ekstrakurikuler</th>
                        <th>Nama Siswa</th>
                        <th>Kelas</th>
                        <th>Tanggal</th>
                        <th>Status</th>
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
                        $label = match($row['status']) {
                            'H' => 'Hadir',
                            'I' => 'Izin',
                            'S' => 'Sakit',
                            'A' => 'Alfa',
                            default => $row['status']
                        };
                        $namaEkstra = $namaEkstraMap[$row['id_ekstra']] ?? '-';
                    ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= htmlspecialchars($namaEkstra) ?></td>
                        <td><?= htmlspecialchars($row['nama']) ?></td>
                        <td><?= htmlspecialchars($row['nama_kelas']) ?></td>
                        <td><?= date('d/m/Y', strtotime($row['tanggal'])) ?></td>
                        <td><span class="badge bg-<?= $warna ?>"><?= $label ?></span></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            </div>
        </div>
  </div>
</section>
