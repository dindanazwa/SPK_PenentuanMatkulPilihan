<?php
session_start();
include 'koneksi.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $identifier = mysqli_real_escape_string($koneksi, $_POST['identifier']);
    $password = $_POST['password'];

    $query = mysqli_query($koneksi, "
        SELECT 
            users.id_user,
            users.username,
            users.password,
            mahasiswa.id_mahasiswa,
            mahasiswa.nim,
            mahasiswa.nama_mahasiswa
        FROM users
        LEFT JOIN mahasiswa 
            ON users.id_user = mahasiswa.id_user
        WHERE users.username = '$identifier'
           OR mahasiswa.nim = '$identifier'
        LIMIT 1
    ");

    if (mysqli_num_rows($query) > 0) {
        $data = mysqli_fetch_assoc($query);

        if ($password === $data['password']) {
            $_SESSION['id_user'] = $data['id_user'];
            $_SESSION['username'] = $data['username'];
            $_SESSION['id_mahasiswa'] = $data['id_mahasiswa'];
            $_SESSION['nama_mahasiswa'] = $data['nama_mahasiswa'];
            $_SESSION['nim'] = $data['nim'];

            header("Location: dashboard.php");
            exit;
        } else {
            $error = "Password salah.";
        }
    } else {
        $error = "Username atau NIM tidak ditemukan.";
    }
}
?>

