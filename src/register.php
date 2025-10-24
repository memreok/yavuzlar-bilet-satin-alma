<?php 

    require 'config.php';

    function test_input($data) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }   

    $error_message = ''; 

    if($_SERVER['REQUEST_METHOD'] == 'POST'){
        $full_name = test_input($_POST['full_name']);
        $email = test_input($_POST['email']);
        $password = test_input($_POST['password']);
    
        try {
            $stmt = $pdo->prepare("SELECT * FROM User WHERE email = :email");
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $error_message = "Bu e-posta adresi zaten kullanımda.";
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $user_id = bin2hex(random_bytes(16)); 
                $role = 'user'; 
                $created_at = date('Y-m-d H:i:s'); 

                $sql = "INSERT INTO User (id, full_name, email, role, password, created_at) VALUES (:id, :full_name, :email, :role, :password, :created_at)";
                $stmt = $pdo->prepare($sql);

                $stmt->bindParam(':id', $user_id);
                $stmt->bindParam(':full_name', $full_name);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':role', $role);
                $stmt->bindParam(':password', $hashed_password);
                $stmt->bindParam(':created_at', $created_at);
                
                if ($stmt->execute()) {
                    header("Location: login.php");
                    exit(); 
                } else {
                    $error_message = "Kayıt sırasında bir hata oluştu. Lütfen tekrar deneyin.";
                }
            }
        } catch (PDOException $e) {

            $error_message = "Beklenmeyen bir hata oluştu!";
        }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <link href="/dist/output.css" rel="stylesheet">
    <link rel="shortcut icon" href="others/Favicon.ico" type="image/x-icon">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <title>Kayıt Ol</title>
</head>
<body>
    
<div class="flex min-h-full flex-col justify-center px-6 py-12 lg:px-8">
  <div class="sm:mx-auto sm:w-full sm:max-w-sm">
    <img src="others/Logo.png" alt="OBSAP" class="mx-auto h-30 w-auto" />
    <h2 class="mt-10 text-center text-2xl/9 font-bold tracking-tight text-gray-900">Kayıt Ol</h2>
  </div>

  <div class="mt-10 sm:mx-auto sm:w-full sm:max-w-sm">
    <?php if (!empty($error_message)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <strong class="font-bold">Hata!</strong>
            <span class="block sm:inline"><?php echo htmlspecialchars($error_message); ?></span>
        </div>
    <?php endif; ?>

    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="POST" class="space-y-6">
        <div>
        <label for="full_name" class="block text-sm/6 font-medium text-gray-900">İsim ve Soyisim</label>
        <div class="mt-2">
          <input id="full_name" type="text" name="full_name" required  class="p-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" />
        </div>
      </div>
      <div>
        <label for="email" class="block text-sm/6 font-medium text-gray-900">E-Posta adresi</label>
        <div class="mt-2">
          <input id="email" type="email" name="email" required autocomplete="email" class="p-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" />
        </div>
      </div>

      <div>
        <div class="flex items-center justify-between">
          <label for="password" class="block text-sm/6 font-medium text-gray-900">Şifre</label>
        </div>
        <div class="mt-2 relative">
          <input id="password" type="password" name="password" required autocomplete="current-password" class="p-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" />
          <i class="fas fa-eye absolute top-1/2 right-3 -translate-y-1/2 cursor-pointer text-gray-400" id="togglePassword"></i>
        </div>
      </div>

      <div>
        <button type="submit" class="flex w-full justify-center rounded-md bg-indigo-600 px-3 py-1.5 text-sm/6 font-semibold text-white shadow-xs hover:bg-indigo-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">Kayıt Ol</button>
      </div>
    </form>

    <p class="mt-10 text-center text-sm/6 text-gray-500">
      Hesabınız var mı?
      <a href="login.php" class="font-semibold text-indigo-600 hover:text-indigo-500">Giriş Yap</a>
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