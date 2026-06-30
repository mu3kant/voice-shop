<?php
// php/cart-favorite-handler.php

session_start();

header('Content-Type: application/json; charset=utf-8');

// Инициализация сессий
if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
if (!isset($_SESSION['favorites'])) $_SESSION['favorites'] = [];

$action = $_POST['action'] ?? ($_GET['action'] ?? '');
$productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : (isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0);

if ($action === 'toggle_favorite' && $productId > 0) {
    $key = array_search($productId, $_SESSION['favorites']);
    if ($key !== false) {
        unset($_SESSION['favorites'][$key]);
        $_SESSION['favorites'] = array_values($_SESSION['favorites']); // переиндексация
        $result = ['success' => true, 'is_favorite' => false];
    } else {
        $_SESSION['favorites'][] = $productId;
        $result = ['success' => true, 'is_favorite' => true];
    }
    echo json_encode($result);
    exit;
}

if ($action === 'add_to_cart' && $productId > 0) {
    // Добавляем в корзину (простая реализация: ID товара + количество)
    if (!isset($_SESSION['cart'][$productId])) {
        $_SESSION['cart'][$productId] = ['qty' => 1];
    } else {
        $_SESSION['cart'][$productId]['qty']++;
    }
    $result = [
        'success' => true,
        'totalItems' => array_sum(array_column($_SESSION['cart'], 'qty')),
    ];
    echo json_encode($result);
    exit;
}

if ($action === 'get_cart_count') {
    $count = 0;
    foreach ($_SESSION['cart'] as $item) {
        $count += $item['qty'] ?? 0;
    }
    echo json_encode(['count' => $count]);
    exit;
}

// По умолчанию
echo json_encode(['success' => false, 'message' => 'Неизвестное действие']);
exit;
