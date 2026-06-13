<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../koneksi.php';

if (isset($_POST['login'])) {

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $conn->prepare("SELECT username, password FROM admin WHERE username = ? LIMIT 1");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $admin = $result ? $result->fetch_assoc() : null;
    $stmt->close();

    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['admin'] = $admin['username'];
        header("Location:index.php");
        exit;
    }

    $error = "Username atau password salah!";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin — RuangRasa</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,400;0,500;0,600;0,700;1,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: #1a120b;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 2rem 1rem;
        }

        .login-box {
            background: #231610;
            border: 1px solid #3a2518;
            border-radius: 18px;
            padding: 2.25rem 1.75rem 2rem;
            width: 100%;
            max-width: 360px;
            text-align: center;
        }

        /* --- Logo --- */
        .login-logo {
            margin-bottom: 1rem;
        }

        .login-logo-text {
            font-size: 24px;
            font-weight: 700;
            letter-spacing: -0.3px;
        }

        .login-logo-text em {
            font-style: italic;
            color: #c8863c;
        }

        .login-logo-text span {
            font-style: normal;
            color: #f5e6d0;
        }

        /* --- Divider --- */
        .login-divider {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 0.75rem 0 1.25rem;
        }

        .login-divider hr {
            flex: 1;
            border: none;
            border-top: 1px solid #3a2518;
        }

        .login-divider i {
            font-size: 18px;
            color: #c8863c;
        }

        /* --- Heading --- */
        h2 {
            font-size: 20px;
            font-weight: 600;
            color: #f5e6d0;
            margin-bottom: 4px;
        }

        .login-subtitle {
            font-size: 13px;
            color: #8a6245;
            margin-bottom: 1.75rem;
        }

        /* --- Error --- */
        .error {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            background: rgba(231, 76, 60, 0.12);
            border: 1px solid rgba(231, 76, 60, 0.3);
            color: #f08080;
            font-size: 13px;
            border-radius: 8px;
            padding: 10px 12px;
            margin-bottom: 1.25rem;
        }

        /* --- Field label --- */
        .field-label {
            display: block;
            text-align: left;
            font-size: 11px;
            font-weight: 500;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: #8a6245;
            margin-bottom: 6px;
            margin-top: 1rem;
        }

        /* --- Field wrap --- */
        .field-wrap {
            position: relative;
            display: flex;
            align-items: center;
        }

        .field-wrap > i.bi {
            position: absolute;
            left: 13px;
            font-size: 16px;
            color: #7a5535;
            pointer-events: none;
            z-index: 1;
        }

        /* --- Input --- */
        .field-wrap input {
            width: 100%;
            height: 46px;
            padding: 0 14px 0 42px;
            background: #2e1d12;
            border: 1px solid #4a2f1c;
            border-radius: 10px;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            color: #f5e6d0;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .field-wrap input::placeholder {
            color: #6b4a30;
        }

        .field-wrap input:focus {
            border-color: #c8863c;
            box-shadow: 0 0 0 3px rgba(200, 134, 60, 0.15);
        }

        /* --- Toggle password --- */
        .toggle-pwd {
            position: absolute;
            right: 10px;
            background: none;
            border: none;
            cursor: pointer;
            padding: 4px;
            color: #7a5535;
            font-size: 16px;
            line-height: 1;
            display: flex;
            align-items: center;
            width: auto;
            height: auto;
            min-width: unset;
            margin: 0;
            border-radius: 4px;
            transition: color 0.2s;
        }

        .toggle-pwd:hover {
            color: #c8863c;
            background: none;
        }

        /* --- Lupa password --- */
        .lupa-pwd {
            display: block;
            text-align: right;
            font-size: 12px;
            color: #c8863c;
            text-decoration: none;
            margin: 8px 0 1.5rem;
            transition: color 0.2s;
        }

        .lupa-pwd:hover {
            color: #e0a050;
        }

        /* --- Submit button --- */
        .btn-login {
            width: 100%;
            height: 46px;
            background: #2e1d12;
            color: #c8a06a;
            border: 1px solid #4a2f1c;
            border-radius: 10px;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            letter-spacing: 0.03em;
            transition: background 0.2s, border-color 0.2s, color 0.2s;
            padding: 0;
            margin: 0;
        }

        .btn-login:hover {
            background: #3a2518;
            border-color: #c8863c;
            color: #f0c880;
        }

        /* --- Footer --- */
        .login-footer {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            font-size: 11px;
            color: #5a3c22;
            margin-top: 1.5rem;
        }

        .login-footer i {
            font-size: 13px;
        }
    </style>
</head>
<body>

<div class="login-box">

    <div class="login-logo">
        <div class="login-logo-text"><em>Ruang</em><span>Rasa</span><span>.</span></div>
    </div>

    <div class="login-divider">
        <hr><i class="bi bi-cup-hot"></i><hr>
    </div>

    <h2>Login Admin</h2>
    <p class="login-subtitle">Akses panel administrasi</p>

    <?php if (isset($error)) : ?>
        <p class="error">
            <i class="bi bi-exclamation-circle"></i>
            <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
        </p>
    <?php endif; ?>

    <form method="POST">

        <label class="field-label" for="username">Username</label>
        <div class="field-wrap">
            <i class="bi bi-envelope"></i>
            <input
                type="text"
                id="username"
                name="username"
                placeholder="Masukkan username"
                required
                autocomplete="username">
        </div>

        <label class="field-label" for="password">Password</label>
        <div class="field-wrap">
            <i class="bi bi-lock"></i>
            <input
                type="password"
                id="password"
                name="password"
                placeholder="Masukkan password"
                required
                autocomplete="current-password"
                style="padding-right: 42px;">
            <button type="button" class="toggle-pwd" onclick="togglePassword()" aria-label="Tampilkan password">
                <i class="bi bi-eye" id="eye-icon"></i>
            </button>
        </div>

        <a href="#" class="lupa-pwd">Lupa password?</a>

        <button type="submit" name="login" class="btn-login">
            <i class="bi bi-box-arrow-in-right"></i>
            Masuk
        </button>

    </form>

    <div class="login-footer">
        <i class="bi bi-shield-check"></i>
        Area terbatas &mdash; khusus administrator
    </div>

</div>

<script>
    function togglePassword() {
        const pwd = document.getElementById('password');
        const icon = document.getElementById('eye-icon');
        if (pwd.type === 'password') {
            pwd.type = 'text';
            icon.className = 'bi bi-eye-slash';
        } else {
            pwd.type = 'password';
            icon.className = 'bi bi-eye';
        }
    }
</script>

</body>
</html>