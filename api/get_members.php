<?php
// api/get_members.php
require '../db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_GET['group_id'])) {
    echo json_encode([]);
    exit;
}

$user_id = $_SESSION['user_id'];
$group_id = (int)$_GET['group_id'];

try {
    // 1. Проверка безопасности: состоит ли текущий юзер в этой группе?
    $stmtCheck = $pdo->prepare("SELECT 1 FROM group_members WHERE user_id = ? AND group_id = ?");
    $stmtCheck->execute([$user_id, $group_id]);
    
    if (!$stmtCheck->fetch()) {
        // Если не состоит — отдаем пустой список (или ошибку)
        echo json_encode([]);
        exit;
    }

    // 2. Получаем список имен участников
    $stmt = $pdo->prepare("
        SELECT u.username 
        FROM users u 
        JOIN group_members gm ON u.id = gm.user_id 
        WHERE gm.group_id = ?
        ORDER BY u.username ASC
    ");
    $stmt->execute([$group_id]);
    $members = $stmt->fetchAll(PDO::FETCH_COLUMN); // Возвращает плоский массив ['Vasya', 'Petya']

    echo json_encode($members);

} catch (PDOException $e) {
    echo json_encode([]);
}