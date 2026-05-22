<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['id_user'])) {
    header("Location: login.php");
    exit;
}

$id_user = $_SESSION['id_user'];

// Mengambil id mahasiswa berdasarkan akun yang sedang login
$queryMahasiswa = mysqli_query($koneksi, "
    SELECT id_mahasiswa 
    FROM mahasiswa 
    WHERE id_user = '$id_user'
");

$mahasiswa = mysqli_fetch_assoc($queryMahasiswa);

if (!$mahasiswa) {
    die("Data mahasiswa tidak ditemukan.");
}

$id_mahasiswa = $mahasiswa['id_mahasiswa'];

// Memastikan data dari form tersedia
if (
    !isset($_POST['id_matkul']) ||
    !isset($_POST['minat']) ||
    !isset($_POST['kesulitan']) ||
    !isset($_POST['relevansi']) ||
    !isset($_POST['nilai_prasyarat'])
) {
    header("Location: pilihmatkul.php");
    exit;
}

$id_matkul = $_POST['id_matkul'];
$minat = $_POST['minat'];
$kesulitan = $_POST['kesulitan'];
$relevansi = $_POST['relevansi'];
$nilai_prasyarat = $_POST['nilai_prasyarat'];

// Menghapus hasil rekomendasi lama agar data tidak dobel
mysqli_query($koneksi, "
    DELETE FROM hasil_rekomendasi 
    WHERE id_mahasiswa = '$id_mahasiswa'
");

// Menentukan bobot kriteria
$bobotMinat = 0.30;
$bobotKesulitan = 0.20;
$bobotRelevansi = 0.30;
$bobotPrasyarat = 0.20;

// Mengkonversi nilai huruf menjadi angka
function nilaiHurufKeAngka($nilai) {
    if ($nilai == "A") {
        return 5;
    } elseif ($nilai == "B") {
        return 4;
    } elseif ($nilai == "C") {
        return 3;
    } elseif ($nilai == "D") {
        return 2;
    } elseif ($nilai == "E") {
        return 1;
    } else {
        return 0;
    }
}

// Menampung semua data penilaian dari form
$dataPenilaian = [];

for ($i = 0; $i < count($id_matkul); $i++) {
    $idMatkul = mysqli_real_escape_string($koneksi, $id_matkul[$i]);

    // Mengambil id_tema berdasarkan mata kuliah
    $queryMatkul = mysqli_query($koneksi, "
        SELECT id_tema 
        FROM mata_kuliah 
        WHERE id_matkul = '$idMatkul'
    ");

    $matkul = mysqli_fetch_assoc($queryMatkul);

    if (!$matkul) {
        continue;
    }

    $dataPenilaian[] = [
        'id_tema' => $matkul['id_tema'],
        'id_matkul' => $idMatkul,
        'minat' => (float) $minat[$i],
        'kesulitan' => (float) $kesulitan[$i],
        'relevansi' => (float) $relevansi[$i],
        'nilai_prasyarat' => (float) nilaiHurufKeAngka($nilai_prasyarat[$i])
    ];
}

// Mengelompokkan data berdasarkan tema
$dataPerTema = [];

foreach ($dataPenilaian as $data) {
    $idTema = $data['id_tema'];

    if (!isset($dataPerTema[$idTema])) {
        $dataPerTema[$idTema] = [];
    }

    $dataPerTema[$idTema][] = $data;
}

// Memproses SAW dilakukan per tema
foreach ($dataPerTema as $idTema => $listMatkul) {
    // Mengambil nilai maksimum dan minimum untuk normalisasi
    $maxMinat = max(array_column($listMatkul, 'minat'));
    $minKesulitan = min(array_column($listMatkul, 'kesulitan'));
    $maxRelevansi = max(array_column($listMatkul, 'relevansi'));
    $maxPrasyarat = max(array_column($listMatkul, 'nilai_prasyarat'));

    $hasilSAW = [];

    foreach ($listMatkul as $data) {
        // Normalisasi masing-masing kriteria

        // Minat = benefit
        $normalMinat = $data['minat'] / $maxMinat;

        // Tingkat Kesulitan = cost
        $normalKesulitan = $minKesulitan / $data['kesulitan'];

        // Relevansi Kerja = benefit
        $normalRelevansi = $data['relevansi'] / $maxRelevansi;

        // Nilai Prasyarat = benefit
        $normalPrasyarat = $data['nilai_prasyarat'] / $maxPrasyarat;

        // Menghitung nilai preferensi
        $nilaiPreferensi =
            ($normalMinat * $bobotMinat) +
            ($normalKesulitan * $bobotKesulitan) +
            ($normalRelevansi * $bobotRelevansi) +
            ($normalPrasyarat * $bobotPrasyarat);

        $hasilSAW[] = [
            'id_tema' => $data['id_tema'],
            'id_matkul' => $data['id_matkul'],
            'skor' => $nilaiPreferensi
        ];
    }

    // Menentukan hasil ranking berdasarkan skor tertinggi
    usort($hasilSAW, function ($a, $b) {
        return $b['skor'] <=> $a['skor'];
    });

    $ranking = 1;

    foreach ($hasilSAW as $hasil) {
        $idTema = $hasil['id_tema'];
        $idMatkul = $hasil['id_matkul'];

        // Skor dibuat 2 angka di belakang koma, misalnya 0.87
        $skor = round($hasil['skor'], 2);

        if ($ranking == 1) {
            $rekomendasi = "Terpilih";
        } else {
            $rekomendasi = "Alternatif";
        }

        mysqli_query($koneksi, "
            INSERT INTO hasil_rekomendasi 
            (id_mahasiswa, id_tema, id_matkul, skor, rekomendasi, ranking)
            VALUES 
            ('$id_mahasiswa', '$idTema', '$idMatkul', '$skor', '$rekomendasi', '$ranking')
        ");

        $ranking++;
    }
}

header("Location: rekomendasi.php");
exit;
?>