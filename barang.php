<?php
require_once 'config/database.php';

// Handle Form Submit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    header('Content-Type: application/json');
    $conn = getConnection();
    $response = ['success' => false];
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'add') {
            // Tambah Barang Baru
            do {
                $id_barang = 'PRF-' . strtoupper(substr(uniqid(), -5));
                $checkStmt = $conn->prepare("SELECT id_barang FROM barang WHERE id_barang = ?");
                $checkStmt->bind_param("s", $id_barang);
                $checkStmt->execute();
                $checkStmt->store_result();
                $isDuplicate = $checkStmt->num_rows > 0;
                $checkStmt->close();
            } while ($isDuplicate);

            $nama_barang = strtoupper($_POST['nama_barang']);
            $harga = $_POST['harga'];
            $volume_ml = $_POST['volume_ml'];
            $stok = $_POST['stok'];
            
            if ($stok < 0) {
                $response['message'] = 'Stok tidak boleh negatif!';
            } else {
                // Tambahkan id_barang ke query INSERT
                $stmt = $conn->prepare("INSERT INTO barang (id_barang, nama_barang, volume_ml, harga, stok) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("ssidi", $id_barang, $nama_barang, $volume_ml, $harga, $stok);
                
                if ($stmt->execute()) {
                    $response['success'] = true;
                    $response['data'] = [
                        'id_barang' => $id_barang, 
                        'nama_barang' => $nama_barang, 
                        'volume_ml' => $volume_ml, 
                        'harga' => $harga, 
                        'stok' => $stok
                    ];
                } else {
                    $response['message'] = 'Gagal menambahkan parfum.';
                }
                $stmt->close();
            }
        } elseif ($_POST['action'] == 'edit') {
            // Edit Barang
            $id_barang = $_POST['id_barang'];
            $nama_barang = strtoupper($_POST['nama_barang']);
            $harga = $_POST['harga'];
            $volume_ml = $_POST['volume_ml'];
            $stok = $_POST['stok'];
            
            if ($stok < 0) {
                $response['message'] = 'Stok tidak boleh negatif!';
            } else {
                $stmt = $conn->prepare("UPDATE barang SET nama_barang = ?, volume_ml = ?, harga = ?, stok = ? WHERE id_barang = ?");
                $stmt->bind_param("sidis", $nama_barang, $volume_ml, $harga, $stok, $id_barang);
                
                if ($stmt->execute()) {
                    $response['success'] = true;
                    $response['data'] = ['id_barang' => $id_barang, 'nama_barang' => $nama_barang, 'volume_ml' => $volume_ml, 'harga' => $harga, 'stok' => $stok];
                } else {
                    $response['message'] = 'Gagal mengupdate parfum.';
                }
                $stmt->close();
            }
        }
    }
    $conn->close();
    echo json_encode($response);
    exit();
}

// Handle Delete
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    header('Content-Type: application/json');
    $conn = getConnection();
    $id_barang = $_GET['id'];

    $checkStmt = $conn->prepare("SELECT COUNT(*) as count FROM transaksi WHERE id_barang = ?");
    $checkStmt->bind_param("s", $id_barang);
    $checkStmt->execute();
    $result = $checkStmt->get_result()->fetch_assoc();
    $checkStmt->close();

    if ($result['count'] == 0) {
        $stmt = $conn->prepare("DELETE FROM barang WHERE id_barang = ?");
        $stmt->bind_param("s", $id_barang);
        $success = $stmt->execute();
        $stmt->close();
        echo json_encode(['success' => $success]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Parfum tidak dapat dihapus karena sudah memiliki riwayat penjualan.']);
    }
    $conn->close();
    exit(); // Hentikan eksekusi setelah response JSON
}

$pageTitle = 'Manajemen Parfum - Toko Parfum Wangi Abadi';
include 'includes/header.php';
include 'includes/navbar.php';

// Ambil Data Barang untuk ditampilkan di tabel
$conn = getConnection();
$result = $conn->query("SELECT * FROM barang ORDER BY id_barang ASC");
$barangList = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $barangList[] = $row;
    }
}
$conn->close();
?>

<div class="container">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold">
                <i class="bi bi-droplet-fill" style="color: var(--primary-color);"></i> Manajemen Parfum
            </h2>
            <p class="text-muted mb-0">Kelola data parfum dan stok</p>
        </div>
        <button class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#modalBarang">
            <i class="bi bi-plus-circle"></i> Tambah Parfum
        </button>
    </div>

    <!-- Tabel Parfum -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="bi bi-table"></i> Daftar Parfum
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="tableBarang" class="table table-hover table-striped">
                    <thead style="background-color: var(--primary-color); color: white;">
                        <tr>
                            <th>ID</th>
                            <th>Nama Parfum</th>
                            <th>Volume</th>
                            <th>Harga</th>
                            <th>Stok</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($barangList as $barang): ?>
                        <tr>
                            <td><?php echo $barang['id_barang']; ?></td>
                            <td><?php echo htmlspecialchars($barang['nama_barang']); ?></td>
                            <td><?php echo $barang['volume_ml']; ?> ml</td>
                            <td>Rp <?php echo number_format($barang['harga'], 0, ',', '.'); ?></td>
                            <td>
                                <span class="badge <?php echo $barang['stok'] < 5 ? 'badge-stock-low' : 'badge-stock-ok'; ?>">
                                    <?php echo $barang['stok']; ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-warning" onclick="editBarang(<?php echo htmlspecialchars(json_encode($barang)); ?>)">
                                    Edit
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="deleteBarang('<?php echo $barang['id_barang']; ?>', this)">
                                    Hapus
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

