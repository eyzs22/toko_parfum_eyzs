<?php
require_once 'config/database.php';

// Handle Form Submit
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add') {
    header('Content-Type: application/json');
    $conn = getConnection();
    $response = ['success' => false];
    
    $id_pembeli = $_POST['id_pembeli'];
    $id_barang = $_POST['id_barang'];
    $jumlah = $_POST['jumlah'];
    
    $conn->begin_transaction();
    try {
        // Cek stok barang (dengan locking untuk keamanan)
        $stmt = $conn->prepare("SELECT nama_barang, harga, stok FROM barang WHERE id_barang = ? FOR UPDATE");
        $stmt->bind_param("s", $id_barang);
        $stmt->execute();
        $barang = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($barang && $jumlah > 0 && $jumlah <= $barang['stok']) {
            $total_harga = $barang['harga'] * $jumlah;
            $id_transaksi = 'TRX-' . strtoupper(substr(uniqid(), -5));

            // Insert transaksi
            $stmt = $conn->prepare("INSERT INTO transaksi (id_transaksi, id_pembeli, id_barang, jumlah, total_harga) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssid", $id_transaksi, $id_pembeli, $id_barang, $jumlah, $total_harga);
            $stmt->execute();
            $stmt->close();

            // Update stok barang
            $new_stok = $barang['stok'] - $jumlah;
            $stmt = $conn->prepare("UPDATE barang SET stok = ? WHERE id_barang = ?");
            $stmt->bind_param("is", $new_stok, $id_barang);
            $stmt->execute();
            $stmt->close();

            $conn->commit();
            $response['success'] = true;
            
            // Ambil nama pembeli dan nama barang untuk response JSON yang lengkap
            $dataStmt = $conn->prepare("
                SELECT p.nama_pembeli, b.nama_barang 
                FROM pembeli p, barang b 
                WHERE p.id_pembeli = ? AND b.id_barang = ?
            ");
            $dataStmt->bind_param("ss", $id_pembeli, $id_barang);
            $dataStmt->execute();
            $extraData = $dataStmt->get_result()->fetch_assoc();
            $dataStmt->close();

            $nama_pembeli = $extraData['nama_pembeli'] ?? 'N/A';

            $response['data'] = ['id_transaksi' => $id_transaksi, 'tanggal' => date('d M Y, H:i'), 'nama_pembeli' => $nama_pembeli, 'nama_barang' => $extraData['nama_barang'], 'harga' => $barang['harga'], 'jumlah' => $jumlah, 'total_harga' => $total_harga];
        } else {
            $conn->rollback();
            $response['message'] = 'Jumlah parfum melebihi stok yang tersedia atau tidak valid.';
        }
    } catch (Exception $e) {
        $conn->rollback();
        $response['message'] = 'Terjadi kesalahan pada database.';
    }
    $conn->close();
    echo json_encode($response);
    exit();
}

// Handle Delete
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) { // AJAX Delete
    header('Content-Type: application/json');
    $conn = getConnection();
    $id_transaksi = $_GET['id'];
    $response = ['success' => false];
    
    $conn->begin_transaction();
    try {
        // Ambil data transaksi untuk mengembalikan stok
        $stmt = $conn->prepare("SELECT id_barang, jumlah FROM transaksi WHERE id_transaksi = ?");
        $stmt->bind_param("s", $id_transaksi);
        $stmt->execute();
        $result = $stmt->get_result();
        $transaksi = $result->fetch_assoc();
        $stmt->close();
        
        if ($transaksi) {
            // Kembalikan stok
            $stmt = $conn->prepare("UPDATE barang SET stok = stok + ? WHERE id_barang = ?");
            $stmt->bind_param("is", $transaksi['jumlah'], $transaksi['id_barang']);
            $stmt->execute();
            $stmt->close();
            
            // Hapus transaksi
            $stmt = $conn->prepare("DELETE FROM transaksi WHERE id_transaksi = ?");
            $stmt->bind_param("s", $id_transaksi);
            $stmt->execute();
            $stmt->close();

            $conn->commit();
            $response['success'] = true;
        }
    } catch (Exception $e) {
        $conn->rollback();
        $response['message'] = 'Terjadi kesalahan pada database.';
    }
    $conn->close();
    echo json_encode($response);
    exit();
}

// ==================================================================
// Jika bukan request AJAX (POST/GET), maka tampilkan halaman HTML
// ==================================================================
$pageTitle = 'Transaksi Parfum - Toko Parfum Wangi Abadi';
include 'includes/header.php';
include 'includes/navbar.php';

// Ambil Data
$conn = getConnection();

