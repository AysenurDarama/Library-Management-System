<?php
session_start();
// Rol Kontrolü: Giriş yapılmadıysa veya rol "Librarian" değilse, giriş sayfasına yönlendir.
if (!isset($_SESSION["loggedin"]) || $_SESSION["Role"] !== "Librarian") {
    header("location: login.html");
    exit;
}

require_once 'db_config.php';

$hataMesaji = '';
$basariMesaji = '';

// --- CRUD İşlemleri ---

// 1. KİTAP EKLEME (Create)
if (isset($_POST['ekle'])) {
    $title = trim($_POST['title']);
    $publicationYear = (int)$_POST['publication_year'];
    $genre = trim($_POST['genre']);
    $availableCopies = (int)$_POST['available_copies'];

    if (!empty($title) && $publicationYear > 0) {
        try {
            // Kitap ekleme SQL sorgusu
            $sql = "INSERT INTO Books (Title, PublicationYear, Genre, AvailableCopies) VALUES (?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            
            // Parametreleri güvenli bir şekilde bağla ve çalıştır
            $stmt->execute([$title, $publicationYear, $genre, $availableCopies]);
            
            $basariMesaji = "Kitap başarıyla eklendi: " . htmlspecialchars($title);
        } catch (PDOException $e) {
            $hataMesaji = "Kitap eklenirken bir hata oluştu: " . $e->getMessage();
        }
    } else {
        $hataMesaji = "Lütfen tüm gerekli alanları doldurun.";
    }
}

// 2. KİTAP GÜNCELLEME (Update)
if (isset($_POST['guncelle'])) {
    $bookID = (int)$_POST['book_id'];
    $title = trim($_POST['title']);
    $publicationYear = (int)$_POST['publication_year'];
    $genre = trim($_POST['genre']);
    $availableCopies = (int)$_POST['available_copies'];

    if ($bookID > 0 && !empty($title)) {
        try {
            $sql = "UPDATE Books SET Title = ?, PublicationYear = ?, Genre = ?, AvailableCopies = ? WHERE BookID = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$title, $publicationYear, $genre, $availableCopies, $bookID]);
            
            $basariMesaji = "Kitap başarıyla güncellendi: " . htmlspecialchars($title);
        } catch (PDOException $e) {
            $hataMesaji = "Kitap güncellenirken bir hata oluştu: " . $e->getMessage();
        }
    } else {
        $hataMesaji = "Geçersiz Kitap ID'si veya başlık.";
    }
}

// 3. KİTAP SİLME (Delete)
if (isset($_POST['sil'])) {
    $bookID = (int)$_POST['book_id'];

    // NOT: Gerçek bir sistemde, silmeden önce bu kitabın Transactions (işlemler)
    // ve Reviews (incelemeler) tablolarında ilişkisinin olup olmadığı kontrol edilmeli veya 
    // ilişkili kayıtlar CASCADE DELETE ile silinmelidir.
    if ($bookID > 0) {
        try {
            // İlişkili tabloları temizle (M:N ilişkileri için zorunlu)
            $pdo->prepare("DELETE FROM book_author WHERE BookID = ?")->execute([$bookID]);
            $pdo->prepare("DELETE FROM book_category WHERE BookID = ?")->execute([$bookID]);
            
            // Kitabı sil
            $sql = "DELETE FROM Books WHERE BookID = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$bookID]);
            
            $basariMesaji = "Kitap başarıyla silindi.";
        } catch (PDOException $e) {
            $hataMesaji = "Kitap silinirken bir hata oluştu. İlişkili kayıtları kontrol edin. Hata: " . $e->getMessage();
        }
    }
}

