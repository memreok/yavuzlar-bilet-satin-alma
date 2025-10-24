<?php

require_once 'config.php';


if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: /login.php');
    exit();
}

$stmt = $pdo->query("SELECT COUNT(id) FROM Bus_Company");
$toplam_firma_sayisi = $stmt->fetchColumn();


$stmt = $pdo->query("SELECT COUNT(id) FROM User");
$toplam_kullanici_sayisi = $stmt->fetchColumn();


$son_islemler_stmt = $pdo->prepare("
    SELECT 
        T.id as ticket_id,
        U.full_name,
        BC.name as company_name,
        TR.departure_city,
        TR.destination_city,
        T.created_at
    FROM Tickets T
    JOIN User U ON T.user_id = U.id
    JOIN Trips TR ON T.trip_id = TR.id
    JOIN Bus_Company BC ON TR.company_id = BC.id
    ORDER BY T.created_at DESC
    LIMIT 5
");
$son_islemler_stmt->execute();
$son_islemler = $son_islemler_stmt->fetchAll(PDO::FETCH_ASSOC);


$admin_adi =  $_SESSION['full_name'];

?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Paneli - OBSAP</title>
    <link href="/dist/output.css" rel="stylesheet">
    </head>
<body class="bg-gray-100 font-sans">

    <div class="flex h-screen">
        <aside class="w-64 bg-gray-800 text-white flex-shrink-0">
            <div class="p-6 text-2xl font-bold border-b border-gray-700">
                <a href="/admin-dashboard.php">Admin Paneli</a>
            </div>
            <nav class="mt-6">
                <a href="/admin-dashboard.php" class="flex items-center px-6 py-3 bg-gray-700 text-white">
                    <span>Dashboard</span>
                </a>
                <a href="manage-companies.php" class="flex items-center px-6 py-3 mt-2 text-gray-300 hover:bg-gray-700 hover:text-white transition">
                    <span>Firma Yönetimi</span>
                </a>
                <a href="manage-company-admins.php" class="flex items-center px-6 py-3 mt-2 text-gray-300 hover:bg-gray-700 hover:text-white transition">
                    <span>Firma Admin Yönetimi</span>
                </a>
<a href="/manage-coupons.php" class="flex items-center px-6 py-3 mt-2 text-gray-300 hover:bg-gray-700 hover:text-white transition">
    <span>Kupon Yönetimi</span>
</a>
            </nav>
            <div class="absolute bottom-0 w-full border-t border-gray-700">
                <a href="/logout.php" class="flex items-center px-6 py-3 mt-2 text-gray-300 hover:bg-gray-700 hover:text-white transition">
                    <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path></svg>
                    <span>Çıkış Yap</span>
                </a>
            </div>
        </aside>

        <div class="flex-1 flex flex-col overflow-hidden">
            <header class="flex justify-between items-center p-6 bg-white border-b">
                <h1 class="text-2xl font-semibold text-gray-800">Dashboard</h1>
                <div class="flex items-center">
                    <span class="mr-3 font-medium"><?php echo htmlspecialchars($admin_adi); ?></span>
                    <div class="w-10 h-10 bg-blue-500 rounded-full text-white flex items-center justify-center font-bold">
                        <?php echo strtoupper(substr($admin_adi, 0, 1)); ?>
                    </div>
                </div>
            </header>

            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div class="bg-white p-6 rounded-lg shadow-md flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Toplam Firma Sayısı</p>
                            <p class="text-3xl font-bold text-gray-800"><?php echo $toplam_firma_sayisi; ?></p>
                        </div>
                        <div class="bg-blue-100 text-blue-600 p-3 rounded-full">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                        </div>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow-md flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Toplam Kullanıcı Sayısı</p>
                            <p class="text-3xl font-bold text-gray-800"><?php echo $toplam_kullanici_sayisi; ?></p>
                        </div>
                        <div class="bg-green-100 text-green-600 p-3 rounded-full">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M15 21v-2a4 4 0 00-4-4H9a4 4 0 00-4 4v2"></path></svg>
                        </div>
                    </div>


                </div>
                <div class="mt-8 bg-white p-6 rounded-lg shadow-md">
                    <h2 class="text-xl font-semibold text-gray-700 mb-4">Son Bilet İşlemleri</h2>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2">Kullanıcı Adı</th>
                                    <th class="px-4 py-2">Firma</th>
                                    <th class="px-4 py-2">Güzergah</th>
                                    <th class="px-4 py-2">İşlem Tarihi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($son_islemler)): ?>
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-gray-500">Henüz bir bilet işlemi bulunmamaktadır.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($son_islemler as $islem): ?>
                                        <tr class="border-b">
                                            <td class="px-4 py-3"><?php echo htmlspecialchars($islem['full_name']); ?></td>
                                            <td class="px-4 py-3"><?php echo htmlspecialchars($islem['company_name']); ?></td>
                                            <td class="px-4 py-3"><?php echo htmlspecialchars($islem['departure_city']) . ' → ' . htmlspecialchars($islem['destination_city']); ?></td>
                                            <td class="px-4 py-3"><?php echo date('d.m.Y H:i', strtotime($islem['created_at'])); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>
</html>