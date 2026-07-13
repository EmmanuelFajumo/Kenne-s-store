<?php
// Classes/Cart.php

class Cart {
    private $db;

    public function __construct($db) {
        $this->db = $db;
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
    }

    public function add($userId, $productId, $quantity = 1) {
        if ($quantity <= 0) return false;
        
        if ($userId) {
            // Logged in: DB cart
            try {
                // Check if product exists in DB
                $stmt = $this->db->prepare("SELECT stock FROM products WHERE id = ?");
                $stmt->execute([$productId]);
                $product = $stmt->fetch();
                if (!$product) return false;

                $stmt = $this->db->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
                $stmt->execute([$userId, $productId]);
                $existing = $stmt->fetch();

                if ($existing) {
                    $newQty = $existing['quantity'] + $quantity;
                    // Cap at stock
                    if ($newQty > $product['stock']) {
                        $newQty = $product['stock'];
                    }
                    $stmt = $this->db->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
                    $stmt->execute([$newQty, $existing['id']]);
                } else {
                    if ($quantity > $product['stock']) {
                        $quantity = $product['stock'];
                    }
                    $stmt = $this->db->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
                    $stmt->execute([$userId, $productId, $quantity]);
                }
                return true;
            } catch (PDOException $e) {
                return false;
            }
        } else {
            // Guest: Session cart
            try {
                $stmt = $this->db->prepare("SELECT stock FROM products WHERE id = ?");
                $stmt->execute([$productId]);
                $product = $stmt->fetch();
                if (!$product) return false;

                if (isset($_SESSION['cart'][$productId])) {
                    $_SESSION['cart'][$productId] += $quantity;
                } else {
                    $_SESSION['cart'][$productId] = $quantity;
                }

                // Check stock
                if ($_SESSION['cart'][$productId] > $product['stock']) {
                    $_SESSION['cart'][$productId] = $product['stock'];
                }
                return true;
            } catch (PDOException $e) {
                return false;
            }
        }
    }

    public function getItems($userId) {
        if ($userId) {
            try {
                $stmt = $this->db->prepare("
                    SELECT c.product_id, c.quantity, p.name, p.price, p.image, p.stock 
                    FROM cart c 
                    JOIN products p ON c.product_id = p.id 
                    WHERE c.user_id = ?
                ");
                $stmt->execute([$userId]);
                return $stmt->fetchAll();
            } catch (PDOException $e) {
                return [];
            }
        } else {
            $items = [];
            if (!empty($_SESSION['cart'])) {
                foreach ($_SESSION['cart'] as $productId => $quantity) {
                    try {
                        $stmt = $this->db->prepare("SELECT id as product_id, name, price, image, stock FROM products WHERE id = ?");
                        $stmt->execute([$productId]);
                        $product = $stmt->fetch();
                        if ($product) {
                            $product['quantity'] = $quantity;
                            $items[] = $product;
                        } else {
                            // Product no longer exists, remove from session cart
                            unset($_SESSION['cart'][$productId]);
                        }
                    } catch (PDOException $e) {
                        // ignore
                    }
                }
            }
            return $items;
        }
    }

    public function updateQuantity($userId, $productId, $quantity) {
        if ($quantity <= 0) {
            return $this->remove($userId, $productId);
        }

        try {
            $stmt = $this->db->prepare("SELECT stock FROM products WHERE id = ?");
            $stmt->execute([$productId]);
            $product = $stmt->fetch();
            if (!$product) return false;

            if ($quantity > $product['stock']) {
                $quantity = $product['stock'];
            }

            if ($userId) {
                $stmt = $this->db->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
                return $stmt->execute([$quantity, $userId, $productId]);
            } else {
                $_SESSION['cart'][$productId] = $quantity;
                return true;
            }
        } catch (PDOException $e) {
            return false;
        }
    }

    public function remove($userId, $productId) {
        if ($userId) {
            try {
                $stmt = $this->db->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
                return $stmt->execute([$userId, $productId]);
            } catch (PDOException $e) {
                return false;
            }
        } else {
            if (isset($_SESSION['cart'][$productId])) {
                unset($_SESSION['cart'][$productId]);
            }
            return true;
        }
    }

    public function clear($userId) {
        if ($userId) {
            try {
                $stmt = $this->db->prepare("DELETE FROM cart WHERE user_id = ?");
                return $stmt->execute([$userId]);
            } catch (PDOException $e) {
                return false;
            }
        } else {
            $_SESSION['cart'] = [];
            return true;
        }
    }

    public function getCount($userId) {
        $items = $this->getItems($userId);
        $count = 0;
        foreach ($items as $item) {
            $count += $item['quantity'];
        }
        return $count;
    }

    public function getTotal($userId) {
        $items = $this->getItems($userId);
        $total = 0;
        foreach ($items as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        return $total;
    }

    public function mergeCart($userId) {
        if (!$userId || empty($_SESSION['cart'])) return;

        foreach ($_SESSION['cart'] as $productId => $quantity) {
            $this->add($userId, $productId, $quantity);
        }
        
        // Clear session cart
        $_SESSION['cart'] = [];
    }
}
