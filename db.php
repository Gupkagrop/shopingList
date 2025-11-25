<?php
// db.php
session_start(); // Запускаем сессию на всех страницах сразу

$host = '127.0.0.1:3306';//слева сверху в phpMyAdmin написано
$db   = 'gupkagrop';//Имя базы данных
$user = 'admin';//Логин
$pass = 'truse2008';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
?>