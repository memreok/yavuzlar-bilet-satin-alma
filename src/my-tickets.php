<?php
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$kullanici_adi = htmlspecialchars($_SESSION['full_name']);
$aktif_biletler = [];
$gecmis_biletler = [];

try {
    $stmt = $pdo->prepare(
        "SELECT
            T.id as ticket_id, T.status,
            TR.departure_city, TR.destination_city, TR.departure_time, TR.arrival_time,
            BC.name as company_name, BS.seat_number, T.total_price
        FROM Tickets AS T
        JOIN Trips AS TR ON T.trip_id = TR.id
        JOIN Bus_Company AS BC ON TR.company_id = BC.id
        JOIN Booked_Seats AS BS ON T.id = BS.ticket_id
        WHERE T.user_id = :user_id
        ORDER BY TR.departure_time DESC"
    );
    $stmt->execute([':user_id' => $user_id]);
    $tum_biletler = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $current_timestamp = time();
    foreach ($tum_biletler as $bilet) {
        $departure_timestamp = strtotime($bilet['departure_time']);
        if ($departure_timestamp > $current_timestamp && $bilet['status'] === 'active') {
            $aktif_biletler[] = $bilet;
        } else {
            $gecmis_biletler[] = $bilet;
        }
    }

} catch (PDOException $e) {
    error_log("Biletleri çekerken hata: " . $e->getMessage());
    $error_message = "Biletlerinizi yüklerken bir sorun oluştu.";
}

?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Biletlerim - OBSAP</title>
    <link href="/dist/output.css" rel="stylesheet">
</head>
<body class="bg-gray-100">

    <header class="bg-white shadow-md">
        <nav class="container mx-auto px-6 py-4">
            <div class="flex justify-between items-center">
                <a href="/" class="text-2xl font-bold text-blue-600">OBSAP</a>
                <div>
                    <span class="text-gray-700 font-medium mr-4">Hoş geldin, <?php echo $kullanici_adi; ?></span>
                    <a href="my-account.php" class="text-gray-600 hover:text-blue-600 px-3 py-2">Hesabım</a>
                    <a href="my-tickets.php" class="text-gray-600 font-bold text-blue-600 px-3 py-2">Biletlerim</a>
                    <a href="logout.php" class="bg-red-500 text-white rounded-md px-4 py-2 hover:bg-red-600">Çıkış Yap</a>
                </div>
            </div>
        </nav>
    </header>

    <main class="container mx-auto px-6 py-12">

        <?php if (isset($_GET['status']) && $_GET['status'] == 'cancel_success'): ?>
             <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-md shadow" role="alert">
                <p class="font-bold">Başarılı!</p>
                <p>Biletiniz başarıyla iptal edildi ve ücret iadesi yapıldı.</p>
            </div>
        <?php elseif (isset($_GET['error'])): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-md shadow" role="alert">
                <p class="font-bold">Hata!</p>
                <p><?php echo htmlspecialchars($_GET['error']); ?></p>
            </div>
        <?php endif; ?>

        <section>
            <h1 class="text-3xl font-bold text-gray-800 mb-6">Aktif Biletlerim</h1>
            <div class="space-y-6">
                <?php if (!empty($aktif_biletler)): ?>
                    <?php foreach ($aktif_biletler as $bilet): ?>
                        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                            <div class="p-6">
                               <div class="flex justify-between items-start">
                                    <div>
                                        <p class="font-bold text-xl text-blue-600"><?php echo htmlspecialchars($bilet['company_name']); ?></p>
                                        <p class="text-lg"><?php echo htmlspecialchars(ucfirst($bilet['departure_city'])); ?> &rarr; <?php echo htmlspecialchars(ucfirst($bilet['destination_city'])); ?></p>
                                    </div>
                                    <div class="text-right">
                                        <p>Koltuk No: <span class="font-bold text-2xl"><?php echo htmlspecialchars($bilet['seat_number']); ?></span></p>
                                        <p class="font-semibold text-lg"><?php echo htmlspecialchars($bilet['total_price']); ?> TL</p>
                                    </div>
                                </div>
                                <div class="border-t border-gray-200 mt-4 pt-4 flex justify-between items-center text-sm text-gray-600">
                                    <div>
                                        <p><span class="font-semibold">Kalkış:</span> <?php echo date('d M Y, H:i', strtotime($bilet['departure_time'])); ?></p>
                                    </div>
                                    <div class="flex space-x-2">
                                        <a href="cancel-ticket.php?id=<?php echo htmlspecialchars($bilet['ticket_id']); ?>" 
                                           class="bg-red-500 text-white font-bold py-2 px-4 rounded hover:bg-red-600"
                                           onclick="return confirm('Bu bileti iptal etmek istediğinizden emin misiniz?');">İptal Et</a>
                                        <a href="download-ticket.php?id=<?php echo htmlspecialchars($bilet['ticket_id']); ?>" target="_blank" class="bg-blue-500 text-white font-bold py-2 px-4 rounded hover:bg-blue-700">PDF İndir</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="bg-white p-8 rounded-xl shadow-lg text-center">
                        <p class="text-gray-500">Henüz satın alınmış aktif bir biletiniz bulunmuyor.</p>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <section class="mt-12">
            <h1 class="text-3xl font-bold text-gray-800 mb-6">Geçmiş Biletlerim</h1>
            <div class="space-y-6">
                 <?php if (!empty($gecmis_biletler)): ?>
                    <?php foreach ($gecmis_biletler as $bilet): ?>
                        <div class="bg-white rounded-xl shadow-lg overflow-hidden opacity-60">
                            <div class="p-6">
                               <div class="flex justify-between items-start">
                                    <div>
                                        <p class="font-bold text-xl text-gray-600"><?php echo htmlspecialchars($bilet['company_name']); ?></p>
                                        <p class="text-lg"><?php echo htmlspecialchars(ucfirst($bilet['departure_city'])); ?> &rarr; <?php echo htmlspecialchars(ucfirst($bilet['destination_city'])); ?></p>
                                        <span class="text-xs font-semibold px-2 py-1 rounded-full <?php echo $bilet['status'] === 'canceled' ? 'bg-red-200 text-red-800' : 'bg-gray-200 text-gray-800'; ?>">
                                            <?php echo $bilet['status'] === 'canceled' ? 'İptal Edildi' : 'Sefer Tamamlandı'; ?>
                                        </span>
                                    </div>
                                    <div class="text-right">
                                        <p>Koltuk No: <span class="font-bold text-2xl"><?php echo htmlspecialchars($bilet['seat_number']); ?></span></p>
                                    </div>
                                </div>
                                <div class="border-t border-gray-200 mt-4 pt-4 flex justify-between items-center text-sm text-gray-600">
                                    <div>
                                        <p><span class="font-semibold">Kalkış Tarihi:</span> <?php echo date('d M Y, H:i', strtotime($bilet['departure_time'])); ?></p>
                                    </div>
                                    <a href="download-ticket.php?id=<?php echo htmlspecialchars($bilet['ticket_id']); ?>" target="_blank" class="bg-gray-500 text-white font-bold py-2 px-4 rounded hover:bg-gray-600">PDF İndir</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="bg-white p-8 rounded-xl shadow-lg text-center">
                        <p class="text-gray-500">Geçmiş bir biletiniz bulunmuyor.</p>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </main>

</body>
</html>
