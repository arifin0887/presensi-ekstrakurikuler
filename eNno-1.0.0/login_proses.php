<?php
session_start();
require_once 'koneksi.php';

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

// Validasi awal
if (empty($username) || empty($password)) {
    echo "<script>alert('Silakan isi username dan password.'); window.location='login.php';</script>";
    exit;
}

// =============================
// 1. Cek di tabel Admin (tb_user)
// =============================
$query = "SELECT *, 'admin' as role FROM tb_user WHERE username = ?";
$stmt  = $pdo->prepare($query);
$stmt->execute([$username]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// =============================
// 2. Kalau tidak ketemu, cek di tabel Pembina
// =============================
if (!$user) {
    $query = "SELECT *, 'pembina' as role FROM tb_pembina WHERE username = ?";
    $stmt  = $pdo->prepare($query);
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
}

// =============================
// 3. Kalau masih tidak ketemu, cek di tabel Siswa (pakai NIS)
// =============================
if (!$user) {
    $query = "SELECT *, 'siswa' as role FROM tb_siswa WHERE nis = ?";
    $stmt  = $pdo->prepare($query);
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
}

// =============================
// Cek password
// =============================
if ($user && password_verify($password, $user['password'])) {
    $_SESSION['loggedin'] = true;
    $_SESSION['role']     = $user['role'];

    // Simpan session sesuai role
    if ($user['role'] === 'admin') {
        $_SESSION['user_id']  = $user['id_user'];
        $_SESSION['username'] = $user['username'];
        header("Location: index.php?page=admin");
    } elseif ($user['role'] === 'pembina') {
        $_SESSION['user_id']  = $user['id_pembina'];
        $_SESSION['username'] = $user['username'];
        header("Location: indek.php?page=pembina");
    } elseif ($user['role'] === 'siswa') {
    $_SESSION['user_id']  = $user['nis'];   
    $_SESSION['username'] = $user['nis'];   
    $_SESSION['username'] = $user['nama'];  
    header("Location: indexx.php?page=siswa");
}
    exit;

} else {
    echo "<script>alert('Login gagal! Username/NIS atau Password salah.'); window.location='login.php';</script>";
    exit;
}
?>
