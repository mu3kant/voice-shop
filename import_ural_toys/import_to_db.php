<?php
/**
 * import_to_db.php — полный импорт с удалением отсутствующих категорий и товаров
 * Самописный сайт, без привязки к CMS.
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('max_execution_time', 600);
ini_set('memory_limit', '512M');

require_once __DIR__ . '/../config/config.php';

if (!isset($pdo) || !$pdo instanceof PDO) {
    die('Ошибка: переменная $pdo не найдена в config.php или не является объектом PDO');
}

// Включаем строгий режим ошибок и начинаем транзакцию
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->beginTransaction();

function log_message($msg) {
    $logFile = __DIR__ . '/import_to_db.log';
    file_put_contents($logFile, date('Y-m-d H:i:s') . ' ' . $msg . "\n", FILE_APPEND | LOCK_EX);
}

log_message('=== ИМПОРТ ЗАПУЩЕН ===');

$source_xml_url = 'https://ural.toys/api/ural-toys.xml';

// Проверка доступности XML
$ch = curl_init($source_xml_url);
curl_setopt_array($ch, [
    CURLOPT_NOBODY     => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_TIMEOUT    => 90,
    CURLOPT_SSL_VERIFYPEER => true,
]);
curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code !== 200) {
    log_message("Ошибка: XML недоступен. HTTP: $http_code");
    $pdo->rollBack();
    die("XML недоступен (HTTP $http_code). Проверь URL.");
}
log_message("XML доступен (HTTP 200)");

// Уникальный хеш для этой сессии импорта
$importHash = md5(microtime());
log_message("Текущий импорт-хеш: $importHash");

// ---------------------------------------------------------
// ОДИН ПРОХОД: парсим категории и товары
// ---------------------------------------------------------
log_message('Начинаем парсинг XML...');

$reader = new XMLReader();
if (!$reader->open($source_xml_url)) {
    log_message('Не удалось открыть XML через XMLReader');
    $pdo->rollBack();
    die('Не удалось открыть XML');
}

$categoriesToInsert = [];
$productsToInsert   = [];

while ($reader->read()) {
    // КАТЕГОРИИ
    if ($reader->nodeType === XMLReader::ELEMENT && $reader->localName === 'group') {
        $id        = $reader->getAttribute('id');
        $parent_id = $reader->getAttribute('parent_id');

        $name = '';
        if ($reader->isEmptyElement === false) {
            $reader->read();
            while ($reader->nodeType !== XMLReader::END_ELEMENT || $reader->localName !== 'group') {
                if ($reader->nodeType === XMLReader::TEXT || $reader->nodeType === XMLReader::CDATA) {
                    $name .= $reader->value;
                }
                $reader->read();
            }
        }
        $name = trim($name);
        if (empty($name)) {
            $name = '(Без названия)';
        }

        $categoriesToInsert[] = [
            'id'        => $id,
            'parent_id' => $parent_id ?: null,
            'name'      => $name,
        ];
    }

    // ТОВАРЫ
    if ($reader->nodeType === XMLReader::ELEMENT && $reader->localName === 'product') {
        $art         = $reader->getAttribute('art');
        $title       = $reader->getAttribute('title');
        $zakup       = (float)$reader->getAttribute('price');
        $count       = (int)$reader->getAttribute('count');
        $category_id = (int)$reader->getAttribute('category_id');
        $text        = $reader->getAttribute('text');
        $shtrih      = $reader->getAttribute('shtrih');
        $article     = $reader->getAttribute('article');
        $art_full    = $reader->getAttribute('art_full');
        $inbox       = (int)$reader->getAttribute('inbox');
        $dlina       = (float)$reader->getAttribute('dlina');
        $shirina     = (float)$reader->getAttribute('shirina');
        $vysota      = (float)$reader->getAttribute('vysota');
        $volume      = (float)$reader->getAttribute('volume');
        $cancel      = (int)$reader->getAttribute('cancel');
        $kratno      = (int)$reader->getAttribute('kratno');
        $moroz       = (int)$reader->getAttribute('moroz');
        $brand_name  = $reader->getAttribute('brand_name');
        $country_name= $reader->getAttribute('country_name');

        $price = (int)round($zakup * 1.8);

        // Фото: максимум 8
        $imgs = [];
        $reader->read();
        while ($reader->nodeType !== XMLReader::END_ELEMENT || $reader->localName !== 'product') {
            if ($reader->nodeType === XMLReader::ELEMENT && $reader->localName === 'img') {
                $imgPath = $reader->getAttribute('ftp_file_img_path');
                if (!empty($imgPath) && count($imgs) < 8) {
                    $imgs[] = $imgPath;
                }
            }
            $reader->read();
        }

        $img_1 = $img_2 = $img_3 = $img_4 = $img_5 = $img_6 = $img_7 = $img_8 = null;
        foreach ($imgs as $i => $img) {
            $idx = $i + 1;
            ${'img_' . $idx} = $img;
        }

        $productsToInsert[] = [
            'art'         => $art,
            'title'       => $title,
            'zakup'       => $zakup,
            'price'       => $price,
            'count'       => $count,
            'category_id' => $category_id,
            'description' => $text,
            'inbox'       => $inbox,
            'dlina'       => $dlina,
            'shirina'    => $shirina,
            'vysota'      => $vysota,
            'volume'      => $volume,
            'cancel'      => $cancel,
            'kratno'      => $kratno,
            'shtrih'      => $shtrih,
            'moroz'       => $moroz,
            'brand_name'  => $brand_name,
            'country_name'=> $country_name,
            'img_1'       => $img_1,
            'img_2'       => $img_2,
            'img_3'       => $img_3,
            'img_4'       => $img_4,
            'img_5'       => $img_5,
            'img_6'       => $img_6,
            'img_7'       => $img_7,
            'img_8'       => $img_8,
        ];
    }
}
$reader->close();
unset($reader);

log_message("Парсинг завершён. Категорий: " . count($categoriesToInsert) . ", товаров: " . count($productsToInsert));

// ---------------------------------------------------------
// ЗАПИСЬ КАТЕГОРИЙ
// ---------------------------------------------------------
log_message('Запись категорий в БД...');

$stmtCat = $pdo->prepare("
    INSERT INTO categories (id, parent_id, name, import_hash)
    VALUES (:id, :parent_id, :name, :import_hash)
    ON DUPLICATE KEY UPDATE
        parent_id = VALUES(parent_id),
        name = VALUES(name),
        import_hash = VALUES(import_hash)
");

$catSuccess = 0;
foreach ($categoriesToInsert as $c) {
    try {
        $stmtCat->execute([
            ':id'          => $c['id'],
            ':parent_id'   => $c['parent_id'],
            ':name'        => $c['name'],
            ':import_hash' => $importHash,
        ]);
        $catSuccess++;
    } catch (PDOException $e) {
        log_message('Ошибка записи категории ID=' . $c['id'] . ': ' . $e->getMessage());
    }
}
log_message("Категории записаны: $catSuccess из " . count($categoriesToInsert));

// Удаляем категории, которых нет в текущем импорте
$delStmtCat = $pdo->prepare('DELETE FROM categories WHERE import_hash != :import_hash');
$delStmtCat->execute([':import_hash' => $importHash]);
$deletedCats = $delStmtCat->rowCount();
log_message("Удалено категорий, которых нет в XML: $deletedCats");

// ---------------------------------------------------------
// ЗАПИСЬ ТОВАРОВ
// ---------------------------------------------------------
log_message('Запись товаров в БД...');

$stmtProd = $pdo->prepare("
    INSERT INTO products (
        art, title, zakup, price, `count`, category_id, description,
        inbox, dlina, shirina, vysota, volume, cancel, kratno, shtrih, moroz,
        brand_name, country_name, img_1, img_2, img_3, img_4, img_5, img_6, img_7, img_8, import_hash
    ) VALUES (
        :art, :title, :zakup, :price, :count, :category_id, :description,
        :inbox, :dlina, :shirina, :vysota, :volume, :cancel, :kratno, :shtrih, :moroz,
        :brand_name, :country_name, :img_1, :img_2, :img_3, :img_4, :img_5, :img_6, :img_7, :img_8, :import_hash
    )
    ON DUPLICATE KEY UPDATE
        title = VALUES(title),
        zakup = VALUES(zakup),
        price = VALUES(price),
        `count` = VALUES(`count`),
        category_id = VALUES(category_id),
        description = VALUES(description),
        inbox = VALUES(inbox),
        dlina = VALUES(dlina),
        shirina = VALUES(shirina),
        vysota = VALUES(vysota),
        volume = VALUES(volume),
        cancel = VALUES(cancel),
        kratno = VALUES(kratno),
        shtrih = VALUES(shtrih),
        moroz = VALUES(moroz),
        brand_name = VALUES(brand_name),
        country_name = VALUES(country_name),
        img_1 = VALUES(img_1),
        img_2 = VALUES(img_2),
        img_3 = VALUES(img_3),
        img_4 = VALUES(img_4),
        img_5 = VALUES(img_5),
        img_6 = VALUES(img_6),
        img_7 = VALUES(img_7),
        img_8 = VALUES(img_8),
        import_hash = VALUES(import_hash)
");

$prodSuccess = 0;
foreach ($productsToInsert as $p) {
    try {
        $stmtProd->execute([
            ':art'          => $p['art'],
            ':title'        => $p['title'],
            ':zakup'        => $p['zakup'],
            ':price'        => $p['price'],
            ':count'        => $p['count'],
            ':category_id'  => $p['category_id'],
            ':description'  => $p['description'],
            ':inbox'        => $p['inbox'],
            ':dlina'        => $p['dlina'],
            ':shirina'     => $p['shirina'],
            ':vysota'       => $p['vysota'],
            ':volume'       => $p['volume'],
            ':cancel'       => $p['cancel'],
            ':kratno'       => $p['kratno'],
            ':shtrih'       => $p['shtrih'],
            ':moroz'        => $p['moroz'],
            ':brand_name'   => $p['brand_name'],
            ':country_name' => $p['country_name'],
            ':img_1'        => $p['img_1'],
            ':img_2'        => $p['img_2'],
            ':img_3'        => $p['img_3'],
            ':img_4'        => $p['img_4'],
            ':img_5'        => $p['img_5'],
            ':img_6'        => $p['img_6'],
            ':img_7'        => $p['img_7'],
            ':img_8'        => $p['img_8'],
            ':import_hash'  => $importHash,
        ]);
        $prodSuccess++;
    } catch (PDOException $e) {
        $key = !empty($p['art']) ? $p['art'] : ($p['art_full'] ?? 'unknown');
        log_message('Ошибка записи товара (art=' . $key . '): ' . $e->getMessage());
    }
}
log_message("Товары записаны: $prodSuccess из " . count($productsToInsert));

// Удаляем товары, которых нет в текущем импорте
$delStmtProd = $pdo->prepare('DELETE FROM products WHERE import_hash != :import_hash');
$delStmtProd->execute([':import_hash' => $importHash]);
$deletedProds = $delStmtProd->rowCount();
log_message("Удалено товаров, которых нет в XML: $deletedProds");

$pdo->commit();

echo "Готово!<br>";
echo "Записано категорий: $catSuccess, удалено: $deletedCats<br>";
echo "Записано товаров: $prodSuccess, удалено: $deletedProds<br>";
echo "Лог импорта: " . __DIR__ . "/import_to_db.log";
