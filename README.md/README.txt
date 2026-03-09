Library Management System

This project is a comprehensive web-based Online Library Management System designed to digitize the management of books, members, and borrowing transactions.

🚀 Technologies Used
* Frontend: HTML5, CSS3
* Backend: PHP
* Database: MySQL
* Connection: PDO (PHP Data Objects)

👥 User Roles & Features (RBAC)
The system implements Role-Based Access Control (RBAC):
* Admin: Full system control, manages user accounts and monitors performance reports.
* Librarian: Responsible for operational tasks. Performs CRUD operations on books and manages borrowing/returns.
* Member: Can browse the catalog, borrow books, view transaction history, and leave reviews.

🗄️ Advanced Database Architecture
To ensure data integrity and optimize performance, the database utilizes:

Stored Procedures (Complex Joins)
* `GetBookDetailsWithAuthors`: Retrieves comprehensive book info using junction tables.
* `GetMemberTransactionHistory`: Lists borrowing history.
* `GetFinesWithDetails`: Displays fine amounts alongside book and user details.

Triggers (Automation)
* `AfterBookBorrowed`: Automatically decreases the "Available Copies" count when a book is borrowed.
* `CheckFineAfterReturn`: Automates fine calculation (e.g., 14 days limit) and inserts records to the Fines table.
* `LogDeletedUser`: Acts as an audit trail, saving deleted user info into a backup table.

⚙️ Setup Instructions
1. Import the `.sql` files located in the `/database` folder into your local MySQL server.
2. Update the database credentials in your PHP connection file (e.g., `db_connect.php`).
3. Run the project on a local server (XAMPP/MAMP/WAMP).