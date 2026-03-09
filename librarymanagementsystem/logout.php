<?php
session_start();

// Oturumdaki tüm değişkenleri kaldır
$_SESSION = array();

// Oturum çerezini sil (varsa)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Oturumu sonlandır
session_destroy();

// Giriş sayfasına yönlendir
header("location: login.html");
exit;
?>