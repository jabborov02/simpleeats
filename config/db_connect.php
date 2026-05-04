<?php
/**
 * Database connection file
 * MySQL version for local SimpleEats project
 */

$host = "127.0.0.1";
$db_name = "simpleeats";
$username = "root";
$password = "Realmadrid@7";

$dsn = "mysql:host=$host;dbname=$db_name;charset=utf8mb4";

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $username, $password, $options);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Function to initialize the database tables if they don't exist
function initDatabase($pdo) {
    try {
        // Create restaurants table
        $pdo->exec("CREATE TABLE IF NOT EXISTS restaurants (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            location VARCHAR(255) NOT NULL
        )");

        // Create menus table
        $pdo->exec("CREATE TABLE IF NOT EXISTS menus (
            id INT AUTO_INCREMENT PRIMARY KEY,
            restaurant_id INT NOT NULL,
            item_name VARCHAR(255) NOT NULL,
            price DECIMAL(10,2) NOT NULL,
            description TEXT,
            FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE
        )");

        // Create customers table
        $pdo->exec("CREATE TABLE IF NOT EXISTS customers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL
        )");

        // Create orders table
        $pdo->exec("CREATE TABLE IF NOT EXISTS orders (
            id INT AUTO_INCREMENT PRIMARY KEY,
            customer_id INT NOT NULL,
            restaurant_id INT NOT NULL,
            order_date DATETIME DEFAULT CURRENT_TIMESTAMP,
            status ENUM('Pending', 'Completed') DEFAULT 'Pending',
            FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
            FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE
        )");

        // Create order_items table
        $pdo->exec("CREATE TABLE IF NOT EXISTS order_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_id INT NOT NULL,
            menu_id INT NOT NULL,
            quantity INT NOT NULL,
            FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
            FOREIGN KEY (menu_id) REFERENCES menus(id) ON DELETE CASCADE
        )");

        // Create admin table
        $pdo->exec("CREATE TABLE IF NOT EXISTS admin (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(255) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL
        )");

        // Insert default admin account if none exists
        $stmt = $pdo->prepare("SELECT * FROM admin LIMIT 1");
        $stmt->execute();

        if ($stmt->rowCount() == 0) {
            $hashed_password = password_hash('admin123', PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO admin (username, password) VALUES (?, ?)");
            $stmt->execute(['admin', $hashed_password]);
        }

    } catch (PDOException $e) {
        die("Database initialization failed: " . $e->getMessage());
    }
}

// Initialize database
initDatabase($pdo);
?>