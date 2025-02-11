<?php
// Memasukkan komponen navbar
include '../komponen/navbar.php';
// Memasukkan file koneksi
include '../koneksi/koneksi.php';

// Mengambil data dari form
$kebutuhan = isset($_POST['kebutuhan']) ? $_POST['kebutuhan'] : '';
$brands = isset($_POST['brand']) ? $_POST['brand'] : [];
$hargaMin = isset($_POST['hargaMin']) ? $_POST['hargaMin'] : '';
$hargaMax = isset($_POST['hargaMax']) ? $_POST['hargaMax'] : '';
$fitur = isset($_POST['fitur']) ? $_POST['fitur'] : [];

// Membuat array hasil
$hasil = [
    'kebutuhan' => $kebutuhan,
    'brands' => $brands,
    'hargaMin' => $hargaMin,
    'hargaMax' => $hargaMax,
    'fitur' => [
        'nfc' => in_array('nfc', $fitur) ? 'true' : '',
        'waterproof' => in_array('waterproof', $fitur) ? 'true' : '',
        'jack_3_5_mm' => in_array('jack_3_5_mm', $fitur) ? 'true' : ''
    ]
];

// Membuat query SQL sesuai dengan array hasil
$brandsCondition = !empty($brands) ? "AND `Brand` IN ('" . implode("', '", $brands) . "')" : '';
$fiturConditions = [];
if ($hasil['fitur']['nfc'] === 'true') {
    $fiturConditions[] = "`NFC` = 'true'";
}
if ($hasil['fitur']['waterproof'] === 'true') {
    $fiturConditions[] = "`Waterproof` = 'true'";
}
if ($hasil['fitur']['jack_3_5_mm'] === 'true') {
    $fiturConditions[] = "`3.5mm Jack` = 'true'";
}
$fiturCondition = !empty($fiturConditions) ? "AND " . implode(" AND ", $fiturConditions) : '';

$sql = "
SELECT DISTINCT `id`, `Brand`, `Nama Produk`, `RAM (GB)`, `Memori Internal (GB)`, `Kapasitas Baterai`, `Technology`,
       `Resolusi Kamera Belakang`, `Resolusi Kamera Depan`, `Screen Resolution`, `Daya Fast Charging`,
       CONCAT('Rp ', FORMAT(CAST(REGEXP_REPLACE(`Harga`, '[^0-9]', '') AS SIGNED), 0)) AS Harga
FROM spek_hp
WHERE CAST(REGEXP_REPLACE(`Harga`, '[^0-9]', '') AS SIGNED) BETWEEN {$hargaMin} AND {$hargaMax}
{$brandsCondition}
{$fiturCondition}
LIMIT 10;
";

// Menjalankan query SQL
$result = $conn->query($sql);

// Memeriksa apakah query berhasil dieksekusi
if ($result) {
    $data = $result->fetch_all(MYSQLI_ASSOC);

    // Mengambil Skor_AnTuTu untuk setiap ID
    foreach ($data as &$row) {
        $id = $row['id'];
        $antutuSql = "
        SELECT
            s.`antutu_10` AS Skor_AnTuTu
        FROM
            spek_hp sp
        LEFT JOIN
            soc s ON INSTR(REPLACE(sp.Prosesor, ',', ''), s.processor) > 0
        WHERE
            sp.id = '{$id}';
        ";
        $antutuResult = $conn->query($antutuSql);
        if ($antutuResult) {
            $antutuData = $antutuResult->fetch_assoc();
            $row['Skor_AnTuTu'] = $antutuData['Skor_AnTuTu'];
        } else {
            $row['Skor_AnTuTu'] = 'N/A';
        }

        // Convert Screen Resolution
        $row['Screen Resolution'] = convertScreenResolution($row['Screen Resolution']);
    }
} else {
    $data = [];
    $error = $conn->error;
}

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

