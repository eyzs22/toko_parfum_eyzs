<?php
// Konfigurasi Database
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'toko_eka_yunizar');

// Koneksi Database
function getConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        die("Koneksi database gagal: " . $conn->connect_error);
    }
    
    $conn->set_charset("utf8");
    return $conn;
}

// Fungsi helper untuk query
function query($sql) {
    $conn = getConnection();
    $result = $conn->query($sql);
    $conn->close();
    return $result;
}

function execute($sql) {
    $conn = getConnection();
    $success = $conn->query($sql);
    $conn->close();
    return $success;
}

// Fungsi untuk prepared statement
function preparedQuery($sql, $types, $params) {
    $conn = getConnection();
    $stmt = $conn->prepare($sql);
    
    if ($stmt === false) {
        $conn->close();
        return false;
    }
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    $conn->close();
    
    return $result;
}

function preparedExecute($sql, $types, $params) {
    $conn = getConnection();
    $stmt = $conn->prepare($sql);
    
    if ($stmt === false) {
        $conn->close();
        return false;
    }
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $success = $stmt->execute();
    $affected_rows = $stmt->affected_rows;
    $insert_id = $conn->insert_id;
    
    $stmt->close();
    $conn->close();
    
    return [
        'success' => $success,
        'affected_rows' => $affected_rows,
        'insert_id' => $insert_id
    ];
}
?>
