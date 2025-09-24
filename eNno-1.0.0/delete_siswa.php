<?php
include 'koneksi.php';

$nis = $_GET['nis'] ?? '';

header('Content-Type: application/json');

if ($nis) {
    try {
        $stmt = $pdo->prepare("UPDATE tb_siswa SET status='tidak' WHERE nis=?");
        $stmt->execute([$nis]);
        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'NIS tidak ditemukan atau status sudah tidak.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'NIS siswa tidak valid.']);
}
?>