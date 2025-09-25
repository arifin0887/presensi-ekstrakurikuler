<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>EkstraKu - Presensi Ekstrakurikuler</title>
  <link href="assets/img/smk.png" rel="icon">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
  <!-- Font Awesome untuk ikon -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(135deg, #2980b9, #6dd5fa);
      color: #fff;
      margin: 0;
      padding: 0;
    }

    /* Navbar */
    .navbar {
      background: rgba(0, 0, 0, 0.2);
      backdrop-filter: blur(8px);
      transition: all 0.3s ease-in-out;
    }

    .navbar.scrolled {
      background: rgba(0, 0, 0, 0.7);
      box-shadow: 0 3px 10px rgba(0,0,0,0.3);
    }

    .navbar-brand {
      display: flex;
      align-items: center;
      gap: 10px;
      font-size: 1.5rem;
      font-weight: 700;
      color: #fff !important;
    }

    .navbar-brand img {
      width: 45px;
      height: 45px;
    }

    /* Hero */
    .hero {
      min-height: 90vh;
      padding: 60px 15px;
    }

    .hero h1 {
      font-size: 3rem;
      font-weight: 700;
      color: #fff;
    }

    .hero p {
      font-size: 1.2rem;
      color: #f8f9fa;
    }

    .hero img {
      animation: float 4s ease-in-out infinite;
    }

    /* Animasi gambar kanan biar lebih hidup */
    @keyframes float {
      0% { transform: translateY(0px); }
      50% { transform: translateY(-15px); }
      100% { transform: translateY(0px); }
    }

    .btn-utama {
      padding: 12px 30px;
      border-radius: 30px;
      text-transform: uppercase;
      font-weight: 600;
      margin: 0 10px;
      transition: all 0.3s ease;
    }

    .btn-login {
      background-color: #0A70B2;
      color: #fff;
    }

    .btn-login:hover {
      background-color: #4793C5;
      transform: translateY(-3px);
    }

    .btn-daftar {
      background-color: #00BE63;
      color: #fff;
    }

    .btn-daftar:hover {
      background-color: #157347;
      transform: translateY(-3px);
    }

    /* Fitur Section */
    .features {
      background: #f0f8ff; /* Warna latar belakang yang lebih terang */
      color: #333;
      padding: 80px 20px;
      text-align: center;
    }

    .features h2 {
      font-weight: 700;
      margin-bottom: 50px; /* Sedikit lebih banyak ruang */
      color: #2980b9;
      position: relative;
    }

    .features h2::after {
      content: '';
      position: absolute;
      bottom: -15px;
      left: 50%;
      transform: translateX(-50%);
      width: 80px;
      height: 4px;
      background-color: #FFBC00; /* Garis bawah yang menarik */
      border-radius: 2px;
    }

    .feature-box {
      padding: 30px;
      border-radius: 15px;
      background: #ffffff; /* Latar belakang putih */
      box-shadow: 0 4px 20px rgba(0,0,0,0.1); /* Bayangan yang lebih lembut */
      transition: transform 0.4s ease, box-shadow 0.4s ease; /* Transisi untuk bayangan juga */
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      min-height: 180px; /* Tinggi minimum agar konsisten */
      border: 1px solid #e0e0e0; /* Border halus */
    }

    .feature-box:hover {
      transform: translateY(-12px); /* Efek angkat yang sedikit lebih tinggi */
      box-shadow: 0 8px 30px rgba(0,0,0,0.15); /* Bayangan yang lebih kuat saat di-hover */
    }

    .feature-box .icon {
      font-size: 3.5rem; /* Ukuran ikon lebih besar */
      color: #2980b9; /* Warna ikon yang konsisten */
      margin-bottom: 15px;
      transition: color 0.3s ease;
    }

    .feature-box:hover .icon {
      color: #FFBC00; /* Perubahan warna ikon saat di-hover */
    }

    .feature-box h5 {
      font-weight: 600;
      margin-top: 0; /* Hapus margin top default */
      color: #333;
      font-size: 1.3rem; /* Ukuran font sedikit lebih besar */
    }

    .feature-box {
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .feature-box:hover {
      transform: translateY(-8px);
      box-shadow: 0 6px 20px rgba(0,0,0,0.15);
    }

    /* Footer */
    footer {
      text-align: center;
      padding: 30px 10px;
      color: #ccc;
      font-size: 0.9rem;
      background: rgb(7, 84, 134);
    }

    footer a {
      color: #6dd5fa;
      text-decoration: none;
    }
  </style>
</head>
<body>

  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg fixed-top px-4">
    <div class="container">
      <a class="navbar-brand" href="#">
        <img src="assets/img/smk.png" alt="Logo SMK">
        EkstraKu
      </a>
    </div>
  </nav>

  <!-- Hero -->
  <section class="hero d-flex align-items-center">
    <div class="container">
      <div class="row align-items-center">
        
        <!-- Kiri: Teks -->
        <div class="col-lg-6 text-center text-lg-start mb-4 mb-lg-0">
          <h1 class="fw-bold">Selamat Datang di <span style="color:#FFBC00;">EkstraKu</span></h1>
          <p class="lead mb-4">Sistem Presensi Ekstrakurikuler <br> SMK Negeri 2 Magelang berbasis Website</p>
          <div class="d-flex flex-wrap justify-content-center justify-content-lg-start">
            <a href="login.php" class="btn btn-utama btn-login me-2">Login</a>
            <a href="daftar.php" class="btn btn-utama btn-daftar">Daftar</a>
          </div>
        </div>

        <!-- Kanan: Icon/Ilustrasi -->
        <div class="col-lg-6 text-center">
          <img src="https://img.icons8.com/external-flaticons-lineal-color-flat-icons/350/external-students-university-flaticons-lineal-color-flat-icons.png" 
              alt="Ilustrasi Ekstrakurikuler" class="img-fluid" style="max-height: 350px;">
        </div>

      </div>
    </div>
  </section>

  <!-- Ekstrakurikuler Section -->
  <section class="features py-5 section-bg">
    <div class="container">
      <h2 class="text-center mb-5">Ekstrakurikuler di SMK N 2 Magelang</h2>
      <div class="row g-4 justify-content-center">
        <?php
        include 'koneksi.php'; 

        // Ambil data ekstra + pembina
        $sql = "SELECT e.id_ekstra, e.nama_ekstra, e.hari, p.nama 
                FROM tb_ekstrakurikuler e
                LEFT JOIN tb_pembina p ON e.id_pembina = p.id_pembina
                ORDER BY e.nama_ekstra ASC";
        $result = $pdo->query($sql);
        $ekstrakurikuler = $result->fetchAll(PDO::FETCH_ASSOC);

        // Mapping nama ekstra ke ikon Font Awesome
        $icon_map = [
            'Pasopati' => 'fa-campground',
            'Tonti' => 'fa-flag',
            'Basket' => 'fa-basketball-ball',
            'Futsal' => 'fa-futbol',
            'Volly' => 'fa-volleyball-ball',
            'Tari' => 'fa-mask',
            'Band' => 'fa-music',
            'OSIS' => 'fa-users',
            'padus' => 'fa-microphone',
            'PMR' => 'fa-briefcase-medical',
            'Cospala' => 'fa-hiking',
            'Screen' => 'fa-camera',
            'Rohis' => 'fa-mosque',
            // 'Karate' => 'fa-hand-rock',
            // 'Pencak Silat' => 'fa-fist-raised',
            // 'Rohkris' => 'fa-church',
            // 'Screen' => 'fa-newspaper',
            // 'Desain Grafis' => 'fa-palette',
            // 'Web Programming' => 'fa-code',
            // Default icon
            'default' => 'fa-shapes'
        ];

        if (count($ekstrakurikuler) > 0) {
          foreach ($ekstrakurikuler as $ekstra) {
            $nama_ekstra = htmlspecialchars($ekstra['nama_ekstra']);
            $jadwal      = !empty($ekstra['hari']) ? htmlspecialchars($ekstra['hari']) : null;
            $pembina     = !empty($ekstra['nama']) ? htmlspecialchars($ekstra['nama']) : null;
            $id_ekstra   = $ekstra['id_ekstra'];

            // pilih ikon sesuai nama ekstra
            $icon_class = isset($icon_map[$nama_ekstra]) ? $icon_map[$nama_ekstra] : $icon_map['default'];
            ?>
            <div class="col-sm-6 col-md-4 col-lg-3">
              <div class="feature-box shadow-sm p-4 h-100 rounded bg-white text-center">
                
                <!-- Ikon -->
                <div class="icon mb-3 text-primary fs-1">
                  <i class="fas <?= $icon_class; ?>"></i>
                </div>

                <!-- Nama -->
                <h5 class="fw-bold mb-2"><?= $nama_ekstra; ?></h5>

                <!-- Jadwal -->
                <?php if ($jadwal): ?>
                  <small class="d-block mb-1">
                    <i class="fas fa-calendar-alt"></i> <?= $jadwal; ?>
                  </small>
                <?php endif; ?>

                <!-- Pembina -->
                <?php if ($pembina): ?>
                  <small class="d-block mb-2">
                    <i class="fas fa-user-tie"></i> Pembina: <?= $pembina; ?>
                  </small>
                <?php endif; ?>

                <!-- Tombol -->
                <div class="mt-3">
                  <a href="detail_ekstra.php?id=<?= $id_ekstra; ?>" 
                    class="btn btn-outline-primary btn-sm rounded-pill px-3">
                    <i class="fas fa-info-circle"></i> Lihat Detail
                  </a>
                </div>

              </div>
            </div>
            <?php
          }
        } else {
          echo "<p class='text-center col-12'>Belum ada data ekstrakurikuler.</p>";
        }
        ?>
      </div>
    </div>
  </section>

  <!-- Footer -->
  <footer>
    &copy; <?= date('Y'); ?> EkstraKu - Skanida. All Rights Reserved. | <a href="#">Privacy</a> | <a href="#">Contact</a>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Navbar scroll effect
    window.addEventListener("scroll", function() {
      let nav = document.querySelector(".navbar");
      nav.classList.toggle("scrolled", window.scrollY > 50);
    });
  </script>
</body>
</html>