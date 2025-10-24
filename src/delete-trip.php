<?php
require 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'company' || !isset($_GET['id'])) {
    header('Location: /login.php');
    exit();
}

$trip_id = $_GET['id'];
$company_id = $_SESSION['company_id'];

try {
    $stmt = $pdo->prepare("DELETE FROM Trips WHERE id = :id AND company_id = :company_id");
    $stmt->execute([':id' => $trip_id, ':company_id' => $company_id]);


    if ($stmt->rowCount() > 0) {
        header("Location: /company-admin-dashboard.php?status=deleted");
    } else {
        throw new Exception("Sefer bulunamadı veya silme yetkiniz yok.");
    }
    exit();

} catch (Exception $e) {
    header("Location: /company-admin-dashboard.php?error=" . urlencode($e->getMessage()));
    exit();
}
?>