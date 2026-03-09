<?php
session_start();
require_once 'db_config.php';

$hataMesaji = '';
$basariMesaji = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    // Member Rol ID'sini bul (Basit sorgu olduğu için inline kalabilir veya prosedüre alınabilir)
    // Ancak proje şartları için burayı prosedürle yapmak daha iyi.
    // Şimdilik varsayılan 3 (Member) kabul edelim veya SQL'den çekelim.
    $roleID = 3; 

    // E-posta kontrolü için de prosedür kullanılabilir ama
    // doğrudan kayıt prosedürünü deneyelim, hata verirse e-posta var demektir (Unique key varsa).
    
    try {
        // PROSEDÜR ÇAĞRISI: Kayıt Ekleme
        $sql = "CALL sp_RegisterUser(:name, :email, :password, :role)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(":name", $name);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":password", $password);
        $stmt->bindParam(":role", $roleID);

        $stmt->execute();
        $stmt->closeCursor();

        $basariMesaji = "Kayıt başarılı! Giriş sayfasına yönlendiriliyorsunuz...";
        header("refresh:3; url=login.html");

    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
            $hataMesaji = "Bu e-posta adresi zaten kullanılıyor.";
        } else {
            $hataMesaji = "Kayıt hatası: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Kayıt Sonucu</title>
</head>
<body>
    <div style="text-align:center; margin-top:50px;">
        <?php if ($basariMesaji) echo "<h2 style='color:green'>$basariMesaji</h2>"; ?>
        <?php if ($hataMesaji) echo "<h2 style='color:red'>$hataMesaji</h2> <a href='register.html'>Geri Dön</a>"; ?>
    </div>
</body>
</html>