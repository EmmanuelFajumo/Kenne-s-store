<?php
// Classes/Product.php

class Product {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getAll($categoryId = null, $search = null, $sort = null) {
        try {
            $query = "SELECT p.*, c.name as category_name 
                      FROM products p 
                      LEFT JOIN categories c ON p.category_id = c.id 
                      WHERE 1=1";
            $params = [];

            if ($categoryId !== null && $categoryId !== '') {
                $query .= " AND p.category_id = ?";
                $params[] = $categoryId;
            }

            if ($search !== null && trim($search) !== '') {
                $query .= " AND (p.name LIKE ? OR p.description LIKE ?)";
                $searchTerm = "%" . trim($search) . "%";
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }

            if ($sort === 'price_asc') {
                $query .= " ORDER BY p.price ASC";
            } elseif ($sort === 'price_desc') {
                $query .= " ORDER BY p.price DESC";
            } else {
                $query .= " ORDER BY p.created_at DESC";
            }

            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    public function getById($id) {
        try {
            $stmt = $this->db->prepare("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            return null;
        }
    }

    public function create($name, $description, $price, $image, $stock, $categoryId) {
        try {
            $stmt = $this->db->prepare("INSERT INTO products (name, description, price, image, stock, category_id) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $description, $price, $image, $stock, $categoryId]);
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function update($id, $name, $description, $price, $image, $stock, $categoryId) {
        try {
            if ($image !== null && $image !== '') {
                $stmt = $this->db->prepare("UPDATE products SET name = ?, description = ?, price = ?, image = ?, stock = ?, category_id = ? WHERE id = ?");
                $stmt->execute([$name, $description, $price, $image, $stock, $categoryId, $id]);
            } else {
                $stmt = $this->db->prepare("UPDATE products SET name = ?, description = ?, price = ?, stock = ?, category_id = ? WHERE id = ?");
                $stmt->execute([$name, $description, $price, $stock, $categoryId, $id]);
            }
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function delete($id) {
        try {
            $stmt = $this->db->prepare("DELETE FROM products WHERE id = ?");
            $stmt->execute([$id]);
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function updateStock($id, $quantity) {
        try {
            // Subtract quantity from stock (negative quantity to add back)
            $stmt = $this->db->prepare("UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?");
            return $stmt->execute([$quantity, $id, $quantity]);
        } catch (PDOException $e) {
            return false;
        }
    }

    // Category methods
    public function getAllCategories() {
        try {
            $stmt = $this->db->query("SELECT * FROM categories ORDER BY name ASC");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    public function createCategory($name) {
        try {
            $name = trim($name);
            if (empty($name)) return false;
            $stmt = $this->db->prepare("INSERT INTO categories (name) VALUES (?)");
            $stmt->execute([$name]);
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function deleteCategory($id) {
        try {
            $stmt = $this->db->prepare("DELETE FROM categories WHERE id = ?");
            $stmt->execute([$id]);
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }
}
