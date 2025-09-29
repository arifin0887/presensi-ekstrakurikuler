<?php
// Pastikan session hanya dimulai satu kali
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<!-- ======= Header ======= -->
<header id="header" class="header fixed-top d-flex align-items-center">

    <div class="logo d-flex align-items-center" style="cursor:default;">
        <img src="../eNno-1.0.0/assets/img/smk.png" width="35px" alt="Logo">
        <span class="d-none d-lg-block">EkstraKu</span>
        <i class="bi bi-list toggle-sidebar-btn" style="margin-left: 8rem;"></i>
    </div>
    <!-- End Logo -->

    <!-- <div class="search-bar">
        <form class="search-form d-flex align-items-center" method="POST" action="#">
            <input type="text" name="query" placeholder="Search" title="Enter search keyword">
            <button type="submit" title="Search"><i class="bi bi-search"></i></button>
        </form>
    </div>End Search Bar -->

    <nav class="header-nav ms-auto">
        <ul class="d-flex align-items-center">

            <li class="nav-item dropdown pe-3">
                <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown">
                    <span class="d-none d-md-block dropdown-toggle ps-2">
                        <?php
                        if (!empty($_SESSION['loggedin']) && !empty($_SESSION['username'])) {
                            echo htmlspecialchars($_SESSION['username']);
                        } elseif (!empty($_COOKIE['loggedin_user'])) {
                            echo htmlspecialchars($_COOKIE['loggedin_user']);
                        } else {
                            echo 'Guest';
                        }
                        ?>
                    </span>
                </a>

                <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile shadow rounded-4 border-0">
                <!-- Header -->
                <li class="dropdown-header text-center py-3 bg-light rounded-top-4">
                    <div class="d-flex flex-column align-items-center">
                    <!-- Avatar Default -->
                    <div class="avatar rounded-circle text-white d-flex align-items-center justify-content-center mb-2" style="width: 50px; height: 50px; font-size: 20px; background-color: #4154f1;">
                        <i class="bi bi-person-fill"></i>
                    </div>
                    <!-- Username -->
                    <h6 class="mb-0 fw-bold">
                        <?= isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Guest' ?>
                    </h6>
                    <!-- Role -->
                    <span class="badge bg-primary-subtle mt-1 px-3 py-1 rounded-pill" style="color: #4154f1;">
                        <?= isset($_SESSION['role']) ? ucfirst(htmlspecialchars($_SESSION['role'])) : '-' ?>
                    </span>
                    </div>
                </li>

                <!-- Divider -->
                <li><hr class="dropdown-divider"></li>

                <!-- Logout -->
                <li>
                    <a class="dropdown-item d-flex align-items-center py-2 text-danger" href="logout.php">
                    <i class="bi bi-box-arrow-right me-2"></i>
                    <span>Log Out</span>
                    </a>
                </li>
                </ul>
                <!-- End Profile Dropdown -->
            </li><!-- End Profile Nav -->

        </ul>
    </nav><!-- End Icons Navigation -->

</header><!-- End Header -->
