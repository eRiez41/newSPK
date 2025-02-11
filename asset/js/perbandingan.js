// Fungsi untuk membersihkan dan mengonversi nilai harga
function cleanAndConvertHarga(harga) {
    // Menghapus karakter yang tidak valid
    harga = harga.replace(/[Rp\s,]/g, '');
    // Mengonversi ke float
    return parseFloat(harga);
}

// Fungsi untuk menentukan warna berdasarkan perbandingan
function getComparisonColor(value1, value2, reverse = false) {
    if (value1 === value2) {
        return 'blue';
    } else if (reverse) {
        return value1 < value2 ? 'green' : 'red';
    } else {
        return value1 > value2 ? 'green' : 'red';
    }
}

// Fungsi untuk membandingkan resolusi layar
function compareScreenResolution(res1, res2) {
    const [width1, height1] = res1.split(' × ').map(Number);
    const [width2, height2] = res2.split(' × ').map(Number);
    if ((width1 * height1) === (width2 * height2)) {
        return 'blue';
    }
    return (width1 * height1) > (width2 * height2) ? 'green' : 'red';
}

// Fungsi untuk membandingkan versi Android
function compareAndroidVersion(version1, version2) {
    const matches1 = version1.match(/Android (\d+)/);
    const matches2 = version2.match(/Android (\d+)/);
    const androidVersion1 = matches1 ? parseInt(matches1[1], 10) : 0;
    const androidVersion2 = matches2 ? parseInt(matches2[1], 10) : 0;
    if (androidVersion1 === androidVersion2) {
        return 'blue';
    }
    return androidVersion1 > androidVersion2 ? 'green' : 'red';
}

// Fungsi untuk membandingkan jaringan
function compareNetwork(network1, network2) {
    const networks1 = network1.split(', ');
    const networks2 = network2.split(', ');
    if (networks1.length === networks2.length) {
        return 'blue';
    }
    return networks1.length > networks2.length ? 'green' : 'red';
}

// Fungsi untuk membandingkan sensor
function compareSensors(sensors1, sensors2) {
    const sensors1Array = sensors1.split(', ');
    const sensors2Array = sensors2.split(', ');
    if (sensors1Array.length === sensors2Array.length) {
        return 'blue';
    }
    return sensors1Array.length > sensors2Array.length ? 'green' : 'red';
}

// Fungsi untuk membandingkan RAM dan memori internal
function compareRamAndMemory(value1, value2) {
    // Menghapus karakter yang tidak valid
    value1 = value1.replace(/[GB\s]/g, '');
    value2 = value2.replace(/[GB\s]/g, '');
    // Mengonversi ke integer
    value1 = parseInt(value1, 10);
    value2 = parseInt(value2, 10);
    if (value1 === value2) {
        return 'blue';
    }
    return value1 > value2 ? 'green' : 'red';
}

// Fungsi untuk membandingkan skor AnTuTu
function compareAntutuScore(score1, score2) {
    // Mengonversi skor ke integer
    score1 = parseInt(score1, 10);
    score2 = parseInt(score2, 10);
    if (score1 === score2) {
        return 'blue';
    }
    return score1 > score2 ? 'green' : 'red';
}