// 4. KİTAP LİSTESİNİ ÇEKME (Read)
try {
    $kitaplar = $pdo->query("SELECT * FROM Books ORDER BY BookID DESC")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $hataMesaji = "Kitaplar listelenirken bir hata oluştu: " . $e->getMessage();
    $kitaplar = [];
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Kitap Yönetimi - Kütüphaneci</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f8f9fa; padding: 20px; }
        .container { max-width: 1200px; margin: auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h1 { color: #007bff; border-bottom: 2px solid #007bff; padding-bottom: 10px; }
        .alert { padding: 10px; margin-bottom: 15px; border-radius: 4px; }
        .alert-success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-danger { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        form { background: #f2f2f2; padding: 20px; border-radius: 6px; margin-bottom: 20px; }
        input[type="text"], input[type="number"] { width: 100%; padding: 10px; margin: 5px 0 15px 0; display: inline-block; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        button { background-color: #007bff; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; margin-right: 10px; }
        button:hover { background-color: #0056b3; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #007bff; color: white; }
        .edit-form { display: none; background: #fff; padding: 15px; border: 1px solid #ccc; margin-top: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Kitap Yönetimi (Librarian)</h1>

        <?php if ($hataMesaji) : ?>
            <div class="alert alert-danger"><?php echo $hataMesaji; ?></div>
        <?php endif; ?>
        
        <?php if ($basariMesaji) : ?>
            <div class="alert alert-success"><?php echo $basariMesaji; ?></div>
        <?php endif; ?>
        
        <p>Merhaba, <?php echo $_SESSION['Name']; ?>. Buradan kitap kayıtlarını yönetebilirsiniz. <a href="librarian_dashboard.php">Panele Geri Dön</a></p>
        
        <h2>Yeni Kitap Ekle</h2>
        <form method="POST" action="">
            <label for="title">Başlık:</label>
            <input type="text" id="title" name="title" required>
            
            <label for="genre">Tür (Genre):</label>
            <input type="text" id="genre" name="genre">
            
            <label for="publication_year">Yayın Yılı:</label>
            <input type="number" id="publication_year" name="publication_year" required min="1000" max="<?php echo date('Y'); ?>">
            
            <label for="available_copies">Mevcut Kopya Sayısı:</label>
            <input type="number" id="available_copies" name="available_copies" required min="0">
            
            <button type="submit" name="ekle">Kitabı Ekle</button>
        </form>

        <h2>Mevcut Kitaplar</h2>
        <table>
            <thead>
                <tr>
                    <th>Başlık</th>
                    <th>Tür</th>
                    <th>Yayın Yılı</th>
                    <th>Mevcut Kopya</th>
                    <th>İşlemler</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($kitaplar as $kitap) : ?>
                    <tr>
                        <td><?php echo htmlspecialchars($kitap['Title']); ?></td>
                        <td><?php echo htmlspecialchars($kitap['Genre']); ?></td>
                        <td><?php echo htmlspecialchars($kitap['PublicationYear']); ?></td>
                        <td><?php echo htmlspecialchars($kitap['AvailableCopies']); ?></td>
                        <td>
                            <button onclick="document.getElementById('edit-form-<?php echo $kitap['BookID']; ?>').style.display='block'">Düzenle</button>
                            <form method="POST" action="" style="display:inline-block;">
                                <input type="hidden" name="book_id" value="<?php echo $kitap['BookID']; ?>">
                                <button type="submit" name="sil" onclick="return confirm('Bu kitabı silmek istediğinizden emin misiniz? İlişkili kayıtlar da silinebilir.');">Sil</button>
                            </form>
                        </td>
                    </tr>
                    <tr class="edit-row">
                        <td colspan="6">
                            <div class="edit-form" id="edit-form-<?php echo $kitap['BookID']; ?>">
                                <h3>Kitap Düzenle (ID: <?php echo $kitap['BookID']; ?>)</h3>
                                <form method="POST" action="">
                                    <input type="hidden" name="book_id" value="<?php echo $kitap['BookID']; ?>">
                                    
                                    <label for="title_<?php echo $kitap['BookID']; ?>">Başlık:</label>
                                    <input type="text" id="title_<?php echo $kitap['BookID']; ?>" name="title" value="<?php echo htmlspecialchars($kitap['Title']); ?>" required>
                                    
                                    <label for="genre_<?php echo $kitap['BookID']; ?>">Tür (Genre):</label>
                                    <input type="text" id="genre_<?php echo $kitap['BookID']; ?>" name="genre" value="<?php echo htmlspecialchars($kitap['Genre']); ?>">

                                    <label for="year_<?php echo $kitap['BookID']; ?>">Yayın Yılı:</label>
                                    <input type="number" id="year_<?php echo $kitap['BookID']; ?>" name="publication_year" value="<?php echo htmlspecialchars($kitap['PublicationYear']); ?>" required>

                                    <label for="copies_<?php echo $kitap['BookID']; ?>">Mevcut Kopya Sayısı:</label>
                                    <input type="number" id="copies_<?php echo $kitap['BookID']; ?>" name="available_copies" value="<?php echo htmlspecialchars($kitap['AvailableCopies']); ?>" required>
                                    
                                    <button type="submit" name="guncelle">Kaydet</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php if (empty($kitaplar)): ?>
            <p>Kütüphanede kayıtlı kitap bulunmamaktadır.</p>
        <?php endif; ?>
    </div>
</body>
</html>