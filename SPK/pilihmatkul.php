<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['id_user'])) {
    header("Location: login.php");
    exit;
}

$id_user = $_SESSION['id_user'];

// Ambil data mahasiswa yang sedang login
$queryMahasiswa = mysqli_query($koneksi, "
    SELECT * FROM mahasiswa 
    WHERE id_user = '$id_user'
");

$mahasiswa = mysqli_fetch_assoc($queryMahasiswa);

if (!$mahasiswa) {
    $mahasiswa = [
        'nama_mahasiswa' => 'Nama Mahasiswa',
        'nim' => 'Belum tersedia'
    ];
}

// Ambil data tema dan mata kuliah dari database
$queryMatkul = mysqli_query($koneksi, "
    SELECT 
        tema.id_tema,
        tema.nama_tema,
        mata_kuliah.id_matkul,
        mata_kuliah.nama_matkul
    FROM tema
    LEFT JOIN mata_kuliah 
        ON tema.id_tema = mata_kuliah.id_tema
    ORDER BY tema.id_tema ASC, mata_kuliah.id_matkul ASC
");

$daftarTema = [];

while ($row = mysqli_fetch_assoc($queryMatkul)) {
    $idTema = $row['id_tema'];

    if (!isset($daftarTema[$idTema])) {
        $daftarTema[$idTema] = [
            'nama_tema' => $row['nama_tema'],
            'mata_kuliah' => []
        ];
    }

    if (!empty($row['id_matkul'])) {
        $daftarTema[$idTema]['mata_kuliah'][] = [
            'id_matkul' => $row['id_matkul'],
            'nama_matkul' => $row['nama_matkul']
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Input Penilaian | SPK Mata Kuliah Pilihan</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">

    <style>
        * {
            box-sizing: border-box;
        }

        html,
        body {
            width: 100%;
            height: 100%;
            margin: 0;
            font-family: "Inter", sans-serif;
        }

        body {
            min-height: 100vh;
            background-color: #f8f9fa;
            background-image: radial-gradient(#dee2e6 0.5px, transparent 0.5px);
            background-size: 24px 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            padding: 24px;
            color: #172033;
        }

        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 500, 'GRAD' 0, 'opsz' 24;
        }

        .page-card {
            width: min(1100px, 100%);
            height: min(650px, calc(100vh - 48px));
            background: #ffffff;
            border: 1px solid #d6dbe3;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
            display: flex;
            flex-direction: column;
        }

        .topbar {
            height: 64px;
            padding: 0 28px;
            border-bottom: 1px solid #edf0f4;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #ffffff;
            flex-shrink: 0;
        }

        .brand {
            font-size: 18px;
            font-weight: 800;
            color: #001f3f;
        }

        .user-area {
            display: flex;
            align-items: center;
            gap: 16px;
            color: #6b7280;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .user-text {
            text-align: right;
        }

        .avatar {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: #001f3f;
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .user-name {
            margin: 0;
            font-size: 13px;
            font-weight: 800;
            color: #001f3f;
        }

        .user-nim {
            margin: 0;
            font-size: 11px;
            color: #6b7280;
        }

        .main-content {
            flex: 1;
            background: #f7f9fc;
            padding: 24px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            min-height: 0;
        }

        .penilaian-form {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 18px;
            min-height: 0;
        }

        .page-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 20px;
            flex-shrink: 0;
        }

        .page-header h1 {
            margin: 0 0 8px;
            font-size: 28px;
            line-height: 1.2;
            color: #001f3f;
            font-weight: 900;
        }

        .page-header p {
            margin: 0;
            font-size: 14px;
            line-height: 1.6;
            color: #6b7280;
        }

        .header-actions {
            display: flex;
            gap: 10px;
        }

        .btn {
            height: 46px;
            padding: 0 20px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 800;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: 0.2s ease;
            white-space: nowrap;
        }

        .btn-secondary {
            background: #ffffff;
            border: 1px solid #cbd5e1;
            color: #334155;
        }

        .btn-secondary:hover {
            background: #f1f5f9;
        }

        .btn-primary {
            background: #001f3f;
            border: 1px solid #001f3f;
            color: #ffffff;
        }

        .btn-primary:hover {
            background: #00152b;
        }

        .instruction-card {
            background: #ffffff;
            border: 1px solid #e2e7ef;
            border-radius: 10px;
            padding: 14px 18px;
            display: flex;
            align-items: center;
            gap: 14px;
            flex-shrink: 0;
        }

        .instruction-icon {
            width: 42px;
            height: 42px;
            border-radius: 8px;
            background: #001f3f;
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .instruction-card h3 {
            margin: 0 0 4px;
            font-size: 14px;
            font-weight: 900;
            color: #001f3f;
        }

        .instruction-card p {
            margin: 0;
            font-size: 13px;
            color: #6b7280;
            line-height: 1.5;
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

        .table-wrap {
            flex: 1;
            min-height: 0;
            overflow-y: auto;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }

        thead {
            position: sticky;
            top: 0;
            z-index: 5;
            background: #ffffff;
        }

        th {
            padding: 16px 18px;
            text-align: left;
            color: #334155;
            font-size: 13px;
            font-weight: 900;
            border-bottom: 1px solid #e5eaf2;
            white-space: nowrap;
            background: #ffffff;
        }

        td {
            padding: 15px 18px;
            border-bottom: 1px solid #edf0f4;
            vertical-align: middle;
        }

        tr:hover {
            background: #f8fafc;
        }

        .theme-row td {
            background: #eef4ff;
            color: #001f3f;
            font-weight: 900;
            padding: 12px 18px;
            border-bottom: 1px solid #dbe7ff;
        }

        .theme-title {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .theme-code {
            width: 28px;
            height: 28px;
            border-radius: 6px;
            background: #001f3f;
            color: #ffffff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            font-weight: 900;
        }

        .mk-name {
            font-weight: 800;
            color: #172033;
            margin-bottom: 4px;
        }

        .mk-code {
            font-size: 11px;
            color: #6b7280;
            font-weight: 600;
        }

        select {
            height: 38px;
            border: 1px solid #d6dbe3;
            border-radius: 7px;
            background: #ffffff;
            color: #172033;
            font-size: 13px;
            font-weight: 700;
            padding: 0 10px;
            outline: none;
            width: 76px;
        }

        select:focus {
            border-color: #001f3f;
            box-shadow: 0 0 0 2px rgba(0, 31, 63, 0.12);
        }

        .footer-note {
            padding: 12px 18px;
            background: #ffffff;
            border-top: 1px solid #e5eaf2;
            display: flex;
            justify-content: space-between;
            gap: 14px;
            align-items: center;
            font-size: 12px;
            color: #6b7280;
            flex-shrink: 0;
        }

        .weight-info {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .weight-badge {
            padding: 5px 9px;
            background: #f1f5f9;
            border-radius: 999px;
            font-weight: 800;
            color: #334155;
        }

        .table-wrap::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        .table-wrap::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 999px;
        }

        @media (max-width: 950px) {
            body {
                overflow-y: auto;
                align-items: flex-start;
            }

            .page-card {
                height: auto;
                min-height: calc(100vh - 48px);
            }

            .main-content {
                overflow: visible;
            }

            .penilaian-form {
                min-height: auto;
            }

            .topbar {
                height: auto;
                padding: 16px 20px;
                gap: 12px;
            }

            .page-header {
                flex-direction: column;
            }

            .header-actions {
                width: 100%;
            }

            .btn {
                flex: 1;
            }

            .table-wrap {
                max-height: 420px;
                overflow-y: auto;
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
            <form action="simpan_penilaian.php" method="POST" class="penilaian-form">
                <div class="page-header">
                    <div>
                        <h1>Input Penilaian Mata Kuliah</h1>
                        <p>
                            Berikan penilaian subjektif Anda untuk membantu sistem menentukan
                            mata kuliah pilihan terbaik pada setiap tema.
                        </p>
                    </div>

                    <div class="header-actions">
                        <button class="btn btn-primary" type="submit">
                            <span class="material-symbols-outlined">save</span>
                            Simpan Nilai
                        </button>

                        <button class="btn btn-secondary" type="button" onclick="window.location.href='dashboard.php'">
                            <span class="material-symbols-outlined">arrow_back</span>
                            Kembali ke Dashboard
                        </button>
                    </div>
                </div>

                <div class="instruction-card">
                    <div class="instruction-icon">
                        <span class="material-symbols-outlined">info</span>
                    </div>
                    <div>
                        <h3>Instruksi Pengisian</h3>
                        <p>
                            Skala 1–5 digunakan untuk Minat, Tingkat Kesulitan, dan Relevansi Kerja.
                            Nilai prasyarat menggunakan pilihan huruf: A, B, C, D, dan E.
                        </p>
                    </div>
                </div>

                <div class="table-card">
                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th style="width: 38%;">Mata Kuliah Pilihan</th>
                                    <th>Minat</th>
                                    <th>Tingkat Kesulitan</th>
                                    <th>Relevansi Kerja</th>
                                    <th>Nilai Prasyarat</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php
                                $kodeTema = 'A';

                                if (count($daftarTema) > 0) {
                                    foreach ($daftarTema as $tema) {
                                ?>
                                        <tr class="theme-row">
                                            <td colspan="5">
                                                <div class="theme-title">
                                                    <span class="theme-code"><?= $kodeTema; ?></span>
                                                    <?= htmlspecialchars($tema['nama_tema']); ?>
                                                </div>
                                            </td>
                                        </tr>

                                        <?php
                                        $nomorMatkul = 1;

                                        if (count($tema['mata_kuliah']) > 0) {
                                            foreach ($tema['mata_kuliah'] as $mk) {
                                        ?>
                                                <tr>
                                                    <td>
                                                        <div class="mk-name">
                                                            <?= htmlspecialchars($mk['nama_matkul']); ?>
                                                        </div>

                                                        <div class="mk-code">
                                                            PTI-<?= $kodeTema . str_pad($nomorMatkul, 2, '0', STR_PAD_LEFT); ?> • 3 SKS
                                                        </div>

                                                        <input 
                                                            type="hidden" 
                                                            name="id_matkul[]" 
                                                            value="<?= $mk['id_matkul']; ?>"
                                                        >
                                                    </td>

                                                    <td>
                                                        <select name="minat[]">
                                                            <option value="1">1</option>
                                                            <option value="2">2</option>
                                                            <option value="3" selected>3</option>
                                                            <option value="4">4</option>
                                                            <option value="5">5</option>
                                                        </select>
                                                    </td>

                                                    <td>
                                                        <select name="kesulitan[]">
                                                            <option value="1">1</option>
                                                            <option value="2">2</option>
                                                            <option value="3" selected>3</option>
                                                            <option value="4">4</option>
                                                            <option value="5">5</option>
                                                        </select>
                                                    </td>

                                                    <td>
                                                        <select name="relevansi[]">
                                                            <option value="1">1</option>
                                                            <option value="2">2</option>
                                                            <option value="3" selected>3</option>
                                                            <option value="4">4</option>
                                                            <option value="5">5</option>
                                                        </select>
                                                    </td>

                                                    <td>
                                                        <select name="nilai_prasyarat[]">
                                                            <option value="A">A</option>
                                                            <option value="B" selected>B</option>
                                                            <option value="C">C</option>
                                                            <option value="D">D</option>
                                                            <option value="E">E</option>
                                                        </select>
                                                    </td>
                                                </tr>
                                        <?php
                                                $nomorMatkul++;
                                            }
                                        } else {
                                        ?>
                                            <tr>
                                                <td colspan="5">
                                                    Belum ada mata kuliah pada tema ini.
                                                </td>
                                            </tr>
                                        <?php
                                        }

                                        $kodeTema++;
                                    }
                                } else {
                                ?>
                                    <tr>
                                        <td colspan="5">
                                            Belum ada data tema dan mata kuliah.
                                        </td>
                                    </tr>
                                <?php
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="footer-note">
                        <div>Bobot kriteria:</div>
                        <div class="weight-info">
                            <span class="weight-badge">Minat 30%</span>
                            <span class="weight-badge">Kesulitan 20%</span>
                            <span class="weight-badge">Relevansi 20%</span>
                            <span class="weight-badge">Nilai Prasyarat 30%</span>
                        </div>
                    </div>
                </div>
            </form>
        </section>
    </main>
</body>
</html>