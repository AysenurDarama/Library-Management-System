<?php
session_start();
require_once 'db_config.php';

$_SESSION['login_error'] = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    try {
        // SQL yerine PROSEDÜR ÇAĞIRIYORUZ
        // Madde 7 ve 9'u sağlar
        $sql = "CALL sp_UserLogin(:email)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(":email", $email, PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->rowCount() == 1) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // PDO Cursor'ı kapatmak önemlidir (Diğer prosedürlerin çalışması için)
            $stmt->closeCursor(); 

            if ($password == $user['Password']) {
                $_SESSION["loggedin"] = true;
                $_SESSION["UserID"] = $user['UserID'];
                $_SESSION["Name"] = $user['Name'];
                $_SESSION["Role"] = $user['Role_name'];

                if($user['Role_name'] == 'Admin') header("location: admin_dashboard.php");
                elseif($user['Role_name'] == 'Librarian') header("location: librarian_dashboard.php");
                else header("location: member_dashboard.php");
                exit;
            } else {
                $_SESSION['login_error'] = "Yanlış şifre.";
            }
        } else {
            $_SESSION['login_error'] = "Kullanıcı bulunamadı.";
        }
    } catch (PDOException $e) {
        $_SESSION['login_error'] = "Sistem Hatası: " . $e->getMessage();
    }
}
header("location: login.html"); // login.html (veya .php) sayfasına geri dön
exit;
?>