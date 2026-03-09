-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jan 03, 2026 at 07:15 PM
-- Server version: 8.0.44
-- PHP Version: 8.2.29

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `librarymanagementsystem`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `ReturnBookProcedure` (IN `p_TransID` INT, IN `p_BookID` INT)   BEGIN
    UPDATE Transactions SET Return_Date = CURDATE() WHERE TransactionID = p_TransID;
    UPDATE Books SET AvailableCopies = AvailableCopies + 1 WHERE BookID = p_BookID;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_BorrowBook` (IN `p_UserID` INT, IN `p_BookID` INT, IN `p_Date` DATE)   BEGIN
    INSERT INTO Transactions (UserID, BookID, Checkout_Date) 
    VALUES (p_UserID, p_BookID, p_Date);
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_CheckBookStock` (IN `p_BookID` INT)   BEGIN
    SELECT Title, AvailableCopies FROM Books WHERE BookID = p_BookID;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_GetActiveBorrowers` ()   BEGIN
    SELECT t.TransactionID, u.Name, b.Title, t.Checkout_Date
    FROM Transactions t
    JOIN Users u ON t.UserID = u.UserID
    JOIN Books b ON t.BookID = b.BookID
    WHERE t.Return_Date IS NULL;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_GetAllBooks` ()   BEGIN
    SELECT * FROM Books ORDER BY Title ASC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_GetBooksCausingFines` ()   BEGIN
    SELECT b.Title, u.Name, f.FineAmount
    FROM Fines f
    JOIN Users u ON f.UserID = u.UserID
    JOIN Transactions t ON t.UserID = u.UserID -- Bu bağlantı mantıksal analiz içindir
    JOIN Books b ON t.BookID = b.BookID
    WHERE f.PaidStatus = 'Unpaid'
    GROUP BY b.BookID;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_GetReviewsWithUserRoles` ()   BEGIN
    SELECT b.Title, r.ReviewText, r.Rating, u.Name, rol.Role_name
    FROM Reviews r
    JOIN Users u ON r.UserID = u.UserID
    JOIN Roles rol ON u.RoleID = rol.RoleID
    JOIN Books b ON r.BookID = b.BookID;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_GetSciFiReaders` ()   BEGIN
    SELECT DISTINCT u.Name, u.Email, b.Genre
    FROM Users u
    JOIN Transactions t ON u.UserID = t.UserID
    JOIN Books b ON t.BookID = b.BookID
    WHERE b.Genre = 'Science Fiction'; -- Veya parametre gönderilebilir
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_GetTransactionHistoryWithRoles` ()   BEGIN
    SELECT t.TransactionID, u.Name, rol.Role_name, t.Checkout_Date, t.Return_Date
    FROM Transactions t
    JOIN Users u ON t.UserID = u.UserID
    JOIN Roles rol ON u.RoleID = rol.RoleID
    ORDER BY t.Checkout_Date DESC LIMIT 20;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_GetUsersWithFines` ()   BEGIN
    SELECT u.Name, u.Email, r.Role_name, SUM(f.FineAmount) as TotalUnpaid
    FROM Users u
    JOIN Roles r ON u.RoleID = r.RoleID
    JOIN Fines f ON u.UserID = f.UserID
    WHERE f.PaidStatus = 'Unpaid'
    GROUP BY u.UserID;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_RegisterUser` (IN `p_Name` VARCHAR(100), IN `p_Email` VARCHAR(100), IN `p_Password` VARCHAR(255), IN `p_RoleID` INT)   BEGIN
    INSERT INTO Users (Name, Email, Password, RoleID) 
    VALUES (p_Name, p_Email, p_Password, p_RoleID);
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TopReadersStats` ()   BEGIN
    SELECT u.Name, rol.Role_name, COUNT(t.TransactionID) as TotalBooksRead
    FROM Users u
    JOIN Roles rol ON u.RoleID = rol.RoleID
    JOIN Transactions t ON u.UserID = t.UserID
    GROUP BY u.UserID
    ORDER BY TotalBooksRead DESC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_UserLogin` (IN `p_Email` VARCHAR(100))   BEGIN
    SELECT u.UserID, u.Name, u.Password, r.Role_name 
    FROM Users u
    JOIN Roles r ON u.RoleID = r.RoleID
    WHERE u.Email = p_Email;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `authors`
