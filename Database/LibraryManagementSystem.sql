-- CREATE DATABASE
DROP DATABASE IF EXISTS LibraryManagementSystem;
CREATE DATABASE LibraryManagementSystem;
USE LibraryManagementSystem;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS Authors;
DROP TABLE IF EXISTS Books;
DROP TABLE IF EXISTS Categories;
DROP TABLE IF EXISTS Roles;
DROP TABLE IF EXISTS Users;
DROP TABLE IF EXISTS Reviews;
DROP TABLE IF EXISTS Transactions;
DROP TABLE IF EXISTS Fines;
DROP TABLE IF EXISTS book_author;
DROP TABLE IF EXISTS book_category;

SET FOREIGN_KEY_CHECKS = 1;

-- CREATE TABLES

CREATE TABLE Authors (
    AuthorID INT NOT NULL AUTO_INCREMENT,
    Name VARCHAR(255) NOT NULL,
    PRIMARY KEY (AuthorID)
);

CREATE TABLE Books (
    BookID INT NOT NULL AUTO_INCREMENT,
    Title VARCHAR(255) NOT NULL,
    Genre VARCHAR(255),
    PublicationYear INT,
    AvailableCopies INT,
    PRIMARY KEY (BookID)
);

CREATE TABLE Categories (
    CategoryID INT NOT NULL AUTO_INCREMENT,
    CategoryName VARCHAR(255) NOT NULL,
    PRIMARY KEY (CategoryID)
);

CREATE TABLE Roles (
    RoleID INT NOT NULL AUTO_INCREMENT,
    Role_name VARCHAR(255) NOT NULL,
    PRIMARY KEY (RoleID)
);

CREATE TABLE Users (
    UserID INT NOT NULL AUTO_INCREMENT,
    Name VARCHAR(255) NOT NULL,
    Email VARCHAR(255) UNIQUE NOT NULL,
    Password VARCHAR(255) NOT NULL,
    RoleID INT,
    PRIMARY KEY (UserID),
    FOREIGN KEY (RoleID) REFERENCES Roles(RoleID)
);

CREATE TABLE Reviews (
    ReviewID INT NOT NULL AUTO_INCREMENT,
    Rating INT NOT NULL,
    ReviewText TEXT,
    UserID INT,
    BookID INT,
    PRIMARY KEY (ReviewID),
    FOREIGN KEY (BookID) REFERENCES Books(BookID),
    FOREIGN KEY (UserID) REFERENCES Users(UserID)
);

CREATE TABLE Transactions (
    TransactionID INT NOT NULL AUTO_INCREMENT,
    Return_Date DATE,
    Checkout_Date DATE,
    UserID INT NOT NULL,
    BookID INT NOT NULL,
    PRIMARY KEY (TransactionID),
    FOREIGN KEY (BookID) REFERENCES Books(BookID),
    FOREIGN KEY (UserID) REFERENCES Users(UserID)
);

CREATE TABLE Fines (
    FineID INT NOT NULL AUTO_INCREMENT,
    UserID INT NOT NULL,
    PaidStatus VARCHAR(50),
    FineAmount DECIMAL(10,2),
    PRIMARY KEY (FineID),
    FOREIGN KEY (UserID) REFERENCES Users(UserID)
);

CREATE TABLE book_author (
    BookID INT,
    AuthorID INT,
    PRIMARY KEY (BookID, AuthorID),
    FOREIGN KEY (BookID) REFERENCES Books(BookID),
    FOREIGN KEY (AuthorID) REFERENCES Authors(AuthorID)
);

CREATE TABLE book_category (
    BookID INT,
    CategoryID INT,
    PRIMARY KEY (BookID, CategoryID),
    FOREIGN KEY (BookID) REFERENCES Books(BookID),
    FOREIGN KEY (CategoryID) REFERENCES Categories(CategoryID)
);


-- INSERT DATA (10 ROWS PER TABLE)

INSERT INTO Authors (Name) VALUES
('Jane Austen'), ('Mark Twain'), ('George Orwell'), ('J.K. Rowling'),
('Ernest Hemingway'), ('Leo Tolstoy'), ('Agatha Christie'),
('F. Scott Fitzgerald'), ('J.R.R. Tolkien'), ('Stephen King');


