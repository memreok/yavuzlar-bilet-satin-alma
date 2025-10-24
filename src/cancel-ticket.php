<?php
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit();
}
if (!isset($_GET['id'])) {
    header('Location: /my-tickets.php?error=Geçersiz istek.');
    exit();
}

$ticket_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

try {
    $pdo->beginTransaction();
    $stmt = $pdo->prepare(
        "SELECT T.total_price, TR.departure_time 
         FROM Tickets AS T
         JOIN Trips AS TR ON T.trip_id = TR.id
         WHERE T.id = :ticket_id AND T.user_id = :user_id AND T.status = 'active'"
    );
    $stmt->execute([':ticket_id' => $ticket_id, ':user_id' => $user_id]);
    $ticket = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$ticket) {

        throw new Exception("İptal edilecek bilet bulunamadı veya bu işlem için yetkiniz yok.");
    }


    $departure_timestamp = strtotime($ticket['departure_time']);
    if (($departure_timestamp - time()) <= 3600) { 
        throw new Exception("Sefer saatine 1 saatten az kaldığı için bilet iptal edilemez.");
    }

    $ticket_price = $ticket['total_price'];

    $stmt = $pdo->prepare("UPDATE Tickets SET status = 'canceled' WHERE id = :ticket_id");
    $stmt->execute([':ticket_id' => $ticket_id]);


    $stmt = $pdo->prepare("DELETE FROM Booked_Seats WHERE ticket_id = :ticket_id");
    $stmt->execute([':ticket_id' => $ticket_id]);

    $stmt = $pdo->prepare("UPDATE User SET balance = balance + :price WHERE id = :user_id");
    $stmt->execute([':price' => $ticket_price, ':user_id' => $user_id]);


    $pdo->commit();


    header('Location: /my-tickets.php?status=cancel_success');
    exit();

} catch (Exception $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    header('Location: /my-tickets.php?error=' ."bir hata oluştu!");
    exit();
}
?>