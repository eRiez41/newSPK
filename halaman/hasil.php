<?php
// Memasukkan komponen navbar
include '../komponen/navbar.php';
// Memasukkan file koneksi
include '../koneksi/koneksi.php';

// Fungsi untuk mengonversi resolusi layar
function convertScreenResolution($resolution) {
    $resolutionParts = explode(' × ', $resolution);
    $width = intval($resolutionParts[0]);
    if ($width >= 720 && $width < 1080) {
        return 'HD';
    } elseif ($width >= 1080 && $width < 1440) {
        return 'FHD';
    } elseif ($width >= 1440 && $width < 2160) {
        return '2K';
    } elseif ($width >= 2160 && $width < 2880) {
        return '2K+';
    } elseif ($width >= 2880 && $width < 3840) {
        return '3K';
    } elseif ($width >= 3840 && $width < 5120) {
        return '3K+';
    } elseif ($width >= 5120 && $width < 7680) {
        return '4K';
    } elseif ($width >= 7680) {
        return '8K';
    } else {
        return 'Unknown Resolution';
    }
}

// Fungsi untuk mendapatkan skor berdasarkan kriteria dan nilai spesifikasi
function getScore($kriteria, $nilai) {
    global $conn;

    // Hapus "GB" dari nilai jika ada
    $nilai = str_replace(' GB', '', $nilai);
    // Hapus "mAh" dari nilai jika ada
    $nilai = str_replace(' mAh', '', $nilai);
    // Hapus "MP" dari nilai jika ada
    $nilai = str_replace(' MP', '', $nilai);
    // Hapus "W" dari nilai jika ada
    $nilai = str_replace(' W', '', $nilai);

    // Query untuk mendapatkan skor dari tabel skoring
    if ($kriteria == 'Screen Resolution' || $kriteria == 'Technology') {
        // Khusus untuk Screen Resolution dan Technology, langsung periksa nilai_string
        $query = "SELECT skor FROM skoring WHERE kategori_kriteria = '$kriteria' AND nilai_string = '$nilai' LIMIT 1";
    } else {
        $query = "SELECT skor FROM skoring WHERE kategori_kriteria = '$kriteria' AND (
            (nilai_min IS NOT NULL AND nilai_max IS NOT NULL AND $nilai BETWEEN nilai_min AND nilai_max) OR
            (nilai_string IS NOT NULL AND nilai_string = '$nilai')
        ) LIMIT 1";
    }

    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['skor'];
    }

    return 0; // Return 0 jika tidak ada skor yang cocok
}

// Fungsi untuk menghitung bobot berdasarkan jumlah kriteria
function calculateWeight($totalCriteria) {
    return 1 / $totalCriteria;
}

// Fungsi untuk menghasilkan tabel
function generateTable($tableTitle, $criteria, $row, $antutuScore) {
    $totalCriteria = count($criteria);
    $weight = calculateWeight($totalCriteria);
    $totalScore = 0;
    $detailedCalculation = [];

    $table = '
    <table class="table table-bordered mt-3">
        <thead>
            <tr><th colspan="4" class="text-center">' . htmlspecialchars($tableTitle) . '</th></tr>
            <tr>
                <th>Kriteria</th>
                <th>Spesifikasi</th>
                <th>Skor</th>
                <th>Bobot</th>
            </tr>
        </thead>
        <tbody>';

    foreach ($criteria as $item) {
        $specification = '';
        $score = 0;
        switch ($item) {
            case 'RAM (GB)':
                $specification = htmlspecialchars($row['RAM (GB)']);
                $score = getScore('RAM (GB)', $row['RAM (GB)']);
                break;
            case 'Memori Internal (GB)':
                $specification = htmlspecialchars($row['Memori Internal (GB)']);
                $score = getScore('Memori Internal (GB)', $row['Memori Internal (GB)']);
                break;
            case 'Skor AnTuTu':
                $specification = $antutuScore !== null ? htmlspecialchars($antutuScore) : 'N/A';
                $score = getScore('Skor_AnTuTu', $antutuScore);
                break;
            case 'Kapasitas Baterai':
                $specification = htmlspecialchars($row['Kapasitas Baterai']);
                $score = getScore('Kapasitas Baterai', $row['Kapasitas Baterai']);
                break;
            case 'Technology':
                $specification = htmlspecialchars($row['Technology']);
                $score = getScore('Technology', $row['Technology']);
                break;
            case 'Resolusi Kamera Belakang':
                $specification = htmlspecialchars($row['Resolusi Kamera Belakang']);
                $score = getScore('Resolusi Kamera Belakang', $row['Resolusi Kamera Belakang']);
                break;
            case 'Resolusi Kamera Depan':
                $specification = htmlspecialchars($row['Resolusi Kamera Depan']);
                $score = getScore('Resolusi Kamera Depan', $row['Resolusi Kamera Depan']);
                break;
            case 'Screen Resolution':
                $specification = convertScreenResolution(htmlspecialchars($row['Screen Resolution']));
                $score = getScore('Screen Resolution', $specification);
                break;
            case 'Daya Fast Charging':
                $specification = htmlspecialchars($row['Daya Fast Charging']);
                $score = getScore('Daya Fast Charging', $row['Daya Fast Charging']);
                break;
            case 'Technology (Jenis Layar)':
                $specification = htmlspecialchars($row['Technology']);
                $score = getScore('Technology', $row['Technology']);
                break;
        }

        $weightedScore = $weight * $score;
        $totalScore += $weightedScore;
        $detailedCalculation[] = "($weight × $score) = " . number_format($weightedScore, 1);

        $table .= '
            <tr>
                <td>' . htmlspecialchars($item) . '</td>
                <td>' . $specification . '</td>
                <td>' . ($score ?: '') . '</td>
                <td>' . rtrim(rtrim(number_format($weight, 2), '0'), '.') . '</td>
            </tr>';
    }

    $table .= '
        </tbody>
    </table>
    <p><strong>Bobot kriteria:</strong> Wj = 1/' . $totalCriteria . ' = ' . rtrim(rtrim(number_format($weight, 2), '0'), '.') . '</p>
    <p><strong>Perhitungan Kriteria ' . $tableTitle . ':</strong> ' . implode(' + ', $detailedCalculation) . '</p>
    <p><strong>Skor akhir ' . $tableTitle . ':</strong> Vi = ∑_(j=1)^n (Wj × rij) = ' . number_format($totalScore, 1) . '</p>';

    return [
        'table' => $table,
        'totalScore' => $totalScore
    ];
}

