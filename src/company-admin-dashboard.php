<?php
require 'config.php';


if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'company') {
    header('Location: /login.php');
    exit();
}

$company_id = $_SESSION['company_id'];
$admin_adi = $_SESSION['full_name'];

try {

    $stmt = $pdo->prepare("SELECT COUNT(id) FROM Trips WHERE company_id = :company_id");
    $stmt->execute([':company_id' => $company_id]);
    $toplam_sefer_sayisi = $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COUNT(id) FROM Trips WHERE company_id = :company_id AND departure_time > datetime('now')");
    $stmt->execute([':company_id' => $company_id]);
    $aktif_sefer_sayisi = $stmt->fetchColumn();

    $stmt = $pdo->prepare("
        SELECT COUNT(T.id) FROM Tickets T
        JOIN Trips TR ON T.trip_id = TR.id
        WHERE TR.company_id = :company_id
    ");
    $stmt->execute([':company_id' => $company_id]);
    $toplam_satilan_bilet = $stmt->fetchColumn();

    $stmt = $pdo->prepare("
        SELECT SUM(T.total_price) FROM Tickets T
        JOIN Trips TR ON T.trip_id = TR.id
        WHERE TR.company_id = :company_id AND T.status = 'active'
    ");
    $stmt->execute([':company_id' => $company_id]);
    $toplam_gelir = $stmt->fetchColumn() ?: 0; 

} catch (PDOException $e) {
    error_log("Firma dashboard veri çekme hatası: " . $e->getMessage());

    $toplam_sefer_sayisi = $aktif_sefer_sayisi = $toplam_satilan_bilet = $toplam_gelir = "Hata";
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Firma Paneli - Dashboard</title>
    <link href="/dist/output.css" rel="stylesheet">
</head>
<body class="bg-gray-100 font-sans">
    <div class="flex h-screen">
        <aside class="w-64 bg-gray-800 text-white flex-shrink-0">
            <div class="p-6 text-2xl font-bold border-b border-gray-700">Firma Paneli</div>
            <nav class="mt-6">
                <a href="/company-admin-dashboard.php" class="flex items-center px-6 py-3 bg-gray-700 text-white">
                    <span>Dashboard</span>
                </a>
                <a href="/manage-trips.php" class="flex items-center px-6 py-3 mt-2 text-gray-300 hover:bg-gray-700 hover:text-white transition">
                    <span>Sefer Yönetimi</span>
                </a>
                <a href="/manage-tickets.php" class="flex items-center px-6 py-3 mt-2 text-gray-300 hover:bg-gray-700 hover:text-white transition">
                    <span>Bilet Yönetimi</span>
                </a>
               
<a href="/manage-coupons.php" class="flex items-center px-6 py-3 mt-2 text-gray-300 hover:bg-gray-700 hover:text-white transition">
    <span>Kupon Yönetimi</span>
</a>
            </nav>
            <div class="absolute bottom-0 w-full border-t border-gray-700">
                 <a href="/logout.php" class="flex items-center px-6 py-3 mt-2 text-gray-300 hover:bg-gray-700 hover:text-white">
                    <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path></svg>
                    <span>Çıkış Yap</span>
                </a>
            </div>
        </aside>

        <div class="flex-1 flex flex-col overflow-hidden">
            <header class="flex justify-between items-center p-6 bg-white border-b">
                <h1 class="text-2xl font-semibold text-gray-800">Dashboard</h1>
                <div class="flex items-center">
                    <span class="mr-3 font-medium"><?php echo htmlspecialchars($admin_adi); ?></span>
                </div>
            </header>

            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="bg-white p-6 rounded-lg shadow-md">
                        <p class="text-sm font-medium text-gray-500">Toplam Sefer Sayısı</p>
                        <p class="text-3xl font-bold text-gray-800"><?php echo $toplam_sefer_sayisi; ?></p>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow-md">
                        <p class="text-sm font-medium text-gray-500">Aktif Sefer Sayısı</p>
                        <p class="text-3xl font-bold text-gray-800"><?php echo $aktif_sefer_sayisi; ?></p>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow-md">
                        <p class="text-sm font-medium text-gray-500">Toplam Satılan Bilet</p>
                        <p class="text-3xl font-bold text-gray-800"><?php echo $toplam_satilan_bilet; ?></p>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow-md">
                        <p class="text-sm font-medium text-gray-500">Toplam Gelir (Aktif Biletler)</p>
                        <p class="text-3xl font-bold text-gray-800"><?php echo number_format($toplam_gelir, 2, ',', '.'); ?> TL</p>
                    </div>
                </div>
                </main>
        </div>
    </div>
</body>
</html>