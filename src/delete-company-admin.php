<?php
require 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin' || !isset($_GET['id'])) {
    header('Location: /login.php');
    exit();
}

$admin_id = $_GET['id'];

try {
    $stmt = $pdo->prepare("DELETE FROM User WHERE id = :id AND role = 'company'");
    $stmt->execute([':id' => $admin_id]);

    if ($stmt->rowCount() > 0) {
        header("Location: manage-company-admins.php?status=deleted");
    } else {
        throw new Exception("Firma admini bulunamadı veya zaten silinmiş.");
    }
    exit();

} catch (Exception $e) {
    header("Location: manage-company-admins.php?error=" . urlencode($e->getMessage()));
    exit();
}
?>