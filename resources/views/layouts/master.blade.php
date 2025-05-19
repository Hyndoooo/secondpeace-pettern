<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard')</title>
    <!-- Link ke Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    @stack('styles')
    <style>
        /* General Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            display: flex;
            background-color: #f4f4f4;
        }

        .wrapper {
            display: flex;
            width: 100%;
        }

        /* Sidebar */
        .sidebar {
            width: 260px;
            background-color: #2c2f38;
            color: white;
            height: 100vh;
            padding: 20px;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.2);
            position: fixed; /* Sidebar tetap di kiri */
            left: 0;
            top: 0;
            bottom: 0;
        }

        .sidebar-title {
            font-size: 22px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 20px;
        }

        .sidebar-menu {
            list-style: none;
            padding: 0;
        }

        .sidebar-menu li {
            padding: 15px 10px;
            border-radius: 5px;
            transition: background 0.3s ease-in-out;
        }

        .sidebar-menu li a {
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center; /* Menyelaraskan ikon dan teks */
        }

        .sidebar-menu li i {
            margin-right: 10px; /* Memberikan jarak antara ikon dan teks */
            font-size: 18px;
        }

        .sidebar-menu li:hover,
        .sidebar-menu .active {
            background-color: #444; /* Warna aktif */
            font-weight: bold;
            border-left: 5px solid #32d22f;
        }

        .logout {
            margin-top: 50px;
            background-color: #555;
        }

        .logout:hover {
            background-color: #777;
        }

        /* Main Content */
        .main-content {
            margin-left: 260px; /* Menghindari sidebar menutupi konten */
            flex: 1;
            padding: 20px;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <nav class="sidebar">
            <h2 class="sidebar-title">SECOND PEACE</h2>
            <ul class="sidebar-menu">
                <li class="active">
                    <a href="{{ route('admin.dashboard') }}"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                </li>
                <li>
                    <a href="{{ route('manajemen.produk') }}"><i class="fas fa-box"></i> Manajemen Produk</a>
                </li>
                <li>
                    <a href="{{ route('manajemen.pesanan') }}"><i class="fas fa-shopping-cart"></i> Manajemen Pesanan</a>
                </li>
                <li>
                    <a href="{{ route('laporan-penjualan') }}"><i class="fas fa-chart-bar"></i> Laporan Penjualan</a>
                </li>                
                {{-- <li>
                    <a href="{{ route('metode.pembayaran') }}"><i class="fas fa-wallet"></i> Metode Pembayaran</a>
                </li>
                <li>
                    <a href="{{ route('ekspedisi') }}"><i class="fas fa-truck"></i> Ekspedisi</a>
                </li> --}}
                <li>
                    <a href="{{ route('admin.chat.index') }}">
                        <i class="fas fa-comments"></i> Chat Pelanggan
                    </a>
                </li>
                <li class="logout">
                    <a href="{{ route('login') }}"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </li>
            </ul>
        </nav>

        <!-- Content -->
        <div class="main-content">
            @yield('content')
        </div>
    </div>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            let menuItems = document.querySelectorAll(".sidebar-menu li");
    
            // Cek di localStorage apakah ada menu yang terakhir diklik
            let activePage = localStorage.getItem("activePage");
    
            if (activePage) {
                // Hapus "active" dari semua menu
                menuItems.forEach(item => item.classList.remove("active"));
    
                // Tambahkan "active" ke menu yang sesuai dengan halaman terakhir
                document.querySelector(`.sidebar-menu li a[href="${activePage}"]`)?.parentElement.classList.add("active");
            }
    
            // Event listener untuk setiap menu
            menuItems.forEach(item => {
                item.addEventListener("click", function () {
                    // Hapus "active" dari semua menu
                    menuItems.forEach(i => i.classList.remove("active"));
    
                    // Tambahkan "active" ke menu yang diklik
                    this.classList.add("active");
    
                    // Simpan halaman aktif di localStorage
                    let pageLink = this.querySelector("a").getAttribute("href");
                    localStorage.setItem("activePage", pageLink);
                });
            });
        });
    </script>        
</body>
</html>
