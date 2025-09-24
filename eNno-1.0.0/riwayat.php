<?php
$koneksi = new mysqli("localhost", "root", "", "presensi_ekstra");
if ($koneksi->connect_error) {
    die("Koneksi gagal: " . $koneksi->connect_error);
}

// Nama Bulan Indonesia
$namaBulan = [
    1 => "Januari", 2 => "Februari", 3 => "Maret", 4 => "April",
    5 => "Mei", 6 => "Juni", 7 => "Juli", 8 => "Agustus",
    9 => "September", 10 => "Oktober", 11 => "November", 12 => "Desember"
];

// Tahun sekarang - 2 sampai tahun depan
$tahunList = range(date('Y') - 2, date('Y') + 1);

// Ambil data filter dari GET
$id_ekstra = isset($_GET['id_ekstra']) ? $_GET['id_ekstra'] : '';
$bulan = isset($_GET['bulan']) ? (int)$_GET['bulan'] : (int)date('m');
$tahun = isset($_GET['tahun']) ? (int)$_GET['tahun'] : (int)date('Y');

// Tambahkan ini:
$isFiltered = !empty($id_ekstra) && isset($_GET['bulan']) && isset($_GET['tahun']);

// Ambil daftar ekstra
$ekstraList = $koneksi->query("SELECT * FROM tb_ekstrakurikuler ORDER BY nama_ekstra ASC");

// Siapkan query presensi
$where = "WHERE 1=1";
if (!empty($id_ekstra)) $where .= " AND p.id_ekstra = '$id_ekstra'";
if (!empty($bulan)) $where .= " AND MONTH(p.tanggal) = '$bulan'";
if (!empty($tahun)) $where .= " AND YEAR(p.tanggal) = '$tahun'";

$queryPresensi = $koneksi->query("
    SELECT p.*, s.nama AS nama_siswa, e.nama_ekstra 
    FROM tb_presensi p
    JOIN tb_siswa s ON p.nis = s.nis
    JOIN tb_ekstrakurikuler e ON p.id_ekstra = e.id_ekstra
    $where
    ORDER BY p.tanggal DESC
");

// Rekap per siswa per ekstra
$sqlRekap = "
    SELECT s.nis, s.nama, e.nama_ekstra,
        SUM(CASE WHEN p.status = 'H' THEN 1 ELSE 0 END) AS hadir,
        SUM(CASE WHEN p.status = 'I' THEN 1 ELSE 0 END) AS izin,
        SUM(CASE WHEN p.status = 'S' THEN 1 ELSE 0 END) AS sakit,
        SUM(CASE WHEN p.status = 'A' THEN 1 ELSE 0 END) AS alpa
    FROM tb_presensi p
    JOIN tb_siswa s ON p.nis = s.nis
    JOIN tb_ekstrakurikuler e ON p.id_ekstra = e.id_ekstra
    WHERE p.id_ekstra = ? 
      AND MONTH(p.tanggal) = ? 
      AND YEAR(p.tanggal) = ?
    GROUP BY s.nis, e.id_ekstra
    ORDER BY s.nama
";
$stmtRekap = $koneksi->prepare($sqlRekap);
$stmtRekap->bind_param("iii", $id_ekstra, $bulan, $tahun);
$stmtRekap->execute();
$rekap = $stmtRekap->get_result();

// Ubah angka bulan ke nama bulan
$NamaBulan = date("F", mktime(0, 0, 0, $bulan, 10));
// Kalau mau pakai bahasa Indonesia:
$bulanIndonesia = [
  1 => 'Januari', 2 => 'Februari', 3 => 'Maret',
  4 => 'April', 5 => 'Mei', 6 => 'Juni',
  7 => 'Juli', 8 => 'Agustus', 9 => 'September',
  10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];
$NamaBulan = $bulanIndonesia[(int)$bulan];

// misalnya di tabel tb_ekstrakurikuler ada field 'hari' (isi: Senin, Selasa, dst)
$stmtHari = $koneksi->prepare("SELECT hari FROM tb_ekstrakurikuler WHERE id_ekstra=? LIMIT 1");
$stmtHari->bind_param("i", $id_ekstra);
$stmtHari->execute();
$resultHari = $stmtHari->get_result();
$dataHari = $resultHari->fetch_assoc();
$namaHari = $dataHari['hari'] ?? null;

$tanggalEkstra = [];

if ($namaHari) {
    // map nama hari ke angka PHP (1=Senin ... 7=Minggu)
    $mapHari = [
        'Senin' => 1,
        'Selasa' => 2,
        'Rabu' => 3,
        'Kamis' => 4,
        'Jumat' => 5,
        'Sabtu' => 6,
        'Minggu' => 7
    ];

    $targetDay = $mapHari[$namaHari] ?? null;

    if ($targetDay) {
        // ambil jumlah hari dalam bulan
        $jmlHariDalamBulan = cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun);

        for ($d = 1; $d <= $jmlHariDalamBulan; $d++) {
            $timestamp = strtotime("$tahun-$bulan-$d");
            if (date('N', $timestamp) == $targetDay) {
                $tanggalEkstra[] = $d;
            }
        }
    }
}

