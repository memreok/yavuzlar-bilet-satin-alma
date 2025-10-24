<?php
require 'config.php';

$sehirler = [
    'Adana' => 'adana',
    'Adıyaman' => 'adiyaman',
    'Afyonkarahisar' => 'afyonkarahisar',
    'Ağrı' => 'agri',
    'Aksaray' => 'aksaray',
    'Amasya' => 'amasya',
    'Ankara' => 'ankara',
    'Antalya' => 'antalya',
    'Ardahan' => 'ardahan',
    'Artvin' => 'artvin',
    'Aydın' => 'aydin',
    'Balıkesir' => 'balikesir',
    'Bartın' => 'bartin',
    'Batman' => 'batman',
    'Bayburt' => 'bayburt',
    'Bilecik' => 'bilecik',
    'Bingöl' => 'bingol',
    'Bitlis' => 'bitlis',
    'Bolu' => 'bolu',
    'Burdur' => 'burdur',
    'Bursa' => 'bursa',
    'Çanakkale' => 'canakkale',
    'Çankırı' => 'cankiri',
    'Çorum' => 'corum',
    'Denizli' => 'denizli',
    'Diyarbakır' => 'diyarbakir',
    'Düzce' => 'duzce',
    'Edirne' => 'edirne',
    'Elazığ' => 'elazig',
    'Erzincan' => 'erzincan',
    'Erzurum' => 'erzurum',
    'Eskişehir' => 'eskisehir',
    'Gaziantep' => 'gaziantep',
    'Giresun' => 'giresun',
    'Gümüşhane' => 'gumushane',
    'Hakkâri' => 'hakkari',
    'Hatay' => 'hatay',
    'Iğdır' => 'igdir',
    'Isparta' => 'isparta',
    'İstanbul' => 'istanbul',
    'İzmir' => 'izmir',
    'Kahramanmaraş' => 'kahramanmaras',
    'Karabük' => 'karabuk',
    'Karaman' => 'karaman',
    'Kars' => 'kars',
    'Kastamonu' => 'kastamonu',
    'Kayseri' => 'kayseri',
    'Kırıkkale' => 'kirikkale',
    'Kırklareli' => 'kirklareli',
    'Kırşehir' => 'kirsehir',
    'Kilis' => 'kilis',
    'Kocaeli' => 'kocaeli',
    'Konya' => 'konya',
    'Kütahya' => 'kutahya',
    'Malatya' => 'malatya',
    'Manisa' => 'manisa',
    'Mardin' => 'mardin',
    'Mersin' => 'mersin',
    'Muğla' => 'mugla',
    'Muş' => 'mus',
    'Nevşehir' => 'nevsehir',
    'Niğde' => 'nigde',
    'Ordu' => 'ordu',
    'Osmaniye' => 'osmaniye',
    'Rize' => 'rize',
    'Sakarya' => 'sakarya',
    'Samsun' => 'samsun',
    'Siirt' => 'siirt',
    'Sinop' => 'sinop',
    'Sivas' => 'sivas',
    'Şanlıurfa' => 'sanliurfa',
    'Şırnak' => 'sirnak',
    'Tekirdağ' => 'tekirdag',
    'Tokat' => 'tokat',
    'Trabzon' => 'trabzon',
    'Tunceli' => 'tunceli',
    'Uşak' => 'usak',
    'Van' => 'van',
    'Yalova' => 'yalova',
    'Yozgat' => 'yozgat',
    'Zonguldak' => 'zonguldak',
];

$seferler = [];
mb_internal_encoding('UTF-8');

$kalkis_yeri_input = $_GET['kalkis'] ?? null;
$varis_yeri_input = $_GET['varis'] ?? null;
$tarih = $_GET['tarih'] ?? null;

$kalkis_yeri = $kalkis_yeri_input ? mb_strtolower($kalkis_yeri_input, 'UTF-8') : null;
$varis_yeri = $varis_yeri_input ? mb_strtolower($varis_yeri_input, 'UTF-8') : null;


$form_gonderildi = $kalkis_yeri && $varis_yeri && $tarih;

