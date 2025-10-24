<?php
require 'config.php';


if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin' || !isset($_GET['id'])) {
    header('Location: /login.php');
    exit();
}

$company_id = $_GET['id'];

try {

    $stmt = $pdo->prepare("SELECT logo_path FROM Bus_Company WHERE id = :id");
    $stmt->execute([':id' => $company_id]);
    $company = $stmt->fetch(PDO::FETCH_ASSOC);


    $stmt_delete = $pdo->prepare("DELETE FROM Bus_Company WHERE id = :id");
    $stmt_delete->execute([':id' => $company_id]);

    if ($stmt_delete->rowCount() > 0) {

        if ($company && !empty($company['logo_path']) && file_exists($company['logo_path'])) {
            unlink($company['logo_path']);
        }
        header("Location: manage-companies.php?status=deleted");
    } else {
        throw new Exception("Firma bulunamadı veya silinemedi.");
    }
    exit();

} catch (PDOException $e) {

    if ($e->getCode() == 23000 || $e->getCode() == 19) { 
        header("Location: manage-companies.php?error=" . urlencode("Bu firmaya ait seferler veya yöneticiler olduğu için silinemez."));
    } else {
        header("Location: manage-companies.php?error=" . urlencode("Bir hata oluştu."));
    }
    exit();
}
?>