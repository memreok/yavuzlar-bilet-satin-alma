<?php
require 'config.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'company']) || !isset($_GET['id'])) {
    header('Location: /login.php');
    exit();
}

$coupon_id = $_GET['id'];

try {
    $stmt_check = $pdo->prepare("SELECT company_id FROM Coupons WHERE id = :id");
    $stmt_check->execute([':id' => $coupon_id]);
    $coupon = $stmt_check->fetch(PDO::FETCH_ASSOC);

    if (!$coupon) {
        throw new Exception("Kupon bulunamadı veya zaten silinmiş.");
    }
    
    if ($_SESSION['role'] === 'company' && $coupon['company_id'] !== $_SESSION['company_id']) {
        throw new Exception("Bu kuponu silme yetkiniz yok.");
    }

    $stmt_delete = $pdo->prepare("DELETE FROM Coupons WHERE id = :id");
    $stmt_delete->execute([':id' => $coupon_id]);

    header("Location: manage-coupons.php?status=deleted");
    exit();

} catch (Exception $e) {
    header("Location: manage-coupons.php?error=" . urlencode($e->getMessage()));
    exit();
}
?>