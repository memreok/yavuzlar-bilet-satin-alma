<?php 

    require 'config.php';

    $error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = htmlspecialchars(trim($_POST['email']));
    $password = trim($_POST['password']);

    if (!empty($email) && !empty($password)) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM User WHERE email = :email");
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                session_regenerate_id(true);

                $_SESSION['user_id'] = $user['id'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['role'] = $user['role']; 
                $_SESSION['logged_in'] = true;
                $_SESSION['balance'] = $user['balance'];
                switch ($user['role']) {
                    case 'admin':
                        header("Location: admin-dashboard.php");
                        break;
                    case 'company':
                        $_SESSION['company_id'] = $user['company_id']; 
                        header("Location: company-admin-dashboard.php");
                        break;
                    default: 
                        header("Location: index.php");
                        break;
                }
                exit(); 

            } else {
                
                $error_message = "E-posta veya şifre hatalı.";
            }

        } catch (PDOException $e) {
            $error_message = "Beklenmeyen bir hata oluştu. Lütfen daha sonra tekrar deneyin.";
        }
    } else {
        $error_message = "Lütfen tüm alanları doldurun.";
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <link href="/dist/output.css" rel="stylesheet">
    <link rel="shortcut icon" href="/others/Favicon.ico" type="image/x-icon">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <title>Giriş Yap</title>
</head>
<body>
    
<div class="flex min-h-full flex-col justify-center px-6 py-12 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-sm">
    <img src="others/Logo.png" alt="OBSAP" class="mx-auto h-30 w-auto" />
  </div>
  <div class="sm:mx-auto sm:w-full sm:max-w-sm">
    <h2 class="mt-10 text-center text-2xl/9 font-bold tracking-tight text-gray-900">Giriş Yapın</h2>
  </div>

  <div class="mt-10 sm:mx-auto sm:w-full sm:max-w-sm">
    <?php if (!empty($error_message)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline"><?php echo htmlspecialchars($error_message); ?></span>
        </div>
    <?php endif; ?>

    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="POST" class="space-y-6">
      <div>
        <label for="email" class="block text-sm/6 font-medium text-gray-900">E-Posta adresi</label>
        <div class="mt-2">
          <input id="email" type="email" name="email" required autocomplete="email" class="p-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" />
        </div>
      </div>

      <div>
        <div class="flex items-center justify-between">
          <label for="password" class="block text-sm/6 font-medium text-gray-900">Şifre</label>
          <div class="text-sm">
            <a href="forget-password.php" class="font-semibold text-indigo-600 hover:text-indigo-500">Şifremi Unuttum?</a>
          </div>
        </div>
        <div class="mt-2 relative">
          <input id="password" type="password" name="password" required autocomplete="current-password" class="p-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" />
          <i class="fas fa-eye absolute top-1/2 right-3 -translate-y-1/2 cursor-pointer text-gray-400" id="togglePassword"></i>
        </div>
      </div>

      <div>
        <button type="submit" class="flex w-full justify-center rounded-md bg-indigo-600 px-3 py-1.5 text-sm/6 font-semibold text-white shadow-xs hover:bg-indigo-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">Giriş Yap</button>
      </div>
    </form>

    <p class="mt-10 text-center text-sm/6 text-gray-500">
      Hesabınız yok mu?
      <a href="register.php" class="font-semibold text-indigo-600 hover:text-indigo-500">Kayıt Ol</a>
    </p>
  </div>
</div>

<script>
    const togglePassword = document.querySelector('#togglePassword');
    const password = document.querySelector('#password');

    if (togglePassword && password) {
        togglePassword.addEventListener('click', function () {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            
            this.classList.toggle('fa-eye-slash');
            this.classList.toggle('fa-eye'); 
        });
    }
</script>

</body>
</html>