--

CREATE TABLE `authors` (
  `AuthorID` int NOT NULL,
  `Name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `authors`
--

INSERT INTO `authors` (`AuthorID`, `Name`) VALUES
(1, 'Jane Austen'),
(2, 'Mark Twain'),
(3, 'George Orwell'),
(4, 'J.K. Rowling'),
(5, 'Ernest Hemingway'),
(6, 'Leo Tolstoy'),
(7, 'Agatha Christie'),
(8, 'F. Scott Fitzgerald'),
(9, 'J.R.R. Tolkien'),
(10, 'Stephen King');

-- --------------------------------------------------------

--
-- Table structure for table `books`
--

CREATE TABLE `books` (
  `BookID` int NOT NULL,
  `Title` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `Genre` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `PublicationYear` int DEFAULT NULL,
  `AvailableCopies` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `books`
--

INSERT INTO `books` (`BookID`, `Title`, `Genre`, `PublicationYear`, `AvailableCopies`) VALUES
(1, 'Pride and Prejudice', 'Romance', 1813, 4),
(2, 'Adventures of Huckleberry Finn', 'Adventure', 1884, 5),
(3, '1984', 'Dystopian', 1949, 12),
(4, 'Harry Potter and the Sorcerer\'s Stone', 'Fantasy', 1997, 17),
(5, 'The Old Man and the Sea', 'Drama', 1952, 33),
(6, 'War and Peace', 'Historical', 1869, 23),
(7, 'Murder on the Orient Express', 'Mystery', 1934, 20),
(8, 'The Great Gatsby', 'Novel', 1925, 13),
(9, 'The Hobbit', 'Fantasy', 1937, 14),
(10, 'The Shining', 'Horror', 1977, 18);

-- --------------------------------------------------------

--
-- Table structure for table `book_author`
--

CREATE TABLE `book_author` (
  `BookID` int NOT NULL,
  `AuthorID` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `book_author`
--

INSERT INTO `book_author` (`BookID`, `AuthorID`) VALUES
(1, 1),
(2, 2),
(3, 3),
(4, 4),
(5, 5),
(6, 6),
(7, 7),
(8, 8),
(9, 9),
(10, 10);

-- --------------------------------------------------------

--
-- Table structure for table `book_category`
--

CREATE TABLE `book_category` (
  `BookID` int NOT NULL,
  `CategoryID` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `book_category`
--

INSERT INTO `book_category` (`BookID`, `CategoryID`) VALUES
(4, 1),
(9, 1),
(10, 2),
(1, 3),
(7, 4),
(5, 5),
(2, 6),
(6, 7),
(3, 8),
(8, 9);

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `CategoryID` int NOT NULL,
  `CategoryName` varchar(255) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`CategoryID`, `CategoryName`) VALUES
(1, 'Fantasy'),
(2, 'Horror'),
(3, 'Romance'),
(4, 'Mystery'),
(5, 'Drama'),
(6, 'Adventure'),
(7, 'Historical'),
(8, 'Science Fiction'),
(9, 'Classic'),
(10, 'Comedy');

-- --------------------------------------------------------

--
-- Table structure for table `deleteduserslog`
--

CREATE TABLE `deleteduserslog` (
  `LogID` int NOT NULL,
  `OriginalUserID` int DEFAULT NULL,
  `UserName` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `UserEmail` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `DeletedAt` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `fines`
--

CREATE TABLE `fines` (
  `FineID` int NOT NULL,
  `UserID` int NOT NULL,
  `PaidStatus` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `FineAmount` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `fines`
--

INSERT INTO `fines` (`FineID`, `UserID`, `PaidStatus`, `FineAmount`) VALUES
(1, 1, 'Paid', 5.00),
(2, 2, 'Unpaid', 10.00),
(3, 3, 'Paid', 7.00),
(4, 4, 'Unpaid', 12.00),
(5, 5, 'Paid', 3.00),
(6, 6, 'Unpaid', 8.50),
(7, 7, 'Paid', 6.00),
(8, 8, 'Unpaid', 15.00),
(9, 9, 'Paid', 4.00),
(10, 10, 'Unpaid', 11.00);

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `ReviewID` int NOT NULL,
  `Rating` int NOT NULL,
  `ReviewText` text COLLATE utf8mb4_general_ci,
  `UserID` int DEFAULT NULL,
  `BookID` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`ReviewID`, `Rating`, `ReviewText`, `UserID`, `BookID`) VALUES
(1, 5, 'Excellent read!', 1, 1),
(2, 4, 'Very good', 2, 2),
(3, 5, 'Classic masterpiece', 3, 3),
(4, 3, 'Enjoyable', 4, 4),
(5, 4, 'Well written', 5, 5),
(6, 5, 'Amazing story', 6, 6),
(7, 4, 'Great plot', 7, 7),
(8, 5, 'Loved it', 8, 8),
(9, 4, 'Awesome!', 9, 9),
(10, 5, 'Incredible!', 10, 10);

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `RoleID` int NOT NULL,
  `Role_name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`RoleID`, `Role_name`) VALUES
