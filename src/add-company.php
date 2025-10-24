<?php
require 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: /login.php');
    exit();
}

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $company_name = trim($_POST['company_name']);
    $logo_path_for_db = null; 

    if (empty($company_name)) {
        $error_message = "Firma adı boş bırakılamaz.";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id FROM Bus_Company WHERE name = :name");
            $stmt->execute([':name' => $company_name]);

            if ($stmt->fetch()) {
                $error_message = "Bu firma adı zaten kayıtlı.";
            } else {
                if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
                    $file_tmp_path = $_FILES['logo']['tmp_name'];
                    $file_name = basename($_FILES['logo']['name']); 
                    $file_size = $_FILES['logo']['size'];
                    $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                    $allowed_extensions = ['jpg', 'jpeg', 'png', 'webp'];

                    if ($file_size > 2000000) { 
                        $error_message = "Dosya boyutu çok büyük. Lütfen 2MB'dan küçük bir dosya seçin.";
                    } elseif (!in_array($file_extension, $allowed_extensions)) {
                        $error_message = "Geçersiz dosya uzantısı. Sadece JPG, PNG, WEBP formatlarına izin verilmektedir.";
                    } else {
                        $company_id_for_logo = bin2hex(random_bytes(16));
                        $new_file_name = $company_id_for_logo . '.' . $file_extension;
                        $upload_dir = 'uploads/logos/';
                        $dest_path = $upload_dir . $new_file_name;

                        if (move_uploaded_file($file_tmp_path, $dest_path)) {
                            $logo_path_for_db = $dest_path;
                        } else {
                            $error_message = "Logo yüklenirken bir hata oluştu.";
                        }
                    }
                }

                if (empty($error_message)) {
                    $company_id = bin2hex(random_bytes(16));
                    $sql = "INSERT INTO Bus_Company (id, name, logo_path, created_at) VALUES (:id, :name, :logo_path, :created_at)";
                    $stmt = $pdo->prepare($sql);
                    $created_at = date('Y-m-d H:i:s');
                    
                    $stmt->execute([
                        ':id' => $company_id,
                        ':name' => $company_name,
                        ':logo_path' => $logo_path_for_db, 
                        ':created_at' => $created_at
                    ]);

                    header("Location: manage-companies.php?status=added");
                    exit();
                }
            }
        } catch (PDOException $e) {
            error_log("Firma ekleme hatası: " . $e->getMessage());
            $error_message = "Sunucuda bir hata oluştu. Lütfen daha sonra tekrar deneyin.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yeni Firma Ekle - Admin Paneli</title>
    <link href="/dist/output.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto mt-10 max-w-lg">
        <div class="bg-white p-8 rounded-lg shadow-md">
            <h1 class="text-2xl font-bold mb-6">Yeni Otobüs Firması Ekle</h1>

            <?php if ($error_message): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4" role="alert">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>
            
            <form action="add-company.php" method="POST" enctype="multipart/form-data">
                <div class="mb-4">
                    <label for="company_name" class="block text-sm font-medium text-gray-700">Firma Adı</label>
                    <input type="text" name="company_name" id="company_name" required class="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label for="logo" class="block text-sm font-medium text-gray-700">Firma Logosu (İsteğe Bağlı, max 2MB)</label>
                    <input type="file" name="logo" id="logo" class="mt-1 block w-full text-sm text-gray-500
                        file:mr-4 file:py-2 file:px-4
                        file:rounded-full file:border-0
                        file:text-sm file:font-semibold
                        file:bg-blue-50 file:text-blue-700
                        hover:file:bg-blue-100
                    ">
                </div>
                <div class="mt-6 flex justify-between">
                    <a href="manage-companies.php" class="bg-gray-500 text-white font-bold py-2 px-4 rounded hover:bg-gray-600 transition">İptal</a>
                    <button type="submit" class="bg-blue-500 text-white font-bold py-2 px-4 rounded hover:bg-blue-600 transition">Firmayı Ekle</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>