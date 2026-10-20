CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('admin', 'cashier') DEFAULT 'cashier',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    barcode VARCHAR(50) UNIQUE,
    name VARCHAR(150) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    stock INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- İlkin Admin İstifadəçisi (Şifrə: admin123)
INSERT INTO users (username, password, full_name, role) 
VALUES ('admin', '$2y$10$e0MYzXyjpJS7Pd0RVvHwHe1H1S3eO64f39h2fK3J1c1v7J8E1Z4yG', 'Sistem Admini', 'admin')
ON DUPLICATE KEY UPDATE id=id;