(1, 'Admin'),
(2, 'Librarian'),
(3, 'Member');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `TransactionID` int NOT NULL,
  `Return_Date` date DEFAULT NULL,
  `Checkout_Date` date DEFAULT NULL,
  `UserID` int NOT NULL,
  `BookID` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`TransactionID`, `Return_Date`, `Checkout_Date`, `UserID`, `BookID`) VALUES
(1, '2024-01-10', '2024-01-02', 1, 1),
(2, '2024-02-11', '2024-02-02', 2, 2),
(3, '2024-03-09', '2024-03-02', 3, 3),
(4, '2024-04-21', '2024-04-05', 4, 4),
(5, '2026-01-03', '2024-05-03', 5, 5),
(6, '2024-06-15', '2024-06-03', 6, 6),
(7, '2024-07-22', '2024-07-05', 7, 7),
(8, NULL, '2024-08-04', 8, 8),
(9, '2024-09-19', '2024-09-03', 9, 9),
(10, NULL, '2024-10-04', 10, 10),
(11, '2026-01-03', '2026-01-03', 11, 3),
(12, '2026-01-03', '2026-01-03', 11, 3),
(13, '2026-01-03', '2026-01-03', 11, 3);

--
-- Triggers `transactions`
--
DELIMITER $$
CREATE TRIGGER `AfterBookBorrowed` AFTER INSERT ON `transactions` FOR EACH ROW BEGIN
    UPDATE Books SET AvailableCopies = AvailableCopies - 1 WHERE BookID = NEW.BookID;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `CheckFineAfterReturn` AFTER UPDATE ON `transactions` FOR EACH ROW BEGIN
    DECLARE gunFarki INT;
    DECLARE cezaMiktari DECIMAL(10,2);

    -- Sadece iade işlemi yapılıyorsa (Return_Date değişmişse) çalış
    IF OLD.Return_Date IS NULL AND NEW.Return_Date IS NOT NULL THEN
        
        -- Gün farkını hesapla
        SET gunFarki = DATEDIFF(NEW.Return_Date, NEW.Checkout_Date);
        
        -- Eğer 14 günden fazla kaldıysa ceza yaz (Örnek: Günlük 2 TL)
        IF gunFarki > 14 THEN
            SET cezaMiktari = (gunFarki - 14) * 2.00;
            
            -- Fines tablosuna ekle
            INSERT INTO Fines (UserID, FineAmount, PaidStatus)
            VALUES (NEW.UserID, cezaMiktari, 'Unpaid');
        END IF;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `UserID` int NOT NULL,
  `Name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `Email` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `Password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `RoleID` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`UserID`, `Name`, `Email`, `Password`, `RoleID`) VALUES