$jmlHari = count($tanggalEkstra);

// Ambil nama ekstra terpilih
if (!empty($id_ekstra)) {
    $stmtNama = $koneksi->prepare("SELECT nama_ekstra FROM tb_ekstrakurikuler WHERE id_ekstra = ?");
    $stmtNama->bind_param("i", $id_ekstra);
    $stmtNama->execute();
    $resultNama = $stmtNama->get_result()->fetch_assoc();
    $nama_ekstra_terpilih = $resultNama['nama_ekstra'] ?? '';
}
?>

<!-- ========== TAMPILAN HALAMAN NORMAL ========== -->
<section class="section">
  <div class="card">
    <div class="card-body">
      <h5 class="card-title">Riwayat Presensi Ekstrakurikuler</h5>

      <!-- Form Filter -->
      <form method="GET" action="index.php" class="row g-2 mb-3">
        <input type="hidden" name="page" value="riwayat_admin">
        <div class="col-md-4">
          <select name="id_ekstra" class="form-select" required>
            <option value="">-- Pilih Ekstrakurikuler --</option>
            <?php while ($ekstra = $ekstraList->fetch_assoc()): ?>
              <option value="<?= $ekstra['id_ekstra'] ?>" <?= $ekstra['id_ekstra'] == $id_ekstra ? 'selected' : '' ?>>
                <?= htmlspecialchars($ekstra['nama_ekstra']) ?>
              </option>
            <?php endwhile ?>
          </select>
        </div>
        <div class="col-md-3">
          <select name="bulan" class="form-select">
            <?php foreach ($namaBulan as $num => $nama): ?>
              <option value="<?= $num ?>" <?= $bulan == $num ? 'selected' : '' ?>><?= $nama ?></option>
            <?php endforeach ?>
          </select>
        </div>
        <div class="col-md-3">
          <select name="tahun" class="form-select">
            <?php foreach ($tahunList as $th): ?>
              <option value="<?= $th ?>" <?= $tahun == $th ? 'selected' : '' ?>><?= $th ?></option>
            <?php endforeach ?>
          </select>
        </div>
        <div class="col-md-2">
          <button type="submit" class="btn btn-primary w-100">Tampilkan</button>
        </div>
      </form>

      <!-- Tombol Export & Print -->
      <?php if ($queryPresensi->num_rows > 0 || !$isFiltered): ?>
      <div class="d-flex justify-content-end mb-3">

        <!-- Tombol Print -->
        <button onclick="window.print()" class="btn btn-secondary d-print-none">
          <i class="bi bi-printer"></i> Print
        </button>
      </div>

        <!-- Versi Web -->
        <?php if ($rekap->num_rows > 0 || !$isFiltered): ?>
        <div class="table-responsive screen-only">
          <table class="table table-bordered table-striped align-middle">
            <thead class="table-primary">
              <tr>
                <th>No</th>
                <th>Nama Siswa</th>
                <th>Rekap</th>
                <th>Detail</th>
              </tr>
            </thead>
            <tbody>
              <?php $no=1; foreach($rekap as $row): ?>
                <?php
                  $stmtPresensi = $koneksi->prepare("
                    SELECT status
                    FROM tb_presensi
                    WHERE nis = ? AND id_ekstra = ?
                      AND MONTH(tanggal) = ? AND YEAR(tanggal) = ?
                  ");
                  $stmtPresensi->bind_param("siii", $row['nis'], $id_ekstra, $bulan, $tahun);
                  $stmtPresensi->execute();
                  $resultPresensi = $stmtPresensi->get_result();

                  // Inisialisasi rekap
                  $rekapDetail = ['H'=>0, 'I'=>0, 'S'=>0, 'A'=>0];

                  // Hitung presensi per status
                  while ($p = $resultPresensi->fetch_assoc()) {
                      $rekapDetail[$p['status']]++;
                  }

                ?>
              <tr>
                <td><?= $no++ ?></td>
                <td><?= htmlspecialchars($row['nama']) ?></td>
                <td>
                  <div class="d-flex flex-wrap gap-1">                    
                    <span class="badge bg-success">Hadir: <?= $rekapDetail['H'] ?></span>
                    <span class="badge bg-warning text-dark">Izin: <?= $rekapDetail['I'] ?></span>
                    <span class="badge bg-info text-dark">Sakit: <?= $rekapDetail['S'] ?></span>
                    <span class="badge bg-danger">Alpa: <?= $rekapDetail['A'] ?></span>
                  </div>
                </td>
                <td>
                  <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#detailModal<?= $row['nis'] ?>">
                    <i class="bi bi-eye"></i> Lihat
                  </button>
                </td>
              </tr>

              <!-- Modal Detail -->
              <div class="modal fade" id="detailModal<?= $row['nis'] ?>" tabindex="-1" aria-labelledby="detailModalLabel<?= $row['nis'] ?>" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                  <div class="modal-content">

                    <div class="modal-header bg-primary text-white">
                      <h5 class="modal-title" id="detailModalLabel<?= $row['nis'] ?>">
                        Detail Kehadiran - <?= htmlspecialchars($row['nama']) ?>
                      </h5>
                      <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    
                    <div class="modal-body">
                      <?php
                        $stmtPresensi = $koneksi->prepare("
                          SELECT tanggal, status
                          FROM tb_presensi
                          WHERE nis = ? AND id_ekstra = ?
                            AND MONTH(tanggal) = ? AND YEAR(tanggal) = ?
                          ORDER BY tanggal
                        ");
                        $stmtPresensi->bind_param("siii", $row['nis'], $id_ekstra, $bulan, $tahun);
                        $stmtPresensi->execute();
                        $presensi = $stmtPresensi->get_result();

                        // Inisialisasi rekap
                        $rekapDetail = ['H'=>0, 'I'=>0, 'S'=>0, 'A'=>0];
                      ?>

                      <?php if ($presensi->num_rows > 0): ?>
                        <!-- Header ala tabel -->
                        <div class="row fw-bold border bg-light text-center">
                          <div class="col-6 p-2 border-end">Tanggal</div>
                          <div class="col-6 p-2">Status</div>
                        </div>

                        <!-- Isi data -->
                        <?php while($p = $presensi->fetch_assoc()): 
                          $rekapDetail[$p['status']]++; 

                          $badge = match($p['status']) {
                            'H' => 'success',
                            'I' => 'warning text-dark',
                            'S' => 'info text-dark',
                            default => 'danger'
                          };
                          $text = match($p['status']) {
                            'H' => 'Hadir',
                            'I' => 'Izin',
                            'S' => 'Sakit',
                            default => 'Alpa'
                          };
                        ?>
                          <div class="row border text-center">
                            <div class="col-6 p-2 border-end">
                              <?= date('d-m-Y', strtotime($p['tanggal'])) ?>
                            </div>
                            <div class="col-6 p-2">
                              <span class="badge bg-<?= $badge ?>"><?= $text ?></span>
                            </div>
                          </div>
                        <?php endwhile ?>

                      <?php else: ?>
                        <div class="alert alert-info text-center mt-2">Tidak ada data presensi</div>
                      <?php endif ?>
                    </div>

                    <div class="modal-footer">
                      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    </div>
                  </div>
                </div>
              </div>

              <?php endforeach ?>
              
            </tbody>
          </table>
        </div>
        <?php else: ?>
          <div class="alert alert-info text-center">
            Ekstrakurikuler <strong><?= htmlspecialchars($nama_ekstra_terpilih ?? 'Tidak diketahui') ?></strong> 
            pada bulan <strong><?= $namaBulan[$bulan] ?? '' ?> <?= $tahun ?></strong> 
            belum ada presensi.
          </div>
        <?php endif; ?>

      <?php else: ?>
        <div class="alert alert-info text-center">
          Ekstrakurikuler <strong><?= htmlspecialchars($nama_ekstra_terpilih ?? 'Tidak diketahui') ?></strong> 
          pada bulan <strong><?= $namaBulan[$bulan] ?? '' ?> <?= $tahun ?></strong> 
          belum ada presensi.
        </div>
      <?php endif; ?>


        <!-- Versi Print (tidak tampil di layar) -->
        <div class="print-only" style="display:none;">
          <!-- Header Laporan -->
          <div style="display: flex; align-items: center; margin-bottom:20px;">
            <!-- Logo di kiri -->
            <div style="flex:0 0 100px; text-align:center;">
              <img src="assets/img/smk.png" alt="Logo Sekolah" style="height:90px;">
            </div>

            <!-- Judul di tengah -->
            <div style="flex:1; text-align:center;">
              <h4 style="margin:0;">REKAP PRESENSI EKSTRAKURIKULER</h4>
              <p style="margin:0; font-size:14px;">
                SMK Negeri 2 Magelang<br>
                Bulan <?= $NamaBulan ?> <?= $tahun ?>
              </p>
            </div>
          </div>

          <hr style="border:1px solid #000; margin:10px 0;">

          <table border="1" style="border-collapse:collapse; width:100%; font-size:12px; text-align:center;">
            <thead>
              <tr>
                <th rowspan="2">No</th>
                <th rowspan="2">Nama Siswa</th>
                <th rowspan="2">Ekstrakurikuler</th>
                <th colspan="<?= $jmlHari ?>">Tanggal</th>
                <th colspan="4">Jumlah</th>
              </tr>
              <tr>
                <?php foreach($tanggalEkstra as $tgl): ?>
                  <th><?= str_pad($tgl,2,'0',STR_PAD_LEFT) ?></th>
                <?php endforeach; ?>
                <th>H</th>
                <th>I</th>
                <th>S</th>
                <th>A</th>
              </tr>
            </thead>
            <tbody>
              <?php $no=1; foreach($rekap as $row): ?>
                <?php
                  // inisialisasi rekap jumlah
                  $rekapDetail = ['H'=>0, 'I'=>0, 'S'=>0, 'A'=>0];

                  // ambil presensi siswa per bulan & ekstra
                  $stmt = $koneksi->prepare("
                    SELECT DAY(tanggal) as tgl, status
                    FROM tb_presensi
                    WHERE nis=? AND id_ekstra=? 
                    AND MONTH(tanggal)=? AND YEAR(tanggal)=?
                  ");
                  $stmt->bind_param("siii", $row['nis'], $id_ekstra, $bulan, $tahun);
                  $stmt->execute();
                  $result = $stmt->get_result();
                  $presensiSiswa = [];
                  while($p = $result->fetch_assoc()){
                    $presensiSiswa[$p['tgl']] = $p['status'];
                    $rekapDetail[$p['status']]++;
                  }
                ?>
                <tr>
                  <td><?= $no++ ?></td>
                  <td style="text-align:left;"><?= htmlspecialchars($row['nama']) ?></td>
                  <td><?= htmlspecialchars($row['nama_ekstra']) ?></td>
                  
                  <?php foreach($tanggalEkstra as $tgl): ?>
                    <?php
                      $st = $presensiSiswa[$tgl] ?? '-'; // default jika tidak ada data
                      $color = match($st) {
                        'H' => 'green',
                        'I' => 'orange',
                        'S' => 'blue',
                        'A' => 'red',
                        default => 'black'
                      };
                    ?>
                    <td style="color:<?= $color ?>;"><?= $st ?></td>
                  <?php endforeach; ?>

                  <td><?= $rekapDetail['H'] ?></td>
                  <td><?= $rekapDetail['I'] ?></td>
                  <td><?= $rekapDetail['S'] ?></td>
                  <td><?= $rekapDetail['A'] ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>

          <br><br>
          <div style="text-align:right; margin-top:40px;">
            <p>Magelang, <?= date('d-m-Y') ?></p>
            <p>Administrator</p>
            <br><br><br>
            <p style="text-decoration:underline; font-weight:bold; margin:0;">(___________________)</p> 
        </div>

        <!-- CSS -->
        <style>
          @media print {
            .screen-only, .d-print-none { display:none !important; }
            .print-only { display:block !important; }
          }
        </style>

    </div>
  </div>
</section>

<!-- Style untuk print -->
<style>
  @media print {
    .btn, form, nav, footer {
      display: none !important;
    }
    table {
      width: 100%;
      border-collapse: collapse;
    }
    th, td {
      border: 1px solid #000;
      padding: 8px;
    }
  }
</style>
<!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script> -->