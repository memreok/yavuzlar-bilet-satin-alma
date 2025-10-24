<?php
require 'config.php';

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($_SESSION['user_id']) || !$input || !isset($input['coupon_code'])) {
    echo json_encode(['success' => false, 'message' => 'Geçersiz istek.']);
    exit();
}

$coupon_code = strtoupper(trim($input['coupon_code']));
$user_id = $_SESSION['user_id'];
$trip_id = $input['trip_id'];
$response = ['success' => false, 'message' => ''];

try {
    $stmt = $pdo->prepare("SELECT * FROM Coupons WHERE code = :code");
    $stmt->execute([':code' => $coupon_code]);
    $coupon = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$coupon) {
        $response['message'] = 'Geçersiz kupon kodu.';
        echo json_encode($response);
        exit();
    }
    $stmt_trip = $pdo->prepare("SELECT company_id FROM Trips WHERE id = :trip_id");
    $stmt_trip->execute([':trip_id' => $trip_id]);
    $trip_company_id = $stmt_trip->fetchColumn();

    if ($coupon['company_id'] !== null && $coupon['company_id'] != $trip_company_id) {
        $response['message'] = 'Bu kupon kodu bu sefer için geçerli değildir.';
        echo json_encode($response);
        exit();
    }

    $current_date = date('Y-m-d');
    if ($coupon['expire_date'] < $current_date) {
        $response['message'] = 'Bu kuponun süresi dolmuş.';
        echo json_encode($response);
        exit();
    }

    $stmt_usage = $pdo->prepare("SELECT COUNT(*) FROM User_Coupons WHERE coupon_id = :coupon_id");
    $stmt_usage->execute([':coupon_id' => $coupon['id']]);
    $usage_count = $stmt_usage->fetchColumn();

    if ($usage_count >= $coupon['usage_limit']) {
        $response['message'] = 'Bu kupon kullanım limitine ulaştı.';
        echo json_encode($response);
        exit();
    }
    
    $stmt_user_usage = $pdo->prepare("SELECT COUNT(*) FROM User_Coupons WHERE coupon_id = :coupon_id AND user_id = :user_id");
    $stmt_user_usage->execute([':coupon_id' => $coupon['id'], ':user_id' => $user_id]);
    if ($stmt_user_usage->fetchColumn() > 0) {
        $response['message'] = 'Bu kuponu daha önce kullandınız.';
        echo json_encode($response);
        exit();
    }

    $response = [
        'success' => true,
        'message' => 'Kupon başarıyla uygulandı!',
        'discount' => $coupon['discount']
    ];
    echo json_encode($response);

} catch (PDOException $e) {
    $response['message'] = 'Veritabanı hatası: ' . $e->getMessage();
    echo json_encode($response);
}
?>

