<?php
// Memasukkan komponen navbar
include '../komponen/navbar.php';
// Memasukkan file koneksi
include '../koneksi/koneksi.php';

// Menerima data dari formulir
$kebutuhan = isset($_POST['kebutuhan']) ? $_POST['kebutuhan'] : '';
$brands = isset($_POST['brand']) ? $_POST['brand'] : [];
$hargaMin = isset($_POST['hargaMin']) ? $_POST['hargaMin'] : '';
$hargaMax = isset($_POST['hargaMax']) ? $_POST['hargaMax'] : '';
$fitur = isset($_POST['fitur']) ? $_POST['fitur'] : [];

// Membuat pernyataan SQL
$sql = "SELECT * FROM spek_hp WHERE ";

// Menambahkan kondisi brand
if (!empty($brands)) {
    $brandConditions = [];
    foreach ($brands as $brand) {
        $brandConditions[] = "Brand = '" . $conn->real_escape_string($brand) . "'";
    }
    $sql .= "(" . implode(" OR ", $brandConditions) . ") AND ";
}

// Menambahkan kondisi harga
$sql .= "CAST(REPLACE(REPLACE(Harga, 'Rp ', ''), '.', '') AS UNSIGNED) BETWEEN " . $conn->real_escape_string($hargaMin) . " AND " . $conn->real_escape_string($hargaMax) . " AND ";

// Menambahkan kondisi fitur
if (!empty($fitur)) {
    $fiturConditions = [];
    foreach ($fitur as $feature) {
        switch ($feature) {
            case 'nfc':
                $fiturConditions[] = "NFC = 'True'";
                break;
            case 'waterproof':
                $fiturConditions[] = "Waterproof = 'True'";
                break;
            case 'jack_3_5_mm':
                $fiturConditions[] = "`3.5mm Jack` = 'True'";
                break;
        }
    }
    $sql .= implode(" AND ", $fiturConditions);
}

// Menghapus " AND " terakhir jika ada
$sql = rtrim($sql, " AND ");

// Menjalankan pernyataan SQL
$result = $conn->query($sql);
if (!$result) {
    die("Query gagal: " . $conn->error);
}

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

// Fungsi untuk membersihkan nilai Technology
function cleanTechnologyValue($value) {
    return preg_replace('/ LCD/', ' LCD', $value);
}

// Fungsi untuk mendapatkan skor berdasarkan spesifikasi
function getScore($kategori, $nilai, $conn) {
    $sql = "SELECT skor FROM skoring WHERE kategori_kriteria = '" . $conn->real_escape_string($kategori) . "'";
    if (is_numeric($nilai)) {
        $sql .= " AND nilai_min <= " . $conn->real_escape_string($nilai) . " AND nilai_max >= " . $conn->real_escape_string($nilai);
    } else {
        $sql .= " AND nilai_string = '" . $conn->real_escape_string($nilai) . "'";
    }
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['skor'];
    }
    return 0;
}