<!DOCTYPE html>
<html class="light" lang="id">
<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Login | SPK Pemilihan Mata Kuliah Pilihan</title>

    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />

    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#001f3f",
                        "primary-dark": "#00152b",
                        "surface": "#f8f9fa",
                        "outline": "#c4c6cf",
                        "text-main": "#191c1d",
                        "text-muted": "#5f6368",
                        "success": "#006e25"
                    },
                    fontFamily: {
                        inter: ["Inter", "sans-serif"]
                    }
                }
            }
        }
    </script>

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
        }

        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }

        .auth-card {
            width: min(1100px, 100%);
            height: min(650px, calc(100vh - 48px));
            display: grid;
            grid-template-columns: 1fr 1fr;
            background: #ffffff;
            border: 1px solid #c4c6cf;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
        }

        .left-panel {
            position: relative;
            background: #001f3f;
            color: white;
            display: flex;
            align-items: center;
            padding: 48px;
            overflow: hidden;
        }

        .left-panel::before {
            content: "";
            position: absolute;
            inset: 0;
            background:
                linear-gradient(rgba(0, 31, 63, 0.88), rgba(0, 31, 63, 0.92)),
                url("https://images.unsplash.com/photo-1524995997946-a1c2e315a42f?q=80&w=1470&auto=format&fit=crop");
            background-size: cover;
            background-position: center;
            transform: scale(1.05);
        }

        .left-content {
            position: relative;
            z-index: 1;
            max-width: 520px;
            transform: translateY(-70px);
        }

        .brand-title {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 28px;
        }

        .brand-title .material-symbols-outlined {
            font-size: 58px;
            font-variation-settings: 'FILL' 1;
        }

        .brand-title h1 {
            font-size: 42px;
            line-height: 1.15;
            font-weight: 700;
            margin: 0;
            letter-spacing: -0.03em;
        }

        .left-content p {
            font-size: 18px;
            line-height: 1.7;
            color: rgba(255, 255, 255, 0.72);
            margin: 0 0 36px;
        }

        .feature-list {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .feature-item {
            display: flex;
            align-items: center;
            gap: 12px;
            color: rgba(255, 255, 255, 0.78);
            font-size: 14px;
            font-weight: 600;
            letter-spacing: 0.03em;
        }

        .feature-item .material-symbols-outlined {
            font-size: 22px;
        }

        .right-panel {
            background: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 48px;
        }

        .login-box {
            width: 100%;
            max-width: 430px;
        }

        .login-box h2 {
            font-size: 28px;
            line-height: 1.2;
            margin: 0 0 10px;
            color: #001f3f;
            font-weight: 700;
        }

        .login-box .subtitle {
            font-size: 16px;
            line-height: 1.45;
            color: #5f6368;
            margin: 0 0 24px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        label {
            display: block;
            font-size: 14px;
            font-weight: 700;
            color: #191c1d;
            margin-bottom: 8px;
            letter-spacing: 0.04em;
        }

        .forgot-link {
            font-size: 13px;
            color: #001f3f;
            font-weight: 600;
            text-decoration: none;
        }

        .forgot-link:hover {
            text-decoration: underline;
        }

        .forgot-bottom {
            margin-top: 8px;
            text-align: right;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper .left-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #74777f;
            font-size: 22px;
        }

        .input-wrapper input {
            width: 100%;
            height: 56px;
            border: 1px solid #c4c6cf;
            border-radius: 6px;
            padding: 0 48px;
            font-size: 16px;
            color: #191c1d;
            background: #ffffff;
            transition: 0.2s ease;
        }

        .input-wrapper input:focus {
            outline: none;
            border-color: #001f3f;
            box-shadow: 0 0 0 2px rgba(0, 31, 63, 0.12);
        }

        .toggle-password {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            border: none;
            background: transparent;
            color: #74777f;
            cursor: pointer;
            display: flex;
            align-items: flex-start;
            justify-content: center;
        }

        .remember-row {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 4px 0 18px;
        }

        .remember-row input {
            width: 16px;
            height: 16px;
            accent-color: #001f3f;
        }

        .remember-row label {
            margin: 0;
            font-size: 14px;
            color: #5f6368;
            font-weight: 500;
            letter-spacing: normal;
            cursor: pointer;
        }

        .login-button {
            width: 100%;
            height: 52px;
            border: none;
            border-radius: 6px;
            background: #001f3f;
            color: white;
            font-size: 15px;
            font-weight: 700;
            letter-spacing: 0.04em;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: 0.2s ease;
        }

        .login-button:hover {
            background: #00152b;
        }

        .login-button:active {
            transform: scale(0.98);
        }

        .admin-info {
            margin-top: 28px;
            padding-top: 22px;
            border-top: 1px solid #e1e3e4;
            text-align: center;
            font-size: 14px;
            color: #5f6368;
        }

        .admin-info a {
            color: #001f3f;
            font-weight: 700;
            text-decoration: none;
        }

        .admin-info a:hover {
            text-decoration: underline;
        }

        .footer-text {
            position: fixed;
            bottom: 12px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 12px;
            color: rgba(116, 119, 127, 0.8);
            pointer-events: none;
        }

        @media (max-width: 900px) {
            body {
                overflow-y: auto;
                padding: 20px;
            }

            .auth-card {
                height: auto;
                min-height: auto;
                grid-template-columns: 1fr;
            }

            .left-panel {
                position: relative;
                background: #001f3f;
                color: white;
                display: flex;
                align-items: center;
                padding: 48px;
                overflow: hidden;
            }

            .brand-title h1 {
                font-size: 32px;
            }

            .left-content p {
                font-size: 16px;
                margin-bottom: 24px;
            }

            .right-panel {
                padding: 36px;
            }

            .footer-text {
                position: static;
                margin-top: 16px;
            }
        }

        @media (max-width: 520px) {
            body {
                padding: 12px;
            }

            .left-panel,
            .right-panel {
                padding: 25px 22px;
            }

            .brand-title {
                align-items: flex-start;
            }

            .brand-title h1 {
                font-size: 28px;
            }

            .brand-title .material-symbols-outlined {
                font-size: 42px;
            }

            .login-box h2 {
                font-size: 24px;
            }
        }
    </style>
</head>

<body>
    <main class="auth-card">
        <section class="left-panel">
            <div class="left-content">
                <div class="brand-title">
                    <span class="material-symbols-outlined">school</span>
                    <h1>Penentuan Mata Kuliah Pilihan</h1>
                </div>

                <p>
                    Optimalkan perjalanan akademik Anda dengan sistem pendukung keputusan
                    berbasis data yang membantu menentukan mata kuliah pilihan sesuai minat,
                    kemampuan, dan tujuan karier.
                </p>

                <div class="feature-list">
                    <div class="feature-item">
                        <span class="material-symbols-outlined">check_circle</span>
                        Analisis Minat Mahasiswa
                    </div>

                    <div class="feature-item">
                        <span class="material-symbols-outlined">check_circle</span>
                        Rekomendasi Berbasis Kriteria
                    </div>

                    <div class="feature-item">
                        <span class="material-symbols-outlined">check_circle</span>
                        Pemilihan Mata Kuliah Lebih Terarah
                    </div>
                </div>
            </div>
        </section>

        <section class="right-panel">
            <div class="login-box">
                <h2>Masuk ke Akun</h2>
                <p class="subtitle">
                    Membantu mahasiswa menentukan mata kuliah pilihan sesuai minat,
                    kemampuan, dan tujuan karier.
                </p>

                <form id="loginForm" method="POST" action="">
                    <div class="form-group">
                        <label for="identifier">NIM atau Username</label>
                        <div class="input-wrapper">
                            <span class="material-symbols-outlined left-icon">person</span>
                            <input
                                id="identifier"
                                name="identifier"
                                type="text"
                                placeholder="Masukkan NIM atau Username"
                                required
                            />
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="form-top">
                            <label for="password">Kata Sandi</label>
                        </div>

                        <div class="input-wrapper">
                            <span class="material-symbols-outlined left-icon">lock</span>
                            <input
                                id="password"
                                name="password"
                                type="password"
                                placeholder="••••••••"
                                required
                            />

                            <button class="toggle-password" type="button" onclick="togglePassword()">
                                <span class="material-symbols-outlined" id="passwordIcon">visibility</span>
                            </button>
                        </div>
                        <div class="forgot-bottom">
                            <a href="#" class="forgot-link">Lupa Kata Sandi?</a>
                        </div>
                    </div>

                    <div class="remember-row">
                        <input id="remember" type="checkbox" />
                        <label for="remember">Ingat saya di perangkat ini</label>
                    </div>

                    <button class="login-button" type="submit">
                        Masuk
                        <span class="material-symbols-outlined">login</span>
                    </button>
                </form>

                <div class="admin-info">
                    Belum punya akun?
                    <a href="#">Hubungi Admin Prodi</a>
                </div>
            </div>
        </section>
    </main>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById("password");
            const passwordIcon = document.getElementById("passwordIcon");

            if (passwordInput.type === "password") {
                passwordInput.type = "text";
                passwordIcon.innerText = "visibility_off";
            } else {
                passwordInput.type = "password";
                passwordIcon.innerText = "visibility";
            }
        }

    </script>
</body>
</html>