<?php
require 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: /login.php');
    exit();
}

try {

    $stmt = $pdo->query("SELECT * FROM Bus_Company ORDER BY name ASC");
    $companies = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Firmaları çekerken hata: " . $e->getMessage());
    $companies = [];
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Firma Yönetimi - Admin Paneli</title>
    <link href="/dist/output.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto mt-10">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold">Firma Yönetimi</h1>
            <div>
                <a href="admin-dashboard.php" class="bg-gray-500 text-white font-bold py-2 px-4 rounded hover:bg-gray-600 transition">Panele Dön</a>
                <a href="add-company.php" class="bg-green-500 text-white font-bold py-2 px-4 rounded hover:bg-green-600 transition ml-2">+ Yeni Firma Ekle</a>
            </div>
        </div>

        <?php if (isset($_GET['status']) && $_GET['status'] == 'deleted'): ?>
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded-md" role="alert">
                <p>Firma başarıyla silindi.</p>
            </div>
        <?php elseif (isset($_GET['status']) && $_GET['status'] == 'updated'): ?>
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded-md" role="alert">
                <p>Firma başarıyla güncellendi.</p>
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
                        <th class="py-3 px-4 text-left">Firma Adı</th>
                        <th class="py-3 px-4 text-left">Logo</th>
                        <th class="py-3 px-4 text-left">Oluşturulma Tarihi</th>
                        <th class="py-3 px-4 text-left">İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($companies)): ?>
                        <?php foreach ($companies as $company): ?>
                            <tr class="border-b">
                                <td class="py-3 px-4 align-middle"><?php echo htmlspecialchars($company['name']); ?></td>
                                <td class="py-3 px-4 align-middle">
                                    <img 
                                        src="<?php echo !empty($company['logo_path']) ? htmlspecialchars($company['logo_path']) : '/uploads/logos/default-logo.png'; ?>" 
                                        alt="<?php echo htmlspecialchars($company['name']); ?> Logo" 
                                        class="h-10 w-10 object-contain rounded-md bg-gray-100 p-1"
                                    >
                                </td>
                                <td class="py-3 px-4 align-middle"><?php echo date('d.m.Y H:i', strtotime($company['created_at'])); ?></td>
                                <td class="py-3 px-4 align-middle">
                                    <a href="edit-company.php?id=<?php echo htmlspecialchars($company['id']); ?>" class="text-blue-500 hover:text-blue-700">Düzenle</a>
                                    <a href="delete-company.php?id=<?php echo htmlspecialchars($company['id']); ?>" class="text-red-500 hover:text-red-700 ml-4" onclick="return confirm('Bu firmayı silmek istediğinizden emin misiniz? Bu firmaya ait tüm seferler ve firma adminleri de etkilenebilir.');">Sil</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="py-4 px-4 text-center text-gray-500">Kayıtlı firma bulunamadı.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>