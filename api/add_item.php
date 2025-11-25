<?php
// api/add_item.php
require '../db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_POST['group_id']) || !isset($_POST['item_name'])) {
    echo json_encode(['success' => false, 'error' => 'Недостаточно данных']);
    exit;
}

$user_id = $_SESSION['user_id'];
$group_id = (int)$_POST['group_id'];
$item_name = trim($_POST['item_name']);

if (empty($item_name)) {
    echo json_encode(['success' => false, 'error' => 'Название товара не может быть пустым']);
    exit;
}

try {
    // Проверка доступа к группе
    $stmtCheck = $pdo->prepare("SELECT 1 FROM group_members WHERE user_id = ? AND group_id = ?");
    $stmtCheck->execute([$user_id, $group_id]);
    
    if (!$stmtCheck->fetch()) {
        echo json_encode(['success' => false, 'error' => 'Нет доступа к группе']);
        exit;
    }

    // Добавляем товар
    $stmt = $pdo->prepare("INSERT INTO items (group_id, user_id, name, is_bought) VALUES (?, ?, ?, 0)");
    $stmt->execute([$group_id, $user_id, $item_name]);
    
    echo json_encode(['success' => true]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Ошибка базы данных']);
}
?>