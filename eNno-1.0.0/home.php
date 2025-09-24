<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>EkstraKu - Presensi Ekstrakurikuler</title>
  <link href="assets/img/smk.png" rel="icon">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
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
      background: #fff;
      color: #333;
      padding: 80px 20px;
      text-align: center;
    }

    .features h2 {
      font-weight: 700;
      margin-bottom: 40px;
      color: #2980b9;
    }

    .feature-box {
      padding: 30px;
      border-radius: 15px;
      background: #f8f9fa;
      box-shadow: 0 4px 15px rgba(0,0,0,0.1);
      transition: transform 0.3s ease;
    }

    .feature-box:hover {
      transform: translateY(-10px);
    }

    .feature-box h5 {
      font-weight: 600;
      margin-top: 15px;
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

  <!-- Features -->
  <section class="features">
    <div class="container">
      <h2>Kenapa Memilih EkstraKu?</h2>
      <div class="row g-4">
        <div class="col-md-4">
          <div class="feature-box">
            <img src="https://img.icons8.com/color/96/000000/easy.png" alt="Mudah Digunakan">
            <h5>Mudah Digunakan</h5>
            <p>Antarmuka yang sederhana, cepat dipahami oleh siswa, pembina, dan admin.</p>
          </div>
        </div>
        <div class="col-md-4">
          <div class="feature-box">
            <img src="https://img.icons8.com/color/96/000000/computer.png" alt="Responsif">
            <h5>Responsif</h5>
            <p>Bisa diakses dari laptop maupun smartphone dengan tampilan menyesuaikan.</p>
          </div>
        </div>
        <div class="col-md-4">
          <div class="feature-box">
            <img src="https://img.icons8.com/color/96/000000/online.png" alt="Realtime">
            <h5>Realtime</h5>
            <p>Presensi tercatat secara langsung dan dapat dipantau kapan saja.</p>
          </div>
        </div>
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