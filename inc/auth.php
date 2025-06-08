<?php
session_start();
require_once 'db.php';

function registerUser($username, $email, $password, $isCreator = false) {
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    $sql = "INSERT INTO users (username, email, password, is_creator, created_at) 
            VALUES (:username, :email, :password, :is_creator, NOW())";
    
    try {
        executeQuery($sql, [
            ':username' => $username,
            ':email' => $email,
            ':password' => $hashedPassword,
            ':is_creator' => $isCreator
        ]);
        return true;
    } catch(PDOException $e) {
        return false;
    }
}

function loginUser($email, $password) {
    $sql = "SELECT * FROM users WHERE email = :email";
    $user = fetchOne($sql, [':email' => $email]);
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['is_creator'] = $user['is_creator'];
        return true;
    }
    return false;
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    $sql = "SELECT * FROM users WHERE id = :id";
    return fetchOne($sql, [':id' => $_SESSION['user_id']]);
}

function logoutUser() {
    session_destroy();
    session_start();
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /login.php');
        exit();
    }
}

function requireCreator() {
    requireLogin();
    if (!$_SESSION['is_creator']) {
        header('Location: /dashboard.php');
        exit();
    }
} 