(1, 'Alice Smith', 'alice@example.com', 'pass1', 1),
(2, 'Bob Johnson', 'bob@example.com', 'pass2', 2),
(3, 'Charlie Brown', 'charlie@example.com', 'pass3', 2),
(4, 'Diana Evans', 'diana@example.com', 'pass4', 3),
(5, 'Edward King', 'edward@example.com', 'pass5', 3),
(6, 'Fiona Clark', 'fiona@example.com', 'pass6', 3),
(7, 'George Hall', 'george@example.com', 'pass7', 3),
(8, 'Hannah Lee', 'hannah@example.com', 'pass8', 3),
(9, 'Ivan Moore', 'ivan@example.com', 'pass9', 3),
(10, 'Julia White', 'julia@example.com', 'pass10', 3),
(11, 'TEST', 'test@example.com', 'test12', 3);

--
-- Triggers `users`
--
DELIMITER $$
CREATE TRIGGER `LogDeletedUser` AFTER DELETE ON `users` FOR EACH ROW BEGIN
    INSERT INTO DeletedUsersLog (OriginalUserID, UserName, UserEmail)
    VALUES (OLD.UserID, OLD.Name, OLD.Email);
END
$$
DELIMITER ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `authors`
--
ALTER TABLE `authors`
  ADD PRIMARY KEY (`AuthorID`);

--
-- Indexes for table `books`
--
ALTER TABLE `books`
  ADD PRIMARY KEY (`BookID`);

--
-- Indexes for table `book_author`
--
ALTER TABLE `book_author`
  ADD PRIMARY KEY (`BookID`,`AuthorID`),
  ADD KEY `AuthorID` (`AuthorID`);

--
-- Indexes for table `book_category`
--
ALTER TABLE `book_category`
  ADD PRIMARY KEY (`BookID`,`CategoryID`),
  ADD KEY `CategoryID` (`CategoryID`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`CategoryID`);

--
-- Indexes for table `deleteduserslog`
--
ALTER TABLE `deleteduserslog`
  ADD PRIMARY KEY (`LogID`);

--
-- Indexes for table `fines`
--
ALTER TABLE `fines`
  ADD PRIMARY KEY (`FineID`),
  ADD KEY `UserID` (`UserID`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`ReviewID`),
  ADD KEY `BookID` (`BookID`),
  ADD KEY `UserID` (`UserID`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`RoleID`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`TransactionID`),
  ADD KEY `BookID` (`BookID`),
  ADD KEY `UserID` (`UserID`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`UserID`),
  ADD UNIQUE KEY `Email` (`Email`),
  ADD KEY `RoleID` (`RoleID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `authors`
--
ALTER TABLE `authors`
  MODIFY `AuthorID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `books`
--
ALTER TABLE `books`
  MODIFY `BookID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `CategoryID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `deleteduserslog`
--
ALTER TABLE `deleteduserslog`
  MODIFY `LogID` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `fines`
--
ALTER TABLE `fines`
  MODIFY `FineID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `ReviewID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `RoleID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `TransactionID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `UserID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `book_author`
--
ALTER TABLE `book_author`
  ADD CONSTRAINT `book_author_ibfk_1` FOREIGN KEY (`BookID`) REFERENCES `books` (`BookID`),
  ADD CONSTRAINT `book_author_ibfk_2` FOREIGN KEY (`AuthorID`) REFERENCES `authors` (`AuthorID`);

--
-- Constraints for table `book_category`
--
ALTER TABLE `book_category`
  ADD CONSTRAINT `book_category_ibfk_1` FOREIGN KEY (`BookID`) REFERENCES `books` (`BookID`),
  ADD CONSTRAINT `book_category_ibfk_2` FOREIGN KEY (`CategoryID`) REFERENCES `categories` (`CategoryID`);

--
-- Constraints for table `fines`
--
ALTER TABLE `fines`
  ADD CONSTRAINT `fines_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `users` (`UserID`);

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`BookID`) REFERENCES `books` (`BookID`),
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`UserID`) REFERENCES `users` (`UserID`);

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`BookID`) REFERENCES `books` (`BookID`),
  ADD CONSTRAINT `transactions_ibfk_2` FOREIGN KEY (`UserID`) REFERENCES `users` (`UserID`);

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`RoleID`) REFERENCES `roles` (`RoleID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
