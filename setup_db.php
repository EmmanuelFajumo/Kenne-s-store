<?php
// setup_db.php
header('Content-Type: text/plain');

$host = 'localhost';
$user = 'root';
$pass = '';

try {
    // 1. Connect without db to create it
    $pdo = new PDO("mysql:host=$host", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create DB
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `kenestore` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "Database 'kenestore' created or already exists.\n";
    
    // Select DB
    $pdo->exec("USE `kenestore`");
    
    // 2. Create Users Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `users` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `name` VARCHAR(100) NOT NULL,
        `email` VARCHAR(150) UNIQUE NOT NULL,
        `password` VARCHAR(255) NOT NULL,
        `role` ENUM('customer', 'admin') DEFAULT 'customer',
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB");
    echo "Table 'users' created.\n";
    
    // 3. Create Categories Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `categories` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `name` VARCHAR(100) UNIQUE NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB");
    echo "Table 'categories' created.\n";
    
    // 4. Create Products Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `products` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `name` VARCHAR(150) NOT NULL,
        `description` TEXT NULL,
        `price` DECIMAL(10, 2) NOT NULL,
        `image` MEDIUMTEXT NULL,
        `stock` INT DEFAULT 0,
        `category_id` INT,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
    ) ENGINE=InnoDB");
    echo "Table 'products' created.\n";
    
    // 5. Create Cart Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `cart` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `user_id` INT NOT NULL,
        `product_id` INT NOT NULL,
        `quantity` INT DEFAULT 1,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
        FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB");
    echo "Table 'cart' created.\n";
    
    // 6. Create Orders Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `orders` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `user_id` INT NOT NULL,
        `total_amount` DECIMAL(10, 2) NOT NULL,
        `shipping_name` VARCHAR(150) NOT NULL,
        `shipping_address` TEXT NOT NULL,
        `shipping_city` VARCHAR(100) NOT NULL,
        `shipping_zip` VARCHAR(20) NOT NULL,
        `contact_number` VARCHAR(30) NOT NULL,
        `status` ENUM('pending', 'paid', 'shipped', 'completed', 'cancelled') DEFAULT 'pending',
        `payment_method` VARCHAR(50) NOT NULL,
        `payment_reference` VARCHAR(100) NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB");
    echo "Table 'orders' created.\n";
    
    // 7. Create Order Items Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `order_items` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `order_id` INT NOT NULL,
        `product_id` INT NOT NULL,
        `quantity` INT NOT NULL,
        `price` DECIMAL(10, 2) NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
        FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB");
    echo "Table 'order_items' created.\n";
    
    // 8. Seed Default Users (Admin and Customer)
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute(['admin@kenestore.com']);
    if (!$stmt->fetch()) {
        $adminPass = password_hash('adminpassword', PASSWORD_DEFAULT);
        $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)")
            ->execute(['Admin System', 'admin@kenestore.com', $adminPass, 'admin']);
        echo "Default admin account seeded (admin@kenestore.com / adminpassword).\n";
    }
    
    $stmt->execute(['customer@kenestore.com']);
    if (!$stmt->fetch()) {
        $customerPass = password_hash('customerpassword', PASSWORD_DEFAULT);
        $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)")
            ->execute(['Kene Customer', 'customer@kenestore.com', $customerPass, 'customer']);
        echo "Default customer account seeded (customer@kenestore.com / customerpassword).\n";
    }
    
    // 9. Seed Default Categories
    $categories = ['Apparel', 'Accessories', 'Footwear', 'Home Goods'];
    $catMap = [];
    foreach ($categories as $cat) {
        $stmt = $pdo->prepare("SELECT id FROM categories WHERE name = ?");
        $stmt->execute([$cat]);
        $res = $stmt->fetch();
        if (!$res) {
            $pdo->prepare("INSERT INTO categories (name) VALUES (?)")->execute([$cat]);
            $catMap[$cat] = $pdo->lastInsertId();
            echo "Category '$cat' seeded.\n";
        } else {
            $catMap[$cat] = $res['id'];
        }
    }
    
    // 10. Seed Default Products
    // SVGs coded in base64 to prevent raw tag interpolation problems.
    // SVG 1: Leather Backpack (Black/Charcoal with Orange touch)
    $svgBackpack = 'data:image/svg+xml;base64,' . base64_encode('
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 400" width="400" height="400">
      <rect width="100%" height="100%" fill="#FAF9F6"/>
      <path d="M120,150 C120,100 280,100 280,150 L280,320 C280,330 270,340 260,340 L140,340 C130,340 120,330 120,320 Z" fill="#1A1A1A"/>
      <path d="M160,110 Q200,90 240,110" stroke="#FF6F00" stroke-width="6" fill="none"/>
      <rect x="140" y="170" width="120" height="10" rx="3" fill="#3A3A3C"/>
      <rect x="140" y="200" width="120" height="100" rx="5" fill="#2C2C2E"/>
      <line x1="200" y1="200" x2="200" y2="300" stroke="#FF6F00" stroke-width="2"/>
    </svg>');
    
    // SVG 2: Cotton Hoodie (Off-white with Orange zipper detail)
    $svgHoodie = 'data:image/svg+xml;base64,' . base64_encode('
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 400" width="400" height="400">
      <rect width="100%" height="100%" fill="#FAF9F6"/>
      <path d="M100,160 L140,140 L160,160 L240,160 L260,140 L300,160 L280,320 L120,320 Z" fill="#E5E5EA"/>
      <path d="M160,160 C160,120 240,120 240,160 Z" fill="#D1D1D6"/>
      <path d="M200,160 L200,320" stroke="#FF6F00" stroke-width="3"/>
      <circle cx="185" cy="180" r="4" fill="#FF6F00"/>
      <circle cx="215" cy="180" r="4" fill="#FF6F00"/>
    </svg>');
    
    // SVG 3: Card Wallet (Charcoal with Orange contrast)
    $svgWallet = 'data:image/svg+xml;base64,' . base64_encode('
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 400" width="400" height="400">
      <rect width="100%" height="100%" fill="#FAF9F6"/>
      <rect x="100" y="140" width="200" height="120" rx="8" fill="#2C2C2E" stroke="#FF6F00" stroke-width="2"/>
      <path d="M100,180 L300,180" stroke="#1C1C1E" stroke-width="4"/>
      <path d="M100,210 L300,210" stroke="#1C1C1E" stroke-width="4"/>
      <circle cx="270" cy="160" r="6" fill="#FF6F00"/>
    </svg>');
    
    // SVG 4: Suede Sneakers (Off-white body, Orange/Black details)
    $svgSneakers = 'data:image/svg+xml;base64,' . base64_encode('
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 400" width="400" height="400">
      <rect width="100%" height="100%" fill="#FAF9F6"/>
      <path d="M100,260 L260,260 C290,260 310,230 310,200 L270,180 L230,170 L160,210 L100,220 Z" fill="#E5E5EA"/>
      <path d="M100,260 L310,260 L310,275 L100,275 Z" fill="#FFFFFF" stroke="#333333" stroke-width="1"/>
      <path d="M230,170 L250,210" stroke="#FF6F00" stroke-width="4"/>
      <path d="M220,175 L240,215" stroke="#FF6F00" stroke-width="4"/>
      <path d="M210,180 L230,220" stroke="#FF6F00" stroke-width="4"/>
    </svg>');
    
    // SVG 5: Ceramic Coffee Mug (Charcoal outer, Orange inner)
    $svgMug = 'data:image/svg+xml;base64,' . base64_encode('
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 400" width="400" height="400">
      <rect width="100%" height="100%" fill="#FAF9F6"/>
      <path d="M280,180 C280,180 320,190 320,220 C320,250 280,260 280,260" stroke="#2C2C2E" stroke-width="12" fill="none"/>
      <rect x="140" y="160" width="140" height="120" rx="10" fill="#2C2C2E"/>
      <ellipse cx="210" cy="160" rx="70" ry="15" fill="#FF6F00"/>
    </svg>');
    
    // SVG 6: Windbreaker (Grey with Orange toggle details)
    $svgWindbreaker = 'data:image/svg+xml;base64,' . base64_encode('
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 400" width="400" height="400">
      <rect width="100%" height="100%" fill="#FAF9F6"/>
      <path d="M120,130 L160,110 L240,110 L280,130 L320,200 L290,220 L270,180 L270,330 L130,330 L130,180 L110,220 L80,200 Z" fill="#3A3A3C"/>
      <path d="M200,110 L200,330" stroke="#FF6F00" stroke-width="2"/>
      <rect x="195" y="150" width="10" height="15" fill="#1C1C1E"/>
      <circle cx="200" cy="157" r="4" fill="#FF6F00"/>
    </svg>');
    
    $products = [
        [
            'name' => 'Minimalist Leather Backpack',
            'description' => 'A sleek, water-resistant matte black leather backpack featuring dual compartments, minimalist hardware, and ergonomic straps.',
            'price' => 120.00,
            'image' => $svgBackpack,
            'stock' => 15,
            'category_name' => 'Accessories'
        ],
        [
            'name' => 'Organic Cotton Hoodie',
            'description' => 'Premium weight organic cotton fleece hoodie in a relaxed fit. Off-white colorway with custom metal-tipped drawstrings.',
            'price' => 85.00,
            'image' => $svgHoodie,
            'stock' => 25,
            'category_name' => 'Apparel'
        ],
        [
            'name' => 'Minimalist Leather Wallet',
            'description' => 'Full-grain leather cardholder with five slots. Charcoal grey with subtle orange contrast stitching.',
            'price' => 45.00,
            'image' => $svgWallet,
            'stock' => 50,
            'category_name' => 'Accessories'
        ],
        [
            'name' => 'Suede Slip-On Sneakers',
            'description' => 'Sleek off-white suede slip-on sneakers with an orange heel accent and vulcanized rubber sole.',
            'price' => 95.00,
            'image' => $svgSneakers,
            'stock' => 8,
            'category_name' => 'Footwear'
        ],
        [
            'name' => 'Matte Ceramic Coffee Mug',
            'description' => 'Hand-thrown ceramic mug finished in a tactile matte charcoal glaze with a striking orange interior.',
            'price' => 28.00,
            'image' => $svgMug,
            'stock' => 30,
            'category_name' => 'Home Goods'
        ],
        [
            'name' => 'Technical Windbreaker',
            'description' => 'Lightweight ripstop nylon windbreaker in dark grey. Waterproof zippers, adjustable hood, and orange toggle details.',
            'price' => 150.00,
            'image' => $svgWindbreaker,
            'stock' => 12,
            'category_name' => 'Apparel'
        ],
    ];
    
    foreach ($products as $prod) {
        $stmt = $pdo->prepare("SELECT id FROM products WHERE name = ?");
        $stmt->execute([$prod['name']]);
        if (!$stmt->fetch()) {
            $catId = isset($catMap[$prod['category_name']]) ? $catMap[$prod['category_name']] : null;
            $pdo->prepare("INSERT INTO products (name, description, price, image, stock, category_id) VALUES (?, ?, ?, ?, ?, ?)")
                ->execute([$prod['name'], $prod['description'], $prod['price'], $prod['image'], $prod['stock'], $catId]);
            echo "Product '{$prod['name']}' seeded.\n";
        }
    }
    
    echo "\nDatabase Setup completed successfully!";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
