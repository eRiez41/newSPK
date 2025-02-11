<?php
// Memasukkan komponen navbar
include '../komponen/navbar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - EzPhone-Guide</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="../asset/css/style.css">
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center mt-5">Selamat Datang di EzPhone-Guide</h1>
        <p class="text-center">Temukan smartphone terbaik sesuai kebutuhan Anda!</p>

        <div class="row mt-5">
            <div class="col-md-4 text-center">
                <i class="fas fa-mobile-alt fa-3x mb-3"></i>
                <h3>Rekomendasi Personal</h3>
                <p>Dapatkan rekomendasi berdasarkan budget dan kebutuhanmu</p>
            </div>
            <div class="col-md-4 text-center">
                <i class="fas fa-balance-scale fa-3x mb-3"></i>
                <h3>Perbandingan Detail</h3>
                <p>Bandingkan spesifikasi dan fitur smartphone secara detail</p>
            </div>
            <div class="col-md-4 text-center">
                <i class="fas fa-trophy fa-3x mb-3"></i>
                <h3>Ranking Terbaik</h3>
                <p>Lihat smartphone terbaik untuk setiap kategori yang di rekomendasikan</p>
            </div>
        </div>

        <div class="text-center mt-5">
            <a href="rekomendasi.php" class="btn btn-dark btn-lg btn-animated">Mulai Rekomendasi</a>
        </div>
    </div>
<br><br>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="../asset/js/script.js"></script>
</body>
</html>
