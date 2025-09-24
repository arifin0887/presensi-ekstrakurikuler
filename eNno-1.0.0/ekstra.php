<?php
error_reporting(0);

// koneksi ke database
$koneksi = new mysqli("localhost", "root", "", "presensi_ekstra");

// cek koneksi
if ($koneksi->connect_error) {
    die("Koneksi gagal: " . $koneksi->connect_error);
}

// Tambah Ekstrakurikuler
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tambah_ekstra'])) {
    $nama_ekstra = $_POST['nama_ekstra'];
    $id_pembina  = $_POST['id_pembina'];
    $hari        = $_POST['hari'];
    $jam_mulai   = $_POST['jam_mulai'];
    $jam_selesai = $_POST['jam_selesai'];

    try {
        $query = $koneksi->prepare("INSERT INTO tb_ekstrakurikuler 
            (nama_ekstra, id_pembina, hari, jam_mulai, jam_selesai) 
            VALUES (?, ?, ?, ?, ?)");

        if ($query->execute([$nama_ekstra, $id_pembina, $hari, $jam_mulai, $jam_selesai])) {
            echo "
            <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
            <script>
            Swal.fire({
              title: 'Berhasil!',
              text: 'Ekstrakurikuler berhasil ditambahkan',
              icon: 'success'
            }).then(function() {
                window.location = 'index.php?page=ekstra_admin'; 
            });
            </script>
            ";
        } else {
            echo "
            <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
            <script>
            Swal.fire({
              icon: 'error',
              title: 'Gagal!',
              text: 'Data gagal ditambahkan, coba lagi.',
              confirmButtonText: 'OK'
            });
            </script>
            ";
        }
    } catch (PDOException $e) {
        echo "
        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        <script>
        Swal.fire({
          icon: 'error',
          title: 'Kesalahan!',
          text: 'Error: " . addslashes($e->getMessage()) . "'
        });
        </script>
        ";
    }   
}

// Update Ekstrakurikuler
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_ekstra'])) {
    $id_ekstra   = $_POST['id_ekstra'];
    $nama_ekstra = $_POST['nama_ekstra'];
    $id_pembina  = $_POST['id_pembina'];
    $hari        = $_POST['hari'];
    $jam_mulai   = $_POST['jam_mulai'];
    $jam_selesai = $_POST['jam_selesai'];

    // Update query termasuk id_pembina
    $query = $koneksi->prepare("UPDATE tb_ekstrakurikuler 
                                SET nama_ekstra=?, id_pembina=?, hari=?, jam_mulai=?, jam_selesai=? 
                                WHERE id_ekstra=?");
    $query->bind_param("sisssi", $nama_ekstra, $id_pembina, $hari, $jam_mulai, $jam_selesai, $id_ekstra);

    if ($query->execute()) {
        echo "
        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        <script>
        Swal.fire({
          title: 'Berhasil!',
          text: 'Ekstrakurikuler berhasil diperbarui',
          icon: 'success'
        }).then(function() {
            window.location = 'index.php?page=ekstra_admin'; 
        });
        </script>
        ";
    } else {
        echo "
        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        <script>
        Swal.fire({
          icon: 'error',
          title: 'Gagal!',
          text: 'Data gagal diperbarui, coba lagi.',
          confirmButtonText: 'OK'
        });
        </script>
        ";
    }
}


// ambil pembina yang belum punya ekstra
$sql = "
    SELECT p.id_pembina, p.nama 
    FROM tb_pembina p
    LEFT JOIN tb_ekstrakurikuler e ON p.id_pembina = e.id_pembina
    WHERE e.id_pembina IS NULL
";
$pembinaList = $koneksi->query($sql)->fetch_all(MYSQLI_ASSOC);;

// Ambil pembina yang sudah dipakai di ekstra ini
$currentPembinaId = $row['id_pembina'];

// Fungsi Hapus Produk
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    
    $query = $koneksi->prepare("DELETE FROM produk WHERE id=?");
    $query->bind_param("s", $id);
    $query->execute();

    header("Location: index.php?page=pembina&success=hapus");
    exit();
}

?>

