<?php
require 'config.php';


if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin' || !isset($_GET['id'])) {
    header('Location: /login.php');
    exit();
}

$admin_id = $_GET['id'];
$error_message = '';
$admin_user = null;
$companies = [];


try {
    $stmt = $pdo->prepare("SELECT id, full_name, email, company_id FROM User WHERE id = :id AND role = 'company'");
    $stmt->execute([':id' => $admin_id]);
    $admin_user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$admin_user) {
        header("Location: manage-company-admins.php?error=Firma admini bulunamadı.");
        exit();
    }

    $stmt = $pdo->query("SELECT id, name FROM Bus_Company ORDER BY name ASC");
    $companies = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Hata");
}


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password']; 
    $company_id = $_POST['company_id'];

    if (empty($full_name) || empty($email) || empty($company_id)) {
        $error_message = "İsim, e-posta ve firma alanları zorunludur.";
    } else {
        try {

            if (!empty($password)) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $sql = "UPDATE User SET full_name = :full_name, email = :email, password = :password, company_id = :company_id WHERE id = :id";
                $params = [
                    ':full_name' => $full_name,
                    ':email' => $email,
                    ':password' => $hashed_password,
                    ':company_id' => $company_id,
                    ':id' => $admin_id
                ];
            } else {

                $sql = "UPDATE User SET full_name = :full_name, email = :email, company_id = :company_id WHERE id = :id";
                 $params = [
                    ':full_name' => $full_name,
                    ':email' => $email,
                    ':company_id' => $company_id,
                    ':id' => $admin_id
                ];
            }
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            header("Location: manage-company-admins.php?status=updated");
            exit();

        } catch (PDOException $e) {
             if ($e->getCode() == 23000 || $e->getCode() == 19) {
                 $error_message = "Bu e-posta adresi başka bir kullanıcı tarafından kullanılıyor.";
            } else {
                 $error_message = "Güncelleme sırasında bir hata oluştu!";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Firma Adminini Düzenle</title>
    <link href="/dist/output.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto mt-10 max-w-lg">
        <div class="bg-white p-8 rounded-lg shadow-md">
            <h1 class="text-2xl font-bold mb-6">Firma Adminini Düzenle</h1>

            <?php if ($error_message): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>

            <form action="edit-company-admin.php?id=<?php echo htmlspecialchars($admin_id); ?>" method="POST" class="space-y-4">
                <div>
                    <label for="full_name" class="block text-sm font-medium text-gray-700">İsim Soyisim</label>
                    <input type="text" name="full_name" id="full_name" value="<?php echo htmlspecialchars($admin_user['full_name']); ?>" required class="mt-1 block w-full p-2 border border-gray-300 rounded-md">
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">E-posta Adresi</label>
                    <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($admin_user['email']); ?>" required class="mt-1 block w-full p-2 border border-gray-300 rounded-md">
                </div>
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">Yeni Şifre</label>
                    <input type="password" name="password" id="password" placeholder="Değiştirmek istemiyorsanız boş bırakın" class="mt-1 block w-full p-2 border border-gray-300 rounded-md">
                </div>
                <div>
                    <label for="company_id" class="block text-sm font-medium text-gray-700">Atanacak Firma</label>
                    <select name="company_id" id="company_id" required class="mt-1 block w-full p-2 border border-gray-300 rounded-md">
                        <?php foreach ($companies as $company): ?>
                            <option value="<?php echo htmlspecialchars($company['id']); ?>" <?php echo ($company['id'] == $admin_user['company_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($company['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="pt-4 flex justify-between">
                    <a href="manage-company-admins.php" class="bg-gray-500 text-white font-bold py-2 px-4 rounded hover:bg-gray-600 transition">İptal</a>
                    <button type="submit" class="bg-blue-500 text-white font-bold py-2 px-4 rounded hover:bg-blue-600 transition">Değişiklikleri Kaydet</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>