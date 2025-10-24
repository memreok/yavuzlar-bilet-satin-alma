<?php
require 'config.php';


if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin' || !isset($_GET['id'])) {
    header('Location: /login.php');
    exit();
}

$company_id = $_GET['id'];
$error_message = '';
$company = null;

try {
    $stmt = $pdo->prepare("SELECT * FROM Bus_Company WHERE id = :id");
    $stmt->execute([':id' => $company_id]);
    $company = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$company) {
        header("Location: manage-companies.php?error=Firma bulunamadı.");
        exit();
    }
} catch (PDOException $e) {
    die("Veritabanı hatası: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $company_name = trim($_POST['company_name']);
    $new_logo_path = $company['logo_path']; 

    if (empty($company_name)) {
        $error_message = "Firma adı boş bırakılamaz.";
    } else {
        if (isset($_POST['delete_logo']) && $company['logo_path']) {
            if (file_exists($company['logo_path'])) {
                unlink($company['logo_path']); 
            }
            $new_logo_path = null;
        }


        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {

            $file_tmp_path = $_FILES['logo']['tmp_name'];
            $file_name = basename($_FILES['logo']['name']);
            $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            
            $new_file_name = $company_id . '.' . $file_extension;
            $dest_path = 'uploads/logos/' . $new_file_name;

            if (move_uploaded_file($file_tmp_path, $dest_path)) {

                if ($company['logo_path'] && file_exists($company['logo_path'])) {
                    unlink($company['logo_path']);
                }
                $new_logo_path = $dest_path; 
            } else {
                $error_message = "Yeni logo yüklenirken bir hata oluştu.";
            }
        }

        if (empty($error_message)) {
            try {
                $sql = "UPDATE Bus_Company SET name = :name, logo_path = :logo_path WHERE id = :id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':name' => $company_name,
                    ':logo_path' => $new_logo_path,
                    ':id' => $company_id
                ]);
                header("Location: manage-companies.php?status=updated");
                exit();
            } catch (PDOException $e) {
                $error_message = "Güncelleme sırasında bir veritabanı hatası oluştu.";
                error_log($e->getMessage());
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Firmayı Düzenle - Admin Paneli</title>
    <link href="/dist/output.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto mt-10 max-w-lg">
        <div class="bg-white p-8 rounded-lg shadow-md">
            <h1 class="text-2xl font-bold mb-6">Firmayı Düzenle</h1>
            <?php if ($error_message): ?>
                <div class="bg-red-100 p-4 rounded mb-4 text-red-700"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>
            <form action="edit-company.php?id=<?php echo $company_id; ?>" method="POST" enctype="multipart/form-data">
                <div class="mb-4">
                    <label for="company_name" class="block text-sm font-medium text-gray-700">Firma Adı</label>
                    <input type="text" name="company_name" id="company_name" value="<?php echo htmlspecialchars($company['name']); ?>" required class="mt-1 block w-full p-2 border rounded-md">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Mevcut Logo</label>
                    <?php if ($company['logo_path']): ?>
                        <img src="/<?php echo htmlspecialchars($company['logo_path']); ?>" alt="Mevcut Logo" class="mt-2 h-20 w-auto rounded-md bg-gray-100 p-1">
                        <div class="mt-2">
                            <input type="checkbox" name="delete_logo" id="delete_logo">
                            <label for="delete_logo" class="text-sm text-red-600">Logoyu Sil</label>
                        </div>
                    <?php else: ?>
                        <p class="text-sm text-gray-500 mt-2">Bu firma için bir logo yüklenmemiş.</p>
                    <?php endif; ?>
                </div>
                <div>
                    <label for="logo" class="block text-sm font-medium text-gray-700">Yeni Logo Yükle (Değiştirmek için)</label>
                    <input type="file" name="logo" id="logo" class="mt-1 block w-full text-sm ...">
                </div>
                <div class="mt-6 flex justify-between">
                    <a href="manage-companies.php" class="bg-gray-500 text-white font-bold py-2 px-4 rounded hover:bg-gray-600">İptal</a>
                    <button type="submit" class="bg-blue-500 text-white font-bold py-2 px-4 rounded hover:bg-blue-600">Güncelle</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>