if ($form_gonderildi) {
    try {
        $tarih_baslangic = $tarih . ' 00:00:00';
        $tarih_bitis = $tarih . ' 23:59:59';
        
        $simdiki_zaman = date('Y-m-d H:i:s');

        error_log("DEBUG: Kalkış Yeri: " . $kalkis_yeri);
        error_log("DEBUG: Varış Yeri: " . $varis_yeri);
        error_log("DEBUG: Tarih Başlangıç: " . $tarih_baslangic);
        error_log("DEBUG: Tarih Bitiş: " . $tarih_bitis);
        error_log("DEBUG: Şimdiki Zaman: " . $simdiki_zaman);

        $stmt = $pdo->prepare(
"SELECT Trips.*, Bus_Company.name AS company_name, Bus_Company.logo_path FROM Trips
            INNER JOIN Bus_Company ON Trips.company_id = Bus_Company.id
            WHERE LOWER(Trips.departure_city) = :kalkis 
            AND LOWER(Trips.destination_city) = :varis 
            AND Trips.departure_time BETWEEN :tarih_baslangic AND :tarih_bitis
            AND Trips.departure_time > :simdiki_zaman" 
        );
        $stmt->execute([
            ':kalkis' => $kalkis_yeri, 
            ':varis' => $varis_yeri, 
            ':tarih_baslangic' => $tarih_baslangic,
            ':tarih_bitis' => $tarih_bitis,
            ':simdiki_zaman' => $simdiki_zaman 
        ]);
        $seferler = $stmt->fetchAll(PDO::FETCH_ASSOC);

        error_log("DEBUG: Bulunan Sefer Sayısı: " . count($seferler));

        foreach ($seferler as $key => $sefer) {
            $stmt_seats = $pdo->prepare(
                "SELECT bs.seat_number FROM Booked_Seats bs
                INNER JOIN Tickets t ON bs.ticket_id = t.id
                WHERE t.trip_id = :trip_id"
            );
            $stmt_seats->execute([':trip_id' => $sefer['id']]);
            $booked_seats = $stmt_seats->fetchAll(PDO::FETCH_COLUMN);
            $seferler[$key]['booked_seats'] = $booked_seats;
        }
    } catch (PDOException $e) {
        error_log("Sefer arama hatası: " . $e->getMessage());
    }
}

$kullanici_giris_yapti = isset($_SESSION['user_id']);
$kullanici_rolu_user = isset($_SESSION['role']) && $_SESSION['role'] === 'user';
$kullanici_adi = $kullanici_giris_yapti ? htmlspecialchars($_SESSION['full_name']) : '';
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bilet Satın Alma Platformu</title>
    <link href="/dist/output.css" rel="stylesheet">
    <style>
        .seat-radio { display: none; }
        
        
        .seat-radio:not(:disabled) + .seat-label {
            background-color: #e0e7ff; 
            color: #3730a3; 
            border-color: #a5b4fc;
            cursor: pointer;
        }

        .seat-radio:not(:disabled) + .seat-label:hover {
            background-color: #c7d2fe;
        }
        
        .seat-radio:checked + .seat-label {
            background-color: #22c55e;
            color: white;
            border-color: #16a34a; 
        }
        
        .seat-radio:disabled + .seat-label {
            background-color: #ef4444; 
            color: white;
            cursor: not-allowed;
            border-color: #b91c1c; 
        }
        
        .collapsible-content {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.5s ease-in-out;
        }
    </style>
