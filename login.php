<?php
session_start();
require_once 'includes/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (!empty($username) && !empty($password)) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];
            header('Location: index.php');
            exit;
        } else {
            $error = 'İstifadəçi adı və ya şifrə yanlışdır!';
        }
    } else {
        $error = 'Bütün xanaları doldurun!';
    }
}
?>
<!DOCTYPE html>
<html lang="az">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giriş - Mağaza Patrul</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body.login-page {
            margin: 0;
            padding: 0;
            height: 100vh;
            background-color: #0f172a;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .login-wrapper {
            display: flex;
            width: 850px;
            max-width: 90%;
            height: 520px;
            background: #1e293b;
            border: 1px solid #334155;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.5);
        }

        .login-banner {
            flex: 1;
            background: linear-gradient(135deg, #4f46e5 0%, #06b6d4 100%);
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            color: #ffffff;
            position: relative;
        }

        .login-banner-content {
            position: relative;
            z-index: 2;
        }

        .login-banner h1 {
            font-size: 32px;
            margin: 0 0 10px 0;
            font-weight: 800;
        }

        .login-banner p {
            font-size: 15px;
            opacity: 0.9;
            line-height: 1.5;
        }

        .login-form-container {
            flex: 1;
            padding: 50px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .login-form-container h2 {
            margin: 0 0 8px 0;
            color: #f8fafc;
            font-size: 24px;
        }

        .login-form-container p.subtitle {
            margin: 0 0 25px 0;
            color: #94a3b8;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #94a3b8;
            font-size: 13px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-group input {
            width: 100%;
            padding: 12px 14px;
            background-color: #0f172a;
            border: 1px solid #334155;
            border-radius: 8px;
            color: #f8fafc;
            box-sizing: border-box;
            font-size: 14px;
            outline: none;
            transition: border-color 0.2s ease;
        }

        .form-group input:focus {
            border-color: #6366f1;
        }

        .btn-login {
            background-color: #6366f1;
            color: #ffffff;
            border: none;
            padding: 14px;
            width: 100%;
            cursor: pointer;
            border-radius: 8px;
            font-weight: 600;
            font-size: 15px;
            transition: background-color 0.2s ease;
            margin-top: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn-login:hover {
            background-color: #4f46e5;
        }

        .error-msg {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid #ef4444;
            color: #ef4444;
            padding: 10px;
            border-radius: 6px;
            font-size: 13px;
            margin-bottom: 20px;
            text-align: center;
        }

        /* Yüklənmə effekti üçün animasiya */
        .spinner {
            display: none;
            width: 18px;
            height: 18px;
            border: 3px solid rgba(255,255,255,0.3);
            border-radius: 50%;
            border-top-color: #fff;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body class="login-page">
    <div class="login-wrapper">
        <div class="login-banner">
            <div class="login-banner-content">
                <h1>Mağaza Patrul</h1>
                <p>Peşəkar Kassa və Mağaza İdarəetmə Sistemi</p>
            </div>
            <div class="login-banner-content" style="font-size: 12px; opacity: 0.7;">
                &copy; 2026 Mağaza Patrul. Bütün hüquqlar qorunur.
            </div>
        </div>
        <div class="login-form-container">
            <h2>Xoş gəldiniz</h2>
            <p class="subtitle">Davam etmək üçün hesabınıza daxil olun</p>

            <?php if (!empty($error)): ?>
                <div class="error-msg"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST" action="" id="loginForm">
                <div class="form-group">
                    <label>İstifadəçi adı</label>
                    <input type="text" name="username" placeholder="İstifadəçi adını daxil edin" required>
                </div>
                <div class="form-group">
                    <label>Şifrə</label>
                    <input type="password" name="password" placeholder="••••••••" required>
                </div>
                <button type="submit" class="btn-login" id="submitBtn">
                    <span id="btnText">Daxil ol</span>
                    <div class="spinner" id="btnSpinner"></div>
                </button>
            </form>
        </div>
    </div>

    <script>
        // İstifadəçi "Daxil ol" düyməsini vuran kimi front-end dərhal reaksiya verir və animasiya göstərir
        document.getElementById('loginForm').addEventListener('submit', function() {
            const btn = document.getElementById('submitBtn');
            const btnText = document.getElementById('btnText');
            const spinner = document.getElementById('btnSpinner');
            
            btn.style.opacity = '0.8';
            btn.style.pointerEvents = 'none';
            btnText.textContent = 'Yoxlanılır...';
            spinner.style.display = 'block';
        });
    </script>
</body>
</html>