<!-- ======= Main Content ======= -->
<section class="section">
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Daftar Ekstrakurikuler</h5>
                    <p>Kelola ekstrakurikuler sekolah anda</p>

                    <!-- Success Alert -->
                    <?php if(isset($_GET['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php 
                        switch($_GET['success']) {
                            case 'tambah': echo "Produk berhasil ditambahkan!"; break;
                            case 'edit': echo "Produk berhasil diperbarui!"; break;
                            case 'hapus': echo "Produk berhasil dihapus!"; break;
                        }
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php endif; ?>

                    <!-- Button to Add New Product -->
                    <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#tambahEkstraModal">
                        <i class="bi bi-plus-circle"></i> Tambah Ekstra
                    </button>

                    <!-- Table with stripped rows -->
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th scope="col">No</th>
                                    <th scope="col">Ekstrakurikuler</th>
                                    <th scope="col">Pembina</th>
                                    <th scope="col">Hari</th>
                                    <th scope="col">Mulai</th>
                                    <th scope="col">Selesai</th>
                                    <th scope="col">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php
                            $no = 1;
                            $query = $koneksi->query("
                            SELECT e.*, p.nama AS nama_pembina 
                            FROM tb_ekstrakurikuler e 
                            LEFT JOIN tb_pembina p ON e.id_pembina = p.id_pembina 
                            ORDER BY e.id_ekstra ASC
                            ");

                            while ($row = $query->fetch_assoc()) {
                            ?>
                                <tr>
                                <th scope="row"><?= $no++; ?></th>
                                <td><?= htmlspecialchars($row['nama_ekstra']); ?></td>
                                <td><?= htmlspecialchars($row['nama_pembina']); ?></td>
                                <td><?= htmlspecialchars($row['hari']); ?></td>
                                <td><?= htmlspecialchars($row['jam_mulai']); ?></td>
                                <td><?= htmlspecialchars($row['jam_selesai']); ?></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editEkstraModal<?= $row['id_ekstra']; ?>">
                                    <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#detailEkstraModal<?= $row['id_ekstra']; ?>">
                                    <i class="bi bi-eye"></i>
                                    </button>
                                </td>
                                </tr>

                                <!-- Modal Edit Ekstrakurikuler -->
                                <div class="modal fade" id="editEkstraModal<?= $row['id_ekstra']; ?>" tabindex="-1" aria-labelledby="editEkstraModalLabel<?= $row['id_ekstra']; ?>" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                        <div class="modal-header bg-primary text-white">
                                            <h5 class="modal-title" id="editEkstraModalLabel<?= $row['id_ekstra']; ?>"><i class="bi bi-pencil-square me-2"></i>Edit Ekstrakurikuler</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <form method="POST">
                                            <div class="modal-body">
                                            <input type="hidden" name="id_ekstra" value="<?= $row['id_ekstra']; ?>">

                                            <div class="mb-3">
                                                <label for="nama_ekstra<?= $row['id_ekstra']; ?>" class="form-label">Nama Ekstrakurikuler</label>
                                                <input type="text" class="form-control" name="nama_ekstra" id="nama_ekstra<?= $row['id_ekstra']; ?>" value="<?= htmlspecialchars($row['nama_ekstra']); ?>" required>
                                            </div>

                                            <div class="mb-3">
                                                <label for="hari<?= $row['id_ekstra']; ?>" class="form-label">Hari</label>
                                                <input type="text" class="form-control" name="hari" id="hari<?= $row['id_ekstra']; ?>" value="<?= htmlspecialchars($row['hari']); ?>" required>
                                            </div>

                                            <div class="mb-3">
                                                <label for="jam_mulai<?= $row['id_ekstra']; ?>" class="form-label">Jam Mulai</label>
                                                <input type="time" class="form-control" name="jam_mulai" id="jam_mulai<?= $row['id_ekstra']; ?>" value="<?= $row['jam_mulai']; ?>" required>
                                            </div>

                                            <div class="mb-3">
                                                <label for="jam_selesai<?= $row['id_ekstra']; ?>" class="form-label">Jam Selesai</label>
                                                <input type="time" class="form-control" name="jam_selesai" id="jam_selesai<?= $row['id_ekstra']; ?>" value="<?= $row['jam_selesai']; ?>" required>
                                            </div>
                                            </div>
                                            <div class="modal-footer">
                                            <button type="submit" name="edit_ekstra" class="btn btn-success">Update</button>
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                            </div>
                                        </form>
                                        </div>
                                    </div>
                                </div>
                                <!-- End Modal Edit -->

                                <!-- Modal Detail Ekstrakurikuler -->
                                <div class="modal fade" id="detailEkstraModal<?= $row['id_ekstra']; ?>" tabindex="-1" aria-labelledby="detailEkstraModalLabel<?= $row['id_ekstra']; ?>" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                        <div class="modal-header bg-info text-white">
                                            <h5 class="modal-title" id="detailEkstraModalLabel<?= $row['id_ekstra']; ?>">Detail Ekstrakurikuler</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <ul class="list-group">
                                                <li class="list-group-item"><strong>Nama Ekstrakurikuler:</strong> <?= htmlspecialchars($row['nama_ekstra']); ?></li>
                                                <li class="list-group-item"><strong>Pembina:</strong> <?= htmlspecialchars($row['nama_pembina']); ?></li>
                                                <li class="list-group-item"><strong>Hari:</strong> <?= htmlspecialchars($row['hari']); ?></li>
                                                <li class="list-group-item"><strong>Jam:</strong> <?= htmlspecialchars($row['jam_mulai']) . " - " . htmlspecialchars($row['jam_selesai']); ?></li>
                                            </ul>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDelete(<?= (int)$row['id_ekstra'] ?>)">
                                                Hapus
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
                                        </div>
                                    </div>
                                </div>

                                </div>
                                <!-- End Modal Detail -->
                            <?php } ?>
                            </tbody>

                        </table>
                    </div>
                    <!-- End Table with stripped rows -->
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Modal Tambah Ekstrakurikuler -->
<div class="modal fade" id="tambahEkstraModal" tabindex="-1" aria-labelledby="tambahEkstraModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="tambahEkstraModalLabel">Tambah Ekstrakurikuler</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Form Tambah Ekstrakurikuler -->
                <form method="POST" class="row g-3">
                    <div class="col-md-12">
                        <label for="nama_ekstra" class="form-label">Nama Ekstrakurikuler</label>
                        <input type="text" class="form-control" id="nama_ekstra" name="nama_ekstra" placeholder="Contoh: Pramuka" required>
                    </div>

                    <div class="col-md-12">
                        <label for="id_pembina" class="form-label">Pembina</label>
                        <select class="form-select" id="id_pembina" name="id_pembina" required>
                            <option value="" selected disabled>--- Pilih Pembina ---</option>
                            <?php foreach ($pembinaList as $pembina): ?>
                                <option value="<?= $pembina['id_pembina']; ?>">
                                    <?= htmlspecialchars($pembina['nama']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="hari" class="form-label">Hari</label>
                        <select class="form-select" id="hari" name="hari" required>
                            <option value="" selected disabled>---Pilih Hari---</option>
                            <option value="Senin">Senin</option>
                            <option value="Selasa">Selasa</option>
                            <option value="Rabu">Rabu</option>
                            <option value="Kamis">Kamis</option>
                            <option value="Jumat">Jumat</option>
                            <option value="Sabtu">Sabtu</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="jam_mulai" class="form-label">Jam Mulai</label>
                        <input type="time" class="form-control" id="jam_mulai" name="jam_mulai" required>
                    </div>

                    <div class="col-md-3">
                        <label for="jam_selesai" class="form-label">Jam Selesai</label>
                        <input type="time" class="form-control" id="jam_selesai" name="jam_selesai" required>
                    </div>

                    <div class="modal-footer mt-4">
                        <button type="submit" name="tambah_ekstra" class="btn btn-primary">Simpan</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>
<!-- End Modal Tambah Ekstrakurikuler -->

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// Konfirmasi sebelum menghapus
function confirmDelete(id_ekstra) {
  Swal.fire({
    title: 'Apakah Anda yakin?',
    text: 'Hapus Ekstrakurikuler Sekolah!',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Ya, hapus!',
    cancelButtonText: 'Batal'
  }).then((result) => {
    if (!result.isConfirmed) return;

    const params = new URLSearchParams();
    params.append('id_ekstra', id_ekstra);

    fetch('delete_ekstra.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
      body: params.toString()
    })
    .then(async (res) => {
      let data;
      try { data = await res.json(); }
      catch { throw new Error('Respons bukan JSON'); }

      if (res.ok && data.success) {
        Swal.fire('Terhapus!', data.message || 'Ekstrakurikuler telah dihapus.', 'success')
          .then(() => window.location.reload());
      } else {
        throw new Error(data?.message || 'Gagal menghapus.');
      }
    })
    .catch(err => {
      console.error(err);
      Swal.fire('Error!', err.message || 'Terjadi kesalahan saat menghapus.', 'error');
    });
  });
}
</script>