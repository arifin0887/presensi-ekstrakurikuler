<?php
// Hapus semua session
session_start();
session_unset();
session_destroy();

// Mulai session baru
session_start();
session_regenerate_id(true);

// Set session default
$_SESSION['init'] = true;

echo "Session telah direset. <a href='login.php'>Coba login lagi</a>";
?>