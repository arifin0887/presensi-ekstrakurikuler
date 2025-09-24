<?php
ob_start();
$koneksi = new mysqli("localhost", "root", "", "presensi_ekstra");

if ($koneksi->connect_error) {
    die("Koneksi gagal: " . $koneksi->connect_error);
}

// Update pembina
if (isset($_POST['updatePembina'])) {

    $id_pembina = $_POST['id'];
    $nama = $_POST['nama'];
    $username = $_POST['username'];
    $ekstra = isset($_POST['ekstra']) ? $_POST['ekstra'] : [];
    $password = $_POST['password'];

    // Pastikan hanya boleh 1 ekstra
    if (count($ekstra) > 1) {
        echo "
        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        <script>
        Swal.fire({
          icon: 'error',
          title: 'Gagal!',
          text: 'Setiap pembina hanya boleh membina 1 ekstrakurikuler!'
        }).then(() => {
            window.history.back();
        });
        </script>";
        exit;
    }

    // Update pembina (dengan atau tanpa password)
    if (!empty($password)) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $koneksi->prepare("UPDATE tb_pembina SET nama = ?, username = ?, password = ? WHERE id_pembina = ?");
        $stmt->bind_param("sssi", $nama, $username, $hashed, $id_pembina);
    } else {
        $stmt = $koneksi->prepare("UPDATE tb_pembina SET nama = ?, username = ? WHERE id_pembina = ?");
        $stmt->bind_param("ssi", $nama, $username, $id_pembina);
    }

    if ($stmt->execute()) {
        // Kosongkan semua ekstra yang dibina oleh pembina ini
        $reset_stmt = $koneksi->prepare("UPDATE tb_ekstrakurikuler SET id_pembina = NULL WHERE id_pembina = ?");
        $reset_stmt->bind_param("i", $id_pembina);
        $reset_stmt->execute();

        // Set pembina baru hanya untuk 1 ekstra yang dipilih
        if (!empty($ekstra)) {
            $id_ekstra = $ekstra[0]; // Ambil hanya satu
            $update_ekstra_stmt = $koneksi->prepare("UPDATE tb_ekstrakurikuler SET id_pembina = ? WHERE id_ekstra = ?");
            $update_ekstra_stmt->bind_param("ii", $id_pembina, $id_ekstra);
            $update_ekstra_stmt->execute();
        }

        echo "
        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        <script>
        Swal.fire({
          title: 'Berhasil!',
          icon: 'success',
          text: 'Pembina berhasil diperbarui'
        }).then(function() {
            window.location = 'index.php?page=pembina_admin'; 
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
            text: 'Data gagal diperbarui, coba lagi.'
        });
        </script>
        ";
    }
    exit;
}

// Tambah pembina baru
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['nama'])) {
    $nama     = htmlspecialchars($_POST['nama']);
    $user     = htmlspecialchars($_POST['username']);
    $pass     = $_POST['password'];
    $ekstra   = isset($_POST['ekstra']) ? $_POST['ekstra'] : [];

    // Pastikan hanya boleh 1 ekstra
    if (count($ekstra) > 1) {
        echo "<script>
                alert('Setiap pembina hanya boleh membina 1 ekstrakurikuler!');
                window.location.href='index.php?page=pembina_admin';
              </script>";
        exit;
    }

    // Validasi panjang password
    if (strlen($pass) < 8) {
        echo "<script>
                alert('Password minimal 8 karakter!');
                window.location.href='index.php?page=pembina_admin';
              </script>";
        exit;
    }

    // Enkripsi password
    $passwordHash = password_hash($pass, PASSWORD_DEFAULT);

    // Cek apakah username sudah ada
    $stmt = $koneksi->prepare("SELECT id_pembina FROM tb_pembina WHERE username = ?");
    $stmt->bind_param("s", $user);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo "<script>
                alert('Username sudah digunakan, silakan pilih username lain!');
                window.location.href='index.php?page=pembina_admin';
              </script>";
    } else {
        // Simpan data pembina
        $insert = $koneksi->prepare("INSERT INTO tb_pembina (nama, username, password) VALUES (?, ?, ?)");
        $insert->bind_param("sss", $nama, $user, $passwordHash);
        if ($insert->execute()) {
            $id_pembina_baru = $insert->insert_id;

            // Set ekstra jika ada
            if (!empty($ekstra)) {
                $id_ekstra = $ekstra[0]; // hanya satu
                $update_ekstra_stmt = $koneksi->prepare("UPDATE tb_ekstrakurikuler SET id_pembina = ? WHERE id_ekstra = ?");
                $update_ekstra_stmt->bind_param("ii", $id_pembina_baru, $id_ekstra);
                $update_ekstra_stmt->execute();
            }

            echo "
            <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
            <script>
            Swal.fire({
              title: 'Berhasil!',
              icon: 'success',
              text: 'Pembina berhasil ditambahkan'
            }).then(function() {
                window.location = 'index.php?page=pembina_admin'; 
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
                text: 'Data gagal ditambahkan, coba lagi.'
            });
            </script>
            ";
        }
    }
}

ob_end_flush();
?>

