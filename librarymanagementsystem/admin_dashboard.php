

<!--<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Yönetici Paneli - <?php echo $_SESSION['Name']; ?></title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #e6f7ff; padding: 20px; }
        h1 { color: #004d99; }
        .role-info { background: #ccf2ff; padding: 10px; border-left: 5px solid #007bff; }
    </style>
</head>
<body>
    <h1>🌟 Hoş geldiniz, <?php echo $_SESSION['Name']; ?> (Yönetici)</h1>
    <div class="role-info">
        [cite_start]<p>Yönetici olarak sistem üzerinde **tam kontrole** sahipsiniz[cite: 10].</p>
        <ul>
            [cite_start]<li>Tüm kullanıcıları yönetebilirsiniz[cite: 10].</li>
            [cite_start]<li>Librarian hesaplarını onaylayabilirsiniz[cite: 10].</li>
            [cite_start]<li>Sistem aktivitesini izleyebilir ve raporlar oluşturabilirsiniz[cite: 10].</li>
        </ul>
    </div>
    <p><a href="logout.php">Çıkış Yap</a></p>
</body>
</html>-->

<?php
/*session_start();
// Rol Kontrolü: Giriş yapılmadıysa veya rol "Admin" değilse, giriş sayfasına yönlendir.
if (!isset($_SESSION["loggedin"]) || $_SESSION["Role"] !== "Admin") {
    header("location: login.html");
    exit;
}*/

session_start();
require_once 'db_config.php';

// Rol Kontrolü: Sadece Adminler erişebilir (Role_name: Admin)
if (!isset($_SESSION["loggedin"]) || $_SESSION["Role"] !== "Admin") {
    header("location: login.html");
    exit;
}

$userStats = [];
$totalUsers = 0;
$error = '';

try {
    // Toplam kullanıcı ve role göre dağılımı çekme
    $sql = "SELECT r.Role_name, COUNT(u.UserID) AS user_count 
            FROM Users u
            JOIN Roles r ON u.RoleID = r.RoleID
            GROUP BY r.Role_name";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $statsArray = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // İstatistikleri düzenleme ve toplamı hesaplama
    foreach ($statsArray as $stat) {
        $userStats[$stat['Role_name']] = $stat['user_count'];
        $totalUsers += $stat['user_count'];
    }

} catch (PDOException $e) {
    $error = "Kullanıcı istatistikleri çekilirken hata: " . $e->getMessage();
}

?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Yönetici Paneli - <?php echo $_SESSION['Name']; ?></title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #e6f7ff; padding: 20px; }
        .container { max-width: 900px; margin: auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 0 15px rgba(0,0,0,0.2); }
        h1 { color: #004d99; border-bottom: 3px solid #007bff; padding-bottom: 10px; }
        .stats-box { display: flex; justify-content: space-around; margin-top: 20px; }
        .stat-item { background: #ccf2ff; padding: 20px; border-radius: 6px; text-align: center; flex: 1; margin: 0 10px; border: 1px solid #007bff; }
        .stat-item h3 { margin-top: 0; color: #007bff; }
        .stat-item p { font-size: 24px; font-weight: bold; margin: 5px 0; }
        .menu a { display: block; margin: 10px 0; padding: 10px; background: #007bff; color: white; text-decoration: none; border-radius: 4px; text-align: center; font-weight: bold; }
        .menu a:hover { background: #0056b3; }
        .alert-danger { color: #721c24; background-color: #f8d7da; padding: 10px; border: 1px solid #f5c6cb; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🌟 Yönetici Paneli | <?php echo $_SESSION['Name']; ?></h1>
        <p>Sistem üzerindeki tüm kontrol ve gözlemleme yetkileri sizde.</p>

        <?php if ($error): ?>
            <p class="alert-danger"><?php echo $error; ?></p>
        <?php endif; ?>

        <h2>📊 Kullanıcı İstatistikleri (Toplam: <?php echo $totalUsers; ?>)</h2>
        <div class="stats-box">
            <?php foreach (['Admin', 'Librarian', 'Member'] as $role): 
                $count = $userStats[$role] ?? 0;
            ?>
                <div class="stat-item">
                    <h3><?php echo htmlspecialchars($role); ?></h3>
                    <p><?php echo $count; ?></p>
                </div>
            <?php endforeach; ?>
        </div>

        <h2>🔗 Yönetim Menüsü</h2>
        <div class="menu">
            <a href="user_management.php">Kullanıcı Hesaplarını Yönet (Admin/Librarian Onayları)</a>
            <a href="report_generation.php">Ödünç Alma/İade Performansını Gözlemle</a>
            <a href="admin_logs.php">Loglar</a>
            <a href="logout.php">Çıkış Yap</a>
        </div>
    </div>
</body>
</html>