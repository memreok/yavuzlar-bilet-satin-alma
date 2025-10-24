<?php
require 'config.php';

require 'vendor/dompdf/autoload.inc.php';

use Dompdf\Dompdf;
use Dompdf\Options;

if (!isset($_SESSION['user_id'])) {
    exit();
}
if (!isset($_GET['id'])) {
    die("Geçersiz istek: Bilet ID'si belirtilmemiş.");
}

$ticket_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

try {
    $stmt = $pdo->prepare(
        "SELECT
            T.id as ticket_id, U.full_name as passenger_name, TR.departure_city,
            TR.destination_city, TR.departure_time, TR.arrival_time,
            BC.name as company_name, BS.seat_number, T.total_price
        FROM Tickets AS T
        JOIN User AS U ON T.user_id = U.id
        JOIN Trips AS TR ON T.trip_id = TR.id
        JOIN Bus_Company AS BC ON TR.company_id = BC.id
        JOIN Booked_Seats AS BS ON T.id = BS.ticket_id
        WHERE T.id = :ticket_id AND T.user_id = :user_id"
    );
    $stmt->execute([':ticket_id' => $ticket_id, ':user_id' => $user_id]);
    $ticket = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$ticket) {
        die("Bilet bulunamadı veya bu bileti görüntüleme yetkiniz yok.");
    }


    $html = '
    <!DOCTYPE html>
    <html lang="tr">
    <head>
        <meta charset="UTF-8">
        <title>E-Bilet</title>
        <style>
            body { font-family: DejaVu Sans, sans-serif; }
            .container { border: 2px solid #333; padding: 20px; width: 700px; margin: auto; }
            .header { text-align: center; border-bottom: 1px solid #ccc; padding-bottom: 10px; margin-bottom: 20px; }
            .header h1 { margin: 0; color: #005a9c; }
            .details-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
            .detail-item { padding: 10px; background-color: #f4f4f4; border-left: 4px solid #005a9c; }
            .detail-item strong { display: block; color: #555; font-size: 12px; margin-bottom: 5px; }
            .footer { text-align: center; margin-top: 30px; font-size: 10px; color: #777; }
            .trip-info { font-size: 24px; font-weight: bold; text-align: center; margin: 20px 0; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>OBSAP E-BİLET</h1>
            </div>
            
            <div class="details-grid">
                <div class="detail-item">
                    <strong>YOLCU ADI SOYADI</strong>
                    ' . htmlspecialchars($ticket['passenger_name']) . '
                </div>
                <div class="detail-item">
                    <strong>OTOBÜS FİRMASI</strong>
                    ' . htmlspecialchars($ticket['company_name']) . '
                </div>
                <div class="detail-item">
                    <strong>KOLTUK NUMARASI</strong>
                    <span style="font-size: 20px; font-weight: bold;">' . htmlspecialchars($ticket['seat_number']) . '</span>
                </div>
                <div class="detail-item">
                    <strong>ÖDENEN ÜCRET</strong>
                    <span style="font-size: 20px; font-weight: bold;">' . htmlspecialchars($ticket['total_price']) . ' TL</span>
                </div>
            </div>

            <div class="trip-info">
                ' . htmlspecialchars(ucfirst($ticket['departure_city'])) . ' &rarr; ' . htmlspecialchars(ucfirst($ticket['destination_city'])) . '
            </div>

            <div class="details-grid">
                <div class="detail-item">
                    <strong>KALKIŞ ZAMANI</strong>
                    ' . date('d.m.Y H:i', strtotime($ticket['departure_time'])) . '
                </div>
                <div class="detail-item">
                    <strong>TAHMİNİ VARIŞ ZAMANI</strong>
                    ' . date('d.m.Y H:i', strtotime($ticket['arrival_time'])) . '
                </div>
            </div>

            <div class="footer">
                İyi yolculuklar dileriz! <br>
                Bilet ID: ' . htmlspecialchars($ticket['ticket_id']) . '
            </div>
        </div>
    </body>
    </html>';
    

    $options = new Options();
    $options->set('isHtml5ParserEnabled', true);
    $options->set('defaultFont', 'DejaVu Sans'); 
    
    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);
    

    $dompdf->setPaper('A4', 'portrait');
    

    $dompdf->render();
    

    $dompdf->stream('OBSAP-Bilet-'.$ticket_id.'.pdf', ["Attachment" => true]);

} catch (Exception $e) {
    die("PDF oluşturulurken bir hata oluştu: " . $e->getMessage());
}
?>