<?php
// api/delete_item.php
require '../db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_POST['item_id'])) {
    echo json_encode(['success' => false, 'error' => 'Недостаточно данных']);
    exit;
}

$user_id = $_SESSION['user_id'];
$item_id = (int)$_POST['item_id'];

try {
    // Проверяем доступ и что пользователь является автором товара
    $stmtCheck = $pdo->prepare("
        SELECT 1 FROM items i 
        JOIN group_members gm ON i.group_id = gm.group_id 
        WHERE i.id = ? AND i.user_id = ? AND gm.user_id = ?
    ");
    $stmtCheck->execute([$item_id, $user_id, $user_id]);
    
    if (!$stmtCheck->fetch()) {
        echo json_encode(['success' => false, 'error' => 'Можно удалять только свои товары']);
        exit;
    }

    // Удаляем товар
    $stmt = $pdo->prepare("DELETE FROM items WHERE id = ?");
    $stmt->execute([$item_id]);
    
    echo json_encode(['success' => true]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Ошибка базы данных']);
}
?>