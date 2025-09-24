<?php
include 'koneksi.php';

$nis = $_GET['nis'] ?? '';
$id_ekstra = $_GET['id_ekstra'] ?? '';
$bulan = $_GET['bulan'] ?? '';
$tahun = $_GET['tahun'] ?? '';

$stmt = $pdo->prepare("
  SELECT tanggal, status 
  FROM tb_presensi 
  WHERE nis = ? AND id_ekstra = ? 
    AND MONTH(tanggal) = ? AND YEAR(tanggal) = ?
  ORDER BY tanggal
");
$stmt->execute([$nis, $id_ekstra, $bulan, $tahun]);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($data) {
  foreach ($data as $row) {
    $badge = match ($row['status']) {
      'H' => 'success',
      'I' => 'warning text-dark',
      'S' => 'info text-dark',
      default => 'danger'
    };
    $text = match ($row['status']) {
      'H' => 'Hadir',
      'I' => 'Izin',
      'S' => 'Sakit',
      default => 'Alpa'
    };
    echo "<tr>
            <td>".date('d-m-Y', strtotime($row['tanggal']))."</td>
            <td><span class='badge bg-{$badge}'>{$text}</span></td>
          </tr>";
  }
} else {
  echo "<tr><td colspan='2' class='text-center'>Tidak ada data presensi</td></tr>";
}
