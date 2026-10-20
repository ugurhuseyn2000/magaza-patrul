<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once 'includes/db.php';

// Değişkenleri əvvəlcədən təyin edirik ki, xəta verməsin
$total_products = 0;
$total_orders = 0;
$total_revenue = 0.00;
$total_profit = 0.00;
$recent_orders = [];

try {
    $total_products = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn() ?: 0;
    $total_orders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn() ?: 0;
    $total_revenue = $pdo->query("SELECT SUM(total_amount) FROM orders WHERE status != 'cancelled'")->fetchColumn() ?: 0.00;
    $total_profit = $pdo->query("SELECT SUM(total_profit) FROM orders WHERE status != 'cancelled'")->fetchColumn() ?: 0.00;
    $recent_orders = $pdo->query("SELECT * FROM orders ORDER BY id DESC LIMIT 5")->fetchAll() ?: [];
} catch (PDOException $e) {
    // Cədvəllər hələ tam yoxdursa
}

include 'includes/header.php';
?>

<!-- Statistika Kartları -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px;">
    <div class="card" style="margin-bottom: 0;">
        <span style="color: var(--text-sub); font-size: 12px; font-weight: 600; letter-spacing: 0.5px;">ÜMUMİ SATIŞ</span>
        <h3 style="margin: 8px 0 0 0; color: #6366f1; font-size: 22px; font-weight: 700;"><?php echo number_format((float)$total_revenue, 2); ?> AZN</h3>
    </div>
    <div class="card" style="margin-bottom: 0;">
        <span style="color: var(--text-sub); font-size: 12px; font-weight: 600; letter-spacing: 0.5px;">XALİS QAZANC</span>
        <h3 style="margin: 8px 0 0 0; color: #22c55e; font-size: 22px; font-weight: 700;"><?php echo number_format((float)$total_profit, 2); ?> AZN</h3>
    </div>
    <div class="card" style="margin-bottom: 0;">
        <span style="color: var(--text-sub); font-size: 12px; font-weight: 600; letter-spacing: 0.5px;">SİFARİŞ SAYI</span>
        <h3 style="margin: 8px 0 0 0; color: #f59e0b; font-size: 22px; font-weight: 700;"><?php echo intval($total_orders); ?> ədəd</h3>
    </div>
    <div class="card" style="margin-bottom: 0;">
        <span style="color: var(--text-sub); font-size: 12px; font-weight: 600; letter-spacing: 0.5px;">ANBARDAKI MƏHSUL</span>
        <h3 style="margin: 8px 0 0 0; color: #3b82f6; font-size: 22px; font-weight: 700;"><?php echo intval($total_products); ?> ədəd</h3>
    </div>
</div>

<!-- Son Sifarişlər Cədvəli -->
<div class="card">
    <h3>Son Sifarişlər</h3>
    <div style="overflow-x: auto;">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Sifariş ID</th>
                    <th>Müştəri</th>
                    <th>Məbləğ</th>
                    <th>Qazanc</th>
                    <th>Status</th>
                    <th>Tarix</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($recent_orders) && count($recent_orders) > 0): ?>
                    <?php foreach ($recent_orders as $o): ?>
                        <tr>
                            <td>#<?php echo $o['id']; ?></td>
                            <td><strong><?php echo htmlspecialchars($o['customer_name']); ?></strong></td>
                            <td><?php echo number_format((float)$o['total_amount'], 2); ?> AZN</td>
                            <td><span style="color: #22c55e; font-weight: 600;"><?php echo number_format((float)$o['total_profit'], 2); ?> AZN</span></td>
                            <td><span style="padding: 4px 8px; background: rgba(99, 102, 241, 0.1); color: var(--accent); border-radius: 4px; font-size: 12px; font-weight: 600;"><?php echo htmlspecialchars($o['status']); ?></span></td>
                            <td style="color: var(--text-sub);"><?php echo htmlspecialchars($o['created_at']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align: center; color: var(--text-sub); padding: 30px;">Hələ heç bir sifariş yoxdur.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>