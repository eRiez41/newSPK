<?php
// Memasukkan komponen navbar
include '../komponen/navbar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tentang - EzPhoneGuide</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="../asset/css/style.css">
    <style>
        .icon-text {
            display: flex;
            align-items: flex-start;
            margin-bottom: 20px;
        }
        .icon-text i {
            margin-right: 15px;
            flex-shrink: 0;
        }
        .icon-text div {
            flex-grow: 1;
        }
        @media (max-width: 768px) {
            .icon-text {
                flex-direction: column;
                align-items: center;
            }
            .icon-text i {
                margin-bottom: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center mt-5">Tentang EzPhoneGuide</h1>
        <p class="text-center">EzPhoneGuide adalah aplikasi yang dibuat sebagai tugas akhir skripsi.</p>

        <div class="row mt-5">
            <div class="col-md-6">
                <div class="icon-text">
                    <i class="fas fa-graduation-cap fa-3x mb-3"></i>
                    <div>
                        <h3>Dasar Pembuatan</h3>
                        <p>Aplikasi ini dibuat sebagai tugas akhir skripsi untuk memenuhi persyaratan akademik. Tujuan utama adalah membantu pengguna menemukan smartphone yang paling sesuai dengan kebutuhan mereka.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="icon-text">
                    <i class="fas fa-calculator fa-3x mb-3"></i>
                    <div>
                        <h3>Algoritma</h3>
                        <p>Algoritma yang digunakan dalam aplikasi ini adalah Simple Additive Weighting (SAW). Algoritma ini digunakan untuk penentuan dan perhitungan skoring kebutuhan pengguna dalam memilih smartphone.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-5">
            <div class="col-md-6">
                <div class="icon-text">
                    <i class="fas fa-mobile-alt fa-3x mb-3"></i>
                    <div>
                        <h3>Fitur Utama</h3>
                        <ol>
                            <li>Rekomendasi Personal: Dapatkan rekomendasi smartphone berdasarkan budget dan kebutuhan Anda.</li>
                            <li>Perbandingan Detail: Bandingkan spesifikasi dan fitur smartphone secara detail.</li>
                            <li>Ranking Terbaik: Lihat smartphone terbaik untuk setiap kategori yang direkomendasikan.</li>
                        </ol>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="icon-text">
                    <i class="fab fa-instagram fa-3x mb-3"></i>
                    <div>
                        <h3>Kontak Saya</h3>
                        <p>Jika Anda memiliki pertanyaan atau butuh bantuan, silakan hubungi saya melalui Instagram di <a href="https://www.instagram.com/eriezpurtiwan/" target="_blank">@eriezpurtiwan</a>.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="../asset/js/script.js"></script>
</body>
</html>
