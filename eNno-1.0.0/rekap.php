<?php
require_once 'koneksi.php';

// Validasi login pembina
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'pembina') {
    header('Location: login.php');
    exit;
}

$id_pembina = $_SESSION['user_id'];

// Ambil ekstra yang dibimbing
$stmtEkstra = $pdo->prepare("SELECT * FROM tb_ekstrakurikuler WHERE id_pembina = :id");
$stmtEkstra->execute(['id' => $id_pembina]);
$ekstra = $stmtEkstra->fetch();

if (!$ekstra) {
    echo "<div class='alert alert-warning'>Anda belum membimbing ekstrakurikuler apapun.</div>";
    exit;
}

// Filter bulan dan tahun
$bulan = isset($_GET['bulan']) ? (int)$_GET['bulan'] : date('m');
$tahun = isset($_GET['tahun']) ? (int)$_GET['tahun'] : date('Y');

$namaBulan = [
    "01" => "Januari",
    "02" => "Februari",
    "03" => "Maret",
    "04" => "April",
    "05" => "Mei",
    "06" => "Juni",
    "07" => "Juli",
    "08" => "Agustus",
    "09" => "September",
    "10" => "Oktober",
    "11" => "November",
    "12" => "Desember"
];
$bulan = isset($_GET['bulan']) ? str_pad($_GET['bulan'], 2, "0", STR_PAD_LEFT) : date("m");
$tahun = isset($_GET['tahun']) ? $_GET['tahun'] : date("Y");
$tahunList = range(date('Y')-2, date('Y')+1);

