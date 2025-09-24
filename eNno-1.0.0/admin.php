<?php

$koneksi = new mysqli("localhost", "root", "", "presensi_ekstra");

if ($koneksi->connect_error) {
    die("Koneksi gagal: " . $koneksi->connect_error);
}

// Statistik jumlah
$jumlah_ekstra = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM tb_ekstrakurikuler"))['total'];
$jumlah_pembina = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM tb_pembina"))['total'];
$jumlah_peserta = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM tb_peserta_ekstra"))['total'];

// Ekstra populer (berdasarkan jumlah peserta terbanyak)
$query_ekstra_populer = mysqli_query($koneksi, "SELECT e.nama_ekstra, COUNT(p.nis) as total 
    FROM tb_ekstrakurikuler e 
    LEFT JOIN tb_peserta_ekstra p ON e.id_ekstra = p.id_ekstra 
    GROUP BY e.id_ekstra 
    ORDER BY total DESC 
    LIMIT 6");

// Presensi terbaru
$query_presensi_terakhir = mysqli_query($koneksi, "SELECT pr.*, ps.nis as peserta, e.nama_ekstra 
    FROM tb_presensi pr 
    JOIN tb_peserta_ekstra ps ON pr.nis = ps.nis 
    JOIN tb_ekstrakurikuler e ON pr.id_ekstra = e.id_ekstra 
    ORDER BY pr.tanggal DESC 
    LIMIT 5");
?>

<section class="section dashboard">
    <div class="row">
        <div class="col-lg-8">
            <div class="row">

                <!-- Ekstrakurikuler -->
                <div class="col-xxl-4 col-md-6">
                    <div class="card info-card sales-card">
                        <div class="card-body">
                            <h5 class="card-title">Ekstrakurikuler</h5>
                            <div class="d-flex align-items-center">
                                <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                    <i class="bi bi-star"></i>
                                </div>
                                <div class="ps-3">
                                    <h6><?= $jumlah_ekstra ?></h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pembina -->
                <div class="col-xxl-4 col-md-6">
                    <div class="card info-card revenue-card">
                        <div class="card-body">
                            <h5 class="card-title">Pembina</h5>
                            <div class="d-flex align-items-center">
                                <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                    <i class="bi bi-person-badge"></i>
                                </div>
                                <div class="ps-3">
                                    <h6><?= $jumlah_pembina ?></h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Peserta -->
                <div class="col-xxl-4 col-md-6">
                    <div class="card info-card customers-card">
                        <div class="card-body">
                            <h5 class="card-title">Peserta</h5>
                            <div class="d-flex align-items-center">
                                <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                    <i class="bi bi-people"></i>
                                </div>
                                <div class="ps-3">
                                    <h6><?= $jumlah_peserta ?></h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Grafik Kehadiran -->
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Grafik Kehadiran <span id="label-grafik">/Minggu Ini</span></h5>
                            <div id="grafikKehadiran"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sisi Kanan -->
        <div class="col-lg-4">
            <!-- Ekstra Populer -->
            <div class="card shadow-sm border-0">
                <div class="card-body pb-0">
                    <h5 class="card-title mb-3">
                        <i class="bi bi-people-fill me-2" style="color:#4154f1;"></i> Peserta Ekstrakurikuler
                    </h5>
                    <div class="table-responsive">
                        <table class="table table-borderless align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Ekstra</th>
                                    <th class="text-center">Peserta</th>
                                    <th class="text-center">Progress</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($row = mysqli_fetch_assoc($query_ekstra_populer)): ?>
                                <tr>
                                    <td>
                                        <span class="fw-semibold"><?= htmlspecialchars($row['nama_ekstra']) ?></span>
                                    </td>
                                    <td class="text-center fw-bold"><?= $row['total'] ?></td>
                                    <td style="width: 40%;">
                                        <div class="progress" style="height: 6px;">
                                            <div class="progress-bar bg-success" role="progressbar" 
                                                style="width: <?= min($row['total']*10,100) ?>%">
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>


            <!-- Presensi Terbaru -->
            <div class="card shadow-sm border-0">
                <div class="card-body pb-1">
                    <h5 class="card-title mb-3">
                        <i class="bi bi-clock-history text-primary me-2" style="color:#4154f1;"></i> Presensi Terbaru
                    </h5>
                    <div class="list-group list-group-flush">
                        <?php while($presensi = mysqli_fetch_assoc($query_presensi_terakhir)): ?>
                            <?php
                                // Badge warna sesuai status
                                $status = $presensi['status'];
                                $badgeClass = match($status) {
                                    'H' => 'success',
                                    'A' => 'danger',
                                    'I' => 'warning',
                                    'S' => 'info',
                                    default => 'secondary'                              
                                };
                            ?>
                            <div class="list-group-item d-flex align-items-start border-0 px-0 pb-2">
                                <!-- Avatar inisial nama -->
                                <div class="rounded-circle text-white d-flex align-items-center justify-content-center me-3" style="width:40px; height:40px; font-size:0.9rem;">
                                    <i class="bi bi-star-fill text-warning"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1"><?= htmlspecialchars($presensi['peserta']) ?> 
                                        <small class="text-muted">- <?= htmlspecialchars($presensi['nama_ekstra']) ?></small>
                                    </h6>
                                    <p class="text-muted small mb-0">
                                        <i class="bi bi-calendar-event me-1"></i>
                                        <?= date('d M Y H:i', strtotime($presensi['tanggal'])) ?>
                                    </p>
                                </div>
                                <span class="badge bg-<?= $badgeClass ?> rounded-pill align-self-center">
                                    <?= htmlspecialchars($status) ?>
                                </span>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- Dummy grafik (ganti dengan real AJAX fetch jika sudah siap) -->
<script>
    document.addEventListener("DOMContentLoaded", () => {
        new ApexCharts(document.querySelector("#grafikKehadiran"), {
            series: [{
                name: 'Kehadiran',
                data: [10, 14, 13, 15, 18, 17, 19] // contoh data
            }],
            chart: {
                height: 350,
                type: 'area',
                toolbar: { show: false }
            },
            markers: { size: 4 },
            colors: ['#00b894'],
            fill: {
                type: "gradient",
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.3,
                    opacityTo: 0.4,
                    stops: [0, 90, 100]
                }
            },
            dataLabels: { enabled: false },
            stroke: { curve: 'smooth', width: 2 },
            xaxis: {
                type: 'category',
                categories: ["Senin", "Selasa", "Rabu", "Kamis", "Jumat", "Sabtu", "Minggu"]
            },
            tooltip: {
                y: {
                    formatter: function (val) {
                        return val + " hadir";
                    }
                }
            }
        }).render();
    });
</script>