// Fungsi untuk menampilkan perbandingan smartphone
function displayComparison(smartphone, antutuScore, smartphone2, antutuScore2) {
    const container = document.createElement('div');
    container.classList.add('col-xs-6', 'col-sm-6', 'text-center');

    const img = document.createElement('img');
    img.src = smartphone['Image URL'];
    img.alt = smartphone['Nama Produk'];
    img.classList.add('img-fluid', 'mb-3');
    img.style.maxHeight = '200px';
    container.appendChild(img);

    const h2 = document.createElement('h2');
    h2.textContent = `${smartphone['Brand']} ${smartphone['Nama Produk']}`;
    container.appendChild(h2);

    const specs = [
        { label: 'Harga', value: smartphone['Harga'], compare: getComparisonColor(cleanAndConvertHarga(smartphone['Harga']), cleanAndConvertHarga(smartphone2['Harga']), true) },
        { label: 'RAM', value: smartphone['RAM (GB)'], compare: compareRamAndMemory(smartphone['RAM (GB)'], smartphone2['RAM (GB)']) },
        { label: 'Memori Internal', value: smartphone['Memori Internal (GB)'], compare: compareRamAndMemory(smartphone['Memori Internal (GB)'], smartphone2['Memori Internal (GB)']) },
        { label: 'Kapasitas Baterai', value: `${smartphone['Kapasitas Baterai']} mAh`, compare: getComparisonColor(smartphone['Kapasitas Baterai'], smartphone2['Kapasitas Baterai']) },
        { label: 'Resolusi Kamera Belakang', value: `${smartphone['Resolusi Kamera Belakang']} MP`, compare: getComparisonColor(smartphone['Resolusi Kamera Belakang'], smartphone2['Resolusi Kamera Belakang']) },
        { label: 'Resolusi Kamera Depan', value: `${smartphone['Resolusi Kamera Depan']} MP`, compare: getComparisonColor(smartphone['Resolusi Kamera Depan'], smartphone2['Resolusi Kamera Depan']) },
        { label: 'Technology', value: smartphone['Technology'] },
        { label: 'Screen Resolution', value: smartphone['Screen Resolution'], compare: compareScreenResolution(smartphone['Screen Resolution'], smartphone2['Screen Resolution']) },
        { label: 'Daya Fast Charging', value: smartphone['Daya Fast Charging'], compare: getComparisonColor(smartphone['Daya Fast Charging'], smartphone2['Daya Fast Charging']) },
        { label: 'Skor AnTuTu', value: antutuScore, compare: compareAntutuScore(antutuScore, antutuScore2) },
        { label: 'Prosesor', value: smartphone['Prosesor'] },
        { label: 'GPU', value: smartphone['GPU'] },
        { label: 'Ukuran Layar', value: `${smartphone['Ukuran Layar']} Inci`, compare: getComparisonColor(smartphone['Ukuran Layar'], smartphone2['Ukuran Layar']) },
        { label: 'Sistem Operasi', value: smartphone['OS Version & Version Detail'] || 'N/A', compare: compareAndroidVersion(smartphone['OS Version & Version Detail'], smartphone2['OS Version & Version Detail']) },
        { label: 'Jaringan', value: smartphone['Jaringan'], compare: compareNetwork(smartphone['Jaringan'], smartphone2['Jaringan']) },
        { label: 'USB', value: smartphone['USB'] },
        { label: 'Sensor', value: smartphone['Sensor'], compare: compareSensors(smartphone['Sensor'], smartphone2['Sensor']) },
        { label: 'Material', value: smartphone['Material'] },
        { label: 'Tahun Rilis', value: smartphone['Tahun Rilis'] || 'N/A', compare: getComparisonColor(smartphone['Tahun Rilis'], smartphone2['Tahun Rilis']) }
    ];

    specs.forEach(({ label, value, compare }) => {
        const p = document.createElement('p');
        p.innerHTML = `<strong>${label}:</strong> <span class="${compare || ''}">${value}</span>`;
        container.appendChild(p);
    });

    return container;
}

document.addEventListener('DOMContentLoaded', () => {
    const comparisonResult = document.getElementById('comparisonResult');
    if (comparisonResult) {
        const smartphone1 = JSON.parse(comparisonResult.dataset.smartphone1);
        const smartphone2 = JSON.parse(comparisonResult.dataset.smartphone2);
        const antutuScore1 = comparisonResult.dataset.antutuScore1;
        const antutuScore2 = comparisonResult.dataset.antutuScore2;

        const row = document.createElement('div');
        row.classList.add('row');

        row.appendChild(displayComparison(smartphone1, antutuScore1, smartphone2, antutuScore2));
        row.appendChild(displayComparison(smartphone2, antutuScore2, smartphone1, antutuScore1));

        comparisonResult.appendChild(row);
    }
});
