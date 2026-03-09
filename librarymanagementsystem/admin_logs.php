<?php
session_start();
require_once 'db_config.php';

// Sadece Admin erişebilir
if (!isset($_SESSION["loggedin"]) || $_SESSION["Role"] !== "Admin") {
    header("location: login.php");
    exit;
}

// Logları çek
$logs = $pdo->query("SELECT * FROM DeletedUsersLog ORDER BY DeletedAt DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Silinen Kullanıcı Logları</title>
    <style>
        body { font-family: sans-serif; padding: 20px; background: #e6f7ff; }
        .container { max-width: 800px; margin: auto; background: white; padding: 20px; border-radius: 8px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background: #6c757d; color: white; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🗑️ Silinen Kullanıcı Geçmişi </h1>
        <p>Bu kayıtlar, bir kullanıcı silindiğinde <b>LogDeletedUser</b> Trigger'ı tarafından otomatik oluşturulmuştur.</p>
        <p><a href="admin_dashboard.php">Panele Dön</a></p>

        <table>
            <tr>
                <th>Orijinal ID</th>
                <th>Ad Soyad</th>
                <th>E-posta</th>
                <th>Silinme Zamanı</th>
            </tr>
            <?php foreach ($logs as $log): ?>
            <tr>
                <td><?php echo $log['OriginalUserID']; ?></td>
                <td><?php echo htmlspecialchars($log['UserName']); ?></td>
                <td><?php echo htmlspecialchars($log['UserEmail']); ?></td>
                <td><?php echo $log['DeletedAt']; ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
        <?php if(empty($logs)) echo "<p>Henüz silinen kullanıcı yok.</p>"; ?>
    </div>
</body>
</html>