// Fungsi untuk mendapatkan spesifikasi lengkap berdasarkan ID
function getFullSpecification($id) {
    global $conn;
    $query = "SELECT * FROM spek_hp WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Rekomendasi - EzPhone-Guide</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- <link rel="stylesheet" href="../asset/css/style.css"> -->
    <link rel="stylesheet" href="../asset/css/hasil_rekomendasi.css">
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center mt-5">Hasil Rekomendasi Smartphone</h1>

        <?php
        // Memeriksa apakah data dikirimkan melalui metode POST
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // Mengumpulkan data yang dikirimkan
            $kebutuhan = isset($_POST['kebutuhan']) ? $_POST['kebutuhan'] : 'Tidak dipilih';
            $brand = isset($_POST['brand']) ? $_POST['brand'] : [];
            $hargaMin = isset($_POST['hargaMin']) ? (int)$_POST['hargaMin'] : 0;
            $hargaMax = isset($_POST['hargaMax']) ? (int)$_POST['hargaMax'] : PHP_INT_MAX;
            $fitur = isset($_POST['fitur']) ? $_POST['fitur'] : [];

            // Format nilai harga menjadi format mata uang
            $hargaMinFormatted = "Rp " . number_format($hargaMin, 0, ',', '.');
            $hargaMaxFormatted = ($hargaMax == 999999999) ? "lebih dari Rp 15.000.000" : "Rp " . number_format($hargaMax, 0, ',', '.');

            // Membuat array dari data yang dikirimkan
            $inputan = [
                'Kebutuhan' => $kebutuhan,
                'Brand' => $brand,
                'Rentang Harga' => [
                    'Minimum' => $hargaMinFormatted,
                    'Maksimum' => $hargaMaxFormatted
                ],
                'Fitur' => $fitur
            ];

            echo '<br>';

            // Membangun query SQL
            $query = "
                SELECT sp.*, s.antutu_10, CONCAT(sp.Brand, ' ', sp.`Nama Produk`) AS BrandNamaProduk
                FROM spek_hp sp
                LEFT JOIN soc s
                ON REPLACE(REPLACE(REPLACE(LOWER(sp.Prosesor), ',', ''), 'deca', ''), 'octa', '') LIKE CONCAT('%', LOWER(s.processor), '%')
                WHERE CAST(REPLACE(REPLACE(sp.Harga, 'Rp ', ''), '.', '') AS UNSIGNED) BETWEEN $hargaMin AND $hargaMax
            ";

            // Tambahkan klausa WHERE untuk merek jika ada
            if (!empty($brand)) {
                $brandList = implode("','", $brand);
                $query .= " AND sp.Brand IN ('$brandList')";
            }

            // Tambahkan klausa WHERE untuk fitur jika ada
            if (!empty($fitur)) {
                foreach ($fitur as $feature) {
                    switch ($feature) {
                        case 'nfc':
                            $query .= " AND sp.NFC = 'True'";
                            break;
                        case 'waterproof':
                            $query .= " AND sp.Waterproof = 'True'";
                            break;
                        case 'jack_3_5_mm':
                            $query .= " AND sp.`3.5mm Jack` = 'True'";
                            break;
                    }
                }
            }

            $result = $conn->query($query);
            $phones = [];
            $processedIds = []; // Array untuk menyimpan ID yang telah diproses

            if ($result) {
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $antutuScore = $row['antutu_10'];
                        $id = $row['id'];

                        // Periksa apakah ID sudah diproses sebelumnya
                        if (in_array($id, $processedIds)) {
                            continue; // Lewati ID yang duplikat
                        }

                        // Tambahkan ID ke array jika belum diproses
                        $processedIds[] = $id;

                        // Menghitung skor akhir sesuai kebutuhan
                        switch ($kebutuhan) {
                            case 'gaming':
                                $scoreTable = generateTable('Gaming', ['RAM (GB)', 'Memori Internal (GB)', 'Skor AnTuTu', 'Kapasitas Baterai', 'Technology'], $row, $antutuScore);
                                break;
                            case 'fotografi':
                                $scoreTable = generateTable('Fotografi', ['Resolusi Kamera Belakang', 'Resolusi Kamera Depan', 'Memori Internal (GB)', 'Screen Resolution', 'Technology (Jenis Layar)'], $row, $antutuScore);
                                break;
                            case 'konten_kreator':
                                $scoreTable = generateTable('Konten Kreator', ['Resolusi Kamera Belakang', 'Resolusi Kamera Depan', 'Memori Internal (GB)', 'Kapasitas Baterai', 'Daya Fast Charging'], $row, $antutuScore);
                                break;
                            case 'sehari_hari':
                                $scoreTable = generateTable('Sehari-hari', ['RAM (GB)', 'Memori Internal (GB)', 'Skor AnTuTu', 'Kapasitas Baterai', 'Screen Resolution'], $row, $antutuScore);
                                break;
                            default:
                                $scoreTable = ['totalScore' => 0];
                        }

                        $phones[] = [
                            'row' => $row,
                            'scoreTable' => $scoreTable,
                            'totalScore' => $scoreTable['totalScore']
                        ];
                    }

                    // Urutkan hasil berdasarkan skor akhir
                    usort($phones, function($a, $b) {
                        return $b['totalScore'] <=> $a['totalScore'];
                    });

                    // Ambil 10 item pertama
                    $phones = array_slice($phones, 0, 10);

                    $counter = 1; // Inisialisasi nomor urut
                    foreach ($phones as $phone) {
                        $row = $phone['row'];
                        $scoreTable = $phone['scoreTable'];
                        $namaProduk = htmlspecialchars($row['BrandNamaProduk']);
                        $ram = htmlspecialchars($row['RAM (GB)']);
                        $memoriInternal = htmlspecialchars($row['Memori Internal (GB)']);
                        $fiturList = [];
                        if ($row['NFC'] == 'True') $fiturList[] = 'NFC';
                        if ($row['Waterproof'] == 'True') $fiturList[] = 'Waterproof';
                        if ($row['3.5mm Jack'] == 'True') $fiturList[] = '3.5mm Jack';

                        $fiturString = implode(', ', $fiturList);
                        $harga = $row['Harga'];
                        $imageUrl = $row['Image URL'];
                        $id = $row['id'];

                        echo '<div class="card">';
                        echo '<span class="card-number">' . $counter . '</span>';
                        echo '<div class="card-body">';
                        echo '<div class="card-img-container">';
                        if (!empty($imageUrl)) {
                            echo '<img src="' . htmlspecialchars($imageUrl) . '" class="card-img" alt="' . htmlspecialchars($namaProduk) . '">';
                        }
                        echo '</div>';
                        echo '<div class="card-content">';
                        echo '<h5 class="card-title">' . $namaProduk . ' ' . $ram . '/' . $memoriInternal . '</h5>';
                        echo '<p class="card-text"><strong>Fitur:</strong> ' . $fiturString . '</p>';
                        echo '<p class="card-price">' . $harga . '</p>';

                        // Menampilkan skor akhir sesuai kebutuhan
                        echo '<p class="card-text"><strong>Skor akhir ' . ucfirst(str_replace('_', ' ', $kebutuhan)) . ':</strong> ' . number_format($scoreTable['totalScore'], 1) . '</p>';

                        echo '<div class="card-buttons">';
                        echo '<button class="btn-yellow toggle-table">Perhitungan</button>';
                        echo '<a href="https://forms.gle/ik7qbmuzU4ELgLdz5" class="btn-green" target="_blank">Penilaian</a>';
                        echo '<button class="btn-blue" onclick="showSpecification(' . $id . ')">Spesifikasi</button>';
                        echo '</div>';
                        echo '<div class="table-container hidden">';

                        // Menampilkan tabel sesuai kebutuhan
                        echo $scoreTable['table'];

                        echo '</div>';
                        echo '</div>';
                        echo '</div>';
                        echo '</div>';
                        $counter++; // Tingkatkan nomor urut
                    }
                } else {
                    echo '<p>Tidak ada smartphone yang sesuai dengan kriteria.</p>';
                }
            } else {
                echo '<p>Terjadi kesalahan saat mengambil data: ' . $conn->error . '</p>';
            }
        } else {
            echo '<p class="text-center">Tidak ada data yang dikirimkan.</p>';
        }
        ?>
    </div>

    <!-- Modal untuk menampilkan spesifikasi -->
    <div id="specificationModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <div id="specificationContent"></div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="../asset/js/hasilna.js"></script>
</body>
</html>
