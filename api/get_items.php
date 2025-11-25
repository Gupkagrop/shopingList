<?php
// api/get_items.php
require '../db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_GET['group_id'])) {
    echo json_encode([]);
    exit;
}

$user_id = $_SESSION['user_id'];
$group_id = (int)$_GET['group_id'];

try {
    // Проверка доступа к группе
    $stmtCheck = $pdo->prepare("SELECT 1 FROM group_members WHERE user_id = ? AND group_id = ?");
    $stmtCheck->execute([$user_id, $group_id]);
    
    if (!$stmtCheck->fetch()) {
        echo json_encode([]);
        exit;
    }

    // Получаем товары с информацией о пользователе
    $stmt = $pdo->prepare("
        SELECT 
            i.id,
            i.name,
            i.is_bought,
            i.created_at,
            u.username as added_by
        FROM items i
        JOIN users u ON i.user_id = u.id
        WHERE i.group_id = ?
        ORDER BY i.is_bought ASC, i.created_at DESC
    ");
    $stmt->execute([$group_id]);
    $items = $stmt->fetchAll();

    echo json_encode($items);

} catch (PDOException $e) {
    echo json_encode([]);
}
?>