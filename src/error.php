<?php

$statusCode = $_SERVER['REDIRECT_STATUS'] ?? 500;


$title = 'Bir Sorun Oluştu';
$message = 'Görünüşe göre bir aksaklık oldu. Lütfen daha sonra tekrar deneyin.';
$displayCode = 'HATA';


if ($statusCode == 404) {
    $title = 'Sayfa Bulunamadı';
    $message = 'Aradığınız sayfa bulunamadı.';
    $displayCode = '404';
}


http_response_code($statusCode);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title); ?></title>
    <link href="/dist/output.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen p-4">

    <div class="bg-white rounded-2xl shadow-xl flex flex-col sm:flex-row max-w-2xl w-full overflow-hidden">
        
        <div class="bg-indigo-600 text-white p-8 flex flex-col items-center justify-center space-y-4 sm:w-1/3">
            <i class="fas fa-bus-alt text-6xl"></i>
            <h1 class="text-7xl font-extrabold tracking-wider"><?php echo htmlspecialchars($displayCode); ?></h1>
        </div>

        <div class="p-8 flex flex-col justify-center items-center sm:items-start text-center sm:text-left flex-grow">
            <h2 class="text-3xl font-bold text-gray-800 mb-2"><?php echo htmlspecialchars($title); ?></h2>
            <p class="text-gray-600 mb-6"><?php echo htmlspecialchars($message); ?></p>
            <a href="/" class="inline-block px-6 py-3 bg-indigo-600 text-white font-semibold rounded-lg shadow-md hover:bg-indigo-700 transition-all duration-300 transform hover:scale-105">
                <i class="fas fa-ticket-alt mr-2"></i> Ana Sayfaya Dön
            </a>
        </div>

    </div>

</body>
</html>