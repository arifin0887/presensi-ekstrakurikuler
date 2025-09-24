<?php
header('Content-Type: application/json');
require_once 'koneksi.php'; // harus menghasilkan $pdo = new PDO(...)

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['success' => false, 'message' => 'Metode tidak diizinkan']);
  exit;
}

$id = isset($_POST['id_ekstra']) ? (int)$_POST['id_ekstra'] : 0;
if ($id <= 0) {
  http_response_code(400);
  echo json_encode(['success' => false, 'message' => 'ID tidak valid']);
  exit;
}

try {
  $pdo->beginTransaction();

  $cek = $pdo->prepare("SELECT id_ekstra FROM tb_ekstrakurikuler WHERE id_ekstra = ?");
  $cek->execute([$id]);
  if ($cek->rowCount() === 0) {
    throw new Exception('Data ekstrakurikuler tidak ditemukan');
  }

  $pdo->prepare("DELETE FROM tb_presensi WHERE id_ekstra = ?")->execute([$id]);
  $pdo->prepare("DELETE FROM tb_peserta_ekstra WHERE id_ekstra = ?")->execute([$id]);

  // hapus jadwal jika ada
  $hasJadwal = $pdo->query("SHOW TABLES LIKE 'tb_jadwal_ekstra'")->rowCount() > 0;
  if ($hasJadwal) {
    $pdo->prepare("DELETE FROM tb_jadwal_ekstra WHERE id_ekstra = ?")->execute([$id]);
  }

  // null-kan pembina jika kolom ada (opsional)
  $hasPembinaCol = $pdo->query("SHOW COLUMNS FROM tb_pembina LIKE 'id_ekstra'")->rowCount() > 0;
  if ($hasPembinaCol) {
    $pdo->prepare("UPDATE tb_pembina SET id_ekstra = NULL WHERE id_ekstra = ?")->execute([$id]);
  }

  $stmt = $pdo->prepare("DELETE FROM tb_ekstrakurikuler WHERE id_ekstra = ?");
  $stmt->execute([$id]);
  if ($stmt->rowCount() <= 0) {
    throw new Exception('Tidak ada data yang dihapus.');
  }

  $pdo->commit();
  echo json_encode(['success' => true, 'message' => 'Ekstrakurikuler berhasil dihapus']);
} catch (Throwable $e) {
  if ($pdo->inTransaction()) $pdo->rollBack();
  http_response_code(500);
  echo json_encode(['success' => false, 'message' => 'Gagal menghapus: ' . $e->getMessage()]);
}
