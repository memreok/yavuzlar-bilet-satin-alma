<?php
require 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'company') {
    header('Location: /login.php');
    exit();
}

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

$error_message = '';
mb_internal_encoding('UTF-8');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if (!isset($_SESSION['company_id']) || empty($_SESSION['company_id'])) {
        $error_message = "Kritik Hata: Firma ID'niz oturumda bulunamadı. Lütfen sistem yöneticisi ile iletişime geçin veya tekrar giriş yapın.";
    } else { 

    $departure_city = mb_strtolower(trim($_POST['departure_city']), 'UTF-8');
    $destination_city = mb_strtolower(trim($_POST['destination_city']), 'UTF-8');
    $departure_time = str_replace('T', ' ', trim($_POST['departure_time']));
    $arrival_time = str_replace('T', ' ', trim($_POST['arrival_time']));
    $price = trim($_POST['price']);
    $capacity = trim($_POST['capacity']);
    
    if (empty($departure_city) || empty($destination_city) || empty($departure_time) || empty($arrival_time) || empty($price) || empty($capacity)) {
        $error_message = "Lütfen tüm alanları doldurun.";
    } else {
        try {
            $sql = "INSERT INTO Trips (id, company_id, departure_city, destination_city, departure_time, arrival_time, price, capacity, created_date) 
                    VALUES (:id, :company_id, :departure_city, :destination_city, :departure_time, :arrival_time, :price, :capacity, :created_date)";
            
            $stmt = $pdo->prepare($sql);
            
            $trip_id = bin2hex(random_bytes(16));
            $company_id = $_SESSION['company_id'];
            $created_date = date('Y-m-d H:i:s');
            
            $stmt->bindParam(':id', $trip_id);
            $stmt->bindParam(':company_id', $company_id);
            $stmt->bindParam(':departure_city', $departure_city);
            $stmt->bindParam(':destination_city', $destination_city);
            $stmt->bindParam(':departure_time', $departure_time);
            $stmt->bindParam(':arrival_time', $arrival_time);
            $stmt->bindParam(':price', $price, PDO::PARAM_INT);
            $stmt->bindParam(':capacity', $capacity, PDO::PARAM_INT);
            $stmt->bindParam(':created_date', $created_date);

            if ($stmt->execute()) {
                header("Location: /company-admin-dashboard.php?status=success");
                exit();
            } else {
                $error_message = "Sefer eklenirken bir hata oluştu.";
            }

        } catch (PDOException $e) {
            $error_message = "Hata";
        }
    }
}}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yeni Sefer Ekle</title>
    <link href="/dist/output.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto mt-10 max-w-2xl">
        <div class="bg-white p-8 rounded-lg shadow-md">
            <h1 class="text-2xl font-bold mb-6">Yeni Sefer Ekle</h1>

            <?php if ($error_message): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4"><?php echo $error_message; ?></div>
            <?php endif; ?>

            <form action="add-trip.php" method="POST">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="departure_city" class="block text-sm font-medium text-gray-700">Kalkış Yeri</label>
                        <input list="sehir-listesi" id="departure_city" name="departure_city" required class="mt-1 block w-full p-2 border border-gray-300 rounded-md" placeholder="İl Yazın...">
                    </div>
                    <div>
                        <label for="destination_city" class="block text-sm font-medium text-gray-700">Varış Yeri</label>
                        <input list="sehir-listesi" id="destination_city" name="destination_city" required class="mt-1 block w-full p-2 border border-gray-300 rounded-md" placeholder="İl Yazın...">
                    </div>
                    
                    <datalist id="sehir-listesi">
                        <?php foreach ($sehirler as $gosterim => $deger): ?>
                            <option value="<?php echo $gosterim; ?>">
                        <?php endforeach; ?>
                    </datalist>
                    
                    <div>
                        <label for="departure_time" class="block text-sm font-medium text-gray-700">Kalkış Zamanı</label>
                        <input type="datetime-local" name="departure_time" id="departure_time" required class="mt-1 block w-full p-2 border border-gray-300 rounded-md">
                    </div>
                     <div>
                        <label for="arrival_time" class="block text-sm font-medium text-gray-700">Varış Zamanı</p>
                        <input type="datetime-local" name="arrival_time" id="arrival_time" required class="mt-1 block w-full p-2 border border-gray-300 rounded-md">
                    </div>
                     <div>
                        <label for="price" class="block text-sm font-medium text-gray-700">Fiyat (TL)</label>
                        <input type="number" name="price" id="price" required class="mt-1 block w-full p-2 border border-gray-300 rounded-md">
                    </div>
                    <div>
                        <label for="capacity" class="block text-sm font-medium text-gray-700">Koltuk Sayısı (Kapasite)</label>
                        <input type="number" name="capacity" id="capacity" required class="mt-1 block w-full p-2 border border-gray-300 rounded-md">
                    </div>
                </div>
                <div class="mt-6 flex justify-between">
                    <a href="company-admin-dashboard.php" class="bg-gray-500 text-white font-bold py-2 px-4 rounded hover:bg-gray-600 transition">İptal</a>
                    <button type="submit" class="bg-blue-500 text-white font-bold py-2 px-4 rounded hover:bg-blue-600 transition">Seferi Ekle</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>