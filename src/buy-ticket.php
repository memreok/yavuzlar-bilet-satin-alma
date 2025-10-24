<?php
require 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: /login.php');
    exit();
}

if (!isset($_GET['trip_id']) || !isset($_GET['seat_number'])) {
    header('Location: /index.php?error=Bilet almak için sefer ve koltuk seçmelisiniz.');
    exit();
}

$trip_id = $_GET['trip_id'];
$seat_number = $_GET['seat_number'];
$user_id = $_SESSION['user_id'];
$error_message = '';
$trip = null;
$user = null;

try {
    $stmt = $pdo->prepare(
        "SELECT T.*, BC.name as company_name 
         FROM Trips AS T 
         JOIN Bus_Company AS BC ON T.company_id = BC.id 
         WHERE T.id = :trip_id"
    );
    $stmt->execute([':trip_id' => $trip_id]);
    $trip = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$trip) {
        throw new Exception("Seçilen sefer bulunamadı.");
    }

    $stmt_check_seat = $pdo->prepare(
        "SELECT COUNT(*) FROM Booked_Seats bs 
         JOIN Tickets t ON bs.ticket_id = t.id 
         WHERE t.trip_id = :trip_id AND bs.seat_number = :seat_number AND t.status = 'active'"
    );
    $stmt_check_seat->execute([':trip_id' => $trip_id, ':seat_number' => $seat_number]);
    if ($stmt_check_seat->fetchColumn() > 0) {
        header('Location: /index.php?error=' . urlencode("Üzgünüz, seçtiğiniz {$seat_number} numaralı koltuk siz işlemi başlatırken başkası tarafından alındı."));
        exit();
    }
    
    $stmt_user = $pdo->prepare("SELECT balance FROM User WHERE id = :user_id");
    $stmt_user->execute([':user_id' => $user_id]);
    $user = $stmt_user->fetch(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    error_log("Bilet alım sayfası hatası: " . $e->getMessage());
    header('Location: /index.php?error=' . urlencode('Sistemde bir sorun oluştu.'));
    exit();
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bilet Satın Almayı Onayla</title>
    <link href="/dist/output.css" rel="stylesheet">
</head>
<body class="bg-gray-100">

    <header class="bg-white shadow-md">
        </header>

    <main class="container mx-auto px-6 py-12">
        <div class="max-w-2xl mx-auto bg-white p-8 rounded-xl shadow-lg">
            <h1 class="text-3xl font-bold text-gray-800 mb-6 border-b pb-4">Satın Alma Onayı</h1>
            
            <div class="mb-6">
                </div>

            <form action="purchase-process.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <input type="hidden" name="trip_id" value="<?php echo htmlspecialchars($trip_id); ?>">
                <input type="hidden" name="seat_number" value="<?php echo htmlspecialchars($seat_number); ?>">

                <h2 class="text-xl font-semibold text-gray-700 mb-4">Ödeme Bilgileri</h2>
                
                <div class="bg-gray-50 p-4 rounded-lg">
                    <div class="flex justify-between items-center mb-4">
                        <span class="text-gray-600">Bilet Fiyatı:</span>
                        <span id="price-display" class="text-2xl font-bold text-green-600" data-original-price="<?php echo $trip['price']; ?>">
                            <?php echo htmlspecialchars($trip['price']); ?> TL
                        </span>
                    </div>

                    <div>
                        <label for="coupon_code" class="block text-sm font-medium text-gray-700 mb-1">Kupon Kodu</label>
                        <div class="flex gap-2">
                            <input type="text" name="coupon_code" id="coupon_code" class="flex-grow p-2 border border-gray-300 rounded-md uppercase" placeholder="İndirim Kodu Varsa Girin">
                            <button type="button" id="apply-coupon-btn" class="bg-gray-600 text-white font-bold p-2 rounded-md hover:bg-gray-700">Uygula</button>
                        </div>
                        <div id="coupon-message" class="text-sm mt-2"></div>
                    </div>
                </div>

                <div class="mt-4 p-4 bg-yellow-100 border-l-4 border-yellow-500 text-yellow-800">
                    Mevcut Bakiyeniz: <strong><?php echo number_format($user['balance'], 2, ',', '.'); ?> TL</strong>
                </div>

                <div class="mt-8 text-center">
                    <button type="submit" class="w-full bg-blue-600 text-white font-bold py-3 px-6 rounded-md hover:bg-blue-700 text-lg">
                        Ödemeyi Onayla ve Bileti Al
                    </button>
                    <a href="/" class="block mt-4 text-gray-600 hover:underline">İptal Et ve Geri Dön</a>
                </div>
            </form>
        </div>
    </main>
    
    <script>

    document.addEventListener('DOMContentLoaded', function() {
        const applyBtn = document.getElementById('apply-coupon-btn');
        const couponInput = document.getElementById('coupon_code');
        const messageDiv = document.getElementById('coupon-message');
        const priceDisplay = document.getElementById('price-display');
        const originalPrice = parseFloat(priceDisplay.dataset.originalPrice);
        const tripId = "<?php echo $trip_id; ?>";

        applyBtn.addEventListener('click', async function() {
            const couponCode = couponInput.value.trim().toUpperCase();
            if (!couponCode) {
                messageDiv.textContent = 'Lütfen bir kupon kodu girin.';
                messageDiv.className = 'text-sm mt-2 text-red-600';
                return;
            }

            try {
                const response = await fetch('/validate-coupon.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ coupon_code: couponCode, trip_id: tripId })
                });
                const data = await response.json();

                if (data.success) {
                    const discount = parseFloat(data.discount);
                    const newPrice = originalPrice - (originalPrice * discount / 100);
                    priceDisplay.innerHTML = `${newPrice.toFixed(2)} TL <s class="text-sm text-gray-500">${originalPrice.toFixed(2)} TL</s>`;
                    messageDiv.textContent = `Başarılı! %${discount} indirim uygulandı.`;
                    messageDiv.className = 'text-sm mt-2 text-green-600';
                } else {
                    priceDisplay.innerHTML = `${originalPrice.toFixed(2)} TL`;
                    messageDiv.textContent = data.message;
                    messageDiv.className = 'text-sm mt-2 text-red-600';
                }
            } catch (error) {
                messageDiv.textContent = 'Bir hata oluştu. Lütfen tekrar deneyin.';
                messageDiv.className = 'text-sm mt-2 text-red-600';
            }
        });
    });
    </script>
</body>
</html>