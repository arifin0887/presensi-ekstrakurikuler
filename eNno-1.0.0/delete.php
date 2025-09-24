<?php
require_once 'koneksi.php';
header('Content-Type: application/json');

try {
    if (!isset($_GET['id_pembina']) || !is_numeric($_GET['id_pembina'])) {
        echo json_encode(['success' => false, 'message' => 'ID tidak valid']);
        exit;
    }

    $id = intval($_GET['id_pembina']);

    // Mulai transaksi
    $pdo->beginTransaction();

    // Kosongkan pembina di tabel ekstrakurikuler
    $stmt = $pdo->prepare("UPDATE tb_ekstrakurikuler SET id_pembina = NULL WHERE id_pembina = ?");
    $stmt->execute([$id]);

    // Hapus dari tabel pembina
    $stmt = $pdo->prepare("DELETE FROM tb_pembina WHERE id_pembina = ?");
    $stmt->execute([$id]);

    $pdo->commit();

    echo json_encode(['success' => true, 'message' => 'Pembina berhasil dihapus']);
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['success' => false, 'message' => 'Gagal menghapus data: ' . $e->getMessage()]);
}
?>