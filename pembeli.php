<?php
require_once 'config/database.php';

// Handle Form Submit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    header('Content-Type: application/json');
    $conn = getConnection();
    $response = ['success' => false];
    
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'add') {
            // Tambah Pembeli Baru
            do {
                $id_pembeli = 'PLG-' . strtoupper(substr(uniqid(), -5));
                $checkStmt = $conn->prepare("SELECT id_pembeli FROM pembeli WHERE id_pembeli = ?");
                $checkStmt->bind_param("s", $id_pembeli);
                $checkStmt->execute();
                $checkStmt->store_result();
                $isDuplicate = $checkStmt->num_rows > 0;
                $checkStmt->close();
            } while ($isDuplicate);

            $nama_pembeli = $_POST['nama_pembeli'];
            $alamat = $_POST['alamat'];
            
            $stmt = $conn->prepare("INSERT INTO pembeli (id_pembeli, nama_pembeli, alamat) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $id_pembeli, $nama_pembeli, $alamat);
            
            if ($stmt->execute()) {
                $response['success'] = true;
                $response['data'] = ['id_pembeli' => $id_pembeli, 'nama_pembeli' => $nama_pembeli, 'alamat' => $alamat];
            } else {
                $response['message'] = 'Gagal menambahkan pelanggan.';
            }
            $stmt->close();
        } elseif ($_POST['action'] == 'edit') {
            // Edit Pembeli
            $id_pembeli = $_POST['id_pembeli'];
            $nama_pembeli = $_POST['nama_pembeli'];
            $alamat = $_POST['alamat'];
            
            $stmt = $conn->prepare("UPDATE pembeli SET nama_pembeli = ?, alamat = ? WHERE id_pembeli = ?");
            $stmt->bind_param("sss", $nama_pembeli, $alamat, $id_pembeli);
            
            if ($stmt->execute()) {
                $response['success'] = true;
                $response['data'] = ['id_pembeli' => $id_pembeli, 'nama_pembeli' => $nama_pembeli, 'alamat' => $alamat];
            } else {
                $response['message'] = 'Gagal mengupdate pelanggan.';
            }
            $stmt->close();
        }
    }
    $conn->close();
    echo json_encode($response);
    exit();
}

// Handle Delete
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) { // AJAX Delete
    header('Content-Type: application/json');
    $conn = getConnection();
    $id_pembeli = $_GET['id'];

    // Cek apakah pelanggan pernah ada di transaksi
    $checkStmt = $conn->prepare("SELECT COUNT(*) as count FROM transaksi WHERE id_pembeli = ?");
    $checkStmt->bind_param("s", $id_pembeli);
    $checkStmt->execute();
    $result = $checkStmt->get_result()->fetch_assoc();
    $checkStmt->close();

    if ($result['count'] == 0) {
        $stmt = $conn->prepare("DELETE FROM pembeli WHERE id_pembeli = ?");
        $stmt->bind_param("s", $id_pembeli);
        $success = $stmt->execute();
        $stmt->close();
        echo json_encode(['success' => $success]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Pelanggan tidak dapat dihapus karena sudah memiliki riwayat transaksi.']);
    }
    $conn->close();
    exit();
}

$pageTitle = 'Manajemen Pelanggan - Toko Parfum Wangi Abadi';
include 'includes/header.php';
include 'includes/navbar.php';

// Ambil Data Pembeli
$conn = getConnection();
$result = $conn->query("SELECT * FROM pembeli ORDER BY id_pembeli ASC");
$pembeliList = [];
while ($row = $result->fetch_assoc()) {
    $pembeliList[] = $row;
}
$conn->close();
?>

<div class="container">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold">
                <i class="bi bi-people-fill" style="color: var(--primary-color);"></i> Manajemen Pelanggan
            </h2>
            <p class="text-muted mb-0">Kelola data pelanggan toko parfum</p>
        </div>
        <button class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#modalPembeli">
            <i class="bi bi-plus-circle"></i> Tambah Pelanggan
        </button>
    </div>

    <!-- Tabel Pelanggan -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="bi bi-table"></i> Daftar Pelanggan
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="tablePembeli" class="table table-hover table-striped">
                    <thead style="background-color: var(--primary-color); color: white;">
                        <tr>
                            <th>ID</th>
                            <th>Nama Pelanggan</th>
                            <th>Alamat</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pembeliList as $pembeli): ?>
                        <tr>
                            <td><?php echo $pembeli['id_pembeli']; ?></td>
                            <td><?php echo $pembeli['nama_pembeli']; ?></td>
                            <td><?php echo $pembeli['alamat']; ?></td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-warning" onclick="editPembeli(<?php echo htmlspecialchars(json_encode($pembeli)); ?>)">
                                    Edit
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="deletePembeli('<?php echo $pembeli['id_pembeli']; ?>', this)">
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

