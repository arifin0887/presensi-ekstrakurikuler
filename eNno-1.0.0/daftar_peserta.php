<?php
require 'koneksi.php';

if (isset($_GET['id_ekstra'])) {
    $id_ekstra = $_GET['id_ekstra'];

    // Ambil nama ekstrakurikuler
    $stmtEkstra = $pdo->prepare("SELECT nama_ekstra FROM tb_ekstrakurikuler WHERE id_ekstra = ?");
    $stmtEkstra->execute([$id_ekstra]);
    $namaEkstra = $stmtEkstra->fetchColumn();

    if (!$namaEkstra) {
        die("Ekstrakurikuler tidak ditemukan.");
    }

    // Ambil data peserta + kelas
    $stmt = $pdo->prepare("
        SELECT 
            s.nis, s.nama, s.jk,
            CONCAT(k.jenjang, ' ', k.nama_kelas) AS kelas
        FROM tb_peserta_ekstra pe
        JOIN tb_siswa s ON pe.nis = s.nis
        LEFT JOIN tb_kelas k ON s.id_kelas = k.id_kelas
        WHERE pe.id_ekstra = ? AND s.status = 'aktif'
        ORDER BY s.nama ASC
    ");
    $stmt->execute([$id_ekstra]);
    $pesertaList = $stmt->fetchAll(PDO::FETCH_ASSOC);

} else {
    die("ID Ekstrakurikuler tidak ditemukan.");
}

// Proses Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nis'])) {
    $nis   = $_POST['nis'];
    $nama  = $_POST['nama'];
    $jk    = $_POST['jk'];

    $stmt = $pdo->prepare("UPDATE tb_siswa SET nama = ?, jk = ? WHERE nis = ?");
    $success = $stmt->execute([$nama, $jk, $nis]);

    if ($success) {
        echo "
        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        <script>
        Swal.fire({
          title: 'Berhasil!',
          icon: 'success',
          text: 'Data peserta berhasil diperbarui'
        }).then(function() {
            window.location = 'index.php?page=daftar_peserta&id_ekstra=$id_ekstra'; 
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
            text: 'Terjadi kesalahan saat menyimpan data.'
        });
        </script>
        ";
    }
}
?>

<section class="section">
  <div class="card shadow-sm border-0">
    <div class="card-body">
      <h5 class="card-title">
        <i class="bi bi-star-fill me-2"></i> Daftar Peserta Ekstrakurikuler <?= htmlspecialchars($namaEkstra) ?>
      </h5>

      <div class="table-responsive mt-3">
        <table class="table table-hover align-middle">
          <thead class="table-light">
            <tr class="text-center">
              <th>No</th>
              <th>NIS</th>
              <th>Nama</th>
              <th>Jenis Kelamin</th>
              <th>Kelas</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!empty($pesertaList)): ?>
              <?php $no = 1; foreach ($pesertaList as $peserta): ?>
                <tr>
                  <td class="text-center"><?= $no++ ?></td>
                  <td class="fw-semibold"><?= htmlspecialchars($peserta['nis']) ?></td>
                  <td><?= htmlspecialchars($peserta['nama']) ?></td>
                  <td class="text-center">
                    <span class="badge <?= $peserta['jk'] === 'L' ? 'bg-info' : 'bg-primary' ?>">
                      <?= $peserta['jk'] === 'L' ? 'Laki-laki' : 'Perempuan' ?>
                    </span>
                  </td>
                  <td class="text-center"><?= htmlspecialchars($peserta['kelas']) ?></td>
                  <td class="text-center">
                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editModal<?= $peserta['nis']; ?>">
                      <i class="bi bi-pencil"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-info me-1" data-bs-toggle="modal" data-bs-target="#detailModal<?= $peserta['nis']; ?>">
                      <i class="bi bi-eye"></i>
                    </button>
                  </td>
                </tr>

                <!-- Modal Detail -->
                <div class="modal fade" id="detailModal<?= $peserta['nis']; ?>" tabindex="-1">
                  <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content shadow-lg border-0 rounded-3">
                      <div class="modal-header bg-info text-white">
                        <h5 class="modal-title">
                          <i class="bi bi-person-vcard me-2"></i> Detail Peserta
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                      </div>
                      <div class="modal-body">
                        <ul class="list-group list-group-flush">
                          <li class="list-group-item"><strong>NIS:</strong> <?= $peserta['nis']; ?></li>
                          <li class="list-group-item"><strong>Nama:</strong> <?= htmlspecialchars($peserta['nama']); ?></li>
                          <li class="list-group-item"><strong>Jenis Kelamin:</strong> <?= $peserta['jk']=='L'?'Laki-laki':'Perempuan'; ?></li>
                          <li class="list-group-item"><strong>Kelas:</strong> <?= htmlspecialchars($peserta['kelas']); ?></li>
                          <li class="list-group-item"><strong>Ekstrakurikuler:</strong> <?= htmlspecialchars($namaEkstra); ?></li>
                        </ul>
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDelete('<?= $peserta['nis'] ?>')">
                            <i class="bi bi-trash me-1"></i> Hapus
                        </button>
                
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">
                          <i class="bi bi-x-circle me-1"></i> Tutup
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
                <!-- End Modal Detail -->

                <!-- Modal Edit -->
                <div class="modal fade" id="editModal<?= $peserta['nis'];?>" tabindex="-1">
                  <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content shadow-lg border-0 rounded-4">
                      <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">
                          <i class="bi bi-pencil-square me-2"></i> Edit Data Peserta
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                      </div>
                      <div class="modal-body">
                        <form method="POST" class="needs-validation" novalidate>
                          <input type="hidden" name="nis" value="<?= $peserta['nis']; ?>">
                          <div class="row g-3">
                            <div class="col-md-6">
                              <label class="form-label">Nama Lengkap</label>
                              <input type="text" class="form-control" name="nama" value="<?= htmlspecialchars($peserta['nama']); ?>" required>
                            </div>
                            <div class="col-md-6">
                              <label class="form-label">Jenis Kelamin</label>
                              <select class="form-select" name="jk" required>
                                <option value="L" <?= $peserta['jk']=='L'?'selected':''; ?>>Laki-laki</option>
                                <option value="P" <?= $peserta['jk']=='P'?'selected':''; ?>>Perempuan</option>  
                              </select>
                            </div>
                          </div>
                          <div class="mt-4 text-end">
                            <button type="submit" class="btn btn-success">
                               Update
                            </button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                               Batal
                            </button>
                          </div>
                        </form>
                      </div>
                    </div>
                  </div>
                </div>
                <!-- End Modal Edit -->

              <?php endforeach ?>
            <?php else: ?>
              <tr>
                <td colspan="6" class="text-center text-muted">Belum ada peserta</td>
              </tr>
            <?php endif ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function confirmDelete(nis) {
    Swal.fire({
        title: 'Apakah Anda yakin?',
        text: "Peserta akan dihapus dari ekstrakurikuler ini!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya, hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`delete_siswa.php?nis=${nis}&id_ekstra=<?= $id_ekstra ?>`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire('Terhapus!', 'Peserta telah dihapus.', 'success')
                            .then(() => {
                                window.location.reload();
                            });
                    } else {
                        Swal.fire('Gagal!', data.message, 'error');
                    }
                })
                .catch(error => {
                    Swal.fire('Error!', 'Terjadi kesalahan saat menghapus.', 'error');
                });
        }
    });
}
</script>