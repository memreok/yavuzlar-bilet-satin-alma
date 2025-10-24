<?php
require 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'company' || !isset($_GET['id'])) {
    header('Location: /company-admin-dashboard.php');
    exit();
}

$trip_id = $_GET['id'];
$company_id = $_SESSION['company_id'];
$error_message = '';
$trip = null;

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

try {
    $stmt = $pdo->prepare("SELECT * FROM Trips WHERE id = :id AND company_id = :company_id");
    $stmt->execute([':id' => $trip_id, ':company_id' => $company_id]);
    $trip = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$trip) {
        header("Location: /company-admin-dashboard.php?error=Sefer bulunamadı veya yetkiniz yok.");
        exit();
    }
} catch (PDOException $e) {
    die("Veritabanı hatası: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $departure_city = $_POST['departure_city'];
    $destination_city = $_POST['destination_city'];
    $departure_time = str_replace('T', ' ', trim($_POST['departure_time']));
    $arrival_time = str_replace('T', ' ', trim($_POST['arrival_time']));
    $price = trim($_POST['price']);
    $capacity = trim($_POST['capacity']);
    
    if (empty($departure_city) || empty($destination_city) || empty($departure_time) || empty($arrival_time) || empty($price) || empty($capacity)) {
        $error_message = "Lütfen tüm alanları doldurun.";
    } else {
        try {
            $sql = "UPDATE Trips SET 
                        departure_city = :departure_city, 
                        destination_city = :destination_city, 
                        departure_time = :departure_time, 
                        arrival_time = :arrival_time, 
                        price = :price, 
                        capacity = :capacity 
                    WHERE id = :id AND company_id = :company_id";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':departure_city' => $departure_city,
                ':destination_city' => $destination_city,
                ':departure_time' => $departure_time,
                ':arrival_time' => $arrival_time,
                ':price' => $price,
                ':capacity' => $capacity,
                ':id' => $trip_id,
                ':company_id' => $company_id
            ]);

            header("Location: /company-admin-dashboard.php?status=updated");
            exit();

        } catch (PDOException $e) {
            $error_message = "Veritabanı hatası: " . $e->getMessage();
        }
    }
}

?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seferi Düzenle</title>
    <link href="/dist/output.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto mt-10 max-w-2xl">
        <div class="bg-white p-8 rounded-lg shadow-md">
            <h1 class="text-2xl font-bold mb-6">Seferi Düzenle</h1>

            <?php if ($error_message): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4"><?php echo $error_message; ?></div>
            <?php endif; ?>

            <form action="edit-trip.php?id=<?php echo htmlspecialchars($trip_id); ?>" method="POST">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="departure_city" class="block text-sm font-medium text-gray-700">Kalkış Yeri</label>
                        <input list="sehir-listesi" id="departure_city" name="departure_city" value="<?php echo htmlspecialchars($trip['departure_city']); ?>" required class="mt-1 block w-full p-2 border border-gray-300 rounded-md">
                    </div>
                    <div>
                        <label for="destination_city" class="block text-sm font-medium text-gray-700">Varış Yeri</label>
                        <input list="sehir-listesi" id="destination_city" name="destination_city" value="<?php echo htmlspecialchars($trip['destination_city']); ?>" required class="mt-1 block w-full p-2 border border-gray-300 rounded-md">
                    </div>
                    
                    <datalist id="sehir-listesi">
                        <?php foreach ($sehirler as $value => $label): ?>
                            <option value="<?php echo $label; ?>"><?php echo $value; ?></option>
                        <?php endforeach; ?>
                    </datalist>
                    
                    <div>
                        <label for="departure_time" class="block text-sm font-medium text-gray-700">Kalkış Zamanı</label>
                        <input type="datetime-local" name="departure_time" id="departure_time" value="<?php echo htmlspecialchars($trip['departure_time']); ?>" required class="mt-1 block w-full p-2 border border-gray-300 rounded-md">
                    </div>
                     <div>
                        <label for="arrival_time" class="block text-sm font-medium text-gray-700">Varış Zamanı</label>
                        <input type="datetime-local" name="arrival_time" id="arrival_time" value="<?php echo htmlspecialchars($trip['arrival_time']); ?>" required class="mt-1 block w-full p-2 border border-gray-300 rounded-md">
                    </div>
                     <div>
                        <label for="price" class="block text-sm font-medium text-gray-700">Fiyat (TL)</label>
                        <input type="number" name="price" id="price" value="<?php echo htmlspecialchars($trip['price']); ?>" required class="mt-1 block w-full p-2 border border-gray-300 rounded-md">
                    </div>
                    <div>
                        <label for="capacity" class="block text-sm font-medium text-gray-700">Koltuk Sayısı (Kapasite)</label>
                        <input type="number" name="capacity" id="capacity" value="<?php echo htmlspecialchars($trip['capacity']); ?>" required class="mt-1 block w-full p-2 border border-gray-300 rounded-md">
                    </div>
                </div>
                <div class="mt-6 flex justify-between">
                    <a href="company-admin-dashboard.php" class="bg-gray-500 text-white font-bold py-2 px-4 rounded hover:bg-gray-600 transition">İptal</a>
                    <button type="submit" class="bg-blue-500 text-white font-bold py-2 px-4 rounded hover:bg-blue-600 transition">Değişiklikleri Kaydet</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>