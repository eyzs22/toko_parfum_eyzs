<?php
header('Content-Type: application/json');
require_once 'config/database.php';

$conn = getConnection();

// Ambil semua data barang
$result = $conn->query("SELECT id_barang, nama_barang, volume_ml, harga, stok FROM barang ORDER BY id_barang ASC");

$data = [];
while ($row = $result->fetch_assoc()) {
    // Format data sesuai kebutuhan DataTables
    $data[] = [
        $row['id_barang'],
        htmlspecialchars($row['nama_barang']),
        $row['volume_ml'] . ' ml',
        'Rp ' . number_format($row['harga'], 0, ',', '.'),
        '<span class="badge ' . ($row['stok'] < 5 ? 'badge-stock-low' : 'badge-stock-ok') . '">' . $row['stok'] . '</span>',
        '<button class="btn btn-sm btn-warning" onclick=\'editBarang(' . json_encode($row) . ')\'>Edit</button> <button class="btn btn-sm btn-danger" onclick="deleteBarang(\'' . $row['id_barang'] . '\', this)">Hapus</button>'
    ];
}

$conn->close();

echo json_encode(['data' => $data]);