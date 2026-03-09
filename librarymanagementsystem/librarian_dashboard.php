<?php
session_start();
// Rol Kontrolü: Giriş yapılmadıysa veya rol "Librarian" değilse, giriş sayfasına yönlendir.
if (!isset($_SESSION["loggedin"]) || $_SESSION["Role"] !== "Librarian") {
    header("location: login.html");
    exit;
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Kütüphaneci Paneli - <?php echo $_SESSION['Name']; ?></title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #fff9e6; padding: 20px; }
        h1 { color: #cc8400; }
        .role-info { background: #fff2cc; padding: 10px; border-left: 5px solid #ffaa00; }
    </style>
</head>
<body>
    <h1>📚 Hoş geldiniz, <?php echo $_SESSION['Name']; ?> (Kütüphaneci)</h1>
    <div class="role-info">
        <!--[cite_start]<p>Kütüphaneci olarak **günlük operasyonlardan** sorumlusunuz[cite: 11].</p>-->
        <ul>
            <!--[cite_start]<li>Yeni kitap ekleyebilir, mevcut kitap bilgilerini güncelleyebilirsiniz[cite: 12].</li>
            [cite_start]<li>Kitap ödünç alma ve iade işlemlerini yönetebilirsiniz[cite: 12].</li>
            [cite_start]<li>Üyelere işlemlerde yardımcı olabilir ve gecikmiş kayıtları görüntüleyebilirsiniz[cite: 13].</li>-->

            <li>Yeni kitap ekleyebilir, mevcut kitap bilgilerini güncelleyebilirsiniz.</li>
            <li><a href="return_book.php">Kitap İade İşlemleri </a></li>
            <li><a href="book_management.php">Kitap Yönetimi Sayfasına Git</a></li>
        </ul>
    </div>
    <p><a href="logout.php">Çıkış Yap</a></p>
</body>
</html>