<?php
require 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: /login.php');
    exit();
}

try {
    $sql = "SELECT User.id, User.full_name, User.email, Bus_Company.name AS company_name
            FROM User
            LEFT JOIN Bus_Company ON User.company_id = Bus_Company.id
            WHERE User.role = 'company'";
    $stmt = $pdo->query($sql);
    $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Firma adminlerini çekerken hata: " . $e->getMessage());
    $admins = [];
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Firma Admin Yönetimi - Admin Paneli</title>
    <link href="/dist/output.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto mt-10">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold">Firma Admin Yönetimi</h1>
            <div>
                <a href="admin-dashboard.php" class="bg-gray-500 text-white font-bold py-2 px-4 rounded hover:bg-gray-600 transition">Panele Dön</a>
                <a href="add-company-admin.php" class="bg-green-500 text-white font-bold py-2 px-4 rounded hover:bg-green-600 transition ml-2">+ Yeni Admin Ekle</a>
            </div>
        </div>

        <?php if (isset($_GET['status']) && $_GET['status'] == 'deleted'): ?>
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded-md" role="alert">
                <p>Firma admini başarıyla silindi.</p>
            </div>
        <?php elseif (isset($_GET['status']) && $_GET['status'] == 'updated'): ?>
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded-md" role="alert">
                <p>Firma admini başarıyla güncellendi.</p>
            </div>
         <?php elseif (isset($_GET['error'])): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded-md" role="alert">
                <p><?php echo htmlspecialchars($_GET['error']); ?></p>
            </div>
        <?php endif; ?>

        <div class="bg-white p-6 rounded-lg shadow-md">
            <table class="min-w-full bg-white">
                <thead class="bg-gray-200">
                    <tr>
                        <th class="py-3 px-4 text-left">İsim Soyisim</th>
                        <th class="py-3 px-4 text-left">E-posta</th>
                        <th class="py-3 px-4 text-left">Atandığı Firma</th>
                        <th class="py-3 px-4 text-left">İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($admins)): ?>
                        <?php foreach ($admins as $admin): ?>
                            <tr class="border-b">
                                <td class="py-3 px-4"><?php echo htmlspecialchars($admin['full_name']); ?></td>
                                <td class="py-3 px-4"><?php echo htmlspecialchars($admin['email']); ?></td>
                                <td class="py-3 px-4">
                                    <?php echo $admin['company_name'] ? htmlspecialchars($admin['company_name']) : '<span class="text-red-500">Atanmamış</span>'; ?>
                                </td>
                                <td class="py-3 px-4">
                                    <a href="edit-company-admin.php?id=<?php echo htmlspecialchars($admin['id']); ?>" class="text-blue-500 hover:text-blue-700">Düzenle</a>
                                    <a href="delete-company-admin.php?id=<?php echo htmlspecialchars($admin['id']); ?>" class="text-red-500 hover:text-red-700 ml-4" onclick="return confirm('Bu admini silmek istediğinizden emin misiniz?');">Sil</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="py-4 px-4 text-center text-gray-500">Kayıtlı firma admini bulunamadı.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>