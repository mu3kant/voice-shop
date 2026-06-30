<?php
// inc/cart_favorite_handler.php

// Инициализируем сессии для корзины и избранного.
// Этот файл должен быть подключен ПОСЛЕ session_start() в вашем конфиге.
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = []; // Корзина: [product_id => quantity]
}
if (!isset($_SESSION['favorites'])) {
    $_SESSION['favorites'] = []; // Избранное: [product_id1, product_id2, ...]
}

/**
 * Проверяет, находится ли товар в избранном.
 * @param int $productId
 * @return bool
 */
function is_product_favorite(int $productId): bool {
    return in_array($productId, $_SESSION['favorites'], true);
}

/**
 * Проверяет, находится ли товар в корзине.
 * @param int $productId
 * @return bool
 */
function is_product_in_cart(int $productId): bool {
    return array_key_exists($productId, $_SESSION['cart']);
}