<nav class="navbar navbar-expand-lg navbar-dark mb-4">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="index.php">
            <i class="bi bi-flower3 me-2 fs-4"></i>
            <span class="fw-bold">Toko Parfum Wangi Abadi</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'active' : ''; ?>" href="index.php">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'barang.php') ? 'active' : ''; ?>" href="barang.php">
                        <i class="bi bi-droplet"></i> Parfum
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'pembeli.php') ? 'active' : ''; ?>" href="pembeli.php">
                        <i class="bi bi-people"></i> Pelanggan
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'transaksi.php') ? 'active' : ''; ?>" href="transaksi.php">
                        <i class="bi bi-bag-check"></i> Transaksi
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'laporan.php') ? 'active' : ''; ?>" href="laporan.php">
                        <i class="bi bi-file-text"></i> Laporan
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>
