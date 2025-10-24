<?php
require 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'company') {
    header('Location: /login.php');
    exit();
}

$company_id = $_SESSION['company_id'];
$admin_adi = $_SESSION['full_name'];
$seferler = [];

try {
    $stmt = $pdo->prepare("SELECT * FROM Trips WHERE company_id = :company_id ORDER BY departure_time DESC");
    $stmt->bindParam(':company_id', $company_id, PDO::PARAM_STR);
    $stmt->execute();
    $seferler = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($seferler as $key => $sefer) {
        $stmt_seats = $pdo->prepare(
            "SELECT bs.seat_number, u.full_name, u.email 
             FROM Booked_Seats bs
             INNER JOIN Tickets t ON bs.ticket_id = t.id
             INNER JOIN User u ON t.user_id = u.id
             WHERE t.trip_id = :trip_id"
        );
        $stmt_seats->execute([':trip_id' => $sefer['id']]);
        $booked_seats_raw = $stmt_seats->fetchAll(PDO::FETCH_ASSOC);

        $booked_seats_details = [];
        foreach ($booked_seats_raw as $seat) {
            $booked_seats_details[$seat['seat_number']] = [
                'full_name' => $seat['full_name'],
                'email' => $seat['email']
            ];
        }
        $seferler[$key]['booked_seats_details'] = $booked_seats_details;
    }

} catch (PDOException $e) {
    error_log("Firma seferleri çekme hatası: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Firma Paneli - Sefer Yönetimi</title>
    <link href="/dist/output.css" rel="stylesheet">
    <style>
        .seat-label {
            cursor: pointer;
        }
        .seat-available {
            background-color: #e0e7ff; 
            color: #3730a3; 
            border-color: #a5b4fc; 
        }
        .seat-booked {
            background-color: #ef4444; 
            color: white;
            border-color: #b91c1c; 
            cursor: help; 
        }
        .collapsible-content {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.5s ease-in-out;
        }
    </style>
</head>
<body class="bg-gray-100 font-sans">
    <div class="flex h-screen">
        <aside class="w-64 bg-gray-800 text-white flex-shrink-0">
            <div class="p-6 text-2xl font-bold border-b border-gray-700">Firma Paneli</div>
            <nav class="mt-6">
                <a href="/company-admin-dashboard.php" class="flex items-center px-6 py-3 mt-2 text-gray-300 hover:bg-gray-700 hover:text-white transition">
                    <span>Dashboard</span>
                </a>
                <a href="/manage-trips.php" class="flex items-center px-6 py-3 bg-gray-700 text-white">
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
                    <span>Çıkış Yap</span>
                </a>
            </div>
        </aside>

        <div class="flex-1 flex flex-col overflow-hidden">
            <header class="flex justify-between items-center p-6 bg-white border-b">
                <h1 class="text-2xl font-semibold text-gray-800">Sefer Yönetimi</h1>
                <div class="flex items-center">
                    <span class="mr-3 font-medium"><?php echo htmlspecialchars($admin_adi); ?></span>
                </div>
            </header>

            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-6">
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-xl font-semibold text-gray-700">Mevcut Seferler</h2>
                        <a href="add-trip.php" class="bg-green-500 text-white font-bold py-2 px-4 rounded hover:bg-green-600 transition">
                            + Yeni Sefer Ekle
                        </a>
                    </div>
                    
                    <div class="space-y-4">
                        <?php if (!empty($seferler)): ?>
                            <?php foreach ($seferler as $sefer): ?>
                                <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-200">
                                    <div class="p-4 flex justify-between items-center cursor-pointer trip-header" data-trip-id="<?php echo $sefer['id']; ?>">
                                        <div class="flex items-center gap-6">
                                            <div class="font-bold text-gray-800">
                                                <span class="text-lg"><?php echo htmlspecialchars(ucfirst($sefer['departure_city'])); ?></span>
                                                <span class="text-gray-500 mx-2">→</span>
                                                <span class="text-lg"><?php echo htmlspecialchars(ucfirst($sefer['destination_city'])); ?></span>
                                            </div>
                                            <div class="text-lg font-semibold text-blue-600"><?php echo date('d.m.Y H:i', strtotime($sefer['departure_time'])); ?></div>
                                        </div>
                                        <div class="text-right flex items-center gap-4">
                                            <div>
                                                <div class="text-xl font-bold text-green-600"><?php echo htmlspecialchars($sefer['price']); ?> TL</div>
                                                <div class="text-sm text-blue-500 font-semibold">Koltukları Gör</div>
                                            </div>
                                            <div class="border-l pl-4">
                                                <a href="edit-trip.php?id=<?php echo htmlspecialchars($sefer['id']); ?>" class="text-blue-500 hover:text-blue-700 block mb-1">Düzenle</a>
                                                <a href="delete-trip.php?id=<?php echo htmlspecialchars($sefer['id']); ?>" class="text-red-500 hover:text-red-700 block" onclick="return confirm('Bu seferi silmek istediğinizden emin misiniz?');">Sil</a>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div id="collapsible-<?php echo $sefer['id']; ?>" class="collapsible-content bg-gray-50 border-t">
                                        <div class="p-6">
                                            <h3 class="text-lg font-semibold text-gray-700 mb-4 text-center">Koltuk Düzeni</h3>
                                            <div class="bg-gray-200 p-4 rounded-lg border-2 border-gray-300 max-w-3xl mx-auto overflow-x-auto">
                                                <div class="flex items-end gap-4 min-w-max">
                                                    <div class="flex flex-col items-center justify-center text-gray-600 p-2 bg-gray-300 rounded-md">
                                                        <svg class="w-10 h-10" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M16.5 18.5C16.5 19.8807 15.3807 21 14 21C12.6193 21 11.5 19.8807 11.5 18.5C11.5 17.1193 12.6193 16 14 16C15.3807 16 16.5 17.1193 16.5 18.5Z" stroke="currentColor" stroke-width="1.5"/><path d="M19 11H12.9395C12.4215 11 11.9723 11.4343 11.9995 11.9521L12.5 21" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/><path d="M5 11H8V15" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M5 15H8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M18 6L21 9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M3 6L6 9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M12 21H3.6C3.26863 21 3 20.7314 3 20.4V5.6C3 5.26863 3.26863 5 3.6 5H20.4C20.7314 5 21 5.26863 21 5.6V12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                                        <span class="text-xs font-semibold mt-1">Şoför</span>
                                                    </div>

                                                    <div class="grid grid-flow-col grid-rows-5 gap-y-2 gap-x-3">
                                                        <?php
                                                        $total_seats = $sefer['capacity'];
                                                        $booked_seats_details = $sefer['booked_seats_details'];
                                                        
                                                        for ($i = 1; $i <= $total_seats; $i++):
                                                            $is_booked = isset($booked_seats_details[$i]);
                                                            $seat_class = $is_booked ? 'seat-booked' : 'seat-available';
                                                            $user_info_attr = '';
                                                            if ($is_booked) {
                                                                $user_info_attr = sprintf(
                                                                    "data-fullname='%s' data-email='%s'",
                                                                    htmlspecialchars($booked_seats_details[$i]['full_name']),
                                                                    htmlspecialchars($booked_seats_details[$i]['email'])
                                                                );
                                                            }
                                                        ?>
                                                            <div>
                                                                <div class="seat-label w-10 h-10 flex items-center justify-center rounded-md font-medium border-2 transition-colors duration-200 <?php echo $seat_class; ?>" 
                                                                     <?php echo $user_info_attr; ?>>
                                                                    <?php echo $i; ?>
                                                                </div>
                                                            </div>
                                                            
                                                            <?php if ($i % 4 == 2):  ?>
                                                                <div class="row-start-3 flex items-center justify-center"></div>
                                                            <?php endif; ?>
                                                        <?php endfor; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="py-4 px-4 text-center text-gray-500">Henüz hiç sefer eklenmemiş.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.trip-header').forEach(header => {
                header.addEventListener('click', function() {
                    const tripId = this.dataset.tripId;
                    const collapsibleContent = document.getElementById(`collapsible-${tripId}`);
                    
                    document.querySelectorAll('.collapsible-content').forEach(content => {
                        if (content.id !== `collapsible-${tripId}`) {
                            content.style.maxHeight = null;
                        }
                    });

                    if (collapsibleContent.style.maxHeight) {
                        collapsibleContent.style.maxHeight = null;
                    } else {
                        collapsibleContent.style.maxHeight = collapsibleContent.scrollHeight + 'px';
                    }
                });
            });

            document.querySelectorAll('.seat-booked').forEach(seat => {
                seat.addEventListener('click', function() {
                    const fullName = this.dataset.fullname;
                    const email = this.dataset.email;
                    
                    if (fullName && email) {
                        alert(`Bu koltuk şu kişi tarafından satın alınmıştır:\n\nİsim: ${fullName}\nEmail: ${email}`);
                    }
                });
            });
        });
    </script>
</body>
</html>