<?php
require 'db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (!empty($username) && !empty($password)) {
        // Хешируем пароль
        $hash = password_hash($password, PASSWORD_DEFAULT);

        try {
            $stmt = $pdo->prepare("INSERT INTO users (username, password_hash) VALUES (?, ?)");
            $stmt->execute([$username, $hash]);
            
            header('Location: login.php');
            exit;
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $error = "Этот логин уже занят!";
            } else {
                $error = "Ошибка регистрации.";
            }
        }
    } else {
        $error = "Заполните все поля.";
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Регистрация</title>
</head>
<body>
    <h1>Регистрация</h1>
    
    <?php if($error): ?>
        <p style="color: red;"><?= $error ?></p>
    <?php endif; ?>
    
    <form method="POST">
        <label>Логин:</label><br>
        <input type="text" name="username" required><br><br>
        
        <label>Пароль:</label><br>
        <input type="password" name="password" required><br><br>
        
        <button type="submit">Зарегистрироваться</button>
    </form>
    
    <p><a href="login.php">Уже есть аккаунт? Войти</a></p>
    <p><a href="index.php">На главную</a></p>
</body>
</html>