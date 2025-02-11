<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EzPhone</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/5.1.3/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../asset/css/navbar.css">
    <!-- <style>
        /* CSS untuk animasi loading dengan blur */
        #loading {
            position: fixed;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            background: rgba(255, 255, 255, 0.8) url('../asset/images/loading2.gif') 50% 50% no-repeat;
            backdrop-filter: blur(8px); /* Menambahkan efek blur */
            -webkit-backdrop-filter: blur(8px); /* Dukungan untuk browser berbasis WebKit */
            z-index: 9999;
        }
    </style> -->

</head>
<body>

<div id="loading"></div>

<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="home.php">
            <i class="fas fa-mobile-alt"></i> EzPhone
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'home.php' ? 'active' : ''; ?>" href="home.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'rekomendasi.php' || basename($_SERVER['PHP_SELF']) == 'hasil_rekomendasi.php') ? 'active' : ''; ?>" href="rekomendasi.php">Rekomendasi</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'perbandingan.php' ? 'active' : ''; ?>" href="perbandingan.php">Perbandingan</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'tentang.php' ? 'active' : ''; ?>" href="tentang.php">Tentang</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Bootstrap 5 JS and dependencies -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.min.js"></script>
<script src="../asset/js/navbar.js"></script>
<!-- <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Menyembunyikan animasi loading setelah halaman dimuat
        document.getElementById('loading').style.display = 'none';
    });

    // Fungsi untuk menampilkan animasi loading
    function showLoading() {
        document.getElementById('loading').style.display = 'block';
    }

    // Menambahkan event listener untuk setiap tautan di navbar
    document.querySelectorAll('.nav-link').forEach(function(link) {
        link.addEventListener('click', function(event) {
            event.preventDefault(); // Mencegah tindakan default tautan
            showLoading(); // Menampilkan animasi loading
            setTimeout(function() {
                window.location.href = link.getAttribute('href'); // Mengarahkan ke halaman baru setelah animasi loading
            }, 1000); // Waktu tunda 1 detik (1000 ms)
        });
    });
</script> -->
</body>
</html>