<!-- Modal Tambah/Edit Parfum -->
<div class="modal fade" id="modalBarang" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius: 1rem;">
            <div class="modal-header text-white" style="background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);">
                <h5 class="modal-title" id="modalTitle">Tambah Parfum Baru</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="formBarang">
                <div class="modal-body">
                    <input type="hidden" name="action" id="formAction" value="add">                    
                    <div class="mb-3" id="id_field_container" style="display: none;">
                        <label for="id_barang_display" class="form-label">ID Parfum</label>
                        <input type="text" class="form-control" id="id_barang_display" disabled>
                        <input type="hidden" name="id_barang" id="id_barang">
                    </div>
                    
                    <div class="mb-3">
                        <label for="nama_barang" class="form-label">Nama Parfum</label>
                        <input type="text" class="form-control" id="nama_barang" name="nama_barang" required>
                        <small class="text-muted">* Akan otomatis diubah ke HURUF KAPITAL</small>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="volume_ml" class="form-label">Volume (ml)</label>
                            <input type="number" class="form-control" id="volume_ml" name="volume_ml" required min="1" value="50">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="harga" class="form-label">Harga (Rp)</label>
                            <input type="number" class="form-control" id="harga" name="harga" required min="0">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="stok" class="form-label">Stok</label>
                        <input type="number" class="form-control" id="stok" name="stok" required min="0">
                        <small class="text-muted">* Stok tidak boleh negatif</small>
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
    var table = $('#tableBarang').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json'
        }
    });

    // Menggunakan event delegation untuk memastikan form di dalam modal juga tertangani
    $('#formBarang').on('submit', function(e) {
        // 1. Mencegah form dikirim secara normal (yang menyebabkan halaman refresh)
        e.preventDefault();

        var form = $(this);
        var action = $('#formAction').val();

        // 2. Kirim data menggunakan AJAX
        $.ajax({
            url: 'barang.php',
            type: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(response) {
                // 3. Proses response dari server
                if (response.success) {
                    $('#modalBarang').modal('hide');
                    var message = (action === 'add') ? 'Parfum berhasil ditambahkan!' : 'Parfum berhasil diupdate!';
                    showSuccess(message);

                    var barang = response.data;
                    var buttons = `<button class="btn btn-sm btn-warning" onclick='editBarang(${JSON.stringify(barang)})'>Edit</button> <button class="btn btn-sm btn-danger" onclick="deleteBarang('${barang.id_barang}', this)">Hapus</button>`;
                    var stokBadge = `<span class="badge ${barang.stok < 5 ? 'badge-stock-low' : 'badge-stock-ok'}">${barang.stok}</span>`;

                    if (action === 'add') {
                        table.row.add([barang.id_barang, barang.nama_barang, `${barang.volume_ml} ml`, `Rp ${Number(barang.harga).toLocaleString('id-ID')}`, stokBadge, buttons]).draw(false);
                    } else {
                        var rowNode = table.rows().nodes().toArray().find(row => $(row).find('td:first').text() === barang.id_barang);
                        if (rowNode) {
                            table.row(rowNode).data([barang.id_barang, barang.nama_barang, `${barang.volume_ml} ml`, `Rp ${Number(barang.harga).toLocaleString('id-ID')}`, stokBadge, buttons]).draw(false);
                        }
                    }
                } else {
                    showError(response.message || 'Terjadi kesalahan.');
                }
            }
        });
    });
});

function deleteBarang(id, element) {
    Swal.fire({
        title: 'Apakah Anda yakin?',
        text: "Data parfum ini akan dihapus secara permanen!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: 'barang.php?action=delete&id=' + id,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        showSuccess('Parfum berhasil dihapus.');
                        // Hapus baris dari tabel DataTables
                        $('#tableBarang').DataTable().row($(element).parents('tr')).remove().draw();
                    } else {
                        showError(response.message || 'Gagal menghapus parfum.');
                    }
                }
            });
        }
    });
}

function editBarang(barang) {
    $('#modalTitle').text('Edit Parfum');
    $('#formAction').val('edit');

    // Tampilkan ID dan buat non-editable
    $('#id_field_container').show();
    $('#id_barang_display').val(barang.id_barang);
    $('#id_barang').val(barang.id_barang);

    $('#nama_barang').val(barang.nama_barang);
    $('#volume_ml').val(barang.volume_ml);
    $('#harga').val(barang.harga);
    $('#stok').val(barang.stok);
    $('#modalBarang').modal('show');
}

$('#modalBarang').on('hidden.bs.modal', function() {
    $('#formBarang')[0].reset();
    $('#id_field_container').hide();
    $('#modalTitle').text('Tambah Parfum Baru');
    $('#formAction').val('add');
    $('#volume_ml').val(50); // Reset volume ke default
});
</script>

<?php include 'includes/footer.php'; ?>
