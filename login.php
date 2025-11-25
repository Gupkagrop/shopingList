<?php
require 'db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        header('Location: index.php');
        exit;
    } else {
        $error = "Неверный логин или пароль.";
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Вход</title>
</head>
<body>
    <h1>Вход</h1>
    
    <?php if($error): ?>
        <p style="color: red;"><?= $error ?></p>
    <?php endif; ?>
    
    <form method="POST">
        <label>Логин:</label><br>
        <input type="text" name="username" required><br><br>
        
        <label>Пароль:</label><br>
        <input type="password" name="password" required><br><br>
        
        <button type="submit">Войти</button>
    </form>
    
    <p><a href="register.php">Нет аккаунта? Создать</a></p>
    <p><a href="index.php">На главную</a></p>
</body>
</html>