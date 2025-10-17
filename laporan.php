<?php
require_once 'config/database.php';
$pageTitle = 'Laporan Transaksi - Toko Parfum Wangi Abadi';
include 'includes/header.php';
include 'includes/navbar.php';

// Query transaksi
$conn = getConnection();
$sql = "
    SELECT t.*, p.nama_pembeli, b.nama_barang, b.harga
    FROM transaksi t
    JOIN pembeli p ON t.id_pembeli = p.id_pembeli
    JOIN barang b ON t.id_barang = b.id_barang
";
$sql .= " ORDER BY t.tanggal DESC";

$result = $conn->query($sql);
$transaksiList = [];
$totalPendapatan = 0;

while ($row = $result->fetch_assoc()) {
    $transaksiList[] = $row;
    $totalPendapatan += $row['total_harga'];
}

$totalTransaksi = count($transaksiList);
$conn->close();
?>

<div class="container">
    <!-- Header -->
    <div class="mb-4">
        <h2 class="fw-bold">
            <i class="bi bi-file-text-fill" style="color: var(--primary-color);"></i> Laporan Transaksi Parfum
        </h2>
        <p class="text-muted mb-0">Lihat dan cetak laporan transaksi parfum</p>
    </div>

    <!-- Summary Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="card stat-card emerald">
                <div class="card-body">
                    <h6 class="text-muted">Total Transaksi</h6>
                    <h2 class="fw-bold"><?php echo number_format($totalTransaksi); ?></h2>
                    <small class="text-muted">
                        Semua transaksi
                    </small>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card stat-card green">
                <div class="card-body">
                    <h6 class="text-muted">Total Pendapatan</h6>
                    <h2 class="fw-bold">Rp <?php echo number_format($totalPendapatan, 0, ',', '.'); ?></h2>
                    <small class="text-muted">
                        Semua transaksi
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Tombol Print (FITUR BONUS) -->
    <div class="text-end mb-3 no-print">
        <button onclick="window.print()" class="btn btn-success btn-lg">
            <i class="bi bi-printer"></i> Cetak Laporan
        </button>
    </div>

    <!-- Header untuk Print -->
    <div class="d-none" id="printHeader">
        <div class="text-center mb-4">
            <h2>LAPORAN TRANSAKSI PARFUM</h2>
            <h5>Toko Parfum Wangi Abadi</h5>
            <p>Dicetak pada: <?php echo date('d F Y, H:i'); ?></p>
        </div>
    </div>

    <!-- Tabel Laporan -->
    <div class="card">
        <div class="card-header no-print">
            <h5 class="mb-0">
                <i class="bi bi-table"></i> Daftar Transaksi
            </h5>
        </div>
        <div class="card-body">
            <?php if (empty($transaksiList)): ?>
            <div class="alert alert-info text-center">
                <i class="bi bi-info-circle"></i>
                Belum ada transaksi
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover table-striped">
                    <thead style="background-color: var(--primary-color); color: white;">
                        <tr>
                            <th>ID</th>
                            <th>Tanggal</th>
                            <th>Pelanggan</th>
                            <th>Parfum</th>
                            <th>Harga Satuan</th>
                            <th>Jumlah</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($transaksiList as $transaksi): ?>
                        <tr>
                            <td><?php echo $transaksi['id_transaksi']; ?></td>
                            <td><?php echo date('d M Y, H:i', strtotime($transaksi['tanggal'])); ?></td>
                            <td><?php echo $transaksi['nama_pembeli']; ?></td>
                            <td><?php echo $transaksi['nama_barang']; ?></td>
                            <td>Rp <?php echo number_format($transaksi['harga'], 0, ',', '.'); ?></td>
                            <td><?php echo $transaksi['jumlah']; ?> botol</td>
                            <td>Rp <?php echo number_format($transaksi['total_harga'], 0, ',', '.'); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot style="background-color: #f0f9ff;">
                        <tr>
                            <th colspan="6" class="text-end">TOTAL:</th>
                            <th>Rp <?php echo number_format($totalPendapatan, 0, ',', '.'); ?></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
@media print {
    #printHeader {
        display: block !important;
    }
}
</style>
<?php include 'includes/footer.php'; ?>
