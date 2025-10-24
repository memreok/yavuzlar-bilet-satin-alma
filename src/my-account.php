<?php
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $full_name = trim($_POST['full_name']);
        if (!empty($full_name)) {
            try {
                $stmt = $pdo->prepare("UPDATE User SET full_name = :full_name WHERE id = :user_id");
                $stmt->execute([':full_name' => $full_name, ':user_id' => $user_id]);
                $_SESSION['full_name'] = $full_name; // Session'daki ismi de güncelle
                $success_message = 'Profil bilgileriniz başarıyla güncellendi.';
            } catch (PDOException $e) {
                $error_message = 'Bilgiler güncellenirken bir hata oluştu.';
            }
        } else {
            $error_message = 'İsim alanı boş bırakılamaz.';
        }
    }
    elseif (isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            $error_message = 'Lütfen tüm şifre alanlarını doldurun.';
        } elseif ($new_password !== $confirm_password) {
            $error_message = 'Yeni şifreler eşleşmiyor.';
        } else {
            try {
                $stmt = $pdo->prepare("SELECT password FROM User WHERE id = :user_id");
                $stmt->execute([':user_id' => $user_id]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($user && password_verify($current_password, $user['password'])) {
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE User SET password = :password WHERE id = :user_id");
                    $stmt->execute([':password' => $hashed_password, ':user_id' => $user_id]);
                    $success_message = 'Şifreniz başarıyla değiştirildi.';
                } else {
                    $error_message = 'Mevcut şifreniz yanlış.';
                }
            } catch (PDOException $e) {
                $error_message = 'Şifre değiştirilirken bir hata oluştu.';
            }
        }
    }
}


try {
    $stmt = $pdo->prepare("SELECT full_name, email, balance, created_at FROM User WHERE id = :user_id");
    $stmt->execute([':user_id' => $user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) {

        session_destroy();
        header('Location: /login.php');
        exit();
    }
} catch (PDOException $e) {
    die("Kullanıcı bilgileri alınırken bir hata oluştu.");
}

$kullanici_adi = htmlspecialchars($user['full_name']);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hesabım - OBSAP</title>
    <link href="/dist/output.css" rel="stylesheet">
</head>
<body class="bg-gray-100">

    <header class="bg-white shadow-md">
        <nav class="container mx-auto px-6 py-4">
            <div class="flex justify-between items-center">
                <a href="/" class="text-2xl font-bold text-blue-600">OBSAP</a>
                <div>
                    <span class="text-gray-700 font-medium mr-4">Hoş geldin, <?php echo $kullanici_adi; ?></span>
                    <a href="my-account.php" class="text-gray-600 font-bold text-blue-600 px-3 py-2">Hesabım</a>
                    <a href="my-tickets.php" class="text-gray-600 hover:text-blue-600 px-3 py-2">Biletlerim</a>
                    <a href="logout.php" class="bg-red-500 text-white rounded-md px-4 py-2 hover:bg-red-600">Çıkış Yap</a>
                </div>
            </div>
        </nav>
    </header>

    <main class="container mx-auto px-6 py-12">
        <h1 class="text-3xl font-bold text-gray-800 mb-8">Hesap Bilgilerim</h1>

        <?php if ($success_message): ?>
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-md shadow" role="alert">
                <p><?php echo $success_message; ?></p>
            </div>
        <?php endif; ?>
        <?php if ($error_message): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-md shadow" role="alert">
                <p><?php echo $error_message; ?></p>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="md:col-span-2 bg-white p-8 rounded-xl shadow-lg">
                <h2 class="text-2xl font-bold text-gray-800 mb-6">Profil Bilgileri</h2>
                <form action="my-account.php" method="POST" class="space-y-6">
                    <div>
                        <label for="full_name" class="block text-sm font-medium text-gray-700">Ad Soyad</label>
                        <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" class="mt-1 block w-full p-3 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">E-posta Adresi</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" class="mt-1 block w-full p-3 border border-gray-300 rounded-md shadow-sm bg-gray-100 cursor-not-allowed" readonly>
                    </div>
                    <div>
                        <button type="submit" name="update_profile" class="w-full bg-blue-600 text-white font-bold p-3 rounded-md hover:bg-blue-700">Bilgileri Güncelle</button>
                    </div>
                </form>

                <hr class="my-8">

                <h2 class="text-2xl font-bold text-gray-800 mb-6">Şifre Değiştir</h2>
                <form action="my-account.php" method="POST" class="space-y-6">
                    <div>
                        <label for="current_password" class="block text-sm font-medium text-gray-700">Mevcut Şifre</label>
                        <input type="password" id="current_password" name="current_password" class="mt-1 block w-full p-3 border border-gray-300 rounded-md shadow-sm" required>
                    </div>
                    <div>
                        <label for="new_password" class="block text-sm font-medium text-gray-700">Yeni Şifre</label>
                        <input type="password" id="new_password" name="new_password" class="mt-1 block w-full p-3 border border-gray-300 rounded-md shadow-sm" required>
                    </div>
                    <div>
                        <label for="confirm_password" class="block text-sm font-medium text-gray-700">Yeni Şifre (Tekrar)</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="mt-1 block w-full p-3 border border-gray-300 rounded-md shadow-sm" required>
                    </div>
                    <div>
                        <button type="submit" name="change_password" class="w-full bg-gray-700 text-white font-bold p-3 rounded-md hover:bg-gray-800">Şifreyi Değiştir</button>
                    </div>
                </form>
            </div>

            <div class="bg-white p-8 rounded-xl shadow-lg h-fit">
                <h2 class="text-2xl font-bold text-gray-800 mb-6">Hesap Özeti</h2>
                <div class="space-y-4">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600 font-medium">Mevcut Bakiye:</span>
                        <span class="text-2xl font-bold text-green-600"><?php echo number_format($user['balance'], 2, ',', '.'); ?> TL</span>
                    </div>
                    <div class="flex justify-between items-center pt-4 border-t">
                        <span class="text-gray-600 font-medium">Üyelik Tarihi:</span>
                        <span class="font-semibold text-gray-800"><?php echo date('d.m.Y', strtotime($user['created_at'])); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </main>

</body>
</html>