<!-- ======= Main Content ======= -->
<section class="section">
  <div class="card">
    <div class="card-body">
      <h5 class="card-title">Manajemen Pembina</h5>
      <p>Kelola data pembina ekstrakurikuler sekolah anda</p>

      <!-- Button to Add New Petugas with icon -->
      <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#tambahPembinaModal">
        <i class="bi bi-plus-circle me-2"></i>Tambah Pembina
      </button>

      <!-- Table with hover effect and better spacing -->
      <div class="table-responsive">
        <table class="table table-hover align-middle">
          <thead class="table-light">
            <tr>
              <th scope="col" width="5%">No</th>
              <th scope="col">Nama Pembina</th>
              <th scope="col">Ekstrakurikuler yang Dibina</th>
              <th scope="col" width="15%">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $no = 1;
            $query = $koneksi->query("
              SELECT p.id_pembina, p.nama, p.username, GROUP_CONCAT(e.nama_ekstra SEPARATOR ', ') as ekstra_dibina
              FROM tb_pembina p
              LEFT JOIN tb_ekstrakurikuler e ON p.id_pembina = e.id_pembina
              GROUP BY p.id_pembina
              ORDER BY p.id_pembina ASC
            ");

            while ($row = $query->fetch_assoc()) {

              // Ambil semua ekstra yang dibina pembina ini
              $ekstra_ids = [];
              $res = $koneksi->query("SELECT id_ekstra FROM tb_ekstrakurikuler WHERE id_pembina = " . $row['id_pembina']);
              while ($r = $res->fetch_assoc()) {
                  $ekstra_ids[] = $r['id_ekstra'];
              }

              // Bentuk string dipisahkan koma
              $ekstra_dibina = implode(',', $ekstra_ids);
            ?>
              <tr>
                <th scope="row"><?= $no++; ?></th>
                <td><?= htmlspecialchars($row['nama']); ?></td>
                <td><?= $row['ekstra_dibina'] !== null ? htmlspecialchars($row['ekstra_dibina']) : '<span class="text-muted">Belum membina</span>'; ?></td>
                <td>
                  
                    <button class="btn btn-sm btn-outline-primary btnEditPembina"
                      data-id="<?= $row['id_pembina'] ?>"
                      data-nama="<?= htmlspecialchars($row['nama']) ?>"
                      data-username="<?= htmlspecialchars($row['username']) ?>"
                      data-ekstra="<?= htmlspecialchars($ekstra_dibina) ?>">
                      <i class="bi bi-pencil"></i>
                    </button>

                    <button class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#detailPetugasModal<?= $row['id_pembina']; ?>">
                      <i class="bi bi-eye"></i>
                    </button>
                </td>
              </tr>

            <!-- Modal Detail Pembina -->
            <div class="modal fade" id="detailPetugasModal<?= $row['id_pembina']; ?>" tabindex="-1" aria-labelledby="detailPetugasModalLabel<?= $row['id_pembina']; ?>" aria-hidden="true">
              <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                  <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="detailPetugasModalLabel<?= $row['id_pembina']; ?>">Detail Pembina</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    <ul class="list-group">
                      <li class="list-group-item"><strong>ID:</strong> <?= $row['id_pembina']; ?></li>
                      <li class="list-group-item"><strong>Nama:</strong> <?= htmlspecialchars($row['nama']); ?></li>
                      <li class="list-group-item"><strong>Username:</strong> <?= isset($row['username']) ? htmlspecialchars($row['username']) : '<span class="text-muted">Tidak tersedia</span>'; ?></li>
                      <li class="list-group-item">
                        <strong>Ekstrakurikuler:</strong>
                        <span>
                          <?= $row['ekstra_dibina'] !== null ? htmlspecialchars($row['ekstra_dibina']) : '<span class="text-muted">Belum membina</span>'; ?>
                        </span>
                      </li>
                    </ul>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDelete(<?= $row['id_pembina'] ?>)">Hapus</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
                  </div>
                </div>
              </div>
            </div>
            <!-- End Modal Detail Pembina -->

            <?php } ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</section>

<!-- Edit Modal -->
<div class="modal fade" id="editPetugasModal" tabindex="-1" aria-labelledby="editPetugasModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form id="formEditPetugas" method="POST">
        <div class="modal-header bg-primary text-light">
          <h5 class="modal-title" id="editPetugasModalLabel"></i>Edit Data Pembina</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="id" id="editIdPetugas">
          <input type="hidden" name="updatePembina" value="1">

          <div class="mb-3">
            <label for="editNama" class="form-label">Nama</label>
            <input type="text" class="form-control" name="nama" id="editNama" required>
          </div>

          <div class="mb-3">
            <label for="editUsername" class="form-label">Username</label>
            <input type="text" class="form-control" name="username" id="editUsername" required>
          </div>

          <div class="mb-3">
            <label for="editPassword" class="form-label">Password (kosongkan jika tidak diubah)</label>
            <input type="password" class="form-control" name="password" id="editPassword">
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Update</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Tambah Pembina -->
<div class="modal fade" id="tambahPembinaModal" tabindex="-1" aria-labelledby="tambahPembinaModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title" id="tambahPembinaModalLabel">Tambah Pembina Baru</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <!-- form langsung submit ke file ini sendiri -->
      <form method="POST" id="tambahPembinaForm">
        <div class="modal-body">
          <div class="mb-3">
            <label for="nama" class="form-label">Nama Pembina</label>
            <input type="text" class="form-control" id="nama" name="nama" required>
          </div>
          <div class="mb-3">
            <label for="username" class="form-label">Username</label>
            <input type="text" class="form-control" id="username" name="username" required>
          </div>
          <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <div class="input-group">
              <input type="password" class="form-control" id="password" name="password" required>
              <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                <i class="bi bi-eye"></i>
              </button>
            </div>
            <div class="form-text">Minimal 8 karakter</div>
          </div>
        </div>

        <div class="modal-footer">
          <button type="submit" class="btn btn-success">
            <i class="bi bi-save"></i> Simpan
          </button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// Variable untuk menyimpan ID yang dipilih
let currentId = null;

//edit pembina
document.querySelectorAll('.btnEditPembina').forEach(button => {
  button.addEventListener('click', function () {
    const id = this.dataset.id;
    const nama = this.dataset.nama;
    const username = this.dataset.username;
    const ekstraList = this.dataset.ekstra.split(',').map(e => e.trim());

    document.getElementById('editIdPetugas').value = id;
    document.getElementById('editNama').value = nama;
    document.getElementById('editUsername').value = username;

    // Reset semua checkbox
    document.querySelectorAll('.edit-ekstra-checkbox').forEach(cb => cb.checked = false);

    // Checklist sesuai value id_ekstra
    document.querySelectorAll('.edit-ekstra-checkbox').forEach(cb => {
      if (ekstraList.includes(cb.value)) {
        cb.checked = true;
      }
    });

    const modal = new bootstrap.Modal(document.getElementById('editPetugasModal'));
    modal.show();
  });
});

document.addEventListener("DOMContentLoaded", function() {
  document.querySelectorAll(".btnEditPembina").forEach(function(btn) {
    btn.addEventListener("click", function() {
      const id = this.dataset.id;
      const nama = this.dataset.nama;
      const username = this.dataset.username;
      const ekstraList = this.dataset.ekstra ? this.dataset.ekstra.split(",") : [];

      document.getElementById("editIdPetugas").value = id;
      document.getElementById("editNama").value = nama;
      document.getElementById("editUsername").value = username;

      document.querySelectorAll("#editEkstraCheckboxes .edit-ekstra-checkbox").forEach(cb => {
        if (ekstraList.includes(cb.value)) {
          cb.checked = true;
        }
      });

      document.getElementById("editPassword").value = "";
    });
  });
});

document.getElementById("submitTambahPembina").addEventListener("click", function (e) {
    e.preventDefault();

    const form = document.getElementById("tambahPembinaForm");
    const formData = new FormData(form);

    fetch("pembina.php", {
        method: "POST",
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: data.message
            }).then(() => location.reload());
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: data.message
            });
        }
    // })
    // .catch(err => {
    //     Swal.fire({
    //         icon: 'error',
    //         title: 'Error!',
    //         text: 'Terjadi kesalahan input '
    //     });
    });
});