</head>
<body class="bg-gray-100">

    <header class="bg-white shadow-md">
        <nav class="container mx-auto px-6 py-4">
            <div class="flex justify-between items-center">
                <a href="/" class="text-2xl font-bold text-blue-600">OBSAP</a>
                <div>
                    <?php if ($kullanici_giris_yapti): ?>
                        <span class="text-gray-700 font-medium mr-4">Hoş geldin, <?php echo $kullanici_adi; ?></span>
                        <a href="my-account.php" class="text-gray-600 hover:text-blue-600 px-3 py-2">Hesabım</a>
                        <a href="my-tickets.php" class="text-gray-600 hover:text-blue-600 px-3 py-2">Biletlerim</a>
                        <a href="logout.php" class="bg-red-500 text-white rounded-md px-4 py-2 hover:bg-red-600">Çıkış Yap</a>
                    <?php else: ?>
                        <a href="login.php" class="text-gray-600 hover:text-blue-600 px-3 py-2">Giriş Yap</a>
                        <a href="register.php" class="bg-blue-600 text-white rounded-md px-4 py-2 hover:bg-blue-700">Kayıt Ol</a>
                    <?php endif; ?>
                </div>
            </div>
        </nav>
    </header>

    <main class="container mx-auto px-6 py-12">
        <div class="bg-white p-8 rounded-xl shadow-lg max-w-4xl mx-auto">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Nereye gitmek istersiniz?</h1>
            <p class="text-gray-600 mb-6">Yolculuğunuzu planlamak için aşağıdaki formu doldurun.</p>
            <form action="index.php" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                <div>
                    <label for="kalkis" class="block text-sm font-medium text-gray-700 mb-1">Kalkış Yeri</label>
                    <input list="sehir-listesi" id="kalkis" name="kalkis" value="<?php echo htmlspecialchars($kalkis_yeri_input ?? ''); ?>" class="w-full p-3 border border-gray-300 rounded-md" required placeholder="İl Yazın...">
                </div>
                <div>
                    <label for="varis" class="block text-sm font-medium text-gray-700 mb-1">Varış Yeri</label>
                    <input list="sehir-listesi" id="varis" name="varis" value="<?php echo htmlspecialchars($varis_yeri_input ?? ''); ?>" class="w-full p-3 border border-gray-300 rounded-md" required placeholder="İl Yazın...">
                </div>
                
                <datalist id="sehir-listesi">
                    <?php foreach ($sehirler as $gosterim => $deger): ?>
                        <option value="<?php echo $gosterim; ?>">
                    <?php endforeach; ?>
                </datalist>
                
                <div>
                    <label for="tarih" class="block text-sm font-medium text-gray-700 mb-1">Yolculuk Tarihi</label>
                    <input type="date" id="tarih" name="tarih" value="<?php echo htmlspecialchars($tarih ?? ''); ?>" class="w-full p-3 border border-gray-300 rounded-md" required>
                </div>
                <div>
                    <button type="submit" class="w-full bg-green-500 text-white font-bold p-3 rounded-md hover:bg-green-600">Sefer Ara</button>
                </div>
            </form>
        </div>

        <?php if ($form_gonderildi): ?>
            <div class="mt-12 max-w-4xl mx-auto">
                <h2 class="text-2xl font-bold text-gray-800 mb-4">Arama Sonuçları</h2>
                <?php if (!empty($seferler)): ?>
                    <div class="space-y-4">
                        <?php foreach ($seferler as $sefer): ?>
                             <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                                <div class="p-4 flex justify-between items-center cursor-pointer trip-header" data-trip-id="<?php echo $sefer['id']; ?>">
                                    <div class="flex items-center gap-4">
<img 
    src="<?php echo !empty($sefer['logo_path']) ? htmlspecialchars($sefer['logo_path']) : '/uploads/logos/default-logo.png'; ?>" 
    alt="<?php echo htmlspecialchars($sefer['company_name']); ?> Logo" 
    class="h-10 w-10 object-contain rounded-md bg-gray-100 p-1"
