<?php
require 'db.php';

// ==========================================
// ЕСЛИ НЕ АВТОРИЗОВАН (ГОСТЬ)
// ==========================================
if (!isset($_SESSION['user_id'])) {
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Главная</title>
</head>
<body>
    <h1>Совместные покупки</h1>
    <p>Добро пожаловать! Это простой сервис для ведения списка покупок с друзьями.</p>
    <ul>
        <li><a href="login.php">Войти</a></li>
        <li><a href="register.php">Регистрация</a></li>
    </ul>
</body>
</html>
<?php
    exit;
}
?>

<?php
// ==========================================
// ЕСЛИ АВТОРИЗОВАН (ПРИЛОЖЕНИЕ)
// ==========================================

$user_id = $_SESSION['user_id'];
$message = '';

// --- ОБРАБОТКА ДЕЙСТВИЙ ---

// 1. Выход из аккаунта
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}

// 2. Создание группы
if (isset($_POST['create_group'])) {
    $name = trim($_POST['group_name']);
    if (!empty($name)) {
        $invite_code = bin2hex(random_bytes(3)); 
        
        $stmt = $pdo->prepare("INSERT INTO `groups` (name, invite_code, owner_id) VALUES (?, ?, ?)");
        $stmt->execute([$name, $invite_code, $user_id]);
        $new_group_id = $pdo->lastInsertId();

        $stmt = $pdo->prepare("INSERT INTO group_members (user_id, group_id) VALUES (?, ?)");
        $stmt->execute([$user_id, $new_group_id]);

        header("Location: index.php?group_id=$new_group_id");
        exit;
    }
}

// 3. Вступление в группу
if (isset($_POST['join_group'])) {
    $code = trim($_POST['invite_code']);
    $stmt = $pdo->prepare("SELECT id FROM `groups` WHERE invite_code = ?");
    $stmt->execute([$code]);
    $group = $stmt->fetch();

    if ($group) {
        $stmtCheck = $pdo->prepare("SELECT 1 FROM group_members WHERE user_id = ? AND group_id = ?");
        $stmtCheck->execute([$user_id, $group['id']]);
        
        if (!$stmtCheck->fetch()) {
            $stmt = $pdo->prepare("INSERT INTO group_members (user_id, group_id) VALUES (?, ?)");
            $stmt->execute([$user_id, $group['id']]);
            $message = "Вы успешно вступили в группу!";
            header("Location: index.php?group_id=" . $group['id']);
            exit;
        } else {
            $message = "Вы уже в этой группе.";
        }
    } else {
        $message = "Неверный код приглашения.";
    }
}

// 4. Удаление группы
if (isset($_POST['delete_group'])) {
    $target_group_id = (int)$_POST['target_group_id'];
    $stmt = $pdo->prepare("SELECT owner_id FROM `groups` WHERE id = ?");
    $stmt->execute([$target_group_id]);
    $owner = $stmt->fetchColumn();

    if ($owner == $user_id) {
        $stmt = $pdo->prepare("DELETE FROM `groups` WHERE id = ?");
        $stmt->execute([$target_group_id]);
        header("Location: index.php");
        exit;
    }
}

// 5. Выход из группы
if (isset($_POST['leave_group'])) {
    $target_group_id = (int)$_POST['target_group_id'];
    $stmt = $pdo->prepare("DELETE FROM group_members WHERE user_id = ? AND group_id = ?");
    $stmt->execute([$user_id, $target_group_id]);
    header("Location: index.php");
    exit;
}

// --- ПОЛУЧЕНИЕ ДАННЫХ ---

