<?php
// config/config_catalog.php

if (!isset($pdo)) {
    require_once DIR . '/config.php';
}

// Инициализация сессии для корзины/избранного (если ещё не запущена)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
if (!isset($_SESSION['favorites'])) $_SESSION['favorites'] = [];

// -----------------------------------------
// 1. ПАРАМЕТРЫ И ЗАГРУЗКА ДАННЫХ
// -----------------------------------------

$currentCatId = isset($_GET['cat']) ? (int)$_GET['cat'] : 0;
$page         = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit        = 20;
$offset       = ($page - 1) * $limit;

// Загружаем категории
$stmtCats = $pdo->query("SELECT id, parent_id, name FROM categories ORDER BY parent_id, name");
$allCategories = $stmtCats->fetchAll(PDO::FETCH_ASSOC);

// -----------------------------------------
// 2. ВСПОМОГАТЕЛЬНЫЕ ФУНКЦИИ
// -----------------------------------------
function getCategoryById(array $items, int $id): ?array
{
    foreach ($items as $item) {
        if ($item['id'] === $id) {
            return $item;
        }
    }
    return null;
}

function buildCategoryPath(array $items, int $catId): array
{
    $path = [];
    $curId = $catId;
    while ($curId > 0) {
        $cat = getCategoryById($items, $curId);
        if (!$cat) break;
        array_unshift($path, $cat);
        $curId = (int)($cat['parent_id'] ?? 0);
    }
    return $path;
}

function getTopLevelCategories(array $items): array
{
    $top = [];
    foreach ($items as $item) {
        $pid = (int)($item['parent_id'] ?? 0);
        if ($pid === 0) {
            $top[] = $item;
        }
    }
    return $top;
}

function getChildCategoryIds(array $items, int $parentId, array &$result = []): array
{
    foreach ($items as $item) {
        if ((int)($item['parent_id'] ?? 0) === $parentId) {
            $result[] = $item['id'];
            getChildCategoryIds($items, $item['id'], $result);
        }
    }
    return $result;
}

// -----------------------------------------
// 3. ЛОГИКА ТЕКУЩЕЙ СТРАНИЦЫ
// -----------------------------------------

$currentCatRow = getCategoryById($allCategories, $currentCatId);
$currentCatName = $currentCatRow ? $currentCatRow['name'] : 'Все товары';

$categoryPath = $currentCatId > 0 ? buildCategoryPath($allCategories, $currentCatId) : [];

$targetCategoryIds = [];
if ($currentCatId > 0) {
    $targetCategoryIds[] = $currentCatId;
    getChildCategoryIds($allCategories, $currentCatId, $targetCategoryIds);
} else {
    foreach ($allCategories as $c) {
        $targetCategoryIds[] = $c['id'];
    }
}
$targetCategoryIds = array_unique($targetCategoryIds);

// Подсчёт товаров
$totalProducts = 0;
if (!empty($targetCategoryIds)) {
    $placeholders = implode(',', array_fill(0, count($targetCategoryIds), '?'));
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE category_id IN ($placeholders)");
    $countStmt->execute($targetCategoryIds);
    $totalProducts = (int)$countStmt->fetchColumn();
}

if ($totalProducts === 0) {
    $page = 1;
    $totalPages = 1;
} else {
    $totalPages = max(1, ceil($totalProducts / $limit));
}

// Выборка товаров
$products = [];
if ($totalProducts > 0 && !empty($targetCategoryIds)) {
    $placeholders = implode(',', array_fill(0, count($targetCategoryIds), '?'));
    $sql = "SELECT 
                id, title, article, price, 
                dlina, shirina, vysota, 
                brand_name, country_name, 
                art_full, moroz, description,
                img_1, img_2, img_3, img_4, img_5, img_6, img_7, img_8 
            FROM products 
            WHERE category_id IN ($placeholders)
            ORDER BY title ASC
            LIMIT ? OFFSET ?";
    $prodStmt = $pdo->prepare($sql);
    $params = array_merge($targetCategoryIds, [$limit, $offset]);
    $prodStmt->execute($params);
    $products = $prodStmt->fetchAll(PDO::FETCH_ASSOC);
}

// Обработка данных товаров (картинки, избранное, габариты)
foreach ($products as &$p) {
    // Собираем все картинки в массив
    $p['allImages'] = [];
    for ($i = 1; $i <= 8; $i++) {
        $field = 'img_' . $i;
        if (!empty($p[$field])) {
            $p['allImages'][] = $p[$field];
        }
    }

    // Главное фото
    $p['mainImage'] = !empty($p['allImages'][0]) ? $p['allImages'][0] : '';

    // Габариты строка (если нужно)
    $dims = [];
    if (!empty($p['dlina'])) $dims[] = $p['dlina'];
    if (!empty($p['shirina'])) $dims[] = $p['shirina'];
    if (!empty($p['vysota'])) $dims[] = $p['vysota'];
    $p['dimensionsStr'] = !empty($dims) ? implode('×', $dims) : '';

    // Бренд и страна строка
    $parts = [];
    if (!empty($p['brand_name'])) $parts[] = $p['brand_name'];
    if (!empty($p['country_name'])) $parts[] = '(' . $p['country_name'] . ')';
    $p['brandCountryStr'] = !empty($parts) ? implode(' ', $parts) : '';

    // Избранное (на основе сессии)
    $p['is_favorite'] = in_array((int)$p['id'], $_SESSION['favorites'] ?? []);
}
unset($p);
