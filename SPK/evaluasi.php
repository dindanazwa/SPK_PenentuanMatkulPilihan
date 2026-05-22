<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['id_user'])) {
    header("Location: login.php");
    exit;
}

$id_user = $_SESSION['id_user'];

// Ambil mahasiswa yang sedang login
$queryMahasiswa = mysqli_query($koneksi, "
    SELECT id_mahasiswa, nama_mahasiswa, nim
    FROM mahasiswa
    WHERE id_user = '$id_user'
");

$mahasiswa = mysqli_fetch_assoc($queryMahasiswa);

if (!$mahasiswa) {
    die("Data mahasiswa tidak ditemukan.");
}

$id_mahasiswa = $mahasiswa['id_mahasiswa'];
$nama_mahasiswa = $mahasiswa['nama_mahasiswa'];
$nim_mahasiswa = $mahasiswa['nim'];


// Ground truth dari pakar/dosen
// Angka 1 berarti paling direkomendasikan
$ground_truth = [
    'Tema A' => [
        'Paradigma Berpikir Sistem dan Desain' => 1,
        'Paradigma Berpikir Komputasi' => 2
    ],

    'Tema B' => [
        'Manajemen Layanan Teknologi Informasi' => 1,
        'Manajemen Proyek Teknologi Informasi' => 2
    ],

    'Tema C' => [
        'Pembelajaran Sosial Emosional' => 2,
        'Pembelajaran Inklusi' => 5,
        'Pendidikan Orang Dewasa dan Berkelanjutan' => 3,
        'Manajemen Sistem Pendidikan' => 4,
        'Pengembangan Program Pelatihan Teknologi Informasi' => 1
    ],

    'Tema D' => [
        'Sistem Pendukung Keputusan' => 1,
        'Sistem Belajar Mesin' => 2
    ],

    'Tema E' => [
        'Teknologi Komputasi Awan' => 1,
        'Teknologi Peranti Internet' => 2
    ]
];


// Ambil hasil ranking SPK dari database
$query = mysqli_query($koneksi, "
    SELECT 
        tema.nama_tema,
        mata_kuliah.nama_matkul,
        hasil_rekomendasi.ranking,
        hasil_rekomendasi.skor
    FROM hasil_rekomendasi
    JOIN tema 
        ON hasil_rekomendasi.id_tema = tema.id_tema
    JOIN mata_kuliah 
        ON hasil_rekomendasi.id_matkul = mata_kuliah.id_matkul
    WHERE hasil_rekomendasi.id_mahasiswa = '$id_mahasiswa'
    ORDER BY tema.id_tema ASC, hasil_rekomendasi.ranking ASC
");

$hasil_spk = [];

while ($row = mysqli_fetch_assoc($query)) {
    $tema = $row['nama_tema'];

    if (!isset($hasil_spk[$tema])) {
        $hasil_spk[$tema] = [];
    }

    $hasil_spk[$tema][] = [
        'nama_matkul' => $row['nama_matkul'],
        'ranking' => (int) $row['ranking'],
        'skor' => $row['skor']
    ];
}


// Fungsi menghitung Spearman Rank Correlation
function hitungSpearman($hasilTema, $groundTruthTema) {
    $n = count($hasilTema);

    if ($n < 2) {
        return 0;
    }

    $sum_d2 = 0;

    foreach ($hasilTema as $item) {
        $nama_matkul = $item['nama_matkul'];
        $rank_spk = $item['ranking'];

        if (!isset($groundTruthTema[$nama_matkul])) {
            continue;
        }

        $rank_pakar = $groundTruthTema[$nama_matkul];
        $d = $rank_spk - $rank_pakar;
        $sum_d2 += $d * $d;
    }

    $rs = 1 - (6 * $sum_d2) / ($n * (($n * $n) - 1));

    return $rs;
}


// Evaluasi per tema
$total_spearman = 0;
$jumlah_tema_dihitung = 0;
$total_top1_cocok = 0;
$hasil_evaluasi = [];

foreach ($hasil_spk as $tema => $hasilTema) {
    if (!isset($ground_truth[$tema])) {
        continue;
    }

    $groundTruthTema = $ground_truth[$tema];

    // Spearman per tema
    $spearman = hitungSpearman($hasilTema, $groundTruthTema);

    // Top-1 SPK
    $top_spk = $hasilTema[0]['nama_matkul'];

    // Top-1 pakar
    $top_pakar = array_search(1, $groundTruthTema);

    // Akurasi top-1
    $top1_cocok = ($top_spk == $top_pakar) ? 1 : 0;

    $total_spearman += $spearman;
    $jumlah_tema_dihitung++;
    $total_top1_cocok += $top1_cocok;

    $hasil_evaluasi[] = [
        'tema' => $tema,
        'top_spk' => $top_spk,
        'top_pakar' => $top_pakar,
        'spearman' => $spearman,
        'top1_cocok' => $top1_cocok
    ];
}


// Rata-rata hasil evaluasi
$rata_spearman = 0;
$akurasi_top1 = 0;

if ($jumlah_tema_dihitung > 0) {
    $rata_spearman = $total_spearman / $jumlah_tema_dihitung;
    $akurasi_top1 = ($total_top1_cocok / $jumlah_tema_dihitung) * 100;
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Evaluasi SPK | SPK Mata Kuliah Pilihan</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">

    <style>
        * { 
            box-sizing: border-box; 
        }

        html, body {
            width: 100%;
            min-height: 100%;
            margin: 0;
            font-family: "Inter", sans-serif;
        }

        body {
            min-height: 100vh;
            background-color: #f8f9fa;
            background-image: radial-gradient(#dee2e6 0.5px, transparent 0.5px);
            background-size: 24px 24px;
            display: flex;
            align-items: flex-start;
            justify-content: center;
            overflow-y: auto;
            padding: 14px;
            color: #172033;
        }

        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 500, 'GRAD' 0, 'opsz' 24;
        }

        .page-card {
            width: min(1100px, 100%);
            min-height: min(650px, calc(100vh - 28px));
            height: auto;
            background: #ffffff;
            border: 1px solid #d6dbe3;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
            display: flex;
            flex-direction: column;
        }

        .topbar {
            height: 52px;
            padding: 0 24px;
            border-bottom: 1px solid #edf0f4;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #ffffff;
            flex-shrink: 0;
        }

        .brand {
            font-size: 17px;
            font-weight: 800;
            color: #001f3f;
        }

        .user-area {
            display: flex;
            align-items: center;
            gap: 12px;
            color: #6b7280;
        }

        .user-area > .material-symbols-outlined {
            font-size: 20px;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .avatar {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: #001f3f;
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .avatar .material-symbols-outlined {
            font-size: 20px;
        }

        .user-text {
            text-align: right;
        }

        .user-name {
            margin: 0;
            font-size: 12px;
            font-weight: 800;
            color: #001f3f;
        }

        .user-nim {
            margin: 0;
            font-size: 10px;
            color: #6b7280;
        }

        .main-content {
            flex: 1;
            background: #f7f9fc;
            padding: 14px 22px;
            overflow: visible;
            display: flex;
            flex-direction: column;
            gap: 9px;
            min-height: 0;
        }

        .page-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 18px;
            flex-shrink: 0;
        }

        .page-header h1 {
            margin: 0 0 4px;
            font-size: 23px;
            line-height: 1.1;
            color: #001f3f;
            font-weight: 900;
        }

        .page-header p {
            margin: 0;
            font-size: 12px;
            line-height: 1.35;
            color: #6b7280;
            max-width: 660px;
        }

        .btn {
            height: 38px;
            padding: 0 15px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 800;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            transition: 0.2s ease;
            white-space: nowrap;
            text-decoration: none;
        }

        .btn .material-symbols-outlined {
            font-size: 18px;
        }

        .btn-primary {
            background: #001f3f;
            border: 1px solid #001f3f;
            color: #ffffff;
        }

        .btn-primary:hover { 
            background: #00152b; 
        }

        .btn-secondary {
            background: #ffffff;
            border: 1px solid #cbd5e1;
            color: #334155;
        }

        .btn-secondary:hover {
            background: #f1f5f9;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(2, 230px);
            gap: 10px;
            justify-content: start;
            flex-shrink: 0;
        }

        .summary-card {
            background: #ffffff;
            border: 1px solid #e2e7ef;
            border-radius: 10px;
            padding: 9px 12px;
            display: flex;
            align-items: center;
            gap: 9px;
            box-shadow: 0 3px 10px rgba(15, 23, 42, 0.035);
            min-height: 56px;
        }

        .summary-icon {
            width: 29px;
            height: 29px;
            border-radius: 8px;
            background: #eef4ff;
            color: #001f3f;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .summary-icon .material-symbols-outlined {
            font-size: 18px;
        }

        .summary-label {
            margin: 0 0 1px;
            font-size: 10px;
            color: #64748b;
            font-weight: 700;
        }

        .summary-value {
            margin: 0;
            font-size: 13px;
            color: #0f172a;
            font-weight: 900;
        }

        .table-card {
            flex: 1;
            background: #ffffff;
            border: 1px solid #e2e7ef;
            border-radius: 12px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            min-height: 0;
        }

        .table-header {
            padding: 9px 16px;
            border-bottom: 1px solid #e5eaf2;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-shrink: 0;
        }

        .table-header h2 {
            margin: 0;
            font-size: 16px;
            font-weight: 900;
            color: #001f3f;
        }

        .table-wrap {
            overflow-x: auto;
            flex: 1;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }

        thead { 
            background: #f8fafc; 
        }

        th {
            padding: 9px 14px;
            text-align: left;
            color: #334155;
            font-size: 11px;
            font-weight: 900;
            border-bottom: 1px solid #e5eaf2;
            white-space: nowrap;
        }

        td {
            padding: 8px 14px;
            border-bottom: 1px solid #edf0f4;
            vertical-align: middle;
            color: #1f2937;
        }

        tr:hover { 
            background: #f8fafc; 
        }

        .score {
            font-weight: 900;
            color: #0f172a;
            font-size: 13px;
        }

        .baik {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 92px;
            height: 28px;
            border-radius: 8px;
            background: #0a8f3c;
            color: #ffffff;
            font-size: 10px;
            font-weight: 900;
            text-transform: uppercase;
        }

        .tidak {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 92px;
            height: 28px;
            border-radius: 8px;
            background: #90021f;
            color: #ffffff;
            font-size: 10px;
            font-weight: 900;
            text-transform: uppercase;
        }

        .empty-row {
            text-align: center;
            color: #6b7280;
            font-weight: 700;
            padding: 24px;
        }

        .footer-note {
            padding: 8px 14px;
            background: #ffffff;
            border-top: 1px solid #e5eaf2;
            display: flex;
            justify-content: space-between;
            gap: 14px;
            align-items: center;
            font-size: 11px;
            color: #6b7280;
            flex-shrink: 0;
        }

        .footer-right {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 8px;
        }

        .status-text {
            font-size: 11px;
            color: #6b7280;
        }

        .footer-note strong { 
            color: #001f3f; 
        }

        @media (max-width: 950px) {
            body {
                overflow-y: auto;
                align-items: flex-start;
            }

            .page-card {
                height: auto;
                min-height: calc(100vh - 28px);
            }

            .topbar {
                height: auto;
                padding: 14px 18px;
                gap: 12px;
            }

            .page-header { 
                flex-direction: column; 
            }

            .btn { 
                width: 100%; 
            }

            .summary-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .table-wrap { 
                overflow-x: auto; 
            }

            table { 
                min-width: 850px; 
            }
        }
    </style>
</head>

<body>
    <main class="page-card">
        <header class="topbar">
            <div class="brand">Sistem Pendukung Keputusan</div>

            <div class="user-area">
                <span class="material-symbols-outlined">notifications</span>
                <span class="material-symbols-outlined">help</span>

                <div class="user-profile">
                    <div class="user-text">
                        <p class="user-name">
                            <?= htmlspecialchars($nama_mahasiswa); ?>
                        </p>
                        <p class="user-nim">
                            NIM <?= htmlspecialchars($nim_mahasiswa); ?>
                        </p>
                    </div>

                    <div class="avatar">
                        <span class="material-symbols-outlined">person</span>
                    </div>
                </div>
            </div>
        </header>

        <section class="main-content">
            <div class="page-header">
                <div>
                    <h1>Evaluasi Hasil SPK</h1>
                    <p>
                        Evaluasi ini membandingkan hasil ranking SPK dengan ranking manual dari dosen.
                        Mahasiswa yang dievaluasi: <strong><?= htmlspecialchars($nama_mahasiswa); ?></strong>
                    </p>
                </div>

                <a href="rekomendasi.php" class="btn btn-primary">
                    <span class="material-symbols-outlined">arrow_back</span>
                    Kembali ke Hasil Rekomendasi
                </a>
            </div>

            <div class="summary-grid">
                <div class="summary-card">
                    <div class="summary-icon">
                        <span class="material-symbols-outlined">monitoring</span>
                    </div>
                    <div>
                        <p class="summary-label">Rata-rata Spearman</p>
                        <p class="summary-value"><?= number_format($rata_spearman, 2); ?></p>
                    </div>
                </div>

                <div class="summary-card">
                    <div class="summary-icon">
                        <span class="material-symbols-outlined">verified</span>
                    </div>
                    <div>
                        <p class="summary-label">Akurasi Top-1</p>
                        <p class="summary-value"><?= number_format($akurasi_top1, 2); ?>%</p>
                    </div>
                </div>
            </div>

            <div class="table-card">
                <div class="table-header">
                    <h2>Hasil Evaluasi Ranking</h2>
                </div>

                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Tema</th>
                                <th>Top-1 SPK</th>
                                <th>Top-1 Pakar</th>
                                <th>Spearman</th>
                                <th>Kesesuaian Top-1</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php if (count($hasil_evaluasi) > 0) { ?>
                                <?php foreach ($hasil_evaluasi as $evaluasi) { ?>
                                    <tr>
                                        <td><?= htmlspecialchars($evaluasi['tema']); ?></td>
                                        <td><?= htmlspecialchars($evaluasi['top_spk']); ?></td>
                                        <td><?= htmlspecialchars($evaluasi['top_pakar']); ?></td>
                                        <td class="score"><?= number_format($evaluasi['spearman'], 2); ?></td>
                                        <td>
                                            <?php if ($evaluasi['top1_cocok'] == 1) { ?>
                                                <span class="baik">Cocok</span>
                                            <?php } else { ?>
                                                <span class="tidak">Tidak Cocok</span>
                                            <?php } ?>
                                        </td>
                                    </tr>
                                <?php } ?>
                            <?php } else { ?>
                                <tr>
                                    <td colspan="5" class="empty-row">
                                        Belum ada data evaluasi. Silakan lakukan penilaian dan lihat hasil rekomendasi terlebih dahulu.
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>

                <div class="footer-note">
                    <div>Spearman digunakan untuk melihat kesesuaian urutan ranking SPK dengan ranking pakar.</div>

                    <div class="footer-right">
                        <div class="status-text">
                            Status: <strong><?= count($hasil_evaluasi) > 0 ? 'Selesai' : 'Belum Ada Data'; ?></strong>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
</body>
</html>
