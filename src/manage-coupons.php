<?php
require 'config.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'company'])) {
    header('Location: /login.php');
    exit();
}

try {
    if ($_SESSION['role'] === 'admin') {

        $stmt = $pdo->query("
            SELECT C.*, BC.name as company_name 
            FROM Coupons C 
            LEFT JOIN Bus_Company BC ON C.company_id = BC.id 
            ORDER BY C.created_at DESC
        ");
    } else {
        $company_id = $_SESSION['company_id'];
        $stmt = $pdo->prepare("
            SELECT * FROM Coupons 
            WHERE company_id = :company_id OR company_id IS NULL 
            ORDER BY created_at DESC
        ");
        $stmt->execute([':company_id' => $company_id]);
    }
    $coupons = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Kuponları çekerken hata: " . $e->getMessage());
    $coupons = [];
}

$dashboard_link = ($_SESSION['role'] === 'admin') ? 'admin-dashboard.php' : 'company-admin-dashboard.php';
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kupon Yönetimi - Panel</title>
    <link href="/dist/output.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto mt-10">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold">Kupon Yönetimi</h1>
            <div>
                <a href="<?php echo $dashboard_link; ?>" class="bg-gray-500 text-white font-bold py-2 px-4 rounded hover:bg-gray-600 transition">Panele Dön</a>
                <a href="add-coupon.php" class="bg-green-500 text-white font-bold py-2 px-4 rounded hover:bg-green-600 transition ml-2">+ Yeni Kupon Ekle</a>
            </div>
        </div>

        <?php if (isset($_GET['status'])): ?>
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded-md" role="alert">
                <?php if ($_GET['status'] == 'added') echo '<p>Kupon başarıyla eklendi.</p>'; ?>
                <?php if ($_GET['status'] == 'updated') echo '<p>Kupon başarıyla güncellendi.</p>'; ?>
                <?php if ($_GET['status'] == 'deleted') echo '<p>Kupon başarıyla silindi.</p>'; ?>
            </div>
        <?php elseif (isset($_GET['error'])): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded-md" role="alert">
                <p><?php echo htmlspecialchars($_GET['error']); ?></p>
            </div>
        <?php endif; ?>

        <div class="bg-white p-6 rounded-lg shadow-md overflow-x-auto">
            <table class="min-w-full bg-white">
                <thead class="bg-gray-200">
                    <tr>
                        <th class="py-3 px-4 text-left">Kupon Kodu</th>
                        <th class="py-3 px-4 text-left">İndirim (%)</th>
                        <?php if ($_SESSION['role'] === 'admin'):  ?>
                            <th class="py-3 px-4 text-left">Firma</th>
                        <?php endif; ?>
                        <th class="py-3 px-4 text-left">Kullanım Limiti</th>
                        <th class="py-3 px-4 text-left">Son Kullanma Tarihi</th>
                        <th class="py-3 px-4 text-left">İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($coupons)): ?>
                        <?php foreach ($coupons as $coupon): ?>
                            <tr class="border-b">
                                <td class="py-3 px-4 font-mono text-blue-600 font-bold"><?php echo htmlspecialchars($coupon['code']); ?></td>
                                <td class="py-3 px-4"><?php echo htmlspecialchars($coupon['discount']); ?>%</td>
                                <?php if ($_SESSION['role'] === 'admin'):  ?>
                                    <td class="py-3 px-4">
                                        <?php if (isset($coupon['company_name'])): ?>
                                            <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded">
                                                <?php echo htmlspecialchars($coupon['company_name']); ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="bg-gray-100 text-gray-800 text-xs font-medium px-2.5 py-0.5 rounded">
                                                Genel Kupon
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                <?php endif; ?>
                                <td class="py-3 px-4"><?php echo htmlspecialchars($coupon['usage_limit']); ?></td>
                                <td class="py-3 px-4"><?php echo date('d.m.Y', strtotime($coupon['expire_date'])); ?></td>
                                <td class="py-3 px-4">
                                    <a href="edit-coupon.php?id=<?php echo htmlspecialchars($coupon['id']); ?>" class="text-blue-500 hover:text-blue-700">Düzenle</a>
                                    <a href="delete-coupon.php?id=<?php echo htmlspecialchars($coupon['id']); ?>" class="text-red-500 hover:text-red-700 ml-4" onclick="return confirm('Bu kuponu silmek istediğinizden emin misiniz?');">Sil</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="py-4 px-4 text-center text-gray-500">Kayıtlı kupon bulunamadı.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>