INSERT INTO Books (Title, PublicationYear, Genre, AvailableCopies) VALUES
('Pride and Prejudice', 1813, 'Romance', 4),
('Adventures of Huckleberry Finn', 1884, 'Adventure', 5),
('1984', 1949, 'Dystopian', 12),
('Harry Potter and the Sorcerer''s Stone', 1997, 'Fantasy', 17),
('The Old Man and the Sea', 1952, 'Drama', 32),
('War and Peace', 1869, 'Historical', 23),
('Murder on the Orient Express', 1934, 'Mystery', 20),
('The Great Gatsby', 1925, 'Novel', 13),
('The Hobbit', 1937, 'Fantasy', 14),
('The Shining', 1977, 'Horror', 18);

INSERT INTO Categories (CategoryName) VALUES
('Fantasy'), ('Horror'), ('Romance'), ('Mystery'), ('Drama'),
('Adventure'), ('Historical'), ('Science Fiction'),
('Classic'), ('Comedy');

INSERT INTO Roles (Role_name) VALUES
('Admin'), ('Librarian'), ('Member');

INSERT INTO Users (Name, Email, Password, RoleID) VALUES
('Alice Smith', 'alice@example.com', 'pass1', 1),
('Bob Johnson', 'bob@example.com', 'pass2', 2),
('Charlie Brown', 'charlie@example.com', 'pass3', 2),
('Diana Evans', 'diana@example.com', 'pass4', 3),
('Edward King', 'edward@example.com', 'pass5', 3),
('Fiona Clark', 'fiona@example.com', 'pass6', 3),
('George Hall', 'george@example.com', 'pass7', 3),
('Hannah Lee', 'hannah@example.com', 'pass8', 3),
('Ivan Moore', 'ivan@example.com', 'pass9', 3),
('Julia White', 'julia@example.com', 'pass10', 3);

INSERT INTO Reviews (Rating, ReviewText, UserID, BookID) VALUES
(5, 'Excellent read!', 1, 1),
(4, 'Very good', 2, 2),
(5, 'Classic masterpiece', 3, 3),
(3, 'Enjoyable', 4, 4),
(4, 'Well written', 5, 5),
(5, 'Amazing story', 6, 6),
(4, 'Great plot', 7, 7),
(5, 'Loved it', 8, 8),
(4, 'Awesome!', 9, 9),
(5, 'Incredible!', 10, 10);

INSERT INTO Transactions (UserID, BookID, Return_Date, Checkout_Date) VALUES
(1, 1, '2024-01-10', '2024-01-02'),
(2, 2, '2024-02-11', '2024-02-02'),
(3, 3, '2024-03-09', '2024-03-02'),
(4, 4, '2024-04-21', '2024-04-05'),
(5, 5, NULL, '2024-05-03'),
(6, 6, '2024-06-15', '2024-06-03'),
(7, 7, '2024-07-22', '2024-07-05'),
(8, 8, NULL, '2024-08-04'),
(9, 9, '2024-09-19', '2024-09-03'),
(10, 10, NULL, '2024-10-04');

INSERT INTO Fines (UserID, PaidStatus, FineAmount) VALUES
(1, 'Paid', 5.00),
(2, 'Unpaid', 10.00),
(3, 'Paid', 7.00),
(4, 'Unpaid', 12.00),
(5, 'Paid', 3.00),
(6, 'Unpaid', 8.50),
(7, 'Paid', 6.00),
(8, 'Unpaid', 15.00),
(9, 'Paid', 4.00),
(10, 'Unpaid', 11.00);

INSERT INTO book_author (BookID, AuthorID) VALUES
(1,1),(2,2),(3,3),(4,4),(5,5),
(6,6),(7,7),(8,8),(9,9),(10,10);

INSERT INTO book_category (BookID, CategoryID) VALUES
(1,3),(2,6),(3,8),(4,1),(5,5),
(6,7),(7,4),(8,9),(9,1),(10,2);

