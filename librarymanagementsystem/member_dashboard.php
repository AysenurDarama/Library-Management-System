
<!--<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Üye Paneli - <?php echo $_SESSION['Name']; ?></title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #e6ffe6; padding: 20px; }
        h1 { color: #009900; }
        .role-info { background: #ccffcc; padding: 10px; border-left: 5px solid #33cc33; }
    </style>
</head>
<body>
    <h1>👤 Hoş geldiniz, <?php echo $_SESSION['Name']; ?> (Üye)</h1>
    <div class="role-info">
        [cite_start]<p>Üye olarak kütüphane kaynaklarını kullanabilirsiniz[cite: 14].</p>
        <ul>
            [cite_start]<li>Mevcut kitaplara göz atabilir, ödünç alabilir veya iade edebilirsiniz[cite: 14].</li>
            [cite_start]<li>Kitaplar için inceleme (review) yazabilir[cite: 14].</li>
            [cite_start]<li>Kişisel işlem geçmişinizi görüntüleyebilirsiniz[cite: 14].</li>
        </ul>
    </div>
    <p><a href="logout.php">Çıkış Yap</a></p>
</body>
</html>-->



<?php
/*session_start();
// Rol Kontrolü: Giriş yapılmadıysa veya rol "Member" değilse, giriş sayfasına yönlendir.
if (!isset($_SESSION["loggedin"]) || $_SESSION["Role"] !== "Member") {
    header("location: login.html");
    exit;
}*/

session_start();
require_once 'db_config.php';

// Rol Kontrolü: Sadece Memberlar erişebilir (Role_name: Member)
if (!isset($_SESSION["loggedin"]) || $_SESSION["Role"] !== "Member") {
    header("location: login.html");
    exit;
}

$userID = $_SESSION["UserID"];
$currentBorrowings = [];
$transactionHistory = [];
$error = '';

try {
    // Üyenin şu an ödünç aldığı (iade tarihi NULL olan) kitapları çek
    $sql = "SELECT t.TransactionID, b.Title, t.Checkout_Date
            FROM Transactions t
            JOIN Books b ON t.BookID = b.BookID
            WHERE t.UserID = ? AND t.Return_Date IS NULL";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userID]);
    $currentBorrowings = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Üyenin son 5 işlemini çek (geçmiş)
    $sql = "SELECT b.Title, t.Checkout_Date, t.Return_Date
            FROM Transactions t
            JOIN Books b ON t.BookID = b.BookID
            WHERE t.UserID = ?
            ORDER BY t.Checkout_Date DESC
            LIMIT 5";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userID]);
    $transactionHistory = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error = "Veri çekilirken hata oluştu: " . $e->getMessage();
}

?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Üye Paneli - <?php echo $_SESSION['Name']; ?></title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #e6ffe6; padding: 20px; }
        .container { max-width: 900px; margin: auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 0 15px rgba(0,0,0,0.2); }
        h1 { color: #009900; border-bottom: 3px solid #33cc33; padding-bottom: 10px; }
        .current-borrowings, .history { margin-top: 25px; padding: 15px; border: 1px solid #ccc; border-radius: 6px; }
        h2 { color: #006600; }
        ul { list-style: none; padding: 0; }
        li { background: #ccffcc; padding: 10px; margin-bottom: 5px; border-radius: 4px; border-left: 5px solid #33cc33; }
        .menu a { display: block; margin: 10px 0; padding: 10px; background: #33cc33; color: white; text-decoration: none; border-radius: 4px; text-align: center; font-weight: bold; }
        .menu a:hover { background: #229922; }
        .not-returned { background-color: #ffe0e0; border-left-color: #ff0000; }
        .alert-danger { color: #721c24; background-color: #f8d7da; padding: 10px; border: 1px solid #f5c6cb; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>👤 Üye Paneli | <?php echo $_SESSION['Name']; ?></h1>
        <p>Kütüphane kaynaklarınıza buradan erişebilirsiniz.</p>
        
        <?php if ($error): ?>
            <p class="alert-danger"><?php echo $error; ?></p>
        <?php endif; ?>

        <h2>📖 Mevcut Ödünç Alınan Kitaplarınız (<?php echo count($currentBorrowings); ?>)</h2>
        <div class="current-borrowings">
            <?php if (count($currentBorrowings) > 0) : ?>
                <ul>
                    <?php foreach ($currentBorrowings as $item) : ?>
                        <li class="not-returned">
                            <strong><?php echo htmlspecialchars($item['Title']); ?></strong> - Ödünç Alma Tarihi: <?php echo date('d.m.Y', strtotime($item['Checkout_Date'])); ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>Şu anda ödünç aldığınız bir kitap bulunmamaktadır. Harika!</p>
            <?php endif; ?>
        </div>

        <div class="menu">
            <a href="browse_books.php">Kitaplara Göz At ve Ödünç Al (Browse books)</a>
            <a href="review_management.php">İncelemelerimi (Reviews) Yönet</a>
            <a href="fine_management.php">Para Cezalarımı (Fines) Görüntüle</a>
        </div>

        <h2>⏱️ Son İşlem Geçmişiniz</h2>
        <div class="history">
            <?php if (count($transactionHistory) > 0) : ?>
                <ul>
                    <?php foreach ($transactionHistory as $item) : ?>
                        <li>
                            <strong><?php echo htmlspecialchars($item['Title']); ?></strong> |
                            Ödünç: <?php echo date('d.m.Y', strtotime($item['Checkout_Date'])); ?> |
                            İade: <?php echo $item['Return_Date'] ? date('d.m.Y', strtotime($item['Return_Date'])) : 'HENÜZ İADE EDİLMEDİ'; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>Henüz bir işlem geçmişiniz bulunmamaktadır.</p>
            <?php endif; ?>
        </div>
        
        <p style="margin-top: 20px;"><a href="logout.php">Çıkış Yap</a></p>
    </div>
</body>
</html>