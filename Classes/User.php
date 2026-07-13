<?php
// Classes/User.php

class User {
    private $db;

    public function __construct($db) {
        $this->db = $db;
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function register($name, $email, $password, $role = 'customer') {
        $email = trim(strtolower($email));
        $name = trim($name);
        
        if (empty($name) || empty($email) || empty($password)) {
            return ["success" => false, "message" => "All fields are required."];
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ["success" => false, "message" => "Invalid email address format."];
        }

        try {
            // Check if email already exists
            $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                return ["success" => false, "message" => "Email address is already registered."];
            }

            // Insert new user
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $this->db->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $email, $hashedPassword, $role]);
            
            return ["success" => true, "message" => "Registration successful. You can now log in."];
        } catch (PDOException $e) {
            return ["success" => false, "message" => "Database error: " . $e->getMessage()];
        }
    }

    public function login($email, $password) {
        $email = trim(strtolower($email));
        
        if (empty($email) || empty($password)) {
            return ["success" => false, "message" => "Email and password are required."];
        }

        try {
            $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];
                
                return ["success" => true, "message" => "Login successful.", "user" => $user];
            } else {
                return ["success" => false, "message" => "Invalid email or password."];
            }
        } catch (PDOException $e) {
            return ["success" => false, "message" => "Database error: " . $e->getMessage()];
        }
    }

    public function logout() {
        unset($_SESSION['user_id']);
        unset($_SESSION['user_name']);
        unset($_SESSION['user_email']);
        unset($_SESSION['user_role']);
        return true;
    }

    public function getUserDetails($id) {
        try {
            $stmt = $this->db->prepare("SELECT id, name, email, role, created_at FROM users WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            return null;
        }
    }

    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    public function isAdmin() {
        return (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin');
    }

    public function getAllUsers() {
        try {
            $stmt = $this->db->query("SELECT id, name, email, role, created_at FROM users ORDER BY created_at DESC");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
}
