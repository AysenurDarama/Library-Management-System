<?php
session_start();
require_once 'db_config.php';

if (!isset($_SESSION["loggedin"]) || $_SESSION["Role"] !== "Member") {
    header("location: login.html");
    exit;
}

$hataMesaji = '';
$basariMesaji = '';
$userID = $_SESSION['UserID'];

// --- KİTAP ÖDÜNÇ ALMA ---
if (isset($_POST['borrow_book'])) {
    $bookID = (int)$_POST['book_id'];
    $date = date('Y-m-d');

    try {
        // 1. ADIM: Stok Kontrolü (Prosedür ile)
        $stmtCheck = $pdo->prepare("CALL sp_CheckBookStock(?)");
        $stmtCheck->execute([$bookID]);
        $book = $stmtCheck->fetch(PDO::FETCH_ASSOC);
        $stmtCheck->closeCursor(); // ÖNEMLİ: Cursor kapatılmalı

        if ($book && $book['AvailableCopies'] > 0) {
            // 2. ADIM: Ödünç Alma (Prosedür ile)
            // Trigger otomatik olarak stok düşecek.
            $stmtBorrow = $pdo->prepare("CALL sp_BorrowBook(?, ?, ?)");
            $stmtBorrow->execute([$userID, $bookID, $date]);
            $stmtBorrow->closeCursor();
            
            $basariMesaji = "Başarılı! '" . htmlspecialchars($book['Title']) . "' kitabını ödünç aldınız.";
        } else {
            $hataMesaji = "Bu kitabın stoğu tükenmiş.";
        }
    } catch (PDOException $e) {
        $hataMesaji = "İşlem Hatası: " . $e->getMessage();
    }
}

// --- KİTAPLARI LİSTELEME ---
try {
    // Madde 9: Veri trafiğini azaltmak için "SELECT * ..." yerine CALL kullanıyoruz.
    $stmtList = $pdo->prepare("CALL sp_GetAllBooks()");
    $stmtList->execute();
    $kitaplar = $stmtList->fetchAll(PDO::FETCH_ASSOC);
    $stmtList->closeCursor();
} catch (PDOException $e) {
    $kitaplar = [];
    $hataMesaji = "Liste yüklenemedi: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Kitaplar</title>
    <style>
        body { font-family: sans-serif; padding: 20px; background: #e6ffe6; }
        .container { max-width: 1000px; margin: auto; background: white; padding: 20px; border-radius: 8px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 8px; }
        th { background: #28a745; color: white; }
        .btn { background: #007bff; color: white; padding: 5px 10px; border:none; cursor:pointer; border-radius:4px;}
        .alert-s { color: green; background: #d4edda; padding: 10px; }
        .alert-d { color: red; background: #f8d7da; padding: 10px; }
    </style>
</head>
<body>
<div class="container">
    <h1>📚 Kitap Listesi (Prosedürlü)</h1>
    <p><a href="member_dashboard.php">Panele Dön</a></p>

    <?php if ($basariMesaji) echo "<div class='alert-s'>$basariMesaji</div>"; ?>
    <?php if ($hataMesaji) echo "<div class='alert-d'>$hataMesaji</div>"; ?>

    <table>
        <tr><th>Başlık</th><th>Yıl</th><th>Tür</th><th>Stok</th><th>İşlem</th></tr>
        <?php foreach ($kitaplar as $k): ?>
        <tr>
            <td><?php echo htmlspecialchars($k['Title']); ?></td>
            <td><?php echo $k['PublicationYear']; ?></td>
            <td><?php echo $k['Genre']; ?></td>
            <td><?php echo $k['AvailableCopies']; ?></td>
            <td>
                <?php if($k['AvailableCopies'] > 0): ?>
                <form method="POST">
                    <input type="hidden" name="book_id" value="<?php echo $k['BookID']; ?>">
                    <button type="submit" name="borrow_book" class="btn">Ödünç Al</button>
                </form>
                <?php else: echo "Tükendi"; endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>
</body>
</html>