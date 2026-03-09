<?php
session_start();
require_once 'db_config.php';

// Rol Kontrolü: Sadece Adminler erişebilir
if (!isset($_SESSION["loggedin"]) || $_SESSION["Role"] !== "Admin") {
    header("location: login.html");
    exit;
}

$hataMesaji = '';
$basariMesaji = '';

// --- KULLANICI ROLÜ GÜNCELLEME İŞLEMİ ---
if (isset($_POST['guncelle_rol'])) {
    $userID = (int)$_POST['user_id'];
    $newRoleID = (int)$_POST['role_id'];

    try {
        // Kullanıcının rolünü güncelle
        $sql = "UPDATE Users SET RoleID = ? WHERE UserID = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$newRoleID, $userID]);
        
        $basariMesaji = "Kullanıcı rolü başarıyla güncellendi.";
    } catch (PDOException $e) {
        $hataMesaji = "Rol güncellenirken bir hata oluştu: " . $e->getMessage();
    }
}

// --- KULLANICI SİLME İŞLEMİ (Delete) ---
if (isset($_POST['sil_kullanici'])) {
    $userID = (int)$_POST['user_id'];

    // Yöneticinin kendini silmesini engelleme
    if ($userID == $_SESSION['UserID']) {
        $hataMesaji = "Güvenlik nedeniyle kendi hesabınızı silemezsiniz.";
    } else {
        try {
            // 1. İlişkili kayitları (Fines, Reviews, Transactions) sil
            // Foreign Key kısıtlamaları nedeniyle bu adımlar zorunludur.
            $pdo->prepare("DELETE FROM Fines WHERE UserID = ?")->execute([$userID]);
            $pdo->prepare("DELETE FROM Reviews WHERE UserID = ?")->execute([$userID]);
            $pdo->prepare("DELETE FROM Transactions WHERE UserID = ?")->execute([$userID]);
            
            // 2. Kullanıcıyı sil
            $sql = "DELETE FROM Users WHERE UserID = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$userID]);
            
            $basariMesaji = "Kullanıcı (ID: $userID) ve tüm ilişkili kayıtları başarıyla silindi.";
        } catch (PDOException $e) {
            $hataMesaji = "Kullanıcı silinirken bir hata oluştu: " . $e->getMessage();
        }
    }
}

// Tüm kullanıcıları ve rollerini çekme
try {
    $sql = "SELECT u.UserID, u.Name, u.Email, r.RoleID, r.Role_name 
            FROM Users u
            JOIN Roles r ON u.RoleID = r.RoleID
            ORDER BY u.UserID ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Tüm rolleri çekme (Rol seçimi için)
    $roles = $pdo->query("SELECT RoleID, Role_name FROM Roles")->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $hataMesaji = "Kullanıcılar veya roller çekilirken hata oluştu: " . $e->getMessage();
    $users = [];
    $roles = [];
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Kullanıcı Yönetimi - Admin</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #e6f7ff; padding: 20px; }
        .container { max-width: 1200px; margin: auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h1 { color: #007bff; border-bottom: 2px solid #007bff; padding-bottom: 10px; }
        .alert { padding: 10px; margin-bottom: 15px; border-radius: 4px; }
        .alert-success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-danger { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background-color: #007bff; color: white; }
        select, button { padding: 8px; border-radius: 4px; border: 1px solid #ccc; }
        button[name="guncelle_rol"] { background-color: #28a745; color: white; cursor: pointer; margin-left: 5px; }
        button[name="sil_kullanici"] { background-color: #dc3545; color: white; cursor: pointer; }
    </style>
</head>
<body>
    <div class="container">
        <h1>👤 Kullanıcı Yönetimi (Admin)</h1>
        
        <?php if ($hataMesaji) : ?>
            <div class="alert alert-danger"><?php echo $hataMesaji; ?></div>
        <?php endif; ?>
        
        <?php if ($basariMesaji) : ?>
            <div class="alert alert-success"><?php echo $basariMesaji; ?></div>
        <?php endif; ?>
        
        <p><a href="admin_dashboard.php">Panele Geri Dön</a></p>

        <h2>Sistemdeki Tüm Kullanıcılar</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Ad</th>
                    <th>E-posta</th>
                    <th>Mevcut Rol</th>
                    <th>Rol Güncelle</th>
                    <th>Silme İşlemi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user) : ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['UserID']); ?></td>
                        <td><?php echo htmlspecialchars($user['Name']); ?></td>
                        <td><?php echo htmlspecialchars($user['Email']); ?></td>
                        <td><?php echo htmlspecialchars($user['Role_name']); ?></td>
                        <td>
                            <form method="POST" action="" style="display: flex; align-items: center;">
                                <input type="hidden" name="user_id" value="<?php echo $user['UserID']; ?>">
                                <select name="role_id" required>
                                    <?php foreach ($roles as $role) : ?>
                                        <option value="<?php echo $role['RoleID']; ?>" 
                                            <?php if ($role['RoleID'] == $user['RoleID']) echo 'selected'; ?>>
                                            <?php echo htmlspecialchars($role['Role_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" name="guncelle_rol">Kaydet</button>
                            </form>
                        </td>
                        <td>
                             <form method="POST" action="" style="display:inline;">
                                <input type="hidden" name="user_id" value="<?php echo $user['UserID']; ?>">
                                <button type="submit" name="sil_kullanici" 
                                    onclick="return confirm('<?php echo htmlspecialchars($user['Name']); ?> adlı kullanıcıyı ve tüm ilişkili kayıtlarını (işlemler, cezalar, incelemeler) silmek istediğinizden emin misiniz?');"
                                    <?php if ($user['UserID'] == $_SESSION['UserID']) echo 'disabled title="Kendi hesabınızı silemezsiniz"'; ?>>
                                    Sil
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php if (empty($users)): ?>
            <p>Sistemde kayıtlı kullanıcı bulunmamaktadır.</p>
        <?php endif; ?>
    </div>
</body>
</html>