function getScore($kategori_kriteria, $value) {
    global $conn;
    $value = floatval($value);
    $sql = "
        SELECT skor
        FROM skoring
        WHERE kategori_kriteria = '$kategori_kriteria'
        AND (
            (nilai_min IS NOT NULL AND nilai_max IS NOT NULL AND $value BETWEEN nilai_min AND nilai_max)
            OR
            (nilai_string IS NOT NULL AND nilai_string = '$value')
        )
        LIMIT 1;
    ";
    $result = $conn->query($sql);
    if ($result) {
        $row = $result->fetch_assoc();
        return $row ? $row['skor'] : 'N/A';
    } else {
        return 'N/A';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Rekomendasi - EzPhone-Guide</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="../asset/css/style.css">
    <link rel="stylesheet" href="../asset/css/rekomendasi.css">
    <style>
        .table table-bordered {
            display: none;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center mt-5">Hasil Rekomendasi Smartphone</h1>
        <pre><?php print_r($hasil); ?></pre>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger">
                Error: <?php echo htmlspecialchars($error); ?>
            </div>
        <?php else: ?>
            <h3>Hasil Query:</h3>
            <div class="row" id="card-container">
                <?php foreach ($data as $index => $row): ?>
                    <div class="col-md-12 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($index + 1) . '. ' . $row['Brand'] . ' ' . $row['Nama Produk']; ?></h5>
                                <p class="card-text"><strong>Harga:</strong> <?php echo htmlspecialchars($row['Harga']); ?></p>
                                <button class="btn btn-primary btn-sm toggle-button">Show/Hide Tables</button>
                                <div id="tables-container" class="tables-container" data-specs='<?php echo json_encode($row); ?>'></div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="../asset/js/script.js"></script>
    <script src="../asset/js/rekomendasi.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const tablesContainer = document.querySelectorAll('.tables-container');
            const kebutuhan = '<?php echo isset($hasil['kebutuhan']) ? $hasil['kebutuhan'] : ''; ?>';

            tablesContainer.forEach(container => {
                const specs = JSON.parse(container.getAttribute('data-specs'));

                const tables = [
                    {
                        title: 'Tabel Gaming',
                        criteria: [
                            { name: 'RAM (GB)', value: specs['RAM (GB)'] },
                            { name: 'Memori Internal (GB)', value: specs['Memori Internal (GB)'] },
                            { name: 'Skor_AnTuTu', value: specs['Skor_AnTuTu'] },
                            { name: 'Kapasitas Baterai', value: specs['Kapasitas Baterai'] },
                            { name: 'Technology', value: specs['Technology'] }
                        ]
                    },
                    {
                        title: 'Tabel Fotografi',
                        criteria: [
                            { name: 'Resolusi Kamera Belakang', value: specs['Resolusi Kamera Belakang'] },
                            { name: 'Resolusi Kamera Depan', value: specs['Resolusi Kamera Depan'] },
                            { name: 'Memori Internal (GB)', value: specs['Memori Internal (GB)'] },
                            { name: 'Screen Resolution', value: specs['Screen Resolution'] },
                            { name: 'Technology', value: specs['Technology'] }
                        ]
                    },
                    {
                        title: 'Tabel Konten Kreator',
                        criteria: [
                            { name: 'Resolusi Kamera Belakang', value: specs['Resolusi Kamera Belakang'] },
                            { name: 'Resolusi Kamera Depan', value: specs['Resolusi Kamera Depan'] },
                            { name: 'Memori Internal (GB)', value: specs['Memori Internal (GB)'] },
                            { name: 'Kapasitas Baterai', value: specs['Kapasitas Baterai'] },
                            { name: 'Daya Fast Charging', value: specs['Daya Fast Charging'] }
                        ]
                    },
                    {
                        title: 'Tabel Sehari-hari',
                        criteria: [
                            { name: 'RAM (GB)', value: specs['RAM (GB)'] },
                            { name: 'Memori Internal (GB)', value: specs['Memori Internal (GB)'] },
                            { name: 'Skor_AnTuTu', value: specs['Skor_AnTuTu'] },
                            { name: 'Kapasitas Baterai', value: specs['Kapasitas Baterai'] },
                            { name: 'Screen Resolution', value: specs['Screen Resolution'] }
                        ]
                    }
                ];

                const scores = {};

                tables.forEach(table => {
                    const tableElement = document.createElement('table');
                    tableElement.className = 'table table-bordered';
                    const criteriaCount = table.criteria.length;
                    const weight = 1 / criteriaCount;
                    let totalScore = 0;
                    let calculationText = '';

                    tableElement.innerHTML = `
                        <thead>
                            <tr>
                                <th colspan="4">${table.title}</th>
                            </tr>
                            <tr>
                                <th>Kriteria</th>
                                <th>Spesifikasi</th>
                                <th>Skor</th>
                                <th>Bobot</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${table.criteria.map(criterion => {
                                const score = getScore(criterion.name, criterion.value);
                                const weightedScore = weight * (score === 'N/A' ? 0 : parseFloat(score));
                                totalScore += weightedScore;
                                calculationText += `(${weight.toFixed(1)} x ${score}) + `;
                                return `
                                    <tr>
                                        <td>${criterion.name}</td>
                                        <td>${criterion.value}</td>
                                        <td>${score}</td>
                                        <td>${weight.toFixed(1)}</td>
                                    </tr>
                                `;
                            }).join('')}
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4">Rumus Bobot: 1 / jumlah kriteria : 1 / ${criteriaCount} : ${weight.toFixed(1)}</td>
                            </tr>
                            <tr>
                                <td colspan="4">Perhitungan Kriteria: ${calculationText.slice(0, -3)} = ${totalScore.toFixed(2)}</td>
                            </tr>
                            <tr>
                                <td colspan="4">Skor Akhir: ${totalScore.toFixed(2)}</td>
                            </tr>
                        </tfoot>
                    `;
                    container.appendChild(tableElement);

                    // Store the total score for the current table
                    scores[table.title] = totalScore.toFixed(2);
                });

                // Display the final scores above the tables based on the kebutuhan value
                const finalScoresElement = document.createElement('div');
                finalScoresElement.innerHTML = `
                    <h3>Skor Akhir:</h3>
                    ${kebutuhan === 'gaming' ? `<p>Skor Akhir Gaming: ${scores['Tabel Gaming']}</p>` : ''}
                    ${kebutuhan === 'fotografi' ? `<p>Skor Akhir Fotografi: ${scores['Tabel Fotografi']}</p>` : ''}
                    ${kebutuhan === 'konten_kreator' ? `<p>Skor Akhir Konten Kreator: ${scores['Tabel Konten Kreator']}</p>` : ''}
                    ${kebutuhan === 'sehari_hari' ? `<p>Skor Akhir Sehari-hari: ${scores['Tabel Sehari-hari']}</p>` : ''}
                `;
                container.parentNode.insertBefore(finalScoresElement, container);
            });

            document.querySelectorAll('.toggle-button').forEach(button => {
                button.addEventListener('click', function() {
                    const container = this.nextElementSibling;
                    if (container.style.display === 'none') {
                        container.style.display = 'block';
                    } else {
                        container.style.display = 'none';
                    }
                });
            });

            function getScore(kategori_kriteria, value) {
                // Remove units from the value for numeric criteria
                if (kategori_kriteria !== 'Screen Resolution' && kategori_kriteria !== 'Technology') {
                    value = value.replace(/[^0-9.]/g, '');
                }

                // Make an AJAX request to the server to get the score
                let score = 'N/A';
                $.ajax({
                    url: 'get_score.php', // Create a separate PHP file to handle the score retrieval
                    method: 'POST',
                    data: { kategori_kriteria: kategori_kriteria, value: value },
                    async: false,
                    success: function(response) {
                        console.log("AJAX Response for " + kategori_kriteria + ": " + response);
                        score = response;
                    },
                    error: function(xhr, status, error) {
                        console.error("AJAX Error: " + error);
                    }
                });
                return score;
            }
        });
    </script>
</body>
</html>