// Data Transaksi
$result = $conn->query("
    SELECT t.*, p.nama_pembeli, b.nama_barang, b.harga
    FROM transaksi t
    JOIN pembeli p ON t.id_pembeli = p.id_pembeli
    JOIN barang b ON t.id_barang = b.id_barang
    ORDER BY t.tanggal DESC
");
$transaksiList = [];
while ($row = $result->fetch_assoc()) {
    $transaksiList[] = $row;
}

// Data Pembeli
$pembeliList = [];
$result = $conn->query("SELECT * FROM pembeli ORDER BY nama_pembeli");
while ($row = $result->fetch_assoc()) {
    $pembeliList[] = $row;
}

// Data Barang
$barangList = [];
$result = $conn->query("SELECT * FROM barang WHERE stok > 0 ORDER BY nama_barang");
while ($row = $result->fetch_assoc()) {
    $barangList[] = $row;
}

$conn->close();
?>

<div class="container">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold">
                <i class="bi bi-bag-check-fill" style="color: var(--primary-color);"></i> Transaksi Parfum
            </h2>
            <p class="text-muted mb-0">Catat transaksi parfum</p>
        </div>
        <button class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#modalTransaksi">
            <i class="bi bi-plus-circle"></i> Tambah Transaksi
        </button>
    </div>

    <!-- Tabel Penjualan -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="bi bi-table"></i> Daftar Transaksi
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="tableTransaksi" class="table table-hover table-striped">
                    <thead style="background-color: var(--primary-color); color: white;">
                        <tr>
                            <th>ID</th>
                            <th>Tanggal</th>
                            <th>Pelanggan</th>
                            <th>Parfum</th>
                            <th>Harga Satuan</th>
                            <th>Jumlah</th>
                            <th>Total</th>
                            <th class="text-center">Aksi</th>
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
                            <td class="text-center">
                                <button class="btn btn-sm btn-danger" onclick="deleteTransaksi('<?php echo $transaksi['id_transaksi']; ?>', this)">
                                    <i class="bi bi-trash"></i> Hapus 
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Penjualan -->
<div class="modal fade" id="modalTransaksi" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header text-white" style="background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);">
                <h5 class="modal-title">Tambah Transaksi Baru</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="formTransaksi">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    
                    <div class="mb-3">
                        <label for="id_pembeli" class="form-label">Pelanggan</label>
                        <select class="form-select" id="id_pembeli" name="id_pembeli" required>
                            <option value="">Pilih Pelanggan</option>
                            <?php foreach ($pembeliList as $pembeli): ?>
                            <option value="<?php echo $pembeli['id_pembeli']; ?>">
                                <?php echo $pembeli['nama_pembeli']; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="id_barang" class="form-label">Parfum</label>
                        <select class="form-select" id="id_barang" name="id_barang" required onchange="updateEstimasi()">
                            <option value="">Pilih Parfum</option>
                            <?php foreach ($barangList as $barang): ?>
                            <option value="<?php echo $barang['id_barang']; ?>" 
                                    data-harga="<?php echo $barang['harga']; ?>"
                                    data-stok="<?php echo $barang['stok']; ?>">
                                <?php echo $barang['nama_barang']; ?> - 
                                Rp <?php echo number_format($barang['harga'], 0, ',', '.'); ?> 
                                (Stok: <?php echo $barang['stok']; ?> botol)
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="jumlah" class="form-label">Jumlah (botol)</label>
                        <input type="number" class="form-control" id="jumlah" name="jumlah" 
                               required min="1" onchange="updateEstimasi()">
                        <small class="text-muted" id="stokInfo"></small>
                    </div>
                    
                    <div id="estimasiTotal" class="alert alert-info d-none">
                        <strong>Estimasi Total:</strong> <span id="totalHarga">Rp 0</span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    var table = $('#tableTransaksi').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json'
        },
        order: [[0, 'desc']]
    });

    // Handle form submission with AJAX
    $('#formTransaksi').on('submit', function(e) {
        // 1. Mencegah form dikirim secara normal
        e.preventDefault();
        var form = $(this);

        $.ajax({
            url: 'transaksi.php',
            // 2. Kirim data via AJAX
            type: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(response) {
                // 3. Proses response dari server
                if (response.success) {
                    $('#modalTransaksi').modal('hide');
                    showSuccess('Transaksi parfum berhasil dicatat!');

                    var trx = response.data;
                    var buttons = `
                        <button class="btn btn-sm btn-danger" onclick="deleteTransaksi('${trx.id_transaksi}', this)">
                            <i class="bi bi-trash"></i> Hapus
                        </button>
                    `;

                    // Add new row to DataTable
                    table.row.add([
                        trx.id_transaksi,
                        trx.tanggal,
                        trx.nama_pembeli,
                        trx.nama_barang,
                        `Rp ${Number(trx.harga).toLocaleString('id-ID')}`,
                        `${trx.jumlah} botol`,
                        `Rp ${Number(trx.total_harga).toLocaleString('id-ID')}`,
                        buttons
                    ]).draw(false);

                } else {
                    showError(response.message || 'Terjadi kesalahan.');
                }
            }
        });
    });
});

function deleteTransaksi(id, element) {
    Swal.fire({
        title: 'Apakah Anda yakin?',
        text: "Transaksi akan dihapus dan stok parfum akan dikembalikan!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: 'transaksi.php?action=delete&id=' + id,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        showSuccess('Transaksi dihapus dan stok dikembalikan.');
                        $('#tableTransaksi').DataTable().row($(element).parents('tr')).remove().draw();
                    } else {
                        showError(response.message || 'Gagal menghapus transaksi.');
                    }
                }
            });
        }
    });
}

function updateEstimasi() {
    var barang = $('#id_barang').find(':selected');
    var harga = parseFloat(barang.data('harga')) || 0;
    var stok = parseInt(barang.data('stok')) || 0;
    var jumlah = parseInt($('#jumlah').val()) || 0;
    
    if (stok > 0) {
        $('#stokInfo').text('* Stok tersedia: ' + stok + ' botol');
        $('#jumlah').attr('max', stok);
    }
    
    if (harga > 0 && jumlah > 0) {
        var total = harga * jumlah;
        $('#totalHarga').text('Rp ' + total.toLocaleString('id-ID'));
        $('#estimasiTotal').removeClass('d-none');
    } else {
        $('#estimasiTotal').addClass('d-none');
    }
}

$('#modalTransaksi').on('hidden.bs.modal', function() {
    $('#formTransaksi')[0].reset();
    $('#estimasiTotal').addClass('d-none');
    $('#stokInfo').text('');
});
</script>

<?php include 'includes/footer.php'; ?>
