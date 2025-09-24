<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$koneksi = new mysqli("localhost", "root", "", "presensi_ekstra");
if ($koneksi->connect_error) {
    die("Koneksi gagal: " . $koneksi->connect_error);
}
?>

<!-- ======= Sidebar ======= -->
<aside id="sidebar" class="sidebar">
  <ul class="sidebar-nav" id="sidebar-nav">

    <li class="nav-heading">EkstraKu</li>

    <!-- Dashboard -->
    <li class="nav-item">
      <a class="nav-link <?= ($_GET['page'] ?? '') == 'siswa' ? '' : 'collapsed'; ?>" 
         href="indexx.php?page=siswa">
        <i class="bi bi-grid"></i>
        <span>Dashboard</span>
      </a>
    </li>
    <!-- End Dashboard Nav -->

    <?php
    // ambil id_ekstra yang diikuti siswa
    $id_ekstra_saya = '';
    $stmt = $koneksi->prepare("SELECT id_ekstra FROM tb_peserta_ekstra WHERE nis = ? LIMIT 1");
    $stmt->bind_param("s", $_SESSION['user_id']);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $id_ekstra_saya = $row['id_ekstra'];
    }
    ?>

    <?php if (!empty($id_ekstra_saya)): ?>
      <li class="nav-item">
        <a class="nav-link <?= ($_GET['page'] ?? '') == 'presensi_siswa' ? '' : 'collapsed'; ?>" 
           href="indexx.php?page=presensi_siswa&id_ekstra=<?= urlencode($id_ekstra_saya) ?>">
          <i class="bi bi-calendar-check me-1"></i>
          Presensi Saya
        </a>
      </li>
    <?php endif; ?>

  </ul>
</aside>

<!-- End Sidebar -->

<style>
  .sidebar .nav-link.active,
  .sidebar .nav-link:not(.collapsed) {
    background: #4154f1;   /* biru bootstrap */
    color: #fff;
    border-radius: 8px;
  }
  .sidebar .nav-link.active i {
    color: #fff;
  }

</style>
<script>
  const currentPath = window.location.search; // Mengambil query string (misalnya ?page=transaksi)
  const menuItems = document.querySelectorAll('#sidebar a');

  menuItems.forEach(item => {
    const itemPath = item.getAttribute('href');
    if (itemPath.includes(currentPath)) {
      item.classList.add('active');

      const parentCollapse = item.closest('.collapse');
      if (parentCollapse) {
        parentCollapse.classList.add('show');
        const parentLink = parentCollapse.previousElementSibling;
        if (parentLink) {
          parentLink.classList.remove('collapsed');
        }
      }
    }
  });
</script>