<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>DEBUG ADMIN PAGE</h1>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

// Validasi role
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'admin') {
    die("Akses ditolak. Role Anda: " . ($_SESSION['role'] ?? 'tidak terdeteksi'));
}

echo "<h2>Selamat datang, Admin!</h2>";
?>