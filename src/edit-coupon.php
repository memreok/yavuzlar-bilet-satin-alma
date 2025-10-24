<?php
require 'config.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'company']) || !isset($_GET['id'])) {
    header('Location: /login.php');
    exit();
}

$coupon_id = $_GET['id'];
$error_message = '';
$coupon = null;

try {
    $stmt = $pdo->prepare("SELECT * FROM Coupons WHERE id = :id");
    $stmt->execute([':id' => $coupon_id]);
    $coupon = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$coupon) {
        header("Location: manage-coupons.php?error=Kupon bulunamadı.");
        exit();
    }
    
    if ($_SESSION['role'] === 'company' && $coupon['company_id'] !== $_SESSION['company_id']) {
        header("Location: manage-coupons.php?error=Bu işlem için yetkiniz yok.");
        exit();
    }


} catch (PDOException $e) {
    die("Hata!");
}


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $code = strtoupper(trim($_POST['code']));
    $discount = trim($_POST['discount']);
    $usage_limit = trim($_POST['usage_limit']);
    $expire_date = trim($_POST['expire_date']);

    if (empty($code) || empty($discount) || empty($usage_limit) || empty($expire_date)) {
        $error_message = "Lütfen tüm alanları doldurun.";
    } else {
        try {
            $stmt_check = $pdo->prepare("SELECT id FROM Coupons WHERE code = :code AND id != :id");
            $stmt_check->execute([':code' => $code, ':id' => $coupon_id]);
            if ($stmt_check->fetch()) {
                 $error_message = "Bu kupon kodu zaten başka bir kupon tarafından kullanılıyor.";
            } else {
                $sql = "UPDATE Coupons SET code = :code, discount = :discount, usage_limit = :usage_limit, expire_date = :expire_date WHERE id = :id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':code' => $code,
                    ':discount' => $discount,
                    ':usage_limit' => $usage_limit,
                    ':expire_date' => $expire_date,
                    ':id' => $coupon_id
                ]);
                header("Location: manage-coupons.php?status=updated");
                exit();
            }
        } catch (PDOException $e) {
            $error_message = "Veritabanı hatası: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kuponu Düzenle - Panel</title>
    <link href="/dist/output.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto mt-10 max-w-lg">
        <div class="bg-white p-8 rounded-lg shadow-md">
            <h1 class="text-2xl font-bold mb-6">Kuponu Düzenle</h1>

            <?php if ($error_message): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4" role="alert">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <form action="edit-coupon.php?id=<?php echo htmlspecialchars($coupon_id); ?>" method="POST" class="space-y-4">
                <div>
                    <label for="code" class="block text-sm font-medium text-gray-700">Kupon Kodu</label>
                    <input type="text" name="code" id="code" value="<?php echo htmlspecialchars($coupon['code']); ?>" required class="mt-1 block w-full p-2 border border-gray-300 rounded-md uppercase">
                </div>
                 <div>
                    <label for="discount" class="block text-sm font-medium text-gray-700">İndirim Oranı (%)</label>
                    <input type="number" name="discount" id="discount" min="1" max="100" value="<?php echo htmlspecialchars($coupon['discount']); ?>" required class="mt-1 block w-full p-2 border border-gray-300 rounded-md">
                </div>
                <div>
                    <label for="usage_limit" class="block text-sm font-medium text-gray-700">Kullanım Limiti</label>
                    <input type="number" name="usage_limit" id="usage_limit" min="1" value="<?php echo htmlspecialchars($coupon['usage_limit']); ?>" required class="mt-1 block w-full p-2 border border-gray-300 rounded-md">
                </div>
                <div>
                    <label for="expire_date" class="block text-sm font-medium text-gray-700">Son Kullanma Tarihi</label>
                    <input type="date" name="expire_date" id="expire_date" value="<?php echo date('Y-m-d', strtotime($coupon['expire_date'])); ?>" required class="mt-1 block w-full p-2 border border-gray-300 rounded-md" min="<?php echo date('Y-m-d'); ?>">
                </div>
                <div class="pt-4 flex justify-between">
                    <a href="manage-coupons.php" class="bg-gray-500 text-white font-bold py-2 px-4 rounded hover:bg-gray-600 transition">İptal</a>
                    <button type="submit" class="bg-blue-500 text-white font-bold py-2 px-4 rounded hover:bg-blue-600 transition">Değişiklikleri Kaydet</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>