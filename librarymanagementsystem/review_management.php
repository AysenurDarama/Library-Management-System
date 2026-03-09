<?php
session_start();
require_once 'db_config.php';

// Rol Kontrolü: Sadece Üyeler erişebilir
if (!isset($_SESSION["loggedin"]) || $_SESSION["Role"] !== "Member") {
    header("location: login.html");
    exit;
}

$hataMesaji = '';
$basariMesaji = '';
$userID = $_SESSION['UserID'];

// --- İNCELEME EKLEME İŞLEMİ ---
if (isset($_POST['ekle_review'])) {
    $bookID = (int)$_POST['book_id'];
    $rating = (int)$_POST['rating'];
    $reviewText = trim($_POST['review_text']);

    if ($bookID > 0 && $rating >= 1 && $rating <= 5) {
        try {
            $sql = "INSERT INTO Reviews (Rating, ReviewText, UserID, BookID) VALUES (?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$rating, $reviewText, $userID, $bookID]);
            
            $basariMesaji = "İncelemeniz başarıyla eklendi.";
        } catch (PDOException $e) {
            // Çifte inceleme hatası kontrolü (MySQL kısıtlamasına göre)
            $hataMesaji = "İnceleme eklenirken bir hata oluştu: " . $e->getMessage();
        }
    } else {
        $hataMesaji = "Lütfen tüm alanları doğru doldurun.";
    }
}

// --- İNCELEME SİLME İŞLEMİ ---
if (isset($_POST['sil_review'])) {
    $reviewID = (int)$_POST['review_id'];
    try {
        // Sadece kendi incelemesini silebilmeli
        $sql = "DELETE FROM Reviews WHERE ReviewID = ? AND UserID = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$reviewID, $userID]);
        
        $basariMesaji = "İnceleme başarıyla silindi.";
    } catch (PDOException $e) {
        $hataMesaji = "İnceleme silinirken bir hata oluştu: " . $e->getMessage();
    }
}

// --- ÜYENİN İNCELEMELERİNİ ÇEKME ---
try {
    $sql = "SELECT r.ReviewID, r.Rating, r.ReviewText, b.Title
            FROM Reviews r
            JOIN Books b ON r.BookID = b.BookID
            WHERE r.UserID = ?
            ORDER BY r.ReviewID DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userID]);
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // İnceleme yapabileceği kitapları çekme (Basitçe tüm kitaplar)
    $books_for_review = $pdo->query("SELECT BookID, Title FROM Books ORDER BY Title ASC")->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $hataMesaji = "İncelemeler çekilirken hata oluştu: " . $e->getMessage();
    $reviews = [];
    $books_for_review = [];
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>İnceleme Yönetimi</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #e6ffe6; padding: 20px; }
        .container { max-width: 1000px; margin: auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h1 { color: #009900; border-bottom: 2px solid #33cc33; padding-bottom: 10px; }
        /* Önceki stiller */
        form { background: #f2f2f2; padding: 20px; border-radius: 6px; margin-bottom: 20px; }
        .review-list { margin-top: 20px; }
        .review-item { border: 1px solid #ccc; padding: 15px; margin-bottom: 10px; border-radius: 4px; }
        .review-item h3 { margin-top: 0; color: #006600; }
        .review-item p { margin: 5px 0; }
        button[name="sil_review"] { background-color: #dc3545; color: white; padding: 5px 10px; border: none; border-radius: 4px; cursor: pointer; float: right; }
    </style>
</head>
<body>
    <div class="container">
        <h1>⭐ İnceleme Yönetimi</h1>
        
        <?php if ($hataMesaji) : ?><div class="alert alert-danger"><?php echo $hataMesaji; ?></div><?php endif; ?>
        <?php if ($basariMesaji) : ?><div class="alert alert-success"><?php echo $basariMesaji; ?></div><?php endif; ?>
        
        <p><a href="member_dashboard.php">Panele Geri Dön</a></p>

        <h2>Yeni İnceleme Ekle</h2>
        <form method="POST" action="">
            <label for="book_id">Kitap Seç:</label>
            <select name="book_id" required>
                <option value="">-- Seçiniz --</option>
                <?php foreach ($books_for_review as $book) : ?>
                    <option value="<?php echo $book['BookID']; ?>"><?php echo htmlspecialchars($book['Title']); ?></option>
                <?php endforeach; ?>
            </select><br><br>
            
            <label for="rating">Puan (1-5):</label>
            <input type="number" id="rating" name="rating" min="1" max="5" required style="width: 100px;"><br><br>
            
            <label for="review_text">İnceleme Metni:</label>
            <textarea id="review_text" name="review_text" rows="4" style="width: 100%;" placeholder="Düşüncelerinizi buraya yazın..."></textarea><br>
            
            <button type="submit" name="ekle_review">İncelemeyi Gönder</button>
        </form>

        <h2>Yapılan İncelemeleriniz (<?php echo count($reviews); ?>)</h2>
        <div class="review-list">
            <?php if (!empty($reviews)): ?>
                <?php foreach ($reviews as $review) : ?>
                    <div class="review-item">
                        <form method="POST" action="" style="display:inline;">
                            <input type="hidden" name="review_id" value="<?php echo $review['ReviewID']; ?>">
                            <button type="submit" name="sil_review" onclick="return confirm('Bu incelemeyi silmek istediğinizden emin misiniz?');">Sil</button>
                        </form>
                        <h3><?php echo htmlspecialchars($review['Title']); ?> (Puan: <?php echo $review['Rating']; ?>/5)</h3>
                        <p><?php echo nl2br(htmlspecialchars($review['ReviewText'])); ?></p>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Henüz bir inceleme yapmadınız.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>