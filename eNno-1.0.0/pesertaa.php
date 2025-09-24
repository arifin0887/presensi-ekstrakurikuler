<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'koneksi.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$id_pembina = $_SESSION['user_id'];

// Ambil data siswa & ekstra mereka, hanya yang dibina oleh pembina login
$stmt = $pdo->prepare("
  SELECT 
    s.nis, s.nama, s.jk, k.jenjang, k.jurusan, k.nama_kelas,
    CONCAT(k.jenjang, ' ', k.nama_kelas) AS kelas_lengkap,
    GROUP_CONCAT(e.nama_ekstra SEPARATOR ', ') AS ekstrakurikuler
  FROM tb_siswa s
  LEFT JOIN tb_kelas k ON s.id_kelas = k.id_kelas
  LEFT JOIN tb_peserta_ekstra ae ON s.nis = ae.nis
  LEFT JOIN tb_ekstrakurikuler e ON ae.id_ekstra = e.id_ekstra
  WHERE e.id_pembina = ?
  GROUP BY s.nis
  ORDER BY kelas_lengkap ASC, s.nama ASC
");
$stmt->execute([$id_pembina]);
$dataSiswa = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
            window.location = 'indek.php?page=peserta_pembina'; 
        });
        </script>
        ";
    } else {
        echo "
        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        <script>
        Swal.fire({
          title: 'Gagal!',
          icon: 'error',
          text: 'Terjadi kesalahan saat memperbarui data'
        });
        </script>
        ";
    }
}
?>

<!-- ======= Main Content ======= -->
<section class="section">
  <div class="card">
    <div class="card-body">
      <h5 class="card-title">Peserta Ekstrakurikuler Anda</h5>
      <p>Kelola data peserta ekstrakurikuler yang Anda bimbing</p>

      <?php if (empty($dataSiswa)): ?>
        <div class="alert alert-warning">Tidak ada siswa yang mengikuti ekstrakurikuler Anda.</div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th scope="col" width="5%">No</th>
                <th scope="col">NIS</th>
                <th scope="col">Nama</th>
                <th scope="col">Kelas</th>
                <th scope="col">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php $no = 1; foreach ($dataSiswa as $row): ?>
                <tr>
                  <td><?= $no++ ?></td>
                  <td><?= htmlspecialchars($row['nis']) ?></td>
                  <td><?= htmlspecialchars($row['nama']) ?></td>
                  <td><?= htmlspecialchars($row['kelas_lengkap']) ?></td>
                  <td>
                    <button class="btn btn-sm btn-outline-info me-1" data-bs-toggle="modal" data-bs-target="#detailModal<?= $row['nis']; ?>">
                      <i class="bi bi-eye"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editModal<?= $row['nis']; ?>">
                      <i class="bi bi-pencil"></i>
                    </button>
                  </td>
                </tr>

                <!-- Modal Detail -->
                <div class="modal fade" id="detailModal<?= $row['nis']; ?>" tabindex="-1">
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
                          <li class="list-group-item"><strong>NIS:</strong> <?= $row['nis']; ?></li>
                          <li class="list-group-item"><strong>Nama:</strong> <?= htmlspecialchars($row['nama']); ?></li>
                          <li class="list-group-item"><strong>Jenis Kelamin:</strong> <?= $row['jk']=='L'?'Laki-laki':'Perempuan'; ?></li>
                          <li class="list-group-item"><strong>Kelas:</strong> <?= htmlspecialchars($row['kelas_lengkap']); ?></li>
                          <li class="list-group-item"><strong>Ekstrakurikuler:</strong> <?= htmlspecialchars($row['ekstrakurikuler']); ?></li>
                        </ul>
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDelete('<?= $row['nis'] ?>')">
                          <i class="bi bi-trash me-1"></i> Hapus
                        </button>
                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">
                          <i class="bi bi-x-circle me-1"></i> Tutup
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
                <!-- End Modal Detail -->

                <!-- Modal Edit -->
                <div class="modal fade" id="editModal<?= $row['nis']; ?>" tabindex="-1">
                  <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content shadow-lg border-0 rounded-3">
                      <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">
                          <i class="bi bi-pencil-square me-2"></i> Edit Peserta
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                      </div>
                      <form method="POST" action="">
                        <div class="modal-body">
                          <input type="hidden" name="nis" value="<?= $row['nis']; ?>">
                          <div class="mb-3">
                            <label for="nama<?= $row['nis']; ?>" class="form-label">Nama</label>
                            <input type="text" class="form-control" id="nama<?= $row['nis']; ?>" name="nama" value="<?= htmlspecialchars($row['nama']); ?>" required>
                            <div class="invalid-feedback">Nama tidak boleh kosong.</div>
                          </div>
                          <div class="mb-3">
                            <label for="jk<?= $row['nis']; ?>" class="form-label">Jenis Kelamin</label>
                            <select class="form-select" id="jk<?= $row['nis']; ?>" name="jk" required>
                              <option value="L" <?= $row['jk'] === 'L' ? 'selected' : '' ?>>Laki-laki</option>
                              <option value="P" <?= $row['jk'] === 'P' ? 'selected' : '' ?>>Perempuan</option>
                            </select>
                            <div class="invalid-feedback">Pilih jenis kelamin.</div>
                          </div>
                        </div>
                        <div class="modal-footer">
                          <button type="submit" class="btn btn-success">
                            <i class="bi bi-save me-1"></i> Update
                          </button> 
                          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle me-1"></i> Batal
                          </button>
                        </div>
                      </form>
                    </div>
                  </div>
                </div>
                <!-- End Modal Edit -->
              <?php endforeach ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>

<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
<script>
function confirmDelete(nis) {
    Swal.fire({
        title: 'Apakah Anda yakin?',
        text: "Peserta akan dihapus dari ekstrakurikuler ini!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#16a34a',
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