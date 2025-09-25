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
$bulan = isset($_GET['bulan']) ? str_pad((int)$_GET['bulan'], 2, "0", STR_PAD_LEFT) : date('m');
$tahun = isset($_GET['tahun']) ? (int)$_GET['tahun'] : date('Y');

$namaBulan = [
    "01" => "Januari", "02" => "Februari", "03" => "Maret", "04" => "April",
    "05" => "Mei", "06" => "Juni", "07" => "Juli", "08" => "Agustus",
    "09" => "September", "10" => "Oktober", "11" => "November", "12" => "Desember"
];
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

// Ambil semua presensi peserta di bulan ini untuk detail modal
$sql_presensi_detail = "
    SELECT pr.nis, pr.tanggal, pr.status
    FROM tb_presensi pr
    WHERE pr.id_ekstra = :id_ekstra
      AND MONTH(pr.tanggal) = :bulan
      AND YEAR(pr.tanggal) = :tahun
    ORDER BY pr.tanggal ASC
";
$stmt_presensi_detail = $pdo->prepare($sql_presensi_detail);
$stmt_presensi_detail->execute([
    'id_ekstra' => $ekstra['id_ekstra'],
    'bulan'     => $bulan,
    'tahun'     => $tahun
]);

$presensi = [];
while ($row = $stmt_presensi_detail->fetch(PDO::FETCH_ASSOC)) {
    $presensi[$row['nis']][] = $row;
}

// Ambil rekap per siswa (Hadir, Sakit, Izin, Alfa)
$sql_rekap_siswa = "
  SELECT p.nis, s.nama, k.jenjang, k.jurusan, k.nama_kelas,
    SUM(CASE WHEN pr.status = 'H' THEN 1 ELSE 0 END) AS H,
    SUM(CASE WHEN pr.status = 'S' THEN 1 ELSE 0 END) AS S,
    SUM(CASE WHEN pr.status = 'I' THEN 1 ELSE 0 END) AS I,
    SUM(CASE WHEN pr.status = 'A' THEN 1 ELSE 0 END) AS A
  FROM tb_peserta_ekstra p
  JOIN tb_siswa s ON p.nis = s.nis
  JOIN tb_kelas k ON s.id_kelas = k.id_kelas
  LEFT JOIN tb_presensi pr ON p.nis = pr.nis AND pr.id_ekstra = :id_ekstra AND MONTH(pr.tanggal) = :bulan AND YEAR(pr.tanggal) = :tahun
  WHERE p.id_ekstra = :id_ekstra
  GROUP BY p.nis, s.nama, k.jenjang, k.jurusan, k.nama_kelas
  ORDER BY s.nama ASC
";

$stmt_rekap_siswa = $pdo->prepare($sql_rekap_siswa);
$stmt_rekap_siswa->execute([
    'id_ekstra' => $ekstra['id_ekstra'],
    'bulan'     => $bulan,
    'tahun'     => $tahun
]);

$rekapPerSiswa = $stmt_rekap_siswa->fetchAll(PDO::FETCH_ASSOC);

