<?php
session_start();
require_once 'db_config.php';

// Rol Kontrolü: Sadece Adminler erişebilir
if (!isset($_SESSION["loggedin"]) || $_SESSION["Role"] !== "Admin") {
    header("location: login.html");
    exit;
}

$reportError = '';
$mostBorrowed = [];
$unpaidFines = [];

try {
    // RAPOR 1: En Çok Ödünç Alınan Kitaplar (Performans/Kullanım Raporu)
    $sql_borrowed = "
        SELECT 
            b.Title, COUNT(t.TransactionID) AS BorrowCount
        FROM Transactions t
        JOIN Books b ON t.BookID = b.BookID
        GROUP BY b.BookID, b.Title
        ORDER BY BorrowCount DESC
        LIMIT 10
    ";
    $mostBorrowed = $pdo->query($sql_borrowed)->fetchAll(PDO::FETCH_ASSOC);

    // RAPOR 2: Ödenmemiş Para Cezaları (Aktivite Raporu)
    $sql_fines = "
        SELECT 
            u.Name, u.Email, f.FineAmount
        FROM Fines f
        JOIN Users u ON f.UserID = u.UserID
        WHERE f.PaidStatus = 'Unpaid'
        ORDER BY f.FineAmount DESC
    ";
    $unpaidFines = $pdo->query($sql_fines)->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $reportError = "Raporlar çekilirken bir hata oluştu: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Sistem Raporları - Admin</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #e6f7ff; padding: 20px; }
        .container { max-width: 1000px; margin: auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h1 { color: #004d99; border-bottom: 2px solid #004d99; padding-bottom: 10px; }
        h2 { color: #333; margin-top: 30px; border-left: 5px solid #007bff; padding-left: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background-color: #007bff; color: white; }
        .alert-danger { color: #721c24; background-color: #f8d7da; padding: 10px; border: 1px solid #f5c6cb; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>📊 Sistem Aktivite ve Performans Raporları (Admin)</h1>
        <p>Sistem genelindeki performans ve kullanım verilerini buradan gözlemleyebilirsiniz. <a href="admin_dashboard.php">Panele Geri Dön</a></p>

        <?php if ($reportError): ?>
            <div class="alert-danger"><?php echo $reportError; ?></div>
        <?php endif; ?>

        <h2>En Çok Ödünç Alınan 10 Kitap (Kullanım Performansı)</h2>
        <?php if (!empty($mostBorrowed)): ?>
        <table>
            <thead>
                <tr>
                    <th>Kitap Başlığı</th>
                    <th>Ödünç Alma Sayısı</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($mostBorrowed as $item): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['Title']); ?></td>
                    <td><?php echo htmlspecialchars($item['BorrowCount']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
            <p>Ödünç alma işlemi bulunmamaktadır.</p>
        <?php endif; ?>

        <h2>Ödenmemiş Para Cezaları Listesi</h2>
        <?php if (!empty($unpaidFines)): ?>
        <table>
            <thead>
                <tr>
                    <th>Kullanıcı Adı</th>
                    <th>E-posta</th>
                    <th>Ceza Miktarı</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($unpaidFines as $item): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['Name']); ?></td>
                    <td><?php echo htmlspecialchars($item['Email']); ?></td>
                    <td><?php echo number_format($item['FineAmount'], 2) . ' TL'; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
            <p>Sistemde ödenmemiş para cezası bulunmamaktadır. Tüm cezalar ödenmiş.</p>
        <?php endif; ?>
    </div>
</body>
</html>