// Ambil rekap status global
$stmtRekap = $pdo->prepare("
    SELECT status, COUNT(*) AS total 
    FROM tb_presensi 
    WHERE id_ekstra=:id AND MONTH(tanggal)=:bulan AND YEAR(tanggal)=:tahun
    GROUP BY status
");
$stmtRekap->execute(['id'=>$ekstra['id_ekstra'],'bulan'=>$bulan,'tahun'=>$tahun]);

$rekap = ['H'=>0,'S'=>0,'I'=>0,'A'=>0];
foreach ($stmtRekap as $r) {
    $rekap[$r['status']] = $r['total'];
}

// Rekap per siswa (yang sudah punya data presensi)
$stmtPerSiswa = $pdo->prepare("
    SELECT s.nis, s.nama,
        SUM(CASE WHEN p.status='H' THEN 1 ELSE 0 END) AS hadir,
        SUM(CASE WHEN p.status='S' THEN 1 ELSE 0 END) AS sakit,
        SUM(CASE WHEN p.status='I' THEN 1 ELSE 0 END) AS izin,
        SUM(CASE WHEN p.status='A' THEN 1 ELSE 0 END) AS alfa,
        COUNT(*) AS total_pertemuan
    FROM tb_presensi p
    INNER JOIN tb_siswa s ON s.nis = p.nis
    WHERE p.id_ekstra=:id AND MONTH(p.tanggal)=:bulan AND YEAR(p.tanggal)=:tahun
    GROUP BY s.nis, s.nama
    ORDER BY hadir DESC
");
$stmtPerSiswa->execute(['id'=>$ekstra['id_ekstra'],'bulan'=>$bulan,'tahun'=>$tahun]);
$rekapPerSiswa = $stmtPerSiswa->fetchAll(PDO::FETCH_ASSOC);

// === Ambil semua peserta ekstra (apapun status presensinya) ===
$sql = "SELECT p.nis, s.nis, s.nama, k.jenjang, k.jurusan, k.nama_kelas 
        FROM tb_peserta_ekstra p
        JOIN tb_siswa s ON p.nis = s.nis
        JOIN tb_kelas k ON s.id_kelas = k.id_kelas
        WHERE p.id_ekstra = :id_ekstra
        ORDER BY s.nama";
$stmt = $pdo->prepare($sql);
$stmt->execute(['id_ekstra' => $ekstra['id_ekstra']]);
$peserta = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Ambil semua presensi peserta di bulan ini
$sql_presensi = "SELECT pr.id_presensi, pr.tanggal, pr.status
                 FROM tb_presensi pr
                 JOIN tb_peserta_ekstra p ON pr.nis = p.nis
                 WHERE p.id_ekstra = :id_ekstra
                   AND MONTH(pr.tanggal)=:bulan
                   AND YEAR(pr.tanggal)=:tahun
                 ORDER BY pr.tanggal DESC";
$stmt_presensi = $pdo->prepare($sql_presensi);
$stmt_presensi->execute(['id_ekstra'=>$ekstra['id_ekstra'],'bulan'=>$bulan,'tahun'=>$tahun]);
$presensi = $stmt_presensi->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_ASSOC);

// Tentukan jumlah pertemuan bulan ini (berdasarkan distinct tanggal presensi)
$stmtPertemuan = $pdo->prepare("SELECT COUNT(DISTINCT tanggal) FROM tb_presensi 
    WHERE id_ekstra=:id AND MONTH(tanggal)=:bulan AND YEAR(tanggal)=:tahun");
$stmtPertemuan->execute(['id'=>$ekstra['id_ekstra'],'bulan'=>$bulan,'tahun'=>$tahun]);
$totalPertemuan = $stmtPertemuan->fetchColumn();
?>
<!DOCTYPE html>
<html>
<head>
  <title>Rekap Presensi - <?= htmlspecialchars($ekstra['nama_ekstra']) ?></title>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<div class="container py-4">
  <h3>📊 Rekap Presensi <?= htmlspecialchars($ekstra['nama_ekstra']) ?> - <?= $namaBulan[$bulan]." ".$tahun ?></h3>

  <!-- Filter Bulan & Tahun -->
  <form method="GET" class="row g-2 my-3">
    <input type="hidden" name="page" value="rekap_pembina">
    <div class="col-auto">
      <select name="bulan" class="form-select">
        <?php foreach ($namaBulan as $num => $nm): ?>
          <option value="<?= $num ?>" <?= $bulan == $num ? 'selected' : '' ?>><?= $nm ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-auto">
      <select name="tahun" class="form-select">
        <?php foreach ($tahunList as $th): ?>
          <option value="<?= $th ?>" <?= $tahun==$th?'selected':'' ?>><?= $th ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-auto">
      <button class="btn btn-primary">Lihat</button>
    </div>
  </form>

  <!-- Ringkasan -->
  <div class="row text-center mb-4">
    <div class="col-md-3"><div class="alert alert-success">✅ Hadir<br><b><?= $rekap['H'] ?></b></div></div>
    <div class="col-md-3"><div class="alert alert-warning">🤧 Sakit<br><b><?= $rekap['S'] ?></b></div></div>
    <div class="col-md-3"><div class="alert alert-info">📝 Izin<br><b><?= $rekap['I'] ?></b></div></div>
    <div class="col-md-3"><div class="alert alert-danger">❌ Alfa<br><b><?= $rekap['A'] ?></b></div></div>
  </div>

  <div class="row">
    <div class="col-md-6 mb-4">
      <!-- Pie Chart -->
      <canvas id="pieChart" style="max-height: 400px;"></canvas>
      <script>
      document.addEventListener("DOMContentLoaded", () => {
        new Chart(document.querySelector('#pieChart'), {
          type: 'pie',
          data: {
            labels: ['Hadir', 'Sakit', 'Izin', 'Alfa'],
            datasets: [{
              data: [<?= $rekap['H'] ?>, <?= $rekap['S'] ?>, <?= $rekap['I'] ?>, <?= $rekap['A'] ?>],
              backgroundColor: [
                '#198754', // Hijau - Hadir
                '#ffc107', // Kuning - Sakit
                '#0dcaf0', // Biru Muda - Izin
                '#dc3545'  // Merah - Alfa
              ],
              hoverOffset: 4
            }]
          }
        });
      });
      </script>
    </div>
    <div class="col-md-6 mb-4">
      <!-- Bar Chart -->
      <canvas id="barChart" style="max-height: 400px;"></canvas>
      <script>
      document.addEventListener("DOMContentLoaded", () => {
        new Chart(document.querySelector('#barChart'), {
          type: 'bar',
          data: {
            labels: ['Hadir', 'Sakit', 'Izin', 'Alfa'],
            datasets: [{
              label: 'Jumlah',
              data: [<?= $rekap['H'] ?>, <?= $rekap['S'] ?>, <?= $rekap['I'] ?>, <?= $rekap['A'] ?>],
              backgroundColor: [
                '#198754',
                '#ffc107',
                '#0dcaf0',
                '#dc3545'
              ]
            }]
          },
          options: {
            responsive: true,
            plugins: {
              legend: {
                display: false
              }
            }
          }
        });
      });
      </script>
    </div>
  </div>


  <!-- Rekap per siswa -->
  <h5>📋 Rekap Presensi Siswa</h5>
  <table class="table table-striped table-bordered">
    <thead class="table-dark">
      <tr>
        <th>No</th>
        <th>NIS</th>
        <th>Nama</th>
        <th>Hadir</th>
        <th>Sakit</th>
        <th>Izin</th>
        <th>Alfa</th>
        <th>Detail</th>
      </tr>
    </thead>
    <tbody>
      <?php $no=1; foreach($rekapPerSiswa as $rs): 
        $persen = $rs['total_pertemuan'] ? round(($rs['hadir']/$rs['total_pertemuan'])*100,1) : 0;
      ?>
      <tr>
        <td><?= $no++ ?></td>
        <td><?= htmlspecialchars($rs['nis']) ?></td>
        <td><?= htmlspecialchars($rs['nama']) ?></td>
        <td class="text-success"><?= $rs['hadir'] ?></td>
        <td class="text-warning"><?= $rs['sakit'] ?></td>
        <td class="text-info"><?= $rs['izin'] ?></td>
        <td class="text-danger"><?= $rs['alfa'] ?></td>
        <td>
          <button class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#detailModal<?= $rs['nis'] ?>">Lihat</button>
        </td>
      </tr>

      <!-- Modal Detail -->
      <div class="modal fade" id="detailModal<?= $rs['nis'] ?>" tabindex="-1" aria-labelledby="detailModalLabel<?= $rs['nis'] ?>" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header bg-primary text-white">
              <h5 class="modal-title" id="detailModalLabel<?= $rs['nis'] ?>">Detail Presensi <?= htmlspecialchars($rs['nama']) ?></h5>
              <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>

            <div class="modal-body">
              <!-- Header ala tabel -->
              <div class="row fw-bold border bg-light text-center mb-2">
                <div class="col-6 p-2 border-end">Tanggal</div>
                <div class="col-6 p-2">Status</div>
              </div>
              <?php
              // Ambil detail presensi siswa
              $stmtDetail = $pdo->prepare("
                  SELECT tanggal, status 
                  FROM tb_presensi 
                  WHERE nis=:nis AND id_ekstra=:id AND MONTH(tanggal)=:bulan AND YEAR(tanggal)=:tahun
                  ORDER BY tanggal
              ");
              $stmtDetail->execute([
                  'nis' => $rs['nis'],
                  'id' => $ekstra['id_ekstra'],
                  'bulan' => $bulan,
                  'tahun' => $tahun
              ]);
              $details = $stmtDetail->fetchAll(PDO::FETCH_ASSOC);

              // Rekap Kehadiran
              $rekapDetail = ['H'=>0,'I'=>0,'S'=>0,'A'=>0];
              foreach ($details as $d) {
                  if (isset($rekapDetail[$d['status']])) {
                      $rekapDetail[$d['status']]++;
                  }
              }
              ?>
              
              <?php
              if ($details):
                  foreach ($details as $d):
                      $badgeClass = match ($d['status']) {
                          'H' => 'success',
                          'S' => 'warning text-dark',
                          'I' => 'info text-dark',
                          default => 'danger'
                      };
                      $statusText = match ($d['status']) {
                          'H' => 'Hadir',
                          'S' => 'Sakit',
                          'I' => 'Izin',
                          default => 'Alfa'
                      };
              ?>
                <div class="row align-items-center border-bottom py-2">
                  <div class="col-6 text-center"><?= date('d-m-Y', strtotime($d['tanggal'])) ?></div>
                  <div class="col-6 text-center">
                    <span class="badge bg-<?= $badgeClass ?>"><?= $statusText ?></span>
                  </div>
                </div>

                <!-- Rekap Kehadiran -->
              <!-- <div class="mt-3 p-2 border rounded bg-light">
                <strong>Rekap Kehadiran:</strong><br>
                <span class="badge bg-success">Hadir: <?= $rekapDetail['H'] ?></span>
                <span class="badge bg-warning text-dark">Izin: <?= $rekapDetail['I'] ?></span>
                <span class="badge bg-info text-dark">Sakit: <?= $rekapDetail['S'] ?></span>
                <span class="badge bg-danger">Alpa: <?= $rekapDetail['A'] ?></span>
              </div> -->
              
              <?php
                  endforeach;
              else:
              ?>
                <div class="alert alert-info text-center">Tidak ada data presensi.</div>
              <?php endif; ?>
            </div>

            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

  <!-- Daftar semua peserta dengan status lengkap -->
  <h5 class="mt-5">👥 Daftar Semua Peserta Ekstrakurikuler</h5>
  <table class="table table-bordered table-hover">
    <thead class="table-secondary">
      <tr>
        <th>NIS</th>
        <th>Nama</th>
        <th>Kelas</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($peserta as $row): 
        $rekapPes = ['H'=>0,'I'=>0,'S'=>0,'A'=>0];
        if (!empty($presensi[$row['nis']])) {
            foreach ($presensi[$row['nis']] as $p) {
                if (isset($rekapPes[$p['status']])) {
                    $rekapPes[$p['status']]++;
                }
            }
        }
        $totalKeterangan = array_sum($rekapPes);
        $belum = max(0, $totalPertemuan - $totalKeterangan);
      ?>
      <tr>
        <td><?= $row['nis'] ?></td>
        <td><?= $row['nama'] ?></td>
        <td><?= $row['jenjang'].' '.$row['nama_kelas'] ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<!-- <script>
const pieCtx = document.getElementById('pieChart').getContext('2d');
new Chart(pieCtx, {
  type:'pie',
  data:{
    labels:['Hadir','Sakit','Izin','Alfa'],
    datasets:[{
      data:[<?= $rekap['H'] ?>,<?= $rekap['S'] ?>,<?= $rekap['I'] ?>,<?= $rekap['A'] ?>],
      backgroundColor:['#198754','#ffc107','#0dcaf0','#dc3545']
    }]
  }
});

const barCtx = document.getElementById('barChart').getContext('2d');
new Chart(barCtx, {
  type:'bar',
  data:{
    labels:['Hadir','Sakit','Izin','Alfa'],
    datasets:[{
      label:'Jumlah',
      data:[<?= $rekap['H'] ?>,<?= $rekap['S'] ?>,<?= $rekap['I'] ?>,<?= $rekap['A'] ?>],
      backgroundColor:['#198754','#ffc107','#0dcaf0','#dc3545']
    }]
  }
});
</script> -->
</body>
</html>