<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle : 'Toko Parfum Wangi Abadi'; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <!-- Google Fonts (Untuk tema baru) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;700&display=swap" rel="stylesheet">
    
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    
    <!-- Custom CSS -->
    <style>
        /* 1. Background Gradient Utama & Font */
        body {
            background: linear-gradient(135deg, #f0f3f0 0%, #d9e4d7 100%);
            background-attachment: fixed; /* Membuat gradient tetap saat scroll */
            font-family: 'Poppins', sans-serif; /* Font yang lebih modern */
            color: #333;
        }

        /* 2. Style untuk Navbar (Efek Kaca) */
        .navbar {
            background: rgba(109, 139, 116, 0.6) !important; /* Sage green semi-transparan */
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px); /* Untuk Safari */
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
        }

        .navbar-brand, .nav-link, .navbar-brand:hover, .nav-link:hover {
            color: #ffffff !important;
            font-weight: 500;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.2);
        }

        .navbar-brand i, .nav-link i {
            color: #f0f3f0 !important; /* Warna ikon sedikit lebih terang */
        }

        /* 3. Efek Glassmorphism untuk Card */
        .card {
            background: rgba(255, 255, 255, 0.4);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.25);
            border-radius: 1rem;
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
            color: #4A5D53; /* Warna teks sage tua agar kontras */
            transition: all 0.3s ease;
        }

        .card:hover {
            background: rgba(255, 255, 255, 0.55);
            transform: translateY(-5px);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
        }

        .card-header {
            background: rgba(109, 139, 116, 0.2); /* Sage green sangat transparan */
            color: #3D5247; /* Warna teks header lebih gelap untuk kontras */
            font-weight: 500;
            border-bottom: 1px solid rgba(255, 255, 255, 0.25);
            border-top-left-radius: 1rem !important;
            border-top-right-radius: 1rem !important;
        }

        /* 4. Style untuk Tombol Utama */
        .btn-primary {
            background-color: #6D8B74;
            border-color: #6D8B74;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background-color: #5F7A61;
            border-color: #5F7A61;
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        }

        /* 5. Style untuk Tombol Aksi (Edit, Hapus, dll) */
        .btn-warning {
            background-color: #6D8B74; /* Warna hijau sage */
            border-color: #6D8B74;
            color: white;
        }
        .btn-warning:hover {
            background-color: #5F7A61;
            border-color: #5F7A61;
            color: white;
        }

        .btn-danger {
            background-color: #C05F5C; /* Warna merah terakota */
            border-color: #C05F5C;
        }
        .btn-danger:hover {
            background-color: #a95451;
            border-color: #a95451;
        }

        /* Sembunyikan ikon dari tombol edit/hapus */
        .btn-warning .bi, .btn-danger .bi {
            display: none;
        }

        /* 6. Style untuk Form Input */
        .form-control, .form-select {
            background-color: rgba(255, 255, 255, 0.7);
            border: 1px solid rgba(109, 139, 116, 0.4);
            color: #4A5D53;
        }

        .form-control::placeholder {
            color: #8a9a8d;
        }

        .form-control:focus, .form-select:focus {
            background-color: rgba(255, 255, 255, 0.9);
            border-color: #6D8B74;
            box-shadow: 0 0 0 0.25rem rgba(109, 139, 116, 0.25);
        }

        /* 7. Menyesuaikan warna ikon agar serasi */
        .card .bi {
            color: #6D8B74;
        }

        .btn .bi {
            vertical-align: -0.125em; /* Posisi ikon di tombol lebih baik */
        }

        /* Perbaikan warna badge stok */
        .badge-stock-low {
            background-color: #e6a8a6 !important;
            color: #721c24 !important;
        }

        .badge-stock-ok {
            background-color: #a7c4b0 !important;
            color: #3D5247 !important;
        }

        /* Perbaikan warna teks di tabel DataTables */
        .dataTables_wrapper .dataTables_length, .dataTables_wrapper .dataTables_filter, .dataTables_wrapper .dataTables_info, .dataTables_wrapper .dataTables_paginate .paginate_button {
            color: #4A5D53 !important;
        }

        /* 8. Style untuk Cetak Laporan */
        @media print {
            .no-print, .navbar, .btn, .dataTables_wrapper .row:first-child, .dataTables_wrapper .row:last-child {
                display: none !important;
            }

            body {
                background: #ffffff !important;
                font-family: 'Times New Roman', Times, serif;
            }

            .card {
                box-shadow: none !important;
                border: 1px solid #dee2e6 !important;
                background: #ffffff !important;
            }

            .container, .container-fluid {
                max-width: 100% !important;
                padding: 0 !important;
            }
        }
    </style>
</head>
<body>
