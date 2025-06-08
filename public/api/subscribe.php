<?php
require_once '../../inc/header.php';
requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['creator_wallet']) || !isset($data['subscription_amount']) || !isset($data['transaction_signature'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required fields']);
    exit();
}

// Get creator by wallet address
$sql = "SELECT id FROM users WHERE wallet_address = :wallet_address AND is_creator = 1";
$creator = fetchOne($sql, [':wallet_address' => $data['creator_wallet']]);

if (!$creator) {
    http_response_code(404);
    echo json_encode(['error' => 'Creator not found']);
    exit();
}

// Check if already subscribed
$sql = "SELECT id FROM subscriptions WHERE user_id = :user_id AND creator_id = :creator_id";
$subscription = fetchOne($sql, [
    ':user_id' => $_SESSION['user_id'],
    ':creator_id' => $creator['id']
]);

if ($subscription) {
    http_response_code(400);
    echo json_encode(['error' => 'Already subscribed']);
    exit();
}

// Create subscription
$sql = "INSERT INTO subscriptions (user_id, creator_id, amount, transaction_signature, created_at) 
        VALUES (:user_id, :creator_id, :amount, :transaction_signature, NOW())";

try {
    executeQuery($sql, [
        ':user_id' => $_SESSION['user_id'],
        ':creator_id' => $creator['id'],
        ':amount' => $data['subscription_amount'],
        ':transaction_signature' => $data['transaction_signature']
    ]);
    
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to create subscription']);
} 