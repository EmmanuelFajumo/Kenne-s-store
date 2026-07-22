<?php
// Classes/Order.php

class Order {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function create($userId, $shippingDetails, $totalAmount, $cartItems, $paymentMethod) {
        try {
            $this->db->beginTransaction();

            // 1. Insert order
            $stmt = $this->db->prepare("
                INSERT INTO orders (
                    user_id, total_amount, shipping_name, shipping_address, 
                    shipping_city, shipping_zip, contact_number, status, payment_method
                ) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', ?)
            ");
            $stmt->execute([
                $userId,
                $totalAmount,
                $shippingDetails['name'],
                $shippingDetails['address'],
                $shippingDetails['city'],
                $shippingDetails['zip'],
                $shippingDetails['contact'],
                $paymentMethod
            ]);
            $orderId = $this->db->lastInsertId();

            // 2. Insert order items and deduct stock
            $itemStmt = $this->db->prepare("
                INSERT INTO order_items (order_id, product_id, quantity, price) 
                VALUES (?, ?, ?, ?)
            ");
            
            $stockStmt = $this->db->prepare("
                UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?
            ");

            foreach ($cartItems as $item) {
                // Insert order item
                $itemStmt->execute([
                    $orderId,
                    $item['product_id'],
                    $item['quantity'],
                    $item['price']
                ]);

                // Deduct stock
                $stockStmt->execute([
                    $item['quantity'],
                    $item['product_id'],
                    $item['quantity']
                ]);

                if ($stockStmt->rowCount() === 0) {
                    // Not enough stock, trigger rollback
                    throw new Exception("Product ID " . $item['product_id'] . " has insufficient stock.");
                }
            }

            $this->db->commit();
            return $orderId;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Order creation failed: " . $e->getMessage());
            return false;
        }
    }

    public function getById($orderId) {
        try {
            // Get order metadata
            $stmt = $this->db->prepare("
                SELECT o.*, u.name as customer_name, u.email as customer_email 
                FROM orders o 
                JOIN users u ON o.user_id = u.id 
                WHERE o.id = ?
            ");
            $stmt->execute([$orderId]);
            $order = $stmt->fetch();

            if (!$order) return null;

            // Get items
            $itemStmt = $this->db->prepare("
                SELECT oi.*, p.name as product_name, p.image as product_image 
                FROM order_items oi 
                JOIN products p ON oi.product_id = p.id 
                WHERE oi.order_id = ?
            ");
            $itemStmt->execute([$orderId]);
            $order['items'] = $itemStmt->fetchAll();

            return $order;
        } catch (PDOException $e) {
            return null;
        }
    }

    public function getByUser($userId) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
            $stmt->execute([$userId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    public function getAllOrders() {
        try {
            $stmt = $this->db->query("
                SELECT o.*, u.name as customer_name 
                FROM orders o 
                JOIN users u ON o.user_id = u.id 
                ORDER BY o.created_at DESC
            ");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    public function updateStatus($orderId, $status) {
        try {
            $stmt = $this->db->prepare("UPDATE orders SET status = ? WHERE id = ?");
            return $stmt->execute([$status, $orderId]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function updatePayment($orderId, $reference, $status = 'paid') {
        try {
            $stmt = $this->db->prepare("UPDATE orders SET status = ?, payment_reference = ? WHERE id = ?");
            return $stmt->execute([$status, $reference, $orderId]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function getSalesSummary() {
        try {
            // Metrics for admin dashboard
            $totalSales = $this->db->query("SELECT SUM(total_amount) FROM orders WHERE status != 'cancelled'")->fetchColumn() ?: 0.00;
            $totalOrders = $this->db->query("SELECT COUNT(id) FROM orders")->fetchColumn() ?: 0;
            $pendingOrders = $this->db->query("SELECT COUNT(id) FROM orders WHERE status = 'pending'")->fetchColumn() ?: 0;
            $paidOrders = $this->db->query("SELECT COUNT(id) FROM orders WHERE status = 'paid'")->fetchColumn() ?: 0;
            
            return [
                'total_sales' => $totalSales,
                'total_orders' => $totalOrders,
                'pending_orders' => $pendingOrders,
                'paid_orders' => $paidOrders
            ];
        } catch (PDOException $e) {
            return [
                'total_sales' => 0.00,
                'total_orders' => 0,
                'pending_orders' => 0,
                'paid_orders' => 0
            ];
        }
    }

    
    // Called right after Paystack initialization succeeds, so we can
    // look the order up by reference when Paystack redirects back.
    public function setPaymentReference($orderId, $reference) {
        try {
            $stmt = $this->db->prepare("UPDATE orders SET payment_reference = ? WHERE id = ?");
            return $stmt->execute([$reference, $orderId]);
        } catch (PDOException $e) {
            return false;
        }
    }

    // Puts stock back if a card payment never completes.
    // Mirrors the deduction logic in create().
    public function restoreStock($orderId) {
        try {
            $stmt = $this->db->prepare("SELECT product_id, quantity FROM order_items WHERE order_id = ?");
            $stmt->execute([$orderId]);
            $items = $stmt->fetchAll();

            $restoreStmt = $this->db->prepare("UPDATE products SET stock = stock + ? WHERE id = ?");
            foreach ($items as $item) {
                $restoreStmt->execute([$item['quantity'], $item['product_id']]);
            }
            return true;
        } catch (PDOException $e) {
            error_log("Stock restore failed for order {$orderId}: " . $e->getMessage());
            return false;
        }
    }



}
