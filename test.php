<?php
require_once 'config/config.php';
echo 'OK: БД подключена. Версия MySQL: ' . $pdo->query('SELECT VERSION()')->fetchColumn();
?>
