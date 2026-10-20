<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="az">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mağaza Patrul - Dropshipping Panel</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        :root {
            --bg-dark: #0b132b;
            --card-bg: #1c2541;
            --sidebar-bg: #0b132b;
            --accent: #6366f1;
            --text-main: #ffffff;
            --text-sub: #94a3b8;
            --border-color: #1e293b;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Inter', system-ui, sans-serif; }
        body { background-color: var(--bg-dark); color: var(--text-main); display: flex; min-height: 100vh; overflow-x: hidden; }
        
        /* Sidebar Fix */
        .sidebar-container { width: 250px; background: #0f172a; border-right: 1px solid var(--border-color); display: flex; flex-direction: column; position: fixed; top:0; bottom:0; left:0; z-index: 100; }
        .sidebar-logo { padding: 24px; font-size: 20px; font-weight: 800; color: var(--accent); border-bottom: 1px solid var(--border-color); }
        .sidebar-links { list-style: none; padding: 20px 10px; }
        .sidebar-links li { margin-bottom: 8px; }
        .sidebar-links a { display: block; padding: 12px 16px; color: var(--text-sub); text-decoration: none; border-radius: 8px; font-size: 14px; font-weight: 500; transition: all 0.2s; }
        .sidebar-links a:hover, .sidebar-links a.active { background: rgba(99, 102, 241, 0.15); color: #fff; border-left: 3px solid var(--accent); }
        
        /* Main Layout Fix */
        .main-wrapper { margin-left: 250px; flex: 1; padding: 30px; width: calc(100% - 250px); }
        .header-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 1px solid var(--border-color); }
        .card { background: var(--card-bg); border: 1px solid #334155; border-radius: 12px; padding: 24px; margin-bottom: 24px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.3); }
        
        /* Table & Form Fix */
        .form-row { display: flex; gap: 20px; margin-bottom: 16px; }
        .form-group { flex: 1; display: flex; flex-direction: column; }
        .form-group label { font-size: 13px; color: var(--text-sub); margin-bottom: 6px; font-weight: 500; }
        .form-group input { padding: 10px 14px; background: #0f172a; border: 1px solid #334155; border-radius: 8px; color: #fff; font-size: 14px; outline: none; }
        .form-group input:focus { border-color: var(--accent); }
        .btn-primary { background: var(--accent); color: white; border: none; padding: 12px 24px; border-radius: 8px; font-weight: 600; cursor: pointer; transition: 0.2s; }
        .btn-primary:hover { opacity: 0.9; }
        
        table.data-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.data-table th, table.data-table td { padding: 14px; text-align: left; border-bottom: 1px solid #334155; font-size: 14px; }
        table.data-table th { color: var(--text-sub); font-weight: 600; background: #0f172a; }
    </style>
</head>
<body>

<div class="sidebar-container">
    <div class="sidebar-logo">Mağaza Patrul</div>
    <ul class="sidebar-links">
        <li><a href="index.php" class="<?php echo $current_page == 'index.php' ? 'active' : ''; ?>">Analitika / İcmal</a></li>
        <li><a href="products.php" class="<?php echo $current_page == 'products.php' ? 'active' : ''; ?>">Məhsul İdarəetməsi</a></li>
        <li><a href="orders.php" class="<?php echo $current_page == 'orders.php' ? 'active' : ''; ?>">Sifarişlər</a></li>
        <li><a href="shop.php" target="_blank">Onlayn Vitrin ↗</a></li>
        <li><a href="logout.php">Çıxış</a></li>
    </ul>
</div>

<div class="main-wrapper">
    <div class="header-bar">
        <div>
            <h3 style="font-size: 20px; font-weight: 700;">İdarəetmə Paneli</h3>
            <span style="font-size: 13px; color: var(--text-sub);">SellStreak Dropshipping Suite</span>
        </div>
        <div style="font-size: 14px; color: var(--text-sub);">
            Xoş gəldiniz, <strong style="color: #fff;"><?php echo htmlspecialchars($_SESSION['full_name']); ?></strong>
        </div>
    </div>