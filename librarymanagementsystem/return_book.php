<?php
/**
 * return_book.php - Stored Procedure Entegrasyonu Yapıldı
 */
session_start();
require_once 'db_config.php';

// Sadece Kütüphaneci (Librarian) erişebilir
if (!isset($_SESSION["loggedin"]) || $_SESSION["Role"] !== "Librarian") {
    header("location: login.php");
    exit;
}

$mesaj = '';

// --- KİTAP İADE İŞLEMİ (PROCEDURE ÇAĞIRMA) ---
if (isset($_POST['return_book'])) {
    $transID = (int)$_POST['trans_id'];
    $bookID = (int)$_POST['book_id'];

    try {
        // STORED PROCEDURE ÇAĞIRILIYOR
        // PHP tek satır komut gönderir, tüm mantığı veritabanı halleder.
        $sql = "CALL ReturnBookProcedure(?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$transID, $bookID]);

        $mesaj = "<div class='alert-success'>Kitap başarıyla iade alındı. Stok veritabanı prosedürü tarafından artırıldı.</div>";
    } catch (PDOException $e) {
        $mesaj = "<div class='alert-danger'>Hata: " . $e->getMessage() . "</div>";
    }
}

// İade edilmemiş (Return_Date IS NULL) kitapları listele
$sql = "SELECT t.TransactionID, t.BookID, b.Title, u.Name AS UserName, t.Checkout_Date 
        FROM Transactions t
        JOIN Books b ON t.BookID = b.BookID
        JOIN Users u ON t.UserID = u.UserID
        WHERE t.Return_Date IS NULL
        ORDER BY t.Checkout_Date ASC";
$borrowedBooks = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Kitap İade İşlemi</title>
    <style>
        body { font-family: sans-serif; padding: 20px; background: #fff9e6; }
        .container { max-width: 1000px; margin: auto; background: white; padding: 20px; border-radius: 8px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background: #ffaa00; color: white; }
        .btn-return { background: #28a745; color: white; padding: 8px 12px; border: none; border-radius: 4px; cursor:pointer; }
        .alert-success { background: #d4edda; color: #155724; padding: 10px; }
        .alert-danger { background: #f8d7da; color: #721c24; padding: 10px; }
    </style>
</head>
<body>
<div class="container">
    <h1>🔄 Kitap İade Yönetimi </h1>
    <p><a href="librarian_dashboard.php">Panele Dön</a></p>

    <?php echo $mesaj; ?>

    <h3>Henüz İade Edilmemiş Kitaplar</h3>
    <?php if(count($borrowedBooks) > 0): ?>
    <table>
        <tr>
            <th>İşlem ID</th>
            <th>Kitap</th>
            <th>Üye</th>
            <th>Alma Tarihi</th>
            <th>Aksiyon</th>
        </tr>
        <?php foreach ($borrowedBooks as $row): ?>
        <tr>
            <td><?php echo $row['TransactionID']; ?></td>
            <td><?php echo htmlspecialchars($row['Title']); ?></td>
            <td><?php echo htmlspecialchars($row['UserName']); ?></td>
            <td><?php echo date('d.m.Y', strtotime($row['Checkout_Date'])); ?></td>
            <td>
                <form method="POST">
                    <input type="hidden" name="trans_id" value="<?php echo $row['TransactionID']; ?>">
                    <input type="hidden" name="book_id" value="<?php echo $row['BookID']; ?>">
                    <button type="submit" name="return_book" class="btn-return">İade Al</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
    <?php else: ?>
        <p>Şu anda dışarıda ödünç verilmiş kitap yok.</p>
    <?php endif; ?>
</div>
</body>
</html>