//alert edit
document.getElementById("formEditPetugas").addEventListener("submit", function (e) {
  e.preventDefault();

  const formData = new FormData(this);

  fetch("pembina.php", {
    method: "POST",
    body: formData
  })
    .then(response => response.json())
    .then(data => {
      console.log(data); // ← Tambahkan ini untuk melihat response
      if (data.success) {
        Swal.fire("Berhasil!", data.message, "success").then(() => {
          location.reload();
        });
      } else {
        Swal.fire("Gagal!", data.message, "error");
      }
    })
    .catch(error => {
      console.error("Error:", error);
      Swal.fire("Error!", "Terjadi kesalahan saat menyimpan.", "error");
    });
});

// Fungsi untuk konfirmasi hapus
function confirmDelete(id_pembina) {
    currentId = id_pembina;
    Swal.fire({
        title: 'Apakah Anda yakin?',
        text: "Data pembina akan dihapus permanen!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya, hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            // Proses penghapusan
            fetch(`delete.php?id_pembina=${id_pembina}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire(
                            'Terhapus!',
                            'Data pembina telah dihapus.',
                            'success'
                        ).then(() => {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire(
                            'Gagal!',
                            data.message,
                            'error'
                        );
                    }
                })
                .catch(error => {
                    Swal.fire(
                        'Error!',
                        'Terjadi kesalahan saat menghapus.',
                        'error'
                    );
                    console.error('Error:', error);
                });
        }
    });
}

// Event listener untuk tombol hapus di modal
document.getElementById('deleteBtn').addEventListener('click', function() {
    if (currentId) {
        confirmDelete(currentId);
    }
});

// Toggle password visibility
document.getElementById('togglePassword').addEventListener('click', function() {
    const passwordInput = document.getElementById('password');
    const icon = this.querySelector('i');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');
    } else {
        passwordInput.type = 'password';
        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye');
    }
});
</script>