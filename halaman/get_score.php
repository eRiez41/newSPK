<?php
include '../koneksi/koneksi.php';

if (isset($_POST['kategori_kriteria']) && isset($_POST['value'])) {
    $kategori_kriteria = $_POST['kategori_kriteria'];
    $value = $_POST['value'];

    // Handle Screen Resolution and Technology separately
    if ($kategori_kriteria == 'Screen Resolution' || $kategori_kriteria == 'Technology') {
        $sql = "
            SELECT skor
            FROM skoring
            WHERE kategori_kriteria = '$kategori_kriteria'
            AND nilai_string = '$value';
        ";
    } else {
        // Handle numeric values (e.g., GB, W)
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
    }

    // Debugging: Print the SQL query
    error_log("SQL Query: " . $sql);

    $result = $conn->query($sql);
    if ($result) {
        $row = $result->fetch_assoc();
        // Debugging: Print the result
        error_log("SQL Result: " . print_r($row, true));
        echo $row ? $row['skor'] : 'N/A';
    } else {
        // Debugging: Print the error
        error_log("SQL Error: " . $conn->error);
        echo 'N/A';
    }
} else {
    echo 'N/A';
}
?>
