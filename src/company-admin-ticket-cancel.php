<?php
require 'config.php';


if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'company') {
    header('Location: /login.php');
    exit();
}


if (!isset($_GET['ticket_id'])) {
    header('Location: manage-tickets.php?error=Bilet ID eksik.');
    exit();
}

$ticket_id = $_GET['ticket_id'];
$company_id = $_SESSION['company_id'];

try {

    $pdo->beginTransaction();


    $check_stmt = $pdo->prepare("
        SELECT 
            T.user_id, T.total_price, T.status,
            TR.arrival_time 
        FROM Tickets T
        JOIN Trips TR ON T.trip_id = TR.id
        WHERE T.id = :ticket_id AND TR.company_id = :company_id
    ");
    $check_stmt->execute([':ticket_id' => $ticket_id, ':company_id' => $company_id]);
    $ticket = $check_stmt->fetch(PDO::FETCH_ASSOC);

    if (!$ticket) {
        throw new Exception('Yetkisiz işlem veya geçersiz bilet.');
    }

    if ($ticket['status'] !== 'active') {
        throw new Exception('Bu bilet zaten aktif değil.');
    }


    if (time() > (strtotime($ticket['arrival_time']) + 86400)) {
        throw new Exception('Seferin varış saatinin üzerinden 24 saat geçtiği için bu bilet iptal edilemez.');
    }


    $refund_amount = $ticket['total_price'];
    $user_id = $ticket['user_id'];
    $refund_stmt = $pdo->prepare("UPDATE User SET balance = balance + :refund_amount WHERE id = :user_id");
    $refund_stmt->execute([':refund_amount' => $refund_amount, ':user_id' => $user_id]);


    $update_stmt = $pdo->prepare("UPDATE Tickets SET status = 'canceled' WHERE id = :ticket_id");
    $update_stmt->execute([':ticket_id' => $ticket_id]);
    

    $pdo->commit();

    header('Location: manage-tickets.php?status=cancelled_ok');
    exit();

} catch (Exception $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    error_log("Firma tarafından bilet iptal hatası: " . "hata");

    header('Location: manage-tickets.php?error=' . "hata!");
    exit();
}
?>