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
        'nama_mahasiswa' => 'Nama Mahasiswa',
        'nim' => 'Belum tersedia'
    ];
}

$queryJumlahTema = mysqli_query($koneksi, "
    SELECT COUNT(*) AS total_tema FROM tema
");

$jumlahTema = mysqli_fetch_assoc($queryJumlahTema);

$queryJumlahMatkul = mysqli_query($koneksi, "
    SELECT COUNT(*) AS total_matkul FROM mata_kuliah
");

$jumlahMatkul = mysqli_fetch_assoc($queryJumlahMatkul);

$queryTema = mysqli_query($koneksi, "
    SELECT 
        tema.id_tema,
        tema.nama_tema,
        mata_kuliah.nama_matkul
    FROM tema
    LEFT JOIN mata_kuliah 
        ON tema.id_tema = mata_kuliah.id_tema
    ORDER BY tema.id_tema ASC, mata_kuliah.id_matkul ASC
");

$daftarTema = [];

while ($row = mysqli_fetch_assoc($queryTema)) {
    $idTema = $row['id_tema'];

    if (!isset($daftarTema[$idTema])) {
        $daftarTema[$idTema] = [
            'nama_tema' => $row['nama_tema'],
            'mata_kuliah' => []
        ];
    }

    if (!empty($row['nama_matkul'])) {
        $daftarTema[$idTema]['mata_kuliah'][] = $row['nama_matkul'];
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Dashboard | SPK Mata Kuliah Pilihan</title>

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
            overflow: auto;
            padding: 24px;
            color: #172033;
        }

        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 500, 'GRAD' 0, 'opsz' 24;
        }

        .dashboard-card {
            width: min(1100px, 100%);
            height: min(650px, calc(100vh - 48px));
            background: #ffffff;
            border: 1px solid #d6dbe3;
            border-radius: 16px;
            overflow: auto;
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
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 800;
            color: #001f3f;
            font-size: 18px;
        }

        .brand-icon {
            width: 34px;
            height: 34px;
            border-radius: 10px;
            background: #001f3f;
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .nav {
            display: flex;
            align-items: center;
            gap: 28px;
            font-size: 14px;
            font-weight: 600;
        }

        .nav a {
            text-decoration: none;
            color: #6b7280;
            padding: 22px 0;
            border-bottom: 3px solid transparent;
        }

        .nav a.active {
            color: #001f3f;
            border-bottom-color: #001f3f;
        }

        .top-actions {
            display: flex;
            align-items: center;
            gap: 18px;
            color: #5f6675;
        }

        .logout {
            display: flex;
            align-items: center;
            gap: 6px;
            text-decoration: none;
            color: #5f6675;
            font-weight: 600;
            font-size: 14px;
        }

        .main-content {
            flex: 1;
            display: grid;
            grid-template-columns: 330px 1fr;
            gap: 20px;
            padding: 24px;
            min-height: 0;
            background: #f7f9fc;
        }

        .sidebar {
            display: flex;
            flex-direction: column;
            gap: 18px;
        }

        .profile-card,
        .start-card,
        .content-card,
        .stat-card {
            background: #ffffff;
            border: 1px solid #e2e7ef;
            border-radius: 12px;
            box-shadow: 0 4px 14px rgba(15, 23, 42, 0.04);
        }

        .profile-card {
            padding: 17px;
        }

        .profile-top {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 18px;
        }

        .avatar {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            background: linear-gradient(135deg, #dbeafe, #fef3c7);
            border: 3px solid #ffffff;
            box-shadow: 0 0 0 2px #dbe4f0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #001f3f;
            font-size: 34px;
            position: relative;
        }

        .status-dot {
            position: absolute;
            right: 1px;
            bottom: 4px;
            width: 16px;
            height: 16px;
            background: #17a34a;
            border: 3px solid #ffffff;
            border-radius: 50%;
        }

        .student-name {
            margin: 0 0 4px;
            font-size: 17px;
            font-weight: 800;
            color: #001f3f;
        }

        .student-nim {
            margin: 0;
            font-size: 13px;
            color: #6b7280;
            font-weight: 600;
        }

        .profile-info {
            border-top: 1px solid #edf0f4;
            padding-top: 16px;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .info-label {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            font-weight: 800;
            color: #6b7280;
            margin-bottom: 4px;
        }

        .info-value {
            font-size: 13px;
            font-weight: 700;
            color: #1f2937;
        }

        .badge-success {
            display: inline-flex;
            width: fit-content;
            padding: 5px 10px;
            border-radius: 999px;
            background: #dff8e8;
            color: #15803d;
            font-size: 12px;
            font-weight: 800;
        }

        .start-card {
            padding: 15px;
            background: #001f3f;
            color: #ffffff;
        }

        .start-card h3 {
            margin: 0 0 10px;
            font-size: 18px;
            font-weight: 800;
        }

        .start-card p {
            margin: 0 0 20px;
            font-size: 13px;
            line-height: 1.6;
            color: rgba(255, 255, 255, 0.72);
        }

        .start-button {
            width: 100%;
            height: 48px;
            border: none;
            border-radius: 8px;
            background: #ffffff;
            color: #001f3f;
            font-size: 14px;
            font-weight: 800;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: 0.2s ease;
        }

        .start-button:hover {
            background: #eef4ff;
        }

        .right-area {
            min-height: 0;
            display: flex;
            flex-direction: column;
            gap: 18px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(2, 180px);
            gap: 14px;
            justify-content: start;
        }

        .stat-card {
            padding: 14px;
            min-height: 72px;
            display: flex;
            gap: 12px;
            align-items: center;
        }

        .stat-icon {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            background: #eff6ff;
            color: #001f3f;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .stat-icon.red {
            background: #fff1f2;
            color: #dc2626;
        }

        .stat-icon.green {
            background: #ecfdf3;
            color: #16a34a;
        }

        .stat-label {
            margin: 0 0 4px;
            font-size: 11px;
            font-weight: 800;
            color: #6b7280;
        }

        .stat-value {
            margin: 0;
            font-size: 19px;
            font-weight: 900;
            color: #111827;
        }

        .stat-value.warning {
            color: #dc2626;
            font-size: 14px;
        }

        .content-card {
            flex: 1;
            padding: 22px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .content-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 18px;
        }

        .content-header h2 {
            margin: 0 0 6px;
            font-size: 20px;
            color: #001f3f;
            font-weight: 900;
        }

        .content-header p {
            margin: 0;
            font-size: 13px;
            color: #6b7280;
            line-height: 1.5;
        }

        .course-list {
            overflow-y: auto;
            padding-right: 6px;
        }

        .theme-item {
            border: 1px solid #e5eaf2;
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 12px;
            background: #ffffff;
        }

        .theme-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .theme-title {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
            font-weight: 900;
            color: #1f2937;
        }

        .theme-code {
            width: 28px;
            height: 28px;
            border-radius: 6px;
            background: #001f3f;
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            font-weight: 900;
        }

        .mk-badge {
            padding: 5px 9px;
            border-radius: 8px;
            background: #f1f5f9;
            color: #475569;
            font-size: 11px;
            font-weight: 800;
        }

        .theme-item ul {
            margin: 0;
            padding-left: 48px;
            color: #4b5563;
            font-size: 13px;
            line-height: 1.8;
            font-weight: 500;
        }

        .course-list::-webkit-scrollbar {
            width: 8px;
        }

        .course-list::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 999px;
        }

        @media (max-width: 950px) {
            body {
                overflow-y: auto;
                align-items: flex-start;
            }

            .dashboard-card {
                min-height: min(650px, calc(100vh - 48px));
                height: auto;
            }

            .main-content {
                grid-template-columns: 1fr;
            }

            .stats-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .topbar {
                flex-wrap: wrap;
                height: auto;
                padding: 16px 20px;
                gap: 14px;
            }

            .nav {
                order: 3;
                width: 100%;
                justify-content: center;
                gap: 18px;
                flex-wrap: wrap;
            }

            .nav a {
                padding: 8px 0;
            }
        }

        @media (max-width: 560px) {
            body {
                padding: 12px;
            }

            .main-content {
                padding: 14px;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .content-header {
                flex-direction: column;
            }
        }
    </style>
</head>

<body>
    <main class="dashboard-card">
        <header class="topbar">
            <div class="brand">
                <div class="brand-icon">
                    <span class="material-symbols-outlined">school</span>
                </div>
                SPK Penentuan Mata Kuliah Pilihan
            </div>

            <nav class="nav">
                <a href="#" class="active">Dashboard</a>
                <a href="rekomendasi.php">Hasil Rekomendasi</a>
            </nav>

            <div class="top-actions">
                <a href="login.php" class="logout">
                    <span class="material-symbols-outlined">account_circle</span>
                    Logout
                </a>
            </div>
        </header>

        <section class="main-content">
            <aside class="sidebar">
                <div class="profile-card">
                    <div class="profile-top">
                        <div class="avatar">
                            <span class="material-symbols-outlined">person</span>
                            <span class="status-dot"></span>
                        </div>

                        <div>
                            <h2 class="student-name"><?= htmlspecialchars($mahasiswa['nama_mahasiswa']); ?></h2>
                            <p class="student-nim">NIM <?= htmlspecialchars($mahasiswa['nim']); ?></p>
                        </div>
                    </div>

                    <div class="profile-info">
                        <div>
                            <div class="info-label">Program Studi</div>
                            <div class="info-value">Pendidikan Teknologi Informasi</div>
                        </div>

                        <div>
                            <div class="info-label">Status</div>
                            <div class="badge-success">Mahasiswa Aktif</div>
                        </div>
                    </div>
                </div>

                <div class="start-card">
                    <h3>Siap Menentukan Pilihan?</h3>
                    <p>
                        Isi penilaian berdasarkan minat, tingkat kesulitan,
                        relevansi kerja mendatang, dan nilai mata kuliah prasyarat.
                    </p>

                    <button class="start-button" onclick="window.location.href='pilihmatkul.php'">
                        Mulai Mengisi Penilaian
                        <span class="material-symbols-outlined">arrow_forward</span>
                    </button>
                </div>
            </aside>

            <section class="right-area">
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <span class="material-symbols-outlined">category</span>
                        </div>
                        <div>
                            <p class="stat-label">Jumlah Tema</p>
                            <p class="stat-value"><?= htmlspecialchars($jumlahTema['total_tema']); ?></p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon">
                            <span class="material-symbols-outlined">menu_book</span>
                        </div>
                        <div>
                            <p class="stat-label">Mata Kuliah</p>
                            <p class="stat-value"><?= htmlspecialchars($jumlahMatkul['total_matkul']); ?></p>
                        </div>
                    </div>

                </div>

                <div class="content-card">
                    <div class="content-header">
                        <div>
                            <h2>Mata Kuliah Pilihan yang Tersedia</h2>
                            <p>
                                Daftar tema dan mata kuliah yang dapat Anda ambil semester ini.
                            </p>
                        </div>
                    </div>

                    <div class="course-list">
                        <div class="theme-item">
                            <div class="theme-head">
                                <div class="theme-title">
                                    <span class="theme-code">A</span>
                                    Tema A 
                                </div>
                                <span class="mk-badge">2 MK</span>
                            </div>
                            <ul>
                                <li>Paradigma Berpikir Komputasi</li>
                                <li>Paradigma Berpikir Sistem dan Desain</li>
                            </ul>
                        </div>

                        <div class="theme-item">
                            <div class="theme-head">
                                <div class="theme-title">
                                    <span class="theme-code">B</span>
                                    Tema B 
                                </div>
                                <span class="mk-badge">2 MK</span>
                            </div>
                            <ul>
                                <li>Manajemen Proyek Teknologi Informasi</li>
                                <li>Manajemen Layanan Teknologi Informasi</li>
                            </ul>
                        </div>

                        <div class="theme-item">
                            <div class="theme-head">
                                <div class="theme-title">
                                    <span class="theme-code">C</span>
                                    Tema C 
                                </div>
                                <span class="mk-badge">5 MK</span>
                            </div>
                            <ul>
                                <li>Pengembangan Program Pelatihan Teknologi Informasi</li>
                                <li>Pendidikan Orang Dewasa dan Berkelanjutan</li>
                                <li>Manajemen Sistem Pendidikan</li>
                                <li>Pembelajaran Sosial Emosional</li>
                                <li>Pembelajaran Inklusi</li>
                            </ul>
                        </div>

                        <div class="theme-item">
                            <div class="theme-head">
                                <div class="theme-title">
                                    <span class="theme-code">D</span>
                                    Tema D 
                                </div>
                                <span class="mk-badge">2 MK</span>
                            </div>
                            <ul>
                                <li>Sistem Pendukung Keputusan</li>
                                <li>Sistem Belajar Mesin</li>
                            </ul>
                        </div>

                        <div class="theme-item">
                            <div class="theme-head">
                                <div class="theme-title">
                                    <span class="theme-code">E</span>
                                    Tema E 
                                </div>
                                <span class="mk-badge">2 MK</span>
                            </div>
                            <ul>
                                <li>Teknologi Komputasi Awan</li>
                                <li>Teknologi Peranti Internet</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </section>
        </section>
    </main>
</body>
</html>