<?php
/**
 * config/config.php — подключение к БД
 * 
 * .env лежит вне веб-корня: /var/www/u3514800/.env
 */

// Путь к .env: поднимаемся на 3 уровня вверх от папки config
$envPath = __DIR__ . '/../../../.env';

if (file_exists($envPath)) {
    foreach (file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        // Пропускаем комментарии
        if (strpos(trim($line), '#') === 0) continue;
        
        if (false !== strpos($line, '=')) {
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value, "\"'");
            putenv("$name=$value");
        }
    }
} else {
    // Если вдруг файл не найден там, пробуем поискать в корне сайта (для локальной разработки)
    $envPathFallback = __DIR__ . '/../.env';
    if (file_exists($envPathFallback)) {
        foreach (file($envPathFallback, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            if (strpos(trim($line), '#') === 0) continue;
            if (false !== strpos($line, '=')) {
                list($name, $value) = explode('=', $line, 2);
                $name = trim($name);
                $value = trim($value, "\"'");
                putenv("$name=$value");
            }
        }
    }
}

$host = getenv('DB_HOST') ?: 'localhost';
$name = getenv('DB_NAME') ?: '';
$user = getenv('DB_USER') ?: '';
$pass = getenv('DB_PASS') ?: '';

if (empty($name) || empty($user)) {
    throw new RuntimeException('Не настроены переменные окружения для БД. Проверьте наличие .env и пути к нему.');
}

$dsn = "mysql:host=$host;dbname=$name;charset=utf8mb4";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    error_log('DB Connection Error: ' . $e->getMessage());
    throw new RuntimeException('Ошибка соединения с базой данных.');
}