// Menyimpan hasil query dalam array
$smartphones = [];
while ($row = $result->fetch_assoc()) {
    // Mengambil skor AnTuTu
    $antutuSql = " SELECT s.antutu_10 FROM spek_hp sp LEFT JOIN soc s ON REPLACE(REPLACE(REPLACE(LOWER(sp.Prosesor), ',', ''), 'deca', ''), 'octa', '') LIKE CONCAT('%', LOWER(s.processor), '%') WHERE sp.`Nama Produk` = '" . $conn->real_escape_string($row['Nama Produk']) . "' ";
    $antutuResult = $conn->query($antutuSql);
    $antutuScore = $antutuResult->fetch_assoc()['antutu_10'] ?? 'N/A';

    // Menghitung skor kebutuhan berdasarkan kebutuhan yang diterima
    $kebutuhanScore = 0;
    switch ($kebutuhan) {
        case 'gaming':
            $gamingSpecs = [
                ['RAM', str_replace(' GB', '', $row['RAM (GB)']), getScore('RAM (GB)', str_replace(' GB', '', $row['RAM (GB)']), $conn)],
                ['Memori Internal', str_replace(' GB', '', $row['Memori Internal (GB)']), getScore('Memori Internal (GB)', str_replace(' GB', '', $row['Memori Internal (GB)']), $conn)],
                ['Skor AnTuTu', $antutuScore, getScore('Skor_AnTuTu', $antutuScore, $conn)],
                ['Kapasitas Baterai', $row['Kapasitas Baterai'], getScore('Kapasitas Baterai', $row['Kapasitas Baterai'], $conn)],
                ['Technology', cleanTechnologyValue($row['Technology']), getScore('Technology', cleanTechnologyValue($row['Technology']), $conn)]
            ];
            $gamingBobot = 1 / count($gamingSpecs);
            foreach ($gamingSpecs as $spec) {
                $kebutuhanScore += $spec[2] * $gamingBobot;
            }
            break;
        case 'fotografi':
            $fotografiSpecs = [
                ['Resolusi Kamera Belakang', $row['Resolusi Kamera Belakang'], getScore('Resolusi Kamera Belakang', $row['Resolusi Kamera Belakang'], $conn)],
                ['Resolusi Kamera Depan', $row['Resolusi Kamera Depan'], getScore('Resolusi Kamera Depan', $row['Resolusi Kamera Depan'], $conn)],
                ['Memori Internal', str_replace(' GB', '', $row['Memori Internal (GB)']), getScore('Memori Internal (GB)', str_replace(' GB', '', $row['Memori Internal (GB)']), $conn)],
                ['Screen Resolution', convertScreenResolution($row['Screen Resolution']), getScore('Screen Resolution', convertScreenResolution($row['Screen Resolution']), $conn)],
                ['Technology', cleanTechnologyValue($row['Technology']), getScore('Technology', cleanTechnologyValue($row['Technology']), $conn)]
            ];
            $fotografiBobot = 1 / count($fotografiSpecs);
            foreach ($fotografiSpecs as $spec) {
                $kebutuhanScore += $spec[2] * $fotografiBobot;
            }
            break;
        case 'konten_kreator':
            $kontenKreatorSpecs = [
                ['Resolusi Kamera Belakang', $row['Resolusi Kamera Belakang'], getScore('Resolusi Kamera Belakang', $row['Resolusi Kamera Belakang'], $conn)],
                ['Resolusi Kamera Depan', $row['Resolusi Kamera Depan'], getScore('Resolusi Kamera Depan', $row['Resolusi Kamera Depan'], $conn)],
                ['Memori Internal', str_replace(' GB', '', $row['Memori Internal (GB)']), getScore('Memori Internal (GB)', str_replace(' GB', '', $row['Memori Internal (GB)']), $conn)],
                ['Kapasitas Baterai', $row['Kapasitas Baterai'], getScore('Kapasitas Baterai', $row['Kapasitas Baterai'], $conn)],
                ['Daya Fast Charging', str_replace(' W', '', $row['Daya Fast Charging']), getScore('Daya Fast Charging', str_replace(' W', '', $row['Daya Fast Charging']), $conn)]
            ];
            $kontenKreatorBobot = 1 / count($kontenKreatorSpecs);
            foreach ($kontenKreatorSpecs as $spec) {
                $kebutuhanScore += $spec[2] * $kontenKreatorBobot;
            }
            break;
        case 'sehari_hari':
            $sehariHariSpecs = [
                ['RAM', str_replace(' GB', '', $row['RAM (GB)']), getScore('RAM (GB)', str_replace(' GB', '', $row['RAM (GB)']), $conn)],
                ['Memori Internal', str_replace(' GB', '', $row['Memori Internal (GB)']), getScore('Memori Internal (GB)', str_replace(' GB', '', $row['Memori Internal (GB)']), $conn)],
                ['Skor AnTuTu', $antutuScore, getScore('Skor_AnTuTu', $antutuScore, $conn)],
                ['Kapasitas Baterai', $row['Kapasitas Baterai'], getScore('Kapasitas Baterai', $row['Kapasitas Baterai'], $conn)],
                ['Screen Resolution', convertScreenResolution($row['Screen Resolution']), getScore('Screen Resolution', convertScreenResolution($row['Screen Resolution']), $conn)]
            ];
            $sehariHariBobot = 1 / count($sehariHariSpecs);
            foreach ($sehariHariSpecs as $spec) {
                $kebutuhanScore += $spec[2] * $sehariHariBobot;
            }
            break;
    }

    // Menyimpan data smartphone beserta skor kebutuhan
    $smartphones[] = [
        'row' => $row,
        'kebutuhanScore' => $kebutuhanScore
    ];
}

