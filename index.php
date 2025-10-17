<?php
require_once 'config/database.php';
$pageTitle = 'Dashboard - Toko Parfum Wangi Abadi';
include 'includes/header.php';
include 'includes/navbar.php';

// Query untuk mendapatkan statistik
$conn = getConnection();

// Total Transaksi
$result = $conn->query("SELECT COUNT(*) as total FROM transaksi");
$totalTransaksi = $result->fetch_assoc()['total'];

// Total Pendapatan
$result = $conn->query("SELECT COALESCE(SUM(total_harga), 0) as total FROM transaksi");
$totalPendapatan = $result->fetch_assoc()['total'];

// Barang Terlaris
$result = $conn->query("
    SELECT b.nama_barang, SUM(t.jumlah) as jumlah_terjual
    FROM transaksi t
    JOIN barang b ON t.id_barang = b.id_barang
    GROUP BY b.id_barang, b.nama_barang
    ORDER BY jumlah_terjual DESC
    LIMIT 3
");

$topParfums = [];
if ($result) {
    while($row = $result->fetch_assoc()) {
        $topParfums[] = $row;
    }
}

$conn->close();
?>

<div class="container">
    <!-- Header -->
    <div class="text-center my-4">
        <h1 class="display-5 fw-bold" style="color: var(--primary-color);">
            <i class="bi bi-speedometer2"></i> Dashboard
        </h1>
        <p class="text-muted">Ringkasan data transaksi dan statistik toko parfum</p>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-4 mb-5">
        <!-- Total Transaksi -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Total Transaksi</h6>
                            <h2 class="mb-0 fw-bold"><?php echo number_format($totalTransaksi); ?></h2>
                            <small class="text-muted">Jumlah transaksi parfum</small>
                        </div>
                        <div>
                            <i class="bi bi-bag-check-fill" style="font-size: 3rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Pendapatan -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Total Pendapatan</h6>
                            <h2 class="mb-0 fw-bold">
                                Rp <?php echo number_format($totalPendapatan, 0, ',', '.'); ?>
                            </h2>
                            <small class="text-muted">Total transaksi parfum</small>
                        </div>
                        <div>
                            <i class="bi bi-cash-stack" style="font-size: 3rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Parfum Terlaris - Tampilan Baru (List) -->
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-trophy-fill"></i> Top 3 Parfum Terlaris
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($topParfums)): ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($topParfums as $index => $parfum): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center" style="background: transparent;">
                                    <div>
                                        <span class="fw-bold me-2"><?php echo $index + 1; ?>.</span>
                                        <?php echo htmlspecialchars($parfum['nama_barang']); ?>
                                    </div>
                                    <span class="badge badge-stock-ok rounded-pill"><?php echo number_format($parfum['jumlah_terjual']); ?> terjual</span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p class="text-center text-muted mb-0">Belum ada data transaksi untuk ditampilkan.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Info Cards -->
    <div class="row g-4 mb-4">
        <!-- Tentang Sistem -->
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-info-circle"></i> Tentang Sistem
                    </h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <i class="bi bi-check-circle-fill"></i>
                            Manajemen data Parfum, Pelanggan, dan Transaksi
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-check-circle-fill"></i>
                            Validasi otomatis untuk stok dan input
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-check-circle-fill"></i>
                            Perhitungan total harga otomatis
                        </li>
                        <li>
                            <i class="bi bi-check-circle-fill"></i>
                            Laporan dan filter transaksi
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Fitur Validasi -->
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-shield-check"></i> Fitur Validasi
                    </h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <i class="bi bi-check-circle-fill"></i>
                            Nama parfum otomatis HURUF KAPITAL
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-check-circle-fill"></i>
                            Stok tidak boleh negatif
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-check-circle-fill"></i>
                            Jumlah beli tidak melebihi stok
                        </li>
                        <li>
                            <i class="bi bi-check-circle-fill"></i>
                            Stok berkurang otomatis saat transaksi
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