$stmt = $pdo->prepare("
    SELECT g.id, g.name 
    FROM `groups` g 
    JOIN group_members gm ON g.id = gm.group_id 
    WHERE gm.user_id = ?
    ORDER BY g.created_at DESC
");
$stmt->execute([$user_id]);
$my_groups = $stmt->fetchAll();

$current_group = null;
// Здесь мы больше не выбираем участников через PHP

if (isset($_GET['group_id'])) {
    $gid = (int)$_GET['group_id'];
    $stmt = $pdo->prepare("
        SELECT g.* 
        FROM `groups` g
        JOIN group_members gm ON g.id = gm.group_id
        WHERE g.id = ? AND gm.user_id = ?
    ");
    $stmt->execute([$gid, $user_id]);
    $current_group = $stmt->fetch();
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Мои покупки</title>
</head>
<body>

    <p>
        Привет, <b><?= htmlspecialchars($_SESSION['username']) ?></b> 
        (<a href="?logout=1">Выйти из аккаунта</a>)
    </p>
    <hr>

    <?php if($message): ?>
        <p style="background: #e0f7fa; padding: 5px; border: 1px solid #ccc;"><?= $message ?></p>
    <?php endif; ?>

    <table width="100%" border="0">
        <tr valign="top">
            <td width="30%">
                <h3>Мои группы</h3>
                <ul>
                    <?php foreach($my_groups as $grp): ?>
                        <li>
                            <a href="?group_id=<?= $grp['id'] ?>">
                                <?php if($current_group && $current_group['id'] == $grp['id']): ?>
                                    <b><?= htmlspecialchars($grp['name']) ?></b>
                                <?php else: ?>
                                    <?= htmlspecialchars($grp['name']) ?>
                                <?php endif; ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <h4>Создать новую</h4>
                <form method="POST">
                    <input type="text" name="group_name" placeholder="Название" required>
                    <button type="submit" name="create_group">+</button>
                </form>

                <h4>Вступить по коду</h4>
                <form method="POST">
                    <input type="text" name="invite_code" placeholder="Код приглашения" required>
                    <button type="submit" name="join_group">OK</button>
                </form>
            </td>

            <td width="70%" style="padding-left: 20px; border-left: 1px solid #ccc;">
                <?php if ($current_group): ?>
                    
                    <h2><?= htmlspecialchars($current_group['name']) ?></h2>
                    
                    <p>
                        Код приглашения: 
                        <strong style="background: #eee; padding: 2px 5px;"><?= $current_group['invite_code'] ?></strong>
                    </p>

                    <!-- ТЕПЕРЬ УЧАСТНИКИ ЗАГРУЖАЮТСЯ СЮДА ЧЕРЕЗ JS -->
                    <p>
                        <b>Участники:</b> 
                        <span id="members-list">Загрузка...</span>
                    </p>

                    <div style="margin-bottom: 20px;">
                        <?php if ($current_group['owner_id'] == $user_id): ?>
                            <form method="POST" onsubmit="return confirm('Удалить группу и все данные?');">
                                <input type="hidden" name="target_group_id" value="<?= $current_group['id'] ?>">
                                <button type="submit" name="delete_group" style="color: red;">Удалить группу</button>
                            </form>
                        <?php else: ?>
                            <form method="POST" onsubmit="return confirm('Выйти из группы?');">
                                <input type="hidden" name="target_group_id" value="<?= $current_group['id'] ?>">
                                <button type="submit" name="leave_group">Выйти из группы</button>
                            </form>
                        <?php endif; ?>
                    </div>
                    
                    <hr>

                    <h3>Список покупок:</h3>
                    <div>
                        <input type="text" id="newItemInput" placeholder="Что купить?">
                        <button type="button" id="addItemBtn">Добавить</button>
                    </div>
                    
                    <div id="shoppingList" style="margin-top: 20px;">
                        Загрузка списка...
                    </div>

                    <input type="hidden" id="activeGroupId" value="<?= $current_group['id'] ?>">

                <?php else: ?>
                    <p>← Выберите группу слева или создайте новую.</p>
                <?php endif; ?>
            </td>
        </tr>
    </table>

    <script src="assets/app.js"></script>

</body>
</html>