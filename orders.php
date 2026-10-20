<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once 'includes/db.php';

// Təhlükəsiz baza miqrasiyası (Sifariş cədvəlləri)
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        customer_name VARCHAR(100) NOT NULL,
        customer_phone VARCHAR(50) NOT NULL,
        customer_address TEXT NOT NULL,
        total_amount DECIMAL(10, 2) NOT NULL,
        total_profit DECIMAL(10, 2) NOT NULL,
        status ENUM('pending', 'ordered_from_supplier', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS order_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT NOT NULL,
        product_id INT NOT NULL,
        quantity INT NOT NULL,
        purchase_price DECIMAL(10, 2) NOT NULL,
        sale_price DECIMAL(10, 2) NOT NULL,
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
    )");
} catch (PDOException $e) {
    // Cədvəl mövcuddursa davam edir
}

$message = '';
$error = '';

// Status Yeniləmə
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $order_id = intval($_POST['order_id']);
    $new_status = $_POST['status'];

    $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
    if ($stmt->execute([$new_status, $order_id])) {
        $message = "Sifariş #$order_id statusu uğurla yeniləndi!";
    } else {
        $error = "Status yenilənərkən xəta baş verdi!";
    }
}

// Sifarişlərin Siyahısı
$stmt = $pdo->query("SELECT * FROM orders ORDER BY id DESC");
$orders = $stmt->fetchAll();

include 'includes/header.php';
?>

<div style="margin-bottom: 24px;">
    <h2 style="margin: 0 0 8px 0; font-size: 24px;">Dropshipping Sifarişləri</h2>
    <p style="margin: 0; color: var(--text-secondary); font-size: 14px;">Müştəri sifarişlərini idarə edin, təchizatçı statuslarını və net qazancınızı izləyin.</p>
</div>

<?php if (!empty($message)): ?>
    <div class="alert-success"><?php echo $message; ?></div>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <div class="alert-error"><?php echo $error; ?></div>
<?php endif; ?>

<div class="card">
    <h3>Sifariş Siyahısı</h3>
    <div style="overflow-x: auto;">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Sifariş ID</th>
                    <th>Müştəri</th>
                    <th>Əlaqə / Ünvan</th>
                    <th>Məbləğ</th>
                    <th>Xalis Qazanc</th>
                    <th>Status</th>
                    <th>Tarix</th>
                    <th>Əməliyyat</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($orders) > 0): ?>
                    <?php foreach ($orders as $o): ?>
                        <tr>
                            <td>#<?php echo $o['id']; ?></td>
                            <td><strong><?php echo htmlspecialchars($o['customer_name']); ?></strong></td>
                            <td>
                                <div style="font-size: 13px; color: var(--text-primary);"><?php echo htmlspecialchars($o['customer_phone']); ?></div>
                                <div style="font-size: 12px; color: var(--text-secondary);"><?php echo htmlspecialchars($o['customer_address']); ?></div>
                            </td>
                            <td><strong><?php echo number_format($o['total_amount'], 2); ?> AZN</strong></td>
                            <td><span style="color: #22c55e; font-weight: 700;"><?php echo number_format($o['total_profit'], 2); ?> AZN</span></td>
                            <td>
                                <?php
                                $status_colors = [
                                    'pending' => '#f59e0b',
                                    'ordered_from_supplier' => '#3b82f6',
                                    'shipped' => '#8b5cf6',
                                    'delivered' => '#22c55e',
                                    'cancelled' => '#ef4444'
                                ];
                                $status_labels = [
                                    'pending' => 'Gözləmədə',
                                    'ordered_from_supplier' => 'Təchizatçıya Verildi',
                                    'shipped' => 'Yoldadır',
                                    'delivered' => 'Çatdırıldı',
                                    'cancelled' => 'Ləğv Edildi'
                                ];
                                $color = $status_colors[$o['status']] ?? '#94a3b8';
                                $label = $status_labels[$o['status']] ?? $o['status'];
                                ?>
                                <span style="padding: 4px 10px; background: <?php echo $color; ?>20; color: <?php echo $color; ?>; border: 1px solid <?php echo $color; ?>; border-radius: 6px; font-weight: 600; font-size: 12px;">
                                    <?php echo $label; ?>
                                </span>
                            </td>
                            <td style="color: var(--text-secondary); font-size: 13px;"><?php echo $o['created_at']; ?></td>
                            <td>
                                <form method="POST" style="display: flex; gap: 6px; align-items: center;">
                                    <input type="hidden" name="order_id" value="<?php echo $o['id']; ?>">
                                    <select name="status" style="background: var(--bg-color); color: var(--text-primary); border: 1px solid var(--border-color); padding: 6px; border-radius: 6px; font-size: 12px;">
                                        <option value="pending" <?php echo $o['status'] == 'pending' ? 'selected' : ''; ?>>Gözləmədə</option>
                                        <option value="ordered_from_supplier" <?php echo $o['status'] == 'ordered_from_supplier' ? 'selected' : ''; ?>>Təchizatçıya Verildi</option>
                                        <option value="shipped" <?php echo $o['status'] == 'shipped' ? 'selected' : ''; ?>>Yoldadır</option>
                                        <option value="delivered" <?php echo $o['status'] == 'delivered' ? 'selected' : ''; ?>>Çatdırıldı</option>
                                        <option value="cancelled" <?php echo $o['status'] == 'cancelled' ? 'selected' : ''; ?>>Ləğv Edildi</option>
                                    </select>
                                    <button type="submit" name="update_status" style="background: var(--accent-color); color: white; border: none; padding: 6px 12px; border-radius: 6px; cursor: pointer; font-size: 12px; font-weight: 600;">Yenilə</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" style="text-align: center; color: var(--text-secondary); padding: 30px;">Hələ heç bir sifariş daxil olmayıb.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>