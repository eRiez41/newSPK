<?php
// ../algoritma/perbandingan.php

// Fungsi untuk membersihkan dan mengonversi nilai harga
function cleanAndConvertHarga($harga) {
    // Menghapus karakter yang tidak valid
    $harga = str_replace(['Rp', ' ', ','], '', $harga);
    // Mengonversi ke float
    return (float) $harga;
}

// Fungsi untuk menentukan warna berdasarkan perbandingan
function getComparisonColor($value1, $value2, $reverse = false) {
    if ($reverse) {
        return $value1 < $value2 ? 'green' : 'red';
    } else {
        return $value1 > $value2 ? 'green' : 'red';
    }
}

// Fungsi untuk membandingkan resolusi layar
function compareScreenResolution($res1, $res2) {
    list($width1, $height1) = explode(' × ', $res1);
    list($width2, $height2) = explode(' × ', $res2);
    return ($width1 * $height1) > ($width2 * $height2) ? 'green' : 'red';
}

// Fungsi untuk membandingkan versi Android
function compareAndroidVersion($version1, $version2) {
    preg_match('/Android (\d+)/', $version1, $matches1);
    preg_match('/Android (\d+)/', $version2, $matches2);
    $androidVersion1 = isset($matches1[1]) ? (int)$matches1[1] : 0;
    $androidVersion2 = isset($matches2[1]) ? (int)$matches2[1] : 0;
    return $androidVersion1 > $androidVersion2 ? 'green' : 'red';
}

// Fungsi untuk membandingkan jaringan
function compareNetwork($network1, $network2) {
    $networks1 = explode(', ', $network1);
    $networks2 = explode(', ', $network2);
    return count($networks1) > count($networks2) ? 'green' : 'red';
}

// Fungsi untuk membandingkan sensor
function compareSensors($sensors1, $sensors2) {
    $sensors1 = explode(', ', $sensors1);
    $sensors2 = explode(', ', $sensors2);
    return count($sensors1) > count($sensors2) ? 'green' : 'red';
}

// Fungsi untuk membandingkan RAM dan memori internal
function compareRamAndMemory($value1, $value2) {
    // Menghapus karakter yang tidak valid
    $value1 = str_replace(['GB', ' '], '', $value1);
    $value2 = str_replace(['GB', ' '], '', $value2);
    // Mengonversi ke integer
    $value1 = (int) $value1;
    $value2 = (int) $value2;
    return $value1 > $value2 ? 'green' : 'red';
}
?>
