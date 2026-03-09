<?php
session_start();
require_once 'db_config.php';

// Rol Kontrolü: Sadece Üyeler erişebilir
if (!isset($_SESSION["loggedin"]) || $_SESSION["Role"] !== "Member") {
    header("location: login.html");
    exit;
}

$hataMesaji = '';
$userID = $_SESSION['UserID'];
$fines = [];
$totalUnpaid = 0;

try {
    // Üyenin tüm para cezalarını çekme
    $sql = "SELECT FineID, FineAmount, PaidStatus
            FROM Fines
            WHERE UserID = ?
            ORDER BY PaidStatus DESC, FineID DESC"; // Ödenmemişler en üstte
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userID]);
    $fines = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Toplam ödenmemiş cezayı hesaplama
    foreach ($fines as $fine) {
        if ($fine['PaidStatus'] == 'Unpaid') {
            $totalUnpaid += $fine['FineAmount'];
        }
    }

} catch (PDOException $e) {
    $hataMesaji = "Para cezaları çekilirken hata oluştu: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Para Cezaları Yönetimi</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #e6ffe6; padding: 20px; }
        .container { max-width: 800px; margin: auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h1 { color: #009900; border-bottom: 2px solid #33cc33; padding-bottom: 10px; }
        /* Önceki stiller */
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background-color: #33cc33; color: white; }
        .unpaid { background-color: #ffcccc; font-weight: bold; color: #cc0000; }
        .paid { background-color: #ccffcc; color: #006600; }
        .total-unpaid { margin-top: 20px; padding: 15px; background: #fff2cc; border: 1px solid #ffaa00; font-size: 1.2em; font-weight: bold; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>💰 Para Cezalarınız (Fines)</h1>
        
        <?php if ($hataMesaji) : ?><div class="alert alert-danger"><?php echo $hataMesaji; ?></div><?php endif; ?>
        
        <p><a href="member_dashboard.php">Panele Geri Dön</a></p>

        <?php if ($totalUnpaid > 0): ?>
            <div class="total-unpaid">
                Toplam Ödenmemiş Ceza Miktarı: <?php echo number_format($totalUnpaid, 2) . ' TL'; ?>
            </div>
        <?php endif; ?>

        <h2>Tüm Cezaların Detayları</h2>
        <?php if (!empty($fines)): ?>
        <table>
            <thead>
                <tr>
                    <th>Ceza ID</th>
                    <th>Miktar</th>
                    <th>Durum</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($fines as $fine) : ?>
                    <tr class="<?php echo strtolower($fine['PaidStatus']); ?>">
                        <td><?php echo htmlspecialchars($fine['FineID']); ?></td>
                        <td><?php echo number_format($fine['FineAmount'], 2) . ' TL'; ?></td>
                        <td><?php echo ($fine['PaidStatus'] == 'Paid' ? 'Ödendi' : 'Ödenmedi'); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
            <p>Size ait kayıtlı bir para cezası bulunmamaktadır.</p>
        <?php endif; ?>
    </div>
</body>
</html>