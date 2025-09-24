<!-- ======= Sidebar ======= -->
<aside id="sidebar" class="sidebar">
  <ul class="sidebar-nav" id="sidebar-nav">

    <li class="nav-item">
      <a class="nav-link <?= ($_GET['page'] ?? '') == 'pembina' ? '' : 'collapsed'; ?>" href="indek.php?page=pembina">
        <i class="bi bi-grid"></i>
        <span>Dashboard</span>
      </a>
    </li>
    <!-- End Dashboard Nav -->

    <li class="nav-heading">EkstraKu</li>

    <!-- Manajemen Peserta -->
    <li class="nav-item">
      <a class="nav-link <?= ($_GET['page'] ?? '') == 'peserta_pembina' ? '' : 'collapsed'; ?>" href="indek.php?page=peserta_pembina">
        <i class="bi bi-person"></i>
        <span>Manajemen Peserta</span>
      </a>
    </li>
    <!-- End Nav -->

    <!-- Manajemen Presensi -->
    <li class="nav-item">
      <a class="nav-link <?= ($_GET['page'] ?? '') == 'presensi_pembina' ? '' : 'collapsed'; ?>" href="indek.php?page=presensi_pembina">
        <i class="bi bi-calendar-check"></i>
        <span>Manajemen Presensi</span>
      </a>
    </li>
    <!-- End Nav -->

    <!-- Rekap Presensi -->
    <li class="nav-item">
      <a class="nav-link <?= ($_GET['page'] ?? '') == 'rekap_pembina' ? '' : 'collapsed'; ?>" href="indek.php?page=rekap_pembina">
        <i class="bi bi-bar-chart"></i>
        <span>Rekap Presensi</span>
      </a>
    </li>
    <!-- End Nav -->

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