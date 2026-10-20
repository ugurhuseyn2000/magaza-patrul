<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once 'includes/db.php';

// Təhlükəsiz sütun yoxlanışı
try { $pdo->exec("ALTER TABLE products ADD COLUMN purchase_price DECIMAL(10, 2) DEFAULT 0.00"); } catch (PDOException $e) {}
try { $pdo->exec("ALTER TABLE products ADD COLUMN profit_percent DECIMAL(5, 2) DEFAULT 0.00"); } catch (PDOException $e) {}
try { $pdo->exec("ALTER TABLE products ADD COLUMN added_by VARCHAR(50) DEFAULT ''"); } catch (PDOException $e) {}
try { $pdo->exec("ALTER TABLE products ADD COLUMN image VARCHAR(255) DEFAULT ''"); } catch (PDOException $e) {}

$message = '';
$error = '';
$duplicate_product = null;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $barcode = trim($_POST['barcode']);
    $name = trim($_POST['name']);
    $purchase_price = floatval($_POST['purchase_price']);
    $sale_price = floatval($_POST['sale_price']);
    $stock = intval($_POST['stock']);
    $added_by = $_SESSION['full_name'];
    $action_type = $_POST['action_type'] ?? 'normal';

    // Şəkil yükləmə
    $image_name = '';
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] == 0) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) { mkdir($target_dir, 0777, true); }
        $file_extension = pathinfo($_FILES['product_image']['name'], PATHINFO_EXTENSION);
        $image_name = uniqid() . '.' . $file_extension;
        move_uploaded_file($_FILES['product_image']['tmp_name'], $target_dir . $image_name);
    }

    if (!empty($name) && $sale_price > 0) {
        $profit_percent = 0;
        if ($purchase_price > 0) {
            $profit_percent = (($sale_price - $purchase_price) / $purchase_price) * 100;
        }

        // Təkrar məhsul yoxlanışı (Barkod boş deyilsə barkoda və ya ada görə)
        $existingProduct = false;
        if (!empty($barcode)) {
            $checkStmt = $pdo->prepare("SELECT * FROM products WHERE barcode = ?");
            $checkStmt->execute([$barcode]);
            $existingProduct = $checkStmt->fetch();
        }
        if (!$existingProduct) {
            $checkNameStmt = $pdo->prepare("SELECT * FROM products WHERE name = ?");
            $checkNameStmt->execute([$name]);
            $existingProduct = $checkNameStmt->fetch();
        }

        if ($existingProduct && $action_type == 'normal') {
            $duplicate_product = $existingProduct;
        } else {
            if ($existingProduct && ($action_type == 'update' || $action_type == 'force_insert')) {
                // "Stoku yenilə" və ya "Nəzərə alma (əslində stoku üzərinə gəlir ki, xəta verməsin)" seçildikdə
                $img_sql = !empty($image_name) ? ", image = " . $pdo->quote($image_name) : "";
                $updateStmt = $pdo->prepare("UPDATE products SET stock = stock + ?, price = ?, purchase_price = ?, profit_percent = ?, added_by = ?" . $img_sql . " WHERE id = ?");
                $updateStmt->execute([$stock, $sale_price, $purchase_price, $profit_percent, $added_by, $existingProduct['id']]);
                
                header("Location: products.php?status=updated");
                exit;
            } else {
                // Tamamilə yeni məhsul
                $insertStmt = $pdo->prepare("INSERT INTO products (barcode, name, price, purchase_price, profit_percent, stock, added_by, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $insertStmt->execute([$barcode, $name, $sale_price, $purchase_price, $profit_percent, $stock, $added_by, $image_name]);
                
                header("Location: products.php?status=success");
                exit;
            }
        }
    } else {
        $error = 'Məhsul adı və satış qiyməti mütləq doldurulmalıdır!';
    }
}

// Mesajların URL-dən oxunması
if (isset($_GET['status'])) {
    if ($_GET['status'] == 'success') { $message = 'Yeni məhsul uğurla yadda saxlandı!'; }
    if ($_GET['status'] == 'updated') { $message = 'Mövcud məhsulun stoku və məlumatları yeniləndi!'; }
}

// Məhsulların siyahısı
$stmt = $pdo->query("SELECT * FROM products ORDER BY id DESC");
$products = $stmt->fetchAll();

include 'includes/header.php';
?>

<div style="margin-bottom: 24px;">
    <h2 style="margin: 0 0 8px 0; font-size: 24px;">Məhsul İdarəetməsi</h2>
    <p style="margin: 0; color: var(--text-secondary); font-size: 14px;">Anbardakı məhsulları idarə edin, şəkil əlavə edin və təkrar məhsullara nəzarət edin.</p>
</div>

<?php if (!empty($message)): ?>
    <div class="alert-success"><?php echo $message; ?></div>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <div class="alert-error"><?php echo $error; ?></div>
<?php endif; ?>

