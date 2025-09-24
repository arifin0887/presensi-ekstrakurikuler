<?php
require_once "koneksi.php";

$koneksi = new mysqli("localhost", "root", "", "presensi_ekstra");

// cek koneksi
if ($koneksi->connect_error) {
    die("Koneksi gagal: " . $koneksi->connect_error);
}

$query = "SELECT e.id_ekstra, e.nama_ekstra, p.nama AS pembina, e.hari, e.jam_mulai, e.jam_selesai 
          FROM tb_ekstrakurikuler e 
          LEFT JOIN tb_pembina p ON e.id_pembina = p.id_pembina";
$result = $koneksi->query($query);

//Daftar
if (isset($_POST['daftar'])) {
    $nis       = $_POST['nis'];
    $nama      = $_POST['nama'];
    $jk        = $_POST['jk'];
    $id_kelas  = $_POST['id_kelas'];
    $password  = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $id_ekstra = $_POST['id_ekstra'];
    $tgl_daftar = date('Y-m-d');

    // Cek apakah NIS sudah ada
    $cekNIS = $koneksi->prepare("SELECT nis FROM tb_siswa WHERE nis = ?");
    $cekNIS->bind_param("s", $nis);
    $cekNIS->execute();
    $result = $cekNIS->get_result();

    if ($result->num_rows > 0) {
        // Jika sudah terdaftar tampilkan error
        echo "<div class='alert alert-danger mt-3'>NIS <strong>$nis</strong> sudah terdaftar. Silakan gunakan NIS lain.</div>";
    } else {
        // insert ke tb_siswa
        $simpanSiswa = $koneksi->query("INSERT INTO tb_siswa (nis, nama, jk, id_kelas, password)
                                        VALUES ('$nis', '$nama', '$jk', '$id_kelas', '$password')");

        // insert ke tb_peserta_ekstra
        $simpanEkstra = $koneksi->query("INSERT INTO tb_peserta_ekstra (nis, id_ekstra, tgl_daftar)
                                         VALUES ('$nis', '$id_ekstra', '$tgl_daftar')");

        if ($simpanSiswa && $simpanEkstra) {
            echo "<div class='alert alert-success mt-3'>Pendaftaran berhasil! Silakan login.</div>";
        } else {
            echo "<div class='alert alert-danger mt-3'>Gagal mendaftar: " . $koneksi->error . "</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>Daftar Ekstrakurikuler - Skanida</title>
  <meta content="" name="description">
  <meta content="" name="keywords">

  <!-- Favicons -->
   <link href="../assets/css/main.css" rel="stylesheet">
  <link href="assets/img/smk.png" rel="icon">
  <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">

  <!-- Google Fonts -->
  <link href="https://fonts.gstatic.com" rel="preconnect">
  <link href="https://fonts.googleapis.com/css?family=Open+Sans|Nunito|Poppins" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/css/style.css" rel="stylesheet">

  <!-- Google Font -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">

  <!-- Custom Style -->
  <style>
    body {
      background: linear-gradient(135deg, #3498db, #6dd5fa);
      font-family: 'Poppins', sans-serif;
      color: #fff;
    }

    .card {
      background: rgba(255, 255, 255, 0.1);
      backdrop-filter: blur(10px);
      border: none;
      border-radius: 15px;
      color: #fff;
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
    }

    .form-label,
    .form-select,
    .form-control,
    .input-group-text,
    .card-title,
    .alert {
      color: #fff;
    }

    .form-control,
    .form-select {
      background: rgba(255, 255, 255, 0.2);
      border: none;
      color: #fff;
    }

    .form-control::placeholder {
      color: #d9ecff;
    }

    .form-control:focus,
    .form-select:focus {
      border: none;
      box-shadow: 0 0 0 0.2rem rgba(255, 255, 255, 0.3);
      background: rgba(255, 255, 255, 0.3);
      color: #fff;
    }

    .input-group-text {
      background: rgba(255, 255, 255, 0.15);
      border: none;
    }

    .btn-primary {
      background-color: #1e3c72;
      border: none;
      transition: background 0.3s ease;
    }

    .btn-primary:hover {
      background-color: #2a5298;
    }

    .alert-success {
      background-color: rgba(40, 167, 69, 0.2);
      border: none;
      color: #d4edda;
    }

    .alert-danger {
      background-color: rgba(220, 53, 69, 0.2);
      border: none;
      color: #f8d7da;
    }

    .credits {
      color: #d4eaf7;
    }

    .text-muted {
      color: #d1ecf1 !important;
    }

    .form-select option {
      color: #000; /* supaya dropdown item bisa terbaca */
    }
  </style>

</head>

<body>
  <main>
    <div class="container">
      <section class="section register d-flex flex-column align-items-center justify-content-center min-vh-100 py-4">
        <div class="container">
          <div class="row justify-content-center">
            <div class="col-lg-5 col-md-7 d-flex flex-column align-items-center justify-content-center">

              <!-- Logo -->
              <div class="d-flex justify-content-center align-items-center py-4">
                <a href="index.php" class="d-flex align-items-center gap-2 text-decoration-none">
                  <img src="assets/img/smk.png" alt="Logo SMK" style="width: 60px; height: auto;">
                  <span class="fs-4 fw-semibold text-light">EkstraKu</span>
                </a>
              </div>

              <!-- Card -->
              <div class="card shadow-lg w-100">
                <div class="card-body">

                  <!-- Judul -->
                  <div class="pt-4 pb-2 text-center">
                    <h5 class="card-title text-center pb-0 fs-4">Formulir Pendaftaran</h5>
                    <p class="text-muted small">Silakan isi data lengkap untuk bergabung ekstrakurikuler</p>
                  </div>

                  <!-- Alert Success or Error -->
                  <?php
                  if (isset($simpanSiswa) && $simpanSiswa && $simpanEkstra) {
                      echo "<div class='alert alert-success'>Pendaftaran berhasil! Silakan login.</div>";
                  } elseif (isset($simpanSiswa) || isset($simpanEkstra)) {
                      echo "<div class='alert alert-danger'>Gagal mendaftar. Silakan cek kembali data kamu.</div>";
                  }
                  ?>

                  <!-- Form Daftar -->
                  <div class="card shadow-lg border-0 rounded-4 p-4">
                    <h3 class="text-center mb-4 fw-bold">Daftar Akun EkstraKu</h3>
                    <form class="row g-3 needs-validation" method="post" novalidate>

                      <!-- NIS -->
                      <div class="col-12">
                        <label for="nis" class="form-label">NIS</label>
                        <div class="input-group">
                          <span class="input-group-text"><i class="bi bi-person-badge"></i></span>
                          <input type="text" name="nis" class="form-control" id="nis" placeholder="Masukkan NIS" required>
                          <div class="invalid-feedback">Masukkan NIS kamu.</div>
                        </div>
                      </div>

                      <!-- Nama -->
                      <div class="col-12">
                        <label for="nama" class="form-label">Nama Lengkap</label>
                        <div class="input-group">
                          <span class="input-group-text"><i class="bi bi-person-circle"></i></span>
                          <input type="text" name="nama" class="form-control" id="nama" placeholder="Masukkan nama lengkap" required>
                          <div class="invalid-feedback">Masukkan nama lengkap kamu.</div>
                        </div>
                      </div>

                      <!-- Jenis Kelamin -->
                      <div class="col-12">
                        <label for="jk" class="form-label">Jenis Kelamin</label>
                        <div class="input-group">
                          <span class="input-group-text"><i class="bi bi-gender-ambiguous"></i></span>
                          <select class="form-select" name="jk" required>
                            <option value="">-- Pilih --</option>
                            <option value="L">Laki-laki</option>
                            <option value="P">Perempuan</option>
                          </select>
                          <div class="invalid-feedback">Pilih jenis kelamin.</div>
                        </div>
                      </div>

                      <!-- Kelas -->
                      <div class="col-12">
                        <label for="id_kelas" class="form-label">Kelas</label>
                        <div class="input-group">
                          <span class="input-group-text"><i class="bi bi-building"></i></span>
                          <select class="form-select" name="id_kelas" required>
                            <option value="">-- Pilih Kelas --</option>
                            <?php
                            $kelas = $koneksi->query("SELECT * FROM tb_kelas");
                            while ($row = $kelas->fetch_assoc()) {
                              echo "<option value='{$row['id_kelas']}'>{$row['jenjang']} {$row['jurusan']} {$row['nama_kelas']}</option>";
                            }
                            ?>
                          </select>
                          <div class="invalid-feedback">Pilih kelas kamu.</div>
                        </div>
                      </div>

                      <!-- Password -->
                      <div class="col-12">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                          <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                          <input type="password" name="password" class="form-control" id="password" placeholder="Buat password" required>
                          <span class="input-group-text password-toggle" style="cursor:pointer;"><i class="bi bi-eye-slash" id="togglePassword"></i></span>
                          <div class="invalid-feedback">Masukkan password kamu.</div>
                        </div>
                      </div>

                      <!-- Ekstrakurikuler -->
                      <div class="col-12">
                        <label for="id_ekstra" class="form-label">Ekstrakurikuler</label>
                        <div class="input-group">
                          <span class="input-group-text"><i class="bi bi-people"></i></span>
                          <select class="form-select" name="id_ekstra" required>
                            <option value="">-- Pilih Ekstrakurikuler --</option>
                            <?php
                            $ekstra = $koneksi->query("SELECT * FROM tb_ekstrakurikuler");
                            while ($row = $ekstra->fetch_assoc()) {
                              echo "<option value='{$row['id_ekstra']}'>{$row['nama_ekstra']}</option>";
                            }
                            ?>
                          </select>
                          <div class="invalid-feedback">Pilih ekstrakurikuler.</div>
                        </div>
                      </div>

                      <!-- Tombol -->
                      <div class="col-12 mt-3">
                        <button class="btn w-100 py-2 fw-semibold text-uppercase" 
                                style="background: linear-gradient(to right,#1e3c72,#2a5298); color:white;" 
                                type="submit" name="daftar">
                          Daftar Sekarang
                        </button>
                      </div>

                      <div class="col-12 text-center">
                        <p class="small mt-3 mb-0">Sudah punya akun? <a href="login.php" class="fw-bold text-decoration-none">Login di sini</a></p>
                      </div>
                    </form>
                  </div>
                </div>
              </div>

              <!-- Footer -->
              <div class="credits mt-3 text-muted small text-center">
                © <?= date('Y') ?> Skanida | Dikembangkan oleh Findev
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>
  </main>

  <a href="#" class="back-to-top d-flex align-items-center justify-content-center">
    <i class="bi bi-arrow-up-short"></i>
  </a>

  <!-- JS Files -->
  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="assets/js/main.js"></script>
  <!-- Script toggle password -->
  <script>
    document.getElementById('togglePassword').addEventListener('click', function () {
      const passwordInput = document.getElementById('password');
      if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        this.classList.remove('bi-eye-slash');
        this.classList.add('bi-eye');
      } else {
        passwordInput.type = 'password';
        this.classList.remove('bi-eye');
        this.classList.add('bi-eye-slash');
      }
    });
  </script>
</body>
</html>
