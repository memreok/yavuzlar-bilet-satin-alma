<?php
require 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'company') {
    header('Location: /login.php');
    exit();
}

$company_id = $_SESSION['company_id'];
$admin_adi = $_SESSION['full_name'];
$biletler = [];

try {
    $bilet_stmt = $pdo->prepare("
        SELECT 
            T.id as ticket_id, T.status, T.created_at, U.full_name,
            TR.departure_city, TR.destination_city
        FROM Tickets T
        JOIN User U ON T.user_id = U.id
        JOIN Trips TR ON T.trip_id = TR.id
        WHERE TR.company_id = :company_id
        ORDER BY T.created_at DESC
        LIMIT 50 
    ");
    $bilet_stmt->bindParam(':company_id', $company_id, PDO::PARAM_STR);
    $bilet_stmt->execute();
    $biletler = $bilet_stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Firma biletleri çekme hatası: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Firma Paneli - Bilet Yönetimi</title>
    <link href="/dist/output.css" rel="stylesheet">
</head>
<body class="bg-gray-100 font-sans">
    <div class="flex h-screen">
        <aside class="w-64 bg-gray-800 text-white flex-shrink-0">
            <div class="p-6 text-2xl font-bold border-b border-gray-700">Firma Paneli</div>
            <nav class="mt-6">
                <a href="/company-admin-dashboard.php" class="flex items-center px-6 py-3 mt-2 text-gray-300 hover:bg-gray-700 hover:text-white transition">
                    <span>Dashboard</span>
                </a>
                <a href="/manage-trips.php" class="flex items-center px-6 py-3 mt-2 text-gray-300 hover:bg-gray-700 hover:text-white transition">
                    <span>Sefer Yönetimi</span>
                </a>
                <a href="/manage-tickets.php" class="flex items-center px-6 py-3 bg-gray-700 text-white">
                    <span>Bilet Yönetimi</span>
                </a>
                <a href="/manage-coupons.php" class="flex items-center px-6 py-3 mt-2 text-gray-300 hover:bg-gray-700 hover:text-white transition">
    <span>Kupon Yönetimi</span>
</a>
            </nav>
            <div class="absolute bottom-0 w-full border-t border-gray-700">
                 <a href="/logout.php" class="flex items-center px-6 py-3 mt-2 text-gray-300 hover:bg-gray-700 hover:text-white">
                    <span>Çıkış Yap</span>
                </a>
            </div>
        </aside>

        <div class="flex-1 flex flex-col overflow-hidden">
            <header class="flex justify-between items-center p-6 bg-white border-b">
                <h1 class="text-2xl font-semibold text-gray-800">Bilet Yönetimi</h1>
                <div class="flex items-center">
                    <span class="mr-3 font-medium"><?php echo htmlspecialchars($admin_adi); ?></span>
                </div>
            </header>

            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-6">
                 <?php if (isset($_GET['status']) && $_GET['status'] == 'cancelled_ok'): ?>
                    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
                        <p>Bilet başarıyla iptal edildi ve ücret iadesi kullanıcıya yapıldı.</p>
                    </div>
                <?php elseif (isset($_GET['error'])): ?>
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
                        <p><?php echo htmlspecialchars($_GET['error']); ?></p>
                    </div>
                <?php endif; ?>
                
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <h2 class="text-xl font-semibold text-gray-700 mb-4">Satılan Biletler</h2>
                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white">
                            <thead class="bg-gray-200">
                                <tr>
                                    <th class="py-3 px-4 text-left">Yolcu</th>
                                    <th class="py-3 px-4 text-left">Güzergah</th>
                                    <th class="py-3 px-4 text-left">Satış Tarihi</th>
                                    <th class="py-3 px-4 text-left">Durum</th>
                                    <th class="py-3 px-4 text-left">İşlem</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($biletler)): ?>
                                    <?php foreach ($biletler as $bilet): ?>
                                        <tr class="border-b">
                                            <td class="py-3 px-4"><?php echo htmlspecialchars($bilet['full_name']); ?></td>
                                            <td class="py-3 px-4"><?php echo htmlspecialchars(ucfirst($bilet['departure_city'])) . ' → ' . htmlspecialchars(ucfirst($bilet['destination_city'])); ?></td>
                                            <td class="py-3 px-4"><?php echo date('d.m.Y H:i', strtotime($bilet['created_at'])); ?></td>
                                            <td class="py-3 px-4">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $bilet['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                                    <?php echo ucfirst($bilet['status']); ?>
                                                </span>
                                            </td>
                                            <td class="py-3 px-4">
                                                <?php if ($bilet['status'] === 'active'): ?>
                                                    <a href="company-admin-ticket-cancel.php?ticket_id=<?php echo htmlspecialchars($bilet['ticket_id']); ?>" class="text-red-500 hover:text-red-700" onclick="return confirm('Bu bileti iptal etmek istediğinizden emin misiniz?');">İptal Et</a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="py-4 px-4 text-center text-gray-500">Firmanıza ait hiç bilet satışı bulunmamaktadır.</td>
                                    </tr>
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