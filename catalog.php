<?php
session_start();
require_once 'config/config.php';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Каталог товаров</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/catalog.css">
    <link rel="stylesheet" href="/css/tovar_cat_popup.css">
</head>
<body class="catalog-page">
    <?php include 'header.php'; ?>

    <main class="page-content">
        <?php include 'inc/catalog_inc.php'; ?>
    </main>

    <?php include 'footer.php'; ?>

    <!-- Обработчики корзины и избранного -->
    <script src="/js/cart-favorite.js" defer></script>
    <!-- Каталог (переключатель, попап, прочее) -->
    <script src="/js/catalog.js" defer></script>
    <script src="/js/tovar_cat_popup.js"></script>
</body>
</html>
