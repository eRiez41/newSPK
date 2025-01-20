<?php
include '../komponen/navbar.php';
include '../koneksi/koneksi.php';

// Fungsi-fungsi yang diperlukan
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

function cleanSpecValue($value) {
    return preg_replace('/[^0-9]/', '', $value);
}

function cleanTechnologyValue($value) {
    return preg_replace('/ LCD/', 'LCD', $value);
}

function getScore($conn, $kriteria, $nilai) {
    $query = "SELECT skor FROM skoring WHERE kategori_kriteria = '$kriteria'";

    if (is_numeric($nilai)) {
        $query .= " AND nilai_min <= $nilai AND nilai_max >= $nilai";
    } else {
        $query .= " AND nilai_string = '$nilai'";
    }

    $result = $conn->query($query);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['skor'];
    }
    return '?';
}

// Ambil data dari POST atau sesuaikan dengan kebutuhan Anda
$kebutuhan = $_POST['kebutuhan'];
$brand = isset($_POST['brand']) ? $_POST['brand'] : [];
$hargaMin = $_POST['hargaMin'];
$hargaMax = $_POST['hargaMax'];
$fitur = isset($_POST['fitur']) ? $_POST['fitur'] : [];

$hasil = [
    'kebutuhan' => $kebutuhan,
    'brand' => $brand,
    'hargaMin' => $hargaMin,
    'hargaMax' => $hargaMax,
    'fitur' => $fitur
];

$query = "SELECT sp.*, s.antutu_10
          FROM spek_hp sp
          LEFT JOIN soc s ON TRIM(REPLACE(sp.Prosesor, ',', '')) LIKE CONCAT('%', TRIM(s.processor), '%')
          WHERE 1=1";

if (!empty($brand)) {
    $query .= " AND sp.Brand IN ('" . implode("', '", $brand) . "')";
}

if (!empty($hargaMin) && !empty($hargaMax)) {
    $query .= " AND CAST(REPLACE(REPLACE(sp.Harga, 'Rp ', ''), '.', '') AS UNSIGNED) BETWEEN $hargaMin AND $hargaMax";
}

if (!empty($fitur)) {
    foreach ($fitur as $feature) {
        if ($feature == 'nfc') {
            $query .= " AND sp.NFC = 'Yes'";
        } elseif ($feature == 'waterproof') {
            $query .= " AND sp.Waterproof = 'Yes'";
        } elseif ($feature == 'jack_3_5_mm') {
            $query .= " AND sp.`3.5mm Jack` = 'Yes'";
        }
    }
}

$result = $conn->query($query);

$smartphones = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $smartphones[] = $row;
    }
}

// Menghapus duplikat dari array [smartphones]
$smartphones = array_unique($smartphones, SORT_REGULAR);

foreach ($smartphones as $smartphone) {
    $hasil['smartphones'][] = $smartphone['id'];
}

