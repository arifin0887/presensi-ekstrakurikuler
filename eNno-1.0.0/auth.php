<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Cek user_id bukan loggedin
if (!isset($_SESSION['user_id'])) {
    // Jika headers sudah terkirim, gunakan JavaScript redirect
    if (headers_sent()) {
        echo '<script>window.location.href="login.php";</script>';
    } else {
        header('Location: login.php');
    }
    exit;
}