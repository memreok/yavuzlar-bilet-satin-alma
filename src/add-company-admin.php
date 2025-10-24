<?php
require 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: /login.php');
    exit();
}

try {
    $stmt = $pdo->query("SELECT id, name FROM Bus_Company ORDER BY name ASC");
    $companies = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Firmalar çekilemedi: " . $e->getMessage());
}


$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $company_id = $_POST['company_id'];

    if (empty($full_name) || empty($email) || empty($password) || empty($company_id)) {
        $error_message = "Lütfen tüm alanları doldurun.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Geçersiz e-posta adresi.";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id FROM User WHERE email = :email");
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $error_message = "Bu e-posta adresi zaten kullanılıyor.";
            } else {
                $sql = "INSERT INTO User (id, full_name, email, password, role, company_id, created_at) VALUES (:id, :full_name, :email, :password, :role, :company_id, :created_at)";
                $stmt = $pdo->prepare($sql);

                $user_id = bin2hex(random_bytes(16));
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $role = 'company';
                $created_at = date('Y-m-d H:i:s');

                $stmt->bindParam(':id', $user_id);
                $stmt->bindParam(':full_name', $full_name);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':password', $hashed_password);
                $stmt->bindParam(':role', $role);
                $stmt->bindParam(':company_id', $company_id);
                $stmt->bindParam(':created_at', $created_at);

                if ($stmt->execute()) {
                    header("Location: manage-company-admins.php?status=admin_added");
                    exit();
                } else {
                    $error_message = "Admin eklenirken bir hata oluştu.";
                }
            }
        } catch (PDOException $e) {
            $error_message = "Hata";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yeni Firma Admini Ekle - Admin Paneli</title>
    <link href="/dist/output.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto mt-10 max-w-lg">
        <div class="bg-white p-8 rounded-lg shadow-md">
            <h1 class="text-2xl font-bold mb-6">Yeni Firma Admini Ekle</h1>

            <?php if ($error_message): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>

            <form action="add-company-admin.php" method="POST" class="space-y-4">
                <div>
                    <label for="full_name" class="block text-sm font-medium text-gray-700">İsim Soyisim</label>
                    <input type="text" name="full_name" id="full_name" required class="mt-1 block w-full p-2 border border-gray-300 rounded-md">
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">E-posta Adresi</label>
                    <input type="email" name="email" id="email" required class="mt-1 block w-full p-2 border border-gray-300 rounded-md">
                </div>
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">Şifre</label>
                    <input type="password" name="password" id="password" required class="mt-1 block w-full p-2 border border-gray-300 rounded-md">
                </div>
                <div>
                    <label for="company_id" class="block text-sm font-medium text-gray-700">Atanacak Firma</label>
                    <select name="company_id" id="company_id" required class="mt-1 block w-full p-2 border border-gray-300 rounded-md">
                        <option value="" disabled selected>Bir firma seçin...</option>
                        <?php foreach ($companies as $company): ?>
                            <option value="<?php echo htmlspecialchars($company['id']); ?>">
                                <?php echo htmlspecialchars($company['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="pt-4 flex justify-between">
                    <a href="manage-company-admins.php" class="bg-gray-500 text-white font-bold py-2 px-4 rounded hover:bg-gray-600 transition">İptal</a>
                    <button type="submit" class="bg-blue-500 text-white font-bold py-2 px-4 rounded hover:bg-blue-600 transition">Admini Ekle</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>