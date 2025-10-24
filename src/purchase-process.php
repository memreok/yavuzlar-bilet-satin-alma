<?php
require 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: /index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['trip_id']) || !isset($_POST['seat_number'])) {
    $redirect_url = isset($_POST['trip_id']) ? '/buy-ticket.php?trip_id='.$_POST['trip_id'] : '/index.php';
    header('Location: ' . $redirect_url . '?error=GecersizIstek');
    exit();
}

$trip_id = $_POST['trip_id'];
$seat_number = $_POST['seat_number'];
$user_id = $_SESSION['user_id'];
$coupon_code = isset($_POST['coupon_code']) ? strtoupper(trim($_POST['coupon_code'])) : null;

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("SELECT price, company_id FROM Trips WHERE id = :trip_id");
    $stmt->execute([':trip_id' => $trip_id]);
    $trip = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("SELECT balance FROM User WHERE id = :user_id");
    $stmt->execute([':user_id' => $user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$trip || !$user) {
        throw new Exception("Sefer veya kullanıcı bulunamadı.");
    }

    $final_price = $trip['price'];
    $validated_coupon = null; 

    if (!empty($coupon_code)) {
        $stmt = $pdo->prepare(
            "SELECT * FROM Coupons WHERE code = :code AND (company_id IS NULL OR company_id = :company_id)"
        );
        $stmt->execute([':code' => $coupon_code, ':company_id' => $trip['company_id']]);
        $coupon = $stmt->fetch(PDO::FETCH_ASSOC);

        $error_msg = ''; 
        if (!$coupon) {
            $error_msg = "Geçersiz veya bu sefere uygun olmayan bir kupon kodu girdiniz.";
        } else {

            if ($coupon['expire_date'] < date('Y-m-d')) {
                $error_msg = "Bu kuponun süresi dolmuş.";
            } else {

                $stmt_usage = $pdo->prepare("SELECT COUNT(*) FROM User_Coupons WHERE coupon_id = :coupon_id");
                $stmt_usage->execute([':coupon_id' => $coupon['id']]);
                if ($stmt_usage->fetchColumn() >= $coupon['usage_limit']) {
                    $error_msg = "Bu kupon kullanım limitine ulaştı.";
                } else {

                    $stmt_user_usage = $pdo->prepare("SELECT COUNT(*) FROM User_Coupons WHERE coupon_id = :coupon_id AND user_id = :user_id");
                    $stmt_user_usage->execute([':coupon_id' => $coupon['id'], ':user_id' => $user_id]);
                    if ($stmt_user_usage->fetchColumn() > 0) {
                        $error_msg = "Bu kuponu daha önce kullandınız.";
                    }
                }
            }
        }
        

        if (!empty($error_msg)) {
            throw new Exception($error_msg);
        }


        $discount_amount = $final_price * ($coupon['discount'] / 100);
        $final_price = $final_price - $discount_amount;
        $validated_coupon = $coupon;
    }


    if ($user['balance'] < $final_price) {
        throw new Exception("Yetersiz bakiye.");
    }
    
    $stmt_check_seat = $pdo->prepare("SELECT COUNT(*) FROM Booked_Seats bs JOIN Tickets t ON bs.ticket_id = t.id WHERE t.trip_id = :trip_id AND bs.seat_number = :seat_number AND t.status = 'active'");
    $stmt_check_seat->execute([':trip_id' => $trip_id, ':seat_number' => $seat_number]);
    if ($stmt_check_seat->fetchColumn() > 0) {
        throw new Exception("Bu koltuk siz işlemi tamamlarken alındı. Lütfen başka bir koltuk seçin.");
    }

    $ticket_id = bin2hex(random_bytes(16));
    $created_at = date('Y-m-d H:i:s');

    $stmt = $pdo->prepare("INSERT INTO Tickets (id, user_id, trip_id, total_price, status, created_at) VALUES (:id, :user_id, :trip_id, :total_price, 'active', :created_at)");
    $stmt->execute([':id' => $ticket_id, ':user_id' => $user_id, ':trip_id' => $trip_id, ':total_price' => $final_price, ':created_at' => $created_at]);

    $booked_seat_id = bin2hex(random_bytes(16));
    $stmt = $pdo->prepare("INSERT INTO Booked_Seats (id, ticket_id, seat_number, created_at) VALUES (:id, :ticket_id, :seat_number, :created_at)");
    $stmt->execute([':id' => $booked_seat_id, ':ticket_id' => $ticket_id, ':seat_number' => $seat_number, ':created_at' => $created_at]);

    $new_balance = $user['balance'] - $final_price;
    $stmt = $pdo->prepare("UPDATE User SET balance = :new_balance WHERE id = :user_id");
    $stmt->execute([':new_balance' => $new_balance, ':user_id' => $user_id]);
    
    if ($validated_coupon) {
        $user_coupon_id = bin2hex(random_bytes(16));
        $stmt = $pdo->prepare("INSERT INTO User_Coupons (id, coupon_id, user_id, created_at) VALUES (:id, :coupon_id, :user_id, :created_at)");
        $stmt->execute([':id' => $user_coupon_id, ':coupon_id' => $validated_coupon['id'], ':user_id' => $user_id, ':created_at' => $created_at]);
    }

    $pdo->commit();


    header('Location: /my-tickets.php?status=success');
    exit();

} catch (Exception $e) {

    

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    header('Location: /buy-ticket.php?trip_id='.$trip_id.'&seat_number='.$seat_number.'&error=' . urlencode($e->getMessage()));
    exit();
}
?>