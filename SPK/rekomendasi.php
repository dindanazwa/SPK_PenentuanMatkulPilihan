<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['id_user'])) {
    header("Location: login.php");
    exit;
}

$id_user = $_SESSION['id_user'];

$queryMahasiswa = mysqli_query($koneksi, "
    SELECT * FROM mahasiswa 
    WHERE id_user = '$id_user'
");

$mahasiswa = mysqli_fetch_assoc($queryMahasiswa);

if (!$mahasiswa) {
    $mahasiswa = [
        'id_mahasiswa' => 0,
        'nama_mahasiswa' => 'Nama Mahasiswa',
        'nim' => 'Belum tersedia'
    ];
}

$id_mahasiswa = $mahasiswa['id_mahasiswa'];

// Hitung jumlah tema
$queryJumlahTema = mysqli_query($koneksi, "
    SELECT COUNT(*) AS total_tema FROM tema
");
$jumlahTema = mysqli_fetch_assoc($queryJumlahTema);

$mode = isset($_GET['mode']) ? $_GET['mode'] : 'terpilih';

$filterRanking = "";

if ($mode == 'terpilih') {
    $filterRanking = "AND hasil_rekomendasi.ranking = 1";
}

$queryHasil = mysqli_query($koneksi, "
    SELECT 
        mahasiswa.nama_mahasiswa,
        tema.nama_tema,
        mata_kuliah.nama_matkul,
        hasil_rekomendasi.skor,
        hasil_rekomendasi.rekomendasi,
        hasil_rekomendasi.ranking,
        hasil_rekomendasi.tanggal
    FROM hasil_rekomendasi
    JOIN mahasiswa 
        ON hasil_rekomendasi.id_mahasiswa = mahasiswa.id_mahasiswa
    JOIN tema 
        ON hasil_rekomendasi.id_tema = tema.id_tema
    JOIN mata_kuliah 
        ON hasil_rekomendasi.id_matkul = mata_kuliah.id_matkul
    WHERE hasil_rekomendasi.id_mahasiswa = '$id_mahasiswa'
    $filterRanking
    ORDER BY tema.id_tema ASC, hasil_rekomendasi.ranking ASC
");

$hasilRekomendasi = [];

while ($row = mysqli_fetch_assoc($queryHasil)) {
    $hasilRekomendasi[] = $row;
}

$jumlahTerpilih = count($hasilRekomendasi);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Hasil Rekomendasi | SPK Mata Kuliah Pilihan</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">

    <style>
        * { box-sizing: border-box; }

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
        }

        .btn .material-symbols-outlined {
            font-size: 18px;
        }

        .btn-primary {
            background: #001f3f;
            border: 1px solid #001f3f;
            color: #ffffff;
        }

        .btn-primary:hover { background: #00152b; }

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

        thead { background: #f8fafc; }

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

        tr:hover { background: #f8fafc; }

        .selected-row { background: #fbfffd; }

        .student-name-table {
            font-weight: 800;
            color: #172033;
            white-space: nowrap;
        }

        .theme-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 30px;
            height: 24px;
            padding: 0 8px;
            border-radius: 7px;
            background: #eef4ff;
            color: #001f3f;
            font-weight: 900;
        }

        .course-name {
            font-weight: 800;
            color: #172033;
            line-height: 1.25;
        }

        .score {
            font-weight: 900;
            color: #0f172a;
            font-size: 13px;
        }

        .recommendation {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            width: 120px;
            height: 34px;
            border-radius: 10px;
            color: #ffffff;
            font-size: 11px;
            font-weight: 900;
            text-transform: uppercase;
            text-align: center;
            white-space: nowrap;
        }

        .recommendation .material-symbols-outlined {
            font-size: 16px;
        }

        .recommendation-terpilih {
            background: #0a8f3c;
        }

        .recommendation-alternatif {
            background: #90021f;
            color: #ffffff;
        }

        .recommendation .material-symbols-outlined {
            font-size: 13px;
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

        .filter-buttons {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-secondary {
            background: #ffffff;
            border: 1px solid #cbd5e1;
            color: #334155;
            text-decoration: none;
        }

        .btn-primary {
            text-decoration: none;
        }

        .footer-note strong { color: #001f3f; }

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

            .page-header { flex-direction: column; }

            .btn { width: 100%; }

            .summary-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            table { min-width: 850px; }
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
                            <?= htmlspecialchars($mahasiswa['nama_mahasiswa']); ?>
                        </p>
                        <p class="user-nim">
                            NIM <?= htmlspecialchars($mahasiswa['nim']); ?>
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
                    <h1>Hasil Rekomendasi Mata Kuliah</h1>
                    <p>
                        Berikut hasil rekomendasi mata kuliah pilihan berdasarkan penilaian
                        minat, tingkat kesulitan, relevansi kerja, dan nilai prasyarat.
                    </p>
                </div>

                <div class="header-actions">

                    <button class="btn btn-primary" type="button" onclick="kembaliDashboard()">
                        <span class="material-symbols-outlined">home</span>
                        Kembali ke Dashboard
                    </button>
                </div>
            </div>

            <div class="summary-grid">
                <div class="summary-card">
                    <div class="summary-icon">
                        <span class="material-symbols-outlined">category</span>
                    </div>
                    <div>
                        <p class="summary-label">Jumlah Tema</p>
                        <p class="summary-value">
                            <?= htmlspecialchars($jumlahTema['total_tema']); ?> Tema
                        </p>
                    </div>
                </div>

                <div class="summary-card">
                    <div class="summary-icon">
                        <span class="material-symbols-outlined">menu_book</span>
                    </div>
                    <div>
                        <p class="summary-label">Mata Kuliah Terpilih</p>
                        <p class="summary-value">
                            <?= $jumlahTerpilih; ?> Mata Kuliah
                        </p>
                    </div>
                </div>
            </div>

            <div class="table-card">
                <div class="table-header">
                    <h2>Rekomendasi Teratas</h2>
                </div>

                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Nama Mahasiswa</th>
                                <th>Tema</th>
                                <th>Mata Kuliah</th>
                                <th>Skor</th>
                                <th>Rekomendasi</th>
                                <th>Tanggal</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php if ($jumlahTerpilih > 0) { ?>
                                <?php foreach ($hasilRekomendasi as $hasil) { ?>
                                    <tr class="selected-row">
                                        <td class="student-name-table">
                                            <?= htmlspecialchars($hasil['nama_mahasiswa']); ?>
                                        </td>

                                        <td>
                                            <span class="theme-badge">
                                                <?= htmlspecialchars($hasil['nama_tema']); ?>
                                            </span>
                                        </td>

                                        <td class="course-name">
                                            <?= htmlspecialchars($hasil['nama_matkul']); ?>
                                        </td>

                                        <td class="score">
                                            <?= number_format($hasil['skor'], 2); ?>
                                        </td>

                                        <td>
                                            <?php
                                            $kelasRekomendasi = strtolower($hasil['rekomendasi']) == 'terpilih'
                                                ? 'recommendation recommendation-terpilih'
                                                : 'recommendation recommendation-alternatif';
                                            ?>
                                            <span class="<?= $kelasRekomendasi; ?>">
                                                <span class="material-symbols-outlined">check_circle</span>
                                                <?= htmlspecialchars($hasil['rekomendasi']); ?>
                                            </span>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($hasil['tanggal']); ?>
                                        </td>
                                    </tr>
                                <?php } ?>
                            <?php } else { ?>
                                <tr>
                                    <td colspan="6" class="empty-row">
                                        Belum ada hasil rekomendasi. Silakan isi penilaian mata kuliah terlebih dahulu.
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>

                <div class="footer-note">
                    <div>Mata kuliah dengan skor tertinggi pada setiap tema ditampilkan sebagai rekomendasi.</div>

                    <div class="footer-note">
                        <div>
                            Mata kuliah dengan skor tertinggi pada setiap tema ditampilkan sebagai rekomendasi.
                        </div>

                        <div class="footer-right">
                            <div class="status-text">
                                Status: <strong><?= $jumlahTerpilih > 0 ? 'Selesai' : 'Belum Ada Data'; ?></strong>
                            </div>

                            <div class="filter-buttons">
                                <a 
                                    class="btn <?= $mode == 'terpilih' ? 'btn-primary' : 'btn-secondary'; ?>" 
                                    href="rekomendasi.php?mode=terpilih"
                                >
                                    Terpilih
                                </a>

                                <a 
                                    class="btn <?= $mode == 'semua' ? 'btn-primary' : 'btn-secondary'; ?>" 
                                    href="rekomendasi.php?mode=semua"
                                >
                                    Alternatif
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <script>
        function kembaliDashboard() {
            window.location.href = "dashboard.php";
        }
    </script>
</body>
</html>