//Tentukan jumlah pertemuan bulan ini (berdasarkan distinct tanggal presensi)
$stmtPertemuan = $pdo->prepare("SELECT COUNT(DISTINCT tanggal) FROM tb_presensi
    WHERE id_ekstra=:id AND MONTH(tanggal)=:bulan AND YEAR(tanggal)=:tahun");
$stmtPertemuan->execute(['id'=>$ekstra['id_ekstra'],'bulan'=>$bulan,'tahun'=>$tahun]);
$totalPertemuan = $stmtPertemuan->fetchColumn();

// Ambil semua peserta ekstra untuk daftar di bawah
$sql_peserta_list = "SELECT p.nis, s.nama, k.jenjang, k.nama_kelas
        FROM tb_peserta_ekstra p
        JOIN tb_siswa s ON p.nis = s.nis
        JOIN tb_kelas k ON s.id_kelas = k.id_kelas
        WHERE p.id_ekstra = :id_ekstra
        ORDER BY s.nama";
$stmt_peserta_list = $pdo->prepare($sql_peserta_list);
$stmt_peserta_list->execute(['id_ekstra' => $ekstra['id_ekstra']]);
$allPeserta = $stmt_peserta_list->fetchAll(PDO::FETCH_ASSOC);

?>

<!-- ======= Main Content ======= -->
<section class="section">
  <div class="card shadow-sm border-0">
    <div class="card-body">
      <h5 class="card-title">
        Rekap Presensi <strong><?= htmlspecialchars($ekstra['nama_ekstra']) ?></strong>
      </h5>
      <p class="text-muted mb-4">Periode: <?= $namaBulan[$bulan]." ".$tahun ?></p>

      <!-- Filter Bulan & Tahun -->
      <form method="GET" class="row g-2 mb-4">
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
          <button class="btn btn-primary">
            <i class="bi bi-search me-1"></i> Lihat
          </button>
        </div>
      </form>

      <!-- Ringkasan -->
      <div class="row g-3 mb-4">
        <div class="col-md-3">
          <div class="card stat-card h-100 border-0 shadow-sm">
            <div class="card-body text-center">
              <i class="bi bi-check2-circle text-success fs-2 mb-2"></i>
              <h6 class="text-muted mb-1">Hadir</h6>
              <h3 class="fw-bold text-success"><?= $rekap['H'] ?></h3>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card stat-card h-100 border-0 shadow-sm">
            <div class="card-body text-center">
              <i class="bi bi-emoji-dizzy text-warning fs-2 mb-2"></i>
              <h6 class="text-muted mb-1">Sakit</h6>
              <h3 class="fw-bold text-warning"><?= $rekap['S'] ?></h3>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card stat-card h-100 border-0 shadow-sm">
            <div class="card-body text-center">
              <i class="bi bi-person-lines-fill text-info fs-2 mb-2"></i>
              <h6 class="text-muted mb-1">Izin</h6>
              <h3 class="fw-bold text-info"><?= $rekap['I'] ?></h3>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card stat-card h-100 border-0 shadow-sm">
            <div class="card-body text-center">
              <i class="bi bi-x-circle-fill text-danger fs-2 mb-2"></i>
              <h6 class="text-muted mb-1">Alfa</h6>
              <h3 class="fw-bold text-danger"><?= $rekap['A'] ?></h3>
            </div>
          </div>
        </div>
      </div>

      <!-- Tabel rekap siswa -->
      <h6 class="fw-bold mb-3">Rekap Presensi Per Siswa</h6>
      <div class="table-responsive">
        <table class="table table-hover align-middle">
          <thead class="table-light">
            <tr>
              <th>No</th>
              <th>NIS</th>
              <th>Nama</th>
              <th>Hadir</th>
              <th>Sakit</th>
              <th>Izin</th>
              <th>Alfa</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!empty($rekapPerSiswa)): ?>
              <?php $no=1; foreach($rekapPerSiswa as $row): ?>
              <tr>
                <td><?= $no++ ?></td>
                <td><?= htmlspecialchars($row['nis']) ?></td>
                <td><?= htmlspecialchars($row['nama']) ?></td>
                <td class="text-success fw-bold"><?= $row['H'] ?></td>
                <td class="text-warning fw-bold"><?= $row['S'] ?></td>
                <td class="text-info fw-bold"><?= $row['I'] ?></td>
                <td class="text-danger fw-bold"><?= $row['A'] ?></td>
                <td>
                  <button class="btn btn-sm btn-outline-info"
                          data-bs-toggle="modal"
                          data-bs-target="#detailModal<?= $row['nis'] ?>">
                    <i class="bi bi-eye me-1"></i> Detail
                  </button>
                </td>
              </tr>

              <!-- Modal Detail -->
              <div class="modal fade" id="detailModal<?= $row['nis'] ?>" tabindex="-1">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                  <div class="modal-content border-0 shadow-lg rounded-3">
                    <div class="modal-header bg-primary text-white">
                      <h5 class="modal-title">
                        <i class="bi bi-person-circle me-2"></i> Detail Presensi - <?= htmlspecialchars($row['nama']) ?>
                      </h5>
                      <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                      <?php if (!empty($presensi[$row['nis']])): ?>
                        <!-- Header kolom -->
                        <div class="d-flex justify-content-between fw-bold px-2 mb-2">
                          <span><i class="bi bi-calendar-event me-1"></i> Tanggal</span>
                          <span><i class="bi bi-clipboard-check me-1"></i> Status</span>
                        </div>

                        <ul class="list-group list-group-flush">
                          <?php foreach($presensi[$row['nis']] as $d): ?>
                            <?php
                              $badgeClass = [
                                'H'=>'success','S'=>'warning','I'=>'info','A'=>'danger'
                              ][$d['status']] ?? 'secondary';
                            ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                              <span>
                                <?= htmlspecialchars($d['tanggal']) ?>
                              </span>
                              <span class="badge bg-<?= $badgeClass ?> px-3 py-2">
                                <?= htmlspecialchars($d['status']) ?>
                              </span>
                            </li>
                          <?php endforeach; ?>
                        </ul>
                      <?php else: ?>
                        <div class="alert alert-info text-center mb-0">
                          <i class="bi bi-info-circle me-1"></i> Belum ada presensi untuk siswa ini pada bulan terpilih.
                        </div>
                      <?php endif; ?>
                    </div>

                    <div class="modal-footer">
                      <button class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i> Tutup
                      </button>
                    </div>
                  </div>
                </div>
              </div>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="8" class="text-center text-muted">Belum ada data presensi untuk ekstrakurikuler ini pada bulan dan tahun terpilih.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

      <!-- <h5 class="mt-5 fw-bold">Daftar Semua Peserta Ekstrakurikuler</h5>
      <p class="text-muted">Total Pertemuan Bulan Ini: <?= $totalPertemuan ?> kali</p>
      <div class="table-responsive">
        <table class="table table-hover align-middle">
          <thead class="table-secondary">
            <tr>
              <th>No</th>
              <th>NIS</th>
              <th>Nama</th>
              <th>Kelas</th>
              <th>Hadir</th>
              <th>Sakit</th>
              <th>Izin</th>
              <th>Alfa</th>
              <th>Belum Absen</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!empty($allPeserta)): ?>
              <?php $no_all=1; foreach ($allPeserta as $row_all):
                $rekapPes = ['H'=>0,'I'=>0,'S'=>0,'A'=>0];
                if (!empty($presensi[$row_all['nis']])) {
                    foreach ($presensi[$row_all['nis']] as $p) {
                        if (isset($rekapPes[$p['status']])) {
                            $rekapPes[$p['status']]++;
                        }
                    }
                }
                $totalKeterangan = array_sum($rekapPes);
                $belum = max(0, $totalPertemuan - $totalKeterangan);
              ?>
              <tr>
                <td><?= $no_all++ ?></td>
                <td><?= htmlspecialchars($row_all['nis']) ?></td>
                <td><?= htmlspecialchars($row_all['nama']) ?></td>
                <td><?= htmlspecialchars($row_all['jenjang'].' '.$row_all['nama_kelas']) ?></td>
                <td class="text-success fw-bold"><?= $rekapPes['H'] ?></td>
                <td class="text-warning fw-bold"><?= $rekapPes['S'] ?></td>
                <td class="text-info fw-bold"><?= $rekapPes['I'] ?></td>
                <td class="text-danger fw-bold"><?= $rekapPes['A'] ?></td>
                <td class="text-muted"><?= $belum ?></td>
              </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="9" class="text-center text-muted">Belum ada siswa yang terdaftar di ekstrakurikuler ini.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div> -->

    </div>
  </div>
</section>