>
                                        <div class="text-lg font-bold text-blue-600"><?php echo htmlspecialchars($sefer['company_name']); ?></div>
                                        <div class="text-xl font-bold text-gray-800"><?php echo date('H:i', strtotime($sefer['departure_time'])); ?></div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-2xl font-bold text-green-600 price-display" data-original-price="<?php echo $sefer['price']; ?>">
                                            <?php echo htmlspecialchars($sefer['price']); ?> TL
                                        </div>
                                        <div class="text-sm text-blue-500 font-semibold">Koltuk Seç</div>
                                    </div>
                                </div>
                                
                                <div id="collapsible-<?php echo $sefer['id']; ?>" class="collapsible-content bg-gray-50 border-t">
                                    <div class="p-6">
                                        <h3 class="text-lg font-semibold text-gray-700 mb-4 text-center">Koltuk Seçimi</h3>
                                        <form action="buy-ticket.php" method="GET" class="seat-selection-form">
                                            <input type="hidden" name="trip_id" value="<?php echo $sefer['id']; ?>">
                                            
                                            <div class="bg-gray-200 p-4 rounded-lg border-2 border-gray-300 max-w-3xl mx-auto overflow-x-auto">
                                                <div class="flex items-end gap-4 min-w-max">
                                                    <div class="flex flex-col items-center justify-center text-gray-600 p-2 bg-gray-300 rounded-md">
                                                        <svg class="w-10 h-10" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M16.5 18.5C16.5 19.8807 15.3807 21 14 21C12.6193 21 11.5 19.8807 11.5 18.5C11.5 17.1193 12.6193 16 14 16C15.3807 16 16.5 17.1193 16.5 18.5Z" stroke="currentColor" stroke-width="1.5"/><path d="M19 11H12.9395C12.4215 11 11.9723 11.4343 11.9995 11.9521L12.5 21" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/><path d="M5 11H8V15" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M5 15H8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M18 6L21 9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M3 6L6 9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M12 21H3.6C3.26863 21 3 20.7314 3 20.4V5.6C3 5.26863 3.26863 5 3.6 5H20.4C20.7314 5 21 5.26863 21 5.6V12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                                        <span class="text-xs font-semibold mt-1">Şoför</span>
                                                    </div>

                                                    <div class="grid grid-flow-col grid-rows-5 gap-y-2 gap-x-3">
                                                        <?php
                                                        $total_seats = $sefer['capacity']; 
                                                        $booked_seats = $sefer['booked_seats'];
                                                        
                                                        for ($i = 1; $i <= $total_seats; $i++):
                                                            $is_booked = in_array($i, $booked_seats);
                                                            $disabled_attr = $is_booked ? 'disabled' : '';
                                                        ?>
                                                            <div>
                                                                <input type="radio" id="seat-<?php echo $sefer['id'] . '-' . $i; ?>" name="seat_number" value="<?php echo $i; ?>" class="seat-radio" <?php echo $disabled_attr; ?> required>
                                                                <label for="seat-<?php echo $sefer['id'] . '-' . $i; ?>" class="seat-label w-10 h-10 flex items-center justify-center rounded-md font-medium border-2 transition-colors duration-200">
                                                                    <?php echo $i; ?>
                                                                </label>
                                                            </div>
                                                            
                                                            <?php if ($i % 4 == 2):  ?>
                                                                <div class="row-start-3 flex items-center justify-center text-gray-500 font-semibold text-xs tracking-wider">
                                                                    <?php if ($i == 22):  ?>
                                                                        <span>KORİDOR</span>
                                                                    <?php endif; ?>
                                                                </div>
                                                            <?php endif; ?>

                                                        <?php endfor; ?>
                                                    </div>
                                                </div>
                                            </div>

                                                <div class="text-center mt-6">
                                                    <?php if ($kullanici_giris_yapti && $kullanici_rolu_user): ?>
                                                        <button type="submit" class="bg-blue-600 text-white font-bold p-3 rounded-md hover:bg-blue-700 px-8">Satın Almaya Devam Et</button>
                                                    <?php else: ?>
                                                        <p class="text-red-500">Bilet satın almak için <a href="login.php" class="underline hover:text-red-700">giriş yapmanız</a> veya <a href="register.php" class="underline hover:text-red-700">kayıt olmanız</a> gerekmektedir.</p>
                                                    <?php endif; ?>
                                                </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="bg-white p-8 rounded-xl shadow-lg text-center"><p class="text-gray-500">Bu kriterlere uygun sefer bulunamadı.</p></div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </main>
    
    </body>
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
        });
    </script>
</html>