// Mengurutkan hasil berdasarkan skor kebutuhan dari yang terbesar ke yang terkecil
usort($smartphones, function($a, $b) {
    return $b['kebutuhanScore'] <=> $a['kebutuhanScore'];
});

// Batasi jumlah smartphone yang ditampilkan menjadi 10
$smartphones = array_slice($smartphones, 0, 10);

// Menampilkan hasil query dalam format yang diinginkan
echo '<br>';
echo '<h1 class="text-center mt-5"><a href="rekomendasi.php" class="btn btn-dark back-button" style=" margin-right: 20px; margin-top: -5px">&lt;</a> Top 10 Rekomendasi Smartphone</h1>';
echo '<div class="container mt-5">';
$counter = 1;
foreach ($smartphones as $smartphone) {
    $row = $smartphone['row'];
    $kebutuhanScore = $smartphone['kebutuhanScore'];

    // Mengonversi nama produk untuk URL
    $productName = urlencode($row['Brand'] . ' ' . $row['Nama Produk']);
    echo "<div class='card mb-3 position-relative hover-card'>";
    echo "<div class='card-body d-flex'>";
    echo "<div class='flex-grow-1'>";
    echo "<h5 class='card-title' style='font-size: 1em;'>" . $counter . ". " . htmlspecialchars($row['Brand']) . " " . htmlspecialchars($row['Nama Produk']) . "</h5>";
    echo "<p class='card-text' style='font-size: 0.8em;'>";
    echo "<p class='card-text' style='font-weight\:bold; font-size: 0.8em;'>" . htmlspecialchars($row['RAM (GB)']) . " / " . htmlspecialchars($row['Memori Internal (GB)']) . "</p>";
    echo "<span style='color: " . ($row['NFC'] == 'True' ? 'green' : 'red') . "; font-size: 0.8em;'>NFC</span>, ";
    echo "<span style='color: " . ($row['Waterproof'] == 'True' ? 'green' : 'red') . "; font-size: 0.8em;'>Waterproof</span>, ";
    echo "<span style='color: " . ($row['3.5mm Jack'] == 'True' ? 'green' : 'red') . "; font-size: 0.8em;'>3.5mm Jack</span>";
    echo "</p>";
    echo "<p class='card-text' style='color: green; font-weight: bold; font-size: 0.8em;'>Harga: " . htmlspecialchars($row['Harga']) . "</p>";
    // Menambahkan tombol "Detail"
    echo "<button class='btn btn-dark btn-sm position-absolute top-0 end-0 toggle-button' data-target='#detail-" . $counter . "'>Detail</button>";
    // Menambahkan pop-up spesifikasi lengkap
    echo "<div id='popup-" . $counter . "' class='popup'>";
    echo "<div class='popup-content'>";
    echo "<span class='close-btn'>&times;</span>";
    echo "<h2>" . htmlspecialchars($row['Brand']) . " " . htmlspecialchars($row['Nama Produk']) . "</h2>";
    echo "<p><strong>Harga:</strong> " . htmlspecialchars($row['Harga']) . "</p>";
    echo "<p><strong>RAM:</strong> " . htmlspecialchars($row['RAM (GB)']) . "</p>";
    echo "<p><strong>Memori Internal:</strong> " . htmlspecialchars($row['Memori Internal (GB)']) . "</p>";
    echo "<p><strong>Kapasitas Baterai:</strong> " . htmlspecialchars($row['Kapasitas Baterai']) . " mAH</p>";
    echo "<p><strong>Resolusi Kamera Belakang:</strong> " . htmlspecialchars($row['Resolusi Kamera Belakang']) . " MP</p>";
    echo "<p><strong>Resolusi Kamera Depan:</strong> " . htmlspecialchars($row['Resolusi Kamera Depan']) . " MP</p>";
    echo "<p><strong>Technology:</strong> " . htmlspecialchars($row['Technology']) . "</p>";
    echo "<p><strong>Screen Resolution:</strong> " . htmlspecialchars($row['Screen Resolution']) . "</p>";
    echo "<p><strong>Daya Fast Charging:</strong> " . htmlspecialchars($row['Daya Fast Charging']) . "</p>";
    echo "<p><strong>Skor AnTuTu:</strong> " . htmlspecialchars($antutuScore) . "</p>";
    echo "<p><strong>Prosesor:</strong> " . htmlspecialchars($row['Prosesor']) . "</p>";
    echo "<p><strong>GPU:</strong> " . htmlspecialchars($row['GPU']) . "</p>";
    echo "<p><strong>Ukuran Layar:</strong> " . htmlspecialchars($row['Ukuran Layar']) . " Inci</p>";
    echo "<p><strong>Sistem Operasi:</strong> " . htmlspecialchars($row['OS Version & Version Detail'] ?? 'N/A') . "</p>";
    echo "<p><strong>Jaringan:</strong> " . htmlspecialchars($row['Jaringan']) . "</p>";
    echo "<p><strong>USB:</strong> " . htmlspecialchars($row['USB']) . "</p>";
    echo "<p><strong>Sensor:</strong> " . htmlspecialchars($row['Sensor']) . "</p>";
    echo "<p><strong>Material:</strong> " . htmlspecialchars($row['Material']) . "</p>";
    echo "<p><strong>Tahun Rilis:</strong> " . htmlspecialchars($row['Tahun Rilis'] ?? 'N/A') . "</p>";
    echo "<div class='d-flex justify-content-between mt-3'>";
    echo "<a href='https://www.tokopedia.com/search?st=product&q=" . $productName . "' class='btn btn-success mx-1' target='_blank'>Beli di Tokopedia</a>";
    echo "<a href='https://shopee.co.id/search?keyword=" . $productName . "' class='btn btn-warning mx-1' target='_blank'>Beli di Shopee</a>";
    echo "<a href='https://forms.gle/ik7qbmuzU4ELgLdz5' class='btn btn-primary mx-1' target='_blank'>Penilaian</a>";
    echo "</div>";
    echo "</div>";
    echo "</div>";

    // Menambahkan tabel kebutuhan Gaming
    echo "<div id='detail-" . $counter . "' class='detail-content' style='display:none;'>";
    echo "<h6 class='card-subtitle mb-2 text-muted'>Tabel Kebutuhan Gaming</h6>";
    echo "<div class='table-responsive'>";
    echo "<table class='table table-bordered'>";
    echo "<thead><tr><th>Kriteria</th><th>Spesifikasi</th><th>Skor</th><th>Bobot</th></tr></thead>";
    echo "<tbody>";
    $gamingSpecs = [
        ['RAM', str_replace(' GB', '', $row['RAM (GB)']), getScore('RAM (GB)', str_replace(' GB', '', $row['RAM (GB)']), $conn)],
        ['Memori Internal', str_replace(' GB', '', $row['Memori Internal (GB)']), getScore('Memori Internal (GB)', str_replace(' GB', '', $row['Memori Internal (GB)']), $conn)],
        ['Skor AnTuTu', $antutuScore, getScore('Skor_AnTuTu', $antutuScore, $conn)],
        ['Kapasitas Baterai', $row['Kapasitas Baterai'], getScore('Kapasitas Baterai', $row['Kapasitas Baterai'], $conn)],
        ['Technology', cleanTechnologyValue($row['Technology']), getScore('Technology', cleanTechnologyValue($row['Technology']), $conn)]
    ];
    $gamingBobot = 1 / count($gamingSpecs);
    $totalGamingScore = 0;
    foreach ($gamingSpecs as $spec) {
        echo "<tr><td>" . htmlspecialchars($spec[0]) . "</td><td>" . htmlspecialchars($spec[1]) . "</td><td>" . htmlspecialchars($spec[2]) . "</td><td>" . number_format($gamingBobot, 1) . "</td></tr>";
        $totalGamingScore += $spec[2] * $gamingBobot;
    }
    echo "</tbody></table>";
    echo "<p>Wj = 1 / banyak spesifikasi = 1 / " . count($gamingSpecs) . " = " . number_format($gamingBobot, 1) . "</p>";
    echo "<p>Perhitungan Kriteria: ";
    foreach ($gamingSpecs as $index => $spec) {
        echo "(" . number_format($gamingBobot, 1) . " × " . htmlspecialchars($spec[2]) . ") = " . number_format($gamingBobot * $spec[2], 1);
        if ($index < count($gamingSpecs) - 1) {
            echo " + ";
        }
    }
    echo " = " . number_format($totalGamingScore, 1) . "</p>";
    echo "<p>Skor Kriteria: " . number_format($totalGamingScore, 1) . "</p>";
    echo "</div>";

    // Menambahkan tabel kebutuhan Fotografi
    echo "<h6 class='card-subtitle mb-2 text-muted'>Tabel Kebutuhan Fotografi</h6>";
    echo "<div class='table-responsive'>";
    echo "<table class='table table-bordered'>";
    echo "<thead><tr><th>Kriteria</th><th>Spesifikasi</th><th>Skor</th><th>Bobot</th></tr></thead>";
    echo "<tbody>";
    $fotografiSpecs = [
        ['Resolusi Kamera Belakang', $row['Resolusi Kamera Belakang'], getScore('Resolusi Kamera Belakang', $row['Resolusi Kamera Belakang'], $conn)],
        ['Resolusi Kamera Depan', $row['Resolusi Kamera Depan'], getScore('Resolusi Kamera Depan', $row['Resolusi Kamera Depan'], $conn)],
        ['Memori Internal', str_replace(' GB', '', $row['Memori Internal (GB)']), getScore('Memori Internal (GB)', str_replace(' GB', '', $row['Memori Internal (GB)']), $conn)],
        ['Screen Resolution', convertScreenResolution($row['Screen Resolution']), getScore('Screen Resolution', convertScreenResolution($row['Screen Resolution']), $conn)],
        ['Technology', cleanTechnologyValue($row['Technology']), getScore('Technology', cleanTechnologyValue($row['Technology']), $conn)]
    ];
    $fotografiBobot = 1 / count($fotografiSpecs);
    $totalFotografiScore = 0;
    foreach ($fotografiSpecs as $spec) {
        echo "<tr><td>" . htmlspecialchars($spec[0]) . "</td><td>" . htmlspecialchars($spec[1]) . "</td><td>" . htmlspecialchars($spec[2]) . "</td><td>" . number_format($fotografiBobot, 1) . "</td></tr>";
        $totalFotografiScore += $spec[2] * $fotografiBobot;
    }
    echo "</tbody></table>";
    echo "<p>Wj = 1 / banyak spesifikasi = 1 / " . count($fotografiSpecs) . " = " . number_format($fotografiBobot, 1) . "</p>";
    echo "<p>Perhitungan Kriteria: ";
    foreach ($fotografiSpecs as $index => $spec) {
        echo "(" . number_format($fotografiBobot, 1) . " × " . htmlspecialchars($spec[2]) . ") = " . number_format($fotografiBobot * $spec[2], 1);
        if ($index < count($fotografiSpecs) - 1) {
            echo " + ";
        }
    }
    echo " = " . number_format($totalFotografiScore, 1) . "</p>";
    echo "<p>Skor Kriteria: " . number_format($totalFotografiScore, 1) . "</p>";
    echo "</div>";

    // Menambahkan tabel kebutuhan Konten Kreator
    echo "<h6 class='card-subtitle mb-2 text-muted'>Tabel Kebutuhan Konten Kreator</h6>";
    echo "<div class='table-responsive'>";
    echo "<table class='table table-bordered'>";
    echo "<thead><tr><th>Kriteria</th><th>Spesifikasi</th><th>Skor</th><th>Bobot</th></tr></thead>";
    echo "<tbody>";
    $kontenKreatorSpecs = [
        ['Resolusi Kamera Belakang', $row['Resolusi Kamera Belakang'], getScore('Resolusi Kamera Belakang', $row['Resolusi Kamera Belakang'], $conn)],
        ['Resolusi Kamera Depan', $row['Resolusi Kamera Depan'], getScore('Resolusi Kamera Depan', $row['Resolusi Kamera Depan'], $conn)],
        ['Memori Internal', str_replace(' GB', '', $row['Memori Internal (GB)']), getScore('Memori Internal (GB)', str_replace(' GB', '', $row['Memori Internal (GB)']), $conn)],
        ['Kapasitas Baterai', $row['Kapasitas Baterai'], getScore('Kapasitas Baterai', $row['Kapasitas Baterai'], $conn)],
        ['Daya Fast Charging', str_replace(' W', '', $row['Daya Fast Charging']), getScore('Daya Fast Charging', str_replace(' W', '', $row['Daya Fast Charging']), $conn)]
    ];
    $kontenKreatorBobot = 1 / count($kontenKreatorSpecs);
    $totalKontenKreatorScore = 0;
    foreach ($kontenKreatorSpecs as $spec) {
        echo "<tr><td>" . htmlspecialchars($spec[0]) . "</td><td>" . htmlspecialchars($spec[1]) . "</td><td>" . htmlspecialchars($spec[2]) . "</td><td>" . number_format($kontenKreatorBobot, 1) . "</td></tr>";
        $totalKontenKreatorScore += $spec[2] * $kontenKreatorBobot;
    }
    echo "</tbody></table>";
    echo "<p>Wj = 1 / banyak spesifikasi = 1 / " . count($kontenKreatorSpecs) . " = " . number_format($kontenKreatorBobot, 1) . "</p>";
    echo "<p>Perhitungan Kriteria: ";
    foreach ($kontenKreatorSpecs as $index => $spec) {
        echo "(" . number_format($kontenKreatorBobot, 1) . " × " . htmlspecialchars($spec[2]) . ") = " . number_format($kontenKreatorBobot * $spec[2], 1);
        if ($index < count($kontenKreatorSpecs) - 1) {
            echo " + ";
        }
    }
    echo " = " . number_format($totalKontenKreatorScore, 1) . "</p>";
    echo "<p>Skor Kriteria: " . number_format($totalKontenKreatorScore, 1) . "</p>";
    echo "</div>";

    // Menambahkan tabel kebutuhan Sehari-hari
    echo "<h6 class='card-subtitle mb-2 text-muted'>Tabel Kebutuhan Sehari-hari</h6>";
    echo "<div class='table-responsive'>";
    echo "<table class='table table-bordered'>";
    echo "<thead><tr><th>Kriteria</th><th>Spesifikasi</th><th>Skor</th><th>Bobot</th></tr></thead>";
    echo "<tbody>";
    $sehariHariSpecs = [
        ['RAM', str_replace(' GB', '', $row['RAM (GB)']), getScore('RAM (GB)', str_replace(' GB', '', $row['RAM (GB)']), $conn)],
        ['Memori Internal', str_replace(' GB', '', $row['Memori Internal (GB)']), getScore('Memori Internal (GB)', str_replace(' GB', '', $row['Memori Internal (GB)']), $conn)],
        ['Skor AnTuTu', $antutuScore, getScore('Skor_AnTuTu', $antutuScore, $conn)],
        ['Kapasitas Baterai', $row['Kapasitas Baterai'], getScore('Kapasitas Baterai', $row['Kapasitas Baterai'], $conn)],
        ['Screen Resolution', convertScreenResolution($row['Screen Resolution']), getScore('Screen Resolution', convertScreenResolution($row['Screen Resolution']), $conn)]
    ];
    $sehariHariBobot = 1 / count($sehariHariSpecs);
    $totalSehariHariScore = 0;
    foreach ($sehariHariSpecs as $spec) {
        echo "<tr><td>" . htmlspecialchars($spec[0]) . "</td><td>" . htmlspecialchars($spec[1]) . "</td><td>" . htmlspecialchars($spec[2]) . "</td><td>" . number_format($sehariHariBobot, 1) . "</td></tr>";
        $totalSehariHariScore += $spec[2] * $sehariHariBobot;
    }
    echo "</tbody></table>";
    echo "<p>Wj = 1 / banyak spesifikasi = 1 / " . count($sehariHariSpecs) . " = " . number_format($sehariHariBobot, 1) . "</p>";
    echo "<p>Perhitungan Kriteria: ";
    foreach ($sehariHariSpecs as $index => $spec) {
        echo "(" . number_format($sehariHariBobot, 1) . " × " . htmlspecialchars($spec[2]) . ") = " . number_format($sehariHariBobot * $spec[2], 1);
        if ($index < count($sehariHariSpecs) - 1) {
            echo " + ";
        }
    }
    echo " = " . number_format($totalSehariHariScore, 1) . "</p>";
    echo "<p>Skor Kriteria: " . number_format($totalSehariHariScore, 1) . "</p>";
    echo "</div>";
    echo "</div>";
    echo "</div>";
    echo "<div class='ml-3'>";
    echo "<img src='" . htmlspecialchars($row['Image URL']) . "' alt='" . htmlspecialchars($row['Nama Produk']) . "' class='img-fluid' style='max-width: 130px; margin-left: -170px;'>";
    echo "</div>";
    echo "</div>";
    echo "</div>";
    $counter++;
}
echo "</div>";

// Menutup koneksi
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Rekomendasi - EzPhone-Guide</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="../asset/css/hasil.css">
</head>
<body>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="../asset/js/skrip.js"></script>

</body>
</html>