// Menghitung skor untuk setiap smartphone berdasarkan kriteria kebutuhan
$scores = [];
foreach ($smartphones as $smartphone) {
    $totalScore = 0;
    $criteria = [
        'Kriteria Kebutuhan Gaming' => [
            ['RAM', 'RAM (GB)', $smartphone['RAM (GB)']],
            ['Memori Internal', 'Memori Internal (GB)', $smartphone['Memori Internal (GB)']],
            ['Skor AnTuTu', 'Skor_AnTuTu', $smartphone['antutu_10']],
            ['Kapasitas Baterai', 'Kapasitas Baterai', $smartphone['Kapasitas Baterai']],
            ['Technology', 'Technology', $smartphone['Technology']]
        ],
        'Kriteria Kebutuhan Fotografi' => [
            ['Resolusi Kamera Belakang', 'Resolusi Kamera Belakang', $smartphone['Resolusi Kamera Belakang']],
            ['Resolusi Kamera Depan', 'Resolusi Kamera Depan', $smartphone['Resolusi Kamera Depan']],
            ['Memori Internal', 'Memori Internal (GB)', $smartphone['Memori Internal (GB)']],
            ['Screen Resolution', 'Screen Resolution', convertScreenResolution($smartphone['Screen Resolution'])],
            ['Technology', 'Technology', $smartphone['Technology']]
        ],
        'Kriteria Kebutuhan Konten Kreator' => [
            ['Resolusi Kamera Belakang', 'Resolusi Kamera Belakang', $smartphone['Resolusi Kamera Belakang']],
            ['Resolusi Kamera Depan', 'Resolusi Kamera Depan', $smartphone['Resolusi Kamera Depan']],
            ['Memori Internal', 'Memori Internal (GB)', $smartphone['Memori Internal (GB)']],
            ['Kapasitas Baterai', 'Kapasitas Baterai', $smartphone['Kapasitas Baterai']],
            ['Daya Fast Charging', 'Daya Fast Charging', $smartphone['Daya Fast Charging']]
        ],
        'Kriteria Kebutuhan Sehari-hari' => [
            ['RAM', 'RAM (GB)', $smartphone['RAM (GB)']],
            ['Memori Internal', 'Memori Internal (GB)', $smartphone['Memori Internal (GB)']],
            ['Skor AnTuTu', 'Skor_AnTuTu', $smartphone['antutu_10']],
            ['Kapasitas Baterai', 'Kapasitas Baterai', $smartphone['Kapasitas Baterai']],
            ['Screen Resolution', 'Screen Resolution', convertScreenResolution($smartphone['Screen Resolution'])]
        ]
    ];

    foreach ($criteria as $criterion => $specs) {
        $totalSpecs = count($specs);
        $bobot = 1 / $totalSpecs;
        $totalScore = 0;
        foreach ($specs as $spec) {
            $kriteria = $spec[1];
            $nilai = $spec[2];
            if ($kriteria == 'Technology') {
                $nilai = cleanTechnologyValue($nilai);
            } elseif ($kriteria == 'RAM (GB)' || $kriteria == 'Memori Internal (GB)' || $kriteria == 'Daya Fast Charging') {
                $nilai = cleanSpecValue($nilai);
            }
            $score = getScore($conn, $kriteria, $nilai);
            $totalScore += $bobot * $score;
        }
        $scores[$smartphone['id']][$criterion] = $totalScore;
    }
}

// Mengurutkan smartphone berdasarkan skor untuk setiap kategori kebutuhan
$topSmartphones = [];
foreach ($criteria as $criterion => $specs) {
    uasort($smartphones, function($a, $b) use ($scores, $criterion) {
        return $scores[$b['id']][$criterion] <=> $scores[$a['id']][$criterion];
    });
    $topSmartphones[$criterion] = array_slice($smartphones, 0, 3);
}

// Filter hasil rekomendasi berdasarkan kebutuhan
$filteredTopSmartphones = [];
if (isset($topSmartphones['Kriteria Kebutuhan ' . ucfirst($kebutuhan)])) {
    $filteredTopSmartphones = $topSmartphones['Kriteria Kebutuhan ' . ucfirst($kebutuhan)];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Rekomendasi - EzPhone</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="../asset/css/style.css">
    <link rel="stylesheet" href="../asset/css/rekomendasi.css">
    <style>
        .recommendation-item {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }
        .recommendation-item .rank {
            font-size: 24px;
            font-weight: bold;
            margin-right: 20px;
        }
        .recommendation-item .image {
            margin-right: 20px;
        }
        .recommendation-item .image img {
            max-width: 100px;
            max-height: 100px;
        }
        .recommendation-item .details {
            flex: 1;
        }
        .recommendation-item .details h5 {
            margin: 0;
        }
        .recommendation-item .details p {
            margin: 5px 0;
        }
        .recommendation-item .score {
            font-size: 18px;
            font-weight: bold;
            color: #007bff;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h2 class="mt-5">Hasil Rekomendasi</h2>
        <h3><?php echo htmlspecialchars(ucfirst($kebutuhan)); ?></h3>
        <div class="row">
            <?php foreach ($filteredTopSmartphones as $index => $smartphone): ?>
                <div class="col-md-12 mb-4">
                    <div class="recommendation-item">
                        <div class="rank">#<?php echo $index + 1; ?></div>
                        <div class="image">
                            <img src="<?php echo htmlspecialchars($smartphone['Image URL']); ?>" alt="<?php echo htmlspecialchars($smartphone['Nama Produk']); ?>">
                        </div>
                        <div class="details">
                            <h5><?php echo htmlspecialchars($smartphone['Brand']); ?> <?php echo htmlspecialchars($smartphone['Nama Produk']); ?></h5>
                            <p>Varian Penyimpanan: <?php echo htmlspecialchars($smartphone['Memori Internal (GB)']); ?> GB</p>
                            <p class="score">Skor: <?php echo number_format($scores[$smartphone['id']]['Kriteria Kebutuhan ' . ucfirst($kebutuhan)], 2); ?></p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="../asset/js/script.js"></script>
</body>
</html>
