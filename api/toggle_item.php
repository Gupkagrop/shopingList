<?php
// api/toggle_item.php
require '../db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_POST['item_id'])) {
    echo json_encode(['success' => false, 'error' => 'Недостаточно данных']);
    exit;
}

$user_id = $_SESSION['user_id'];
$item_id = (int)$_POST['item_id'];

try {
    // Проверяем, что пользователь имеет доступ к этому товару
    $stmtCheck = $pdo->prepare("
        SELECT 1 FROM items i 
        JOIN group_members gm ON i.group_id = gm.group_id 
        WHERE i.id = ? AND gm.user_id = ?
    ");
    $stmtCheck->execute([$item_id, $user_id]);
    
    if (!$stmtCheck->fetch()) {
        echo json_encode(['success' => false, 'error' => 'Нет доступа к товару']);
        exit;
    }

    // Переключаем статус
    $stmt = $pdo->prepare("UPDATE items SET is_bought = NOT is_bought WHERE id = ?");
    $stmt->execute([$item_id]);
    
    echo json_encode(['success' => true]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Ошибка базы данных']);
}
?>