<!-- Modal Tambah/Edit Pelanggan -->
<div class="modal fade" id="modalPembeli" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius: 1rem;">
            <div class="modal-header text-white" style="background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);">
                <h5 class="modal-title" id="modalTitle">Tambah Pelanggan Baru</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="formPembeli">
                <div class="modal-body">
                    <input type="hidden" name="action" id="formAction" value="add">
                    
                    <div class="mb-3" id="id_field_container_pembeli" style="display: none;">
                        <label for="id_pembeli_display" class="form-label">ID Pelanggan</label>
                        <input type="text" class="form-control" id="id_pembeli_display" disabled>
                        <input type="hidden" name="id_pembeli" id="id_pembeli">
                    </div>
                    
                    <div class="mb-3">
                        <label for="nama_pembeli" class="form-label">Nama Lengkap</label>
                        <input type="text" class="form-control" id="nama_pembeli" name="nama_pembeli" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="alamat" class="form-label">Alamat</label>
                        <textarea class="form-control" id="alamat" name="alamat" rows="3" required></textarea>
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
    var table = $('#tablePembeli').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json'
        },
        // Menghilangkan search bar default jika tidak ingin digunakan
        // "dom": 'lrtip' 
    });

    // Handle form submission with AJAX
    $('#formPembeli').on('submit', function(e) {
        // 1. Mencegah form dikirim secara normal
        e.preventDefault();
        var form = $(this);
        var action = $('#formAction').val();

        $.ajax({
            // 2. Kirim data via AJAX
            url: 'pembeli.php',
            type: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(response) {
                // 3. Proses response dari server
                if (response.success) {
                    $('#modalPembeli').modal('hide');
                    var message = (action === 'add') ? 'Pelanggan berhasil ditambahkan!' : 'Pelanggan berhasil diupdate!';
                    showSuccess(message);

                    var pembeli = response.data;
                    var buttons = `
                        <button class="btn btn-sm btn-warning" onclick='editPembeli(${JSON.stringify(pembeli)})'>Edit</button>
                        <button class="btn btn-sm btn-danger" onclick="deletePembeli('${pembeli.id_pembeli}', this)">Hapus</button>
                    `;

                    if (action === 'add') {
                        // Add new row to DataTable
                        table.row.add([pembeli.id_pembeli, pembeli.nama_pembeli, pembeli.alamat, buttons]).draw(false);
                    } else {
                        // Find and update existing row
                        var row = table.rows().data().toArray().findIndex(row => row[0] === pembeli.id_pembeli);
                        if (row > -1) {
                            table.row(row).data([pembeli.id_pembeli, pembeli.nama_pembeli, pembeli.alamat, buttons]).draw(false);
                        }
                    }
                } else {
                    showError(response.message || 'Terjadi kesalahan.');
                }
            }
        });
    });
});

function deletePembeli(id, element) {
    Swal.fire({
        title: 'Apakah Anda yakin?',
        text: "Data pelanggan ini akan dihapus secara permanen!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: 'pembeli.php?action=delete&id=' + id,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        showSuccess('Pelanggan berhasil dihapus.');
                        $('#tablePembeli').DataTable().row($(element).parents('tr')).remove().draw();
                    } else {
                        showError(response.message || 'Gagal menghapus pelanggan.');
                    }
                }
            });
        }
    });
}

function editPembeli(pembeli) {
    $('#modalTitle').text('Edit Pelanggan');
    $('#formAction').val('edit');

    // Tampilkan ID dan buat non-editable
    $('#id_field_container_pembeli').show();
    $('#id_pembeli_display').val(pembeli.id_pembeli);
    $('#id_pembeli').val(pembeli.id_pembeli);

    $('#nama_pembeli').val(pembeli.nama_pembeli);
    $('#alamat').val(pembeli.alamat);
    $('#modalPembeli').modal('show');
}

$('#modalPembeli').on('hidden.bs.modal', function() {
    $('#formPembeli')[0].reset();
    $('#id_field_container_pembeli').hide();
    $('#modalTitle').text('Tambah Pelanggan Baru');
    $('#formAction').val('add');
});
</script>

<?php include 'includes/footer.php'; ?>