<!-- Yeni Məhsul Əlavə Etmə Formu -->
<div class="card">
    <h3>Yeni Məhsul Əlavə Et</h3>
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action_type" value="normal">
        <div class="form-row">
            <div class="form-group">
                <label>Barkod</label>
                <input type="text" name="barcode" value="<?php echo htmlspecialchars($_POST['barcode'] ?? ''); ?>" placeholder="Məs: 4800123456789">
            </div>
            <div class="form-group">
                <label>Məhsul Adı *</label>
                <input type="text" name="name" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" placeholder="Məhsulun adını daxil edin" required>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Alış Qiyməti (AZN)</label>
                <input type="number" step="0.01" name="purchase_price" value="<?php echo htmlspecialchars($_POST['purchase_price'] ?? ''); ?>" placeholder="0.00">
            </div>
            <div class="form-group">
                <label>Satış Qiyməti (AZN) *</label>
                <input type="number" step="0.01" name="sale_price" value="<?php echo htmlspecialchars($_POST['sale_price'] ?? ''); ?>" placeholder="0.00" required>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Stok Miqdarı</label>
                <input type="number" name="stock" value="<?php echo htmlspecialchars($_POST['stock'] ?? '1'); ?>">
            </div>
            <div class="form-group">
                <label>Məhsul Şəkli</label>
                <input type="file" name="product_image" accept="image/*" style="padding: 9px; background: var(--bg-color); border: 1px solid var(--border-color); border-radius: 8px; color: var(--text-primary); width: 100%;">
            </div>
        </div>
        <button type="submit" class="btn-primary">Yadda saxla</button>
    </form>
</div>

<!-- TƏKRAR MƏHSUL XƏBƏRDARLIĞI MODALI -->
<?php if ($duplicate_product): ?>
<div style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); display: flex; align-items: center; justify-content: center; z-index: 9999;">
    <div style="background: #1e293b; border: 1px solid #334155; padding: 30px; border-radius: 12px; width: 450px; color: #fff; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.5);">
        <h3 style="margin-top: 0; color: #ef4444;">⚠️ Diqqət: Bu mal sistemdə var!</h3>
        <p style="color: #94a3b8; font-size: 14px; margin-bottom: 20px;">Daxil etdiyiniz barkod və ya ada uyğun sistemdə aşağıdakı məhsul mövcuddur:</p>
        
        <div style="background: #0f172a; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-size: 14px;">
            <p style="margin: 5px 0;"><strong>Ad:</strong> <?php echo htmlspecialchars($duplicate_product['name']); ?></p>
            <p style="margin: 5px 0;"><strong>Barkod:</strong> <?php echo htmlspecialchars($duplicate_product['barcode']); ?></p>
            <p style="margin: 5px 0;"><strong>Mövcud Stok:</strong> <?php echo $duplicate_product['stock']; ?> ədəd</p>
            <p style="margin: 5px 0;"><strong>Satış Qiyməti:</strong> <?php echo number_format($duplicate_product['price'], 2); ?> AZN</p>
        </div>

        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="barcode" value="<?php echo htmlspecialchars($_POST['barcode']); ?>">
            <input type="hidden" name="name" value="<?php echo htmlspecialchars($_POST['name']); ?>">
            <input type="hidden" name="purchase_price" value="<?php echo htmlspecialchars($_POST['purchase_price']); ?>">
            <input type="hidden" name="sale_price" value="<?php echo htmlspecialchars($_POST['sale_price']); ?>">
            <input type="hidden" name="stock" value="<?php echo htmlspecialchars($_POST['stock']); ?>">

            <div style="display: flex; gap: 10px;">
                <button type="submit" name="action_type" value="update" style="flex: 1; background: #6366f1; color: white; border: none; padding: 12px; border-radius: 6px; cursor: pointer; font-weight: 600;">Stoku Üstə Gəl / Yenilə</button>
                <button type="submit" name="action_type" value="force_insert" style="flex: 1; background: #334155; color: white; border: none; padding: 12px; border-radius: 6px; cursor: pointer; font-weight: 600;">Nəzərə Alma (Stoka Əlavə Et)</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<!-- Məhsul Siyahısı Cədvəli -->
<div class="card">
    <h3>Məhsul Siyahısı</h3>
    <div style="overflow-x: auto;">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Şəkil</th>
                    <th>ID</th>
                    <th>Barkod</th>
                    <th>Məhsul Adı</th>
                    <th>Alış Q.</th>
                    <th>Satış Q.</th>
                    <th>Qazanc (%)</th>
                    <th>Stok</th>
                    <th>İşləyən</th>
                    <th>Tarix</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($products) > 0): ?>
                    <?php foreach ($products as $p): ?>
                        <tr>
                            <td>
                                <?php if (!empty($p['image']) && file_exists("uploads/" . $p['image'])): ?>
                                    <img src="uploads/<?php echo htmlspecialchars($p['image']); ?>" style="width: 40px; height: 40px; object-fit: cover; border-radius: 6px; border: 1px solid var(--border-color);">
                                <?php else: ?>
                                    <div style="width: 40px; height: 40px; background: #0f172a; border-radius: 6px; display: flex; align-items: center; justify-content: center; font-size: 10px; color: var(--text-secondary);">Yoxdur</div>
                                <?php endif; ?>
                            </td>
                            <td>#<?php echo $p['id']; ?></td>
                            <td><?php echo htmlspecialchars($p['barcode']); ?></td>
                            <td><strong><?php echo htmlspecialchars($p['name']); ?></strong></td>
                            <td><?php echo number_format($p['purchase_price'], 2); ?> AZN</td>
                            <td><?php echo number_format($p['price'], 2); ?> AZN</td>
                            <td><span style="color: #22c55e; font-weight: 600;"><?php echo number_format($p['profit_percent'], 1); ?>%</span></td>
                            <td><span style="padding: 4px 8px; background: rgba(99, 102, 241, 0.1); color: var(--accent-color); border-radius: 4px; font-weight: 600;"><?php echo $p['stock']; ?> ədəd</span></td>
                            <td><span style="color: var(--text-secondary);"><?php echo htmlspecialchars($p['added_by']); ?></span></td>
                            <td style="color: var(--text-secondary);"><?php echo $p['created_at']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="10" style="text-align: center; color: var(--text-secondary); padding: 30px;">Hələ heç bir məhsul əlavə olunmayıb.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>