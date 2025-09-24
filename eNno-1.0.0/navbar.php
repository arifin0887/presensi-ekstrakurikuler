<!-- ======= Sidebar ======= -->
<aside id="sidebar" class="sidebar">
  <ul class="sidebar-nav" id="sidebar-nav">

    <li class="nav-item">
      <a class="nav-link <?= ($_GET['page'] ?? '') == 'admin' ? '' : 'collapsed'; ?>" href="index.php?page=admin">
        <i class="bi bi-grid"></i>
        <span>Dashboard</span>
      </a>
    </li>
    <!-- End Dashboard Nav -->

    <li class="nav-heading">EkstraKu</li>

    <!-- Manajemen Pembina -->
    <li class="nav-item">
      <a class="nav-link <?= ($_GET['page'] ?? '') == 'pembina_admin' ? '' : 'collapsed'; ?>" href="index.php?page=pembina_admin">
        <i class="bi bi-person-badge"></i>
        <span>Manajemen Pembina</span>
      </a>
    </li>

    <!-- Manajemen Peserta -->
    <li class="nav-item">
      <a class="nav-link <?= ($_GET['page'] ?? '') == 'peserta_admin' ? '' : 'collapsed'; ?>" href="index.php?page=peserta_admin">
        <i class="bi bi-person"></i>
        <span>Manajemen Peserta</span>
      </a>
    </li>

    <!-- Manajemen Ekstra -->
    <li class="nav-item">
      <a class="nav-link <?= ($_GET['page'] ?? '') == 'ekstra_admin' ? '' : 'collapsed'; ?>" href="index.php?page=ekstra_admin">
        <i class="bi bi-grid"></i>
        <span>Manajemen Ekstra</span>
      </a>
    </li>

    <!-- Riwayat Presensi -->
    <li class="nav-item">
      <a class="nav-link <?= ($_GET['page'] ?? '') == 'riwayat_admin' ? '' : 'collapsed'; ?>" href="index.php?page=riwayat_admin">
        <i class="bi bi-clock-history"></i>
        <span>Riwayat Presensi</span>
      </a>
    </li>

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