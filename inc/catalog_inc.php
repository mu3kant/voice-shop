<?php
require_once DIR . '/../config/config_catalog.php';
require_once DIR . '/tovar_cat_popup.php';
?>

<div data-page="catalog" class="catalog-wrapper">
    <!-- Хлебные крошки -->
    <nav class="breadcrumbs" aria-label="Хлебные крошки">
        <a href="catalog.php" class="crumb-item">
            <span class="crumb-text">Каталог</span>
        </a>

        <?php if (!empty($categoryPath)): ?>
            <?php foreach ($categoryPath as $cat): ?>
                <span class="crumb-separator" aria-hidden="true">/</span>
                <a href="catalog.php?cat=<?= htmlspecialchars((string)$cat['id'], ENT_QUOTES) ?>" class="crumb-item">
                    <span class="crumb-text"><?= htmlspecialchars($cat['name'], ENT_QUOTES) ?></span>
                </a>
            <?php endforeach; ?>
        <?php else: ?>
            <span class="crumb-separator" aria-hidden="true">/</span>
            <span class="crumb-item crumb-item-current">
                <span class="crumb-text">Все товары</span>
            </span>
        <?php endif; ?>
    </nav>

    <!-- Фильтры: кнопка категорий + переключатель вида -->
    <div class="catalog-filters-row">
        <button type="button" 
                class="btn-categories-toggle" 
                aria-haspopup="dialog" 
                aria-controls="categories-popup"
                aria-expanded="false"
                id="btnCategoriesToggle"
                title="Показать категории">
            <svg class="icon-filter" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="6" cy="6" r="3"></circle>
                <circle cx="6" cy="18" r="3"></circle>
                <circle cx="18" cy="6" r="3"></circle>
                <circle cx="18" cy="18" r="3"></circle>
            </svg>
            <span class="btn-text" id="btnCategoriesText">
                <?= htmlspecialchars($currentCatName, ENT_QUOTES) ?>
            </span>
        </button>

        <div class="view-switcher">
            <button type="button" class="view-btn active" data-view="grid">
                <svg class="icon-grid" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="3" width="7" height="7"></rect>
                    <rect x="14" y="3" width="7" height="7"></rect>
                    <rect x="3" y="14" width="7" height="7"></rect>
                    <rect x="14" y="14" width="7" height="7"></rect>
                </svg>
                Плитка
            </button>
            <button type="button" class="view-btn" data-view="list">
                <svg class="icon-list" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="8" y1="6" x2="21" y2="6"></line>
                    <line x1="8" y1="12" x2="21" y2="12"></line>
                    <line x1="8" y1="18" x2="21" y2="18"></line>
                    <polyline points="4 6 8 6"></polyline>
                    <polyline points="4 12 8 12"></polyline>
                    <polyline points="4 18 8 18"></polyline>
                </svg>
                Список
            </button>
        </div>
    </div>

    <!-- Popup: дерево категорий -->
    <div id="categories-popup" class="categories-popup" role="dialog" aria-modal="true" aria-labelledby="popupTitle">
        <div class="popup-overlay" tabindex="-1"></div>
        <aside class="popup-sidebar">
            <header class="popup-header">
                <h2 id="popupTitle" class="popup-title">Категории</h2>
                <button type="button" class="popup-close" aria-label="Закрыть">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </header>
            <div class="categories-scroll-area" role="region" aria-label="Список категорий">
                <ul class="categories-tree" role="listbox" aria-label="Дерево категорий">
                    <!-- Пункт «Все товары» -->
                    <li class="cat-node">
                        <a href="catalog.php" 
                           class="cat-row <?= ($currentCatId == 0 ? 'current-category' : '') ?>"
                           data-id="0">
                            <span class="cat-link">Все товары</span>
                            <span class="cat-toggle-wrapper" style="visibility: hidden;"></span>
                        </a>
                    </li>

                    <?php foreach ($allCategories as $cat): ?>
                        <?php
                        $pid = (int)($cat['parent_id'] ?? 0);
                        if ($pid !== 0) continue; // только корневые

                        $catId = (int)$cat['id'];
                        $isCurrent = ($currentCatId === $catId);

                        // Проверка: есть ли у категории дети
                        $hasChildren = false;
                        foreach ($allCategories as $check) {
                            if ((int)($check['parent_id'] ?? 0) === $catId) {
                                $hasChildren = true;
                                break;
                            }
                        }
                        ?>
                        
                        <li class="cat-node <?= $isCurrent ? 'open' : '' ?>">
                            <?php if ($hasChildren): ?>
                                <a href="catalog.php?cat=<?= $catId ?>" 
                                   class="cat-row"
                                   data-id="<?= $catId ?>"
                                   aria-expanded="<?= $isCurrent ? 'true' : 'false' ?>"
                                   aria-controls="cat-<?= $catId ?>-children">
                                    <span class="cat-link <?= $isCurrent ? 'current-category' : '' ?>">
                                        <?= htmlspecialchars($cat['name'], ENT_QUOTES) ?>
                                    </span>
                                    <span class="cat-toggle-wrapper">
                                        <svg class="cat-toggle-icon" viewBox="0 0 24 24">
                                            <path d="M12 5l-6 6 1.41 1.41L12 7.83l4.59 4.58L18 11z"/>
                                        </svg>
                                    </span>
                                </a>

                                <ul id="cat-<?= $catId ?>-children" class="cat-children">
                                    <?php foreach ($allCategories as $sub): ?>
                                        <?php
                                        $subPid = (int)($sub['parent_id'] ?? 0);
                                        if ($subPid !== $catId) continue;

                                        $subId = (int)$sub['id'];
                                        $subIsCurrent = ($currentCatId === $subId);
                                        ?>
                                        <li class="cat-node">
                                            <a href="catalog.php?cat=<?= $subId ?>" 
                                               class="cat-row <?= $subIsCurrent ? 'current-category' : '' ?>"
                                               data-id="<?= $subId ?>">
                                                <span class="cat-link"><?= htmlspecialchars($sub['name'], ENT_QUOTES) ?></span>
                                                <span class="cat-toggle-wrapper" style="visibility: hidden;"></span>
                                            </a>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                <!-- Категория без детей -->
                                <a href="catalog.php?cat=<?= $catId ?>" 
                                   class="cat-row <?= $isCurrent ? 'current-category' : '' ?>"
                                   data-id="<?= $catId ?>">
                                    <span class="cat-link"><?= htmlspecialchars($cat['name'], ENT_QUOTES) ?></span>
                                    <span class="cat-toggle-wrapper" style="visibility: hidden;"></span>
                                </a>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </aside>
    </div>

    <main class="catalog-main">
        <header class="catalog-header">
            <h1 class="catalog-title">Каталог: <?= htmlspecialchars($currentCatName, ENT_QUOTES) ?></h1>
            <p class="catalog-count">Товаров: <?= (int)$totalProducts ?></p>
        </header>

        <div class="products-container" data-view="grid" id="productsContainer">
            <?php if (empty($products)): ?>
                <p class="no-products">В этой категории пока нет товаров.</p>
            <?php else: ?>
                <!-- Сетка (Grid) -->
                <div class="products-grid" id="productsGrid">
                    <?php foreach ($products as $p): ?>
                        <article class="product-card" data-id="<?= (int)$p['id'] ?>">
                            <!-- Кнопка «Избранное» -->
                            <button type="button"
                                    class="card-favorite-btn <?= !empty($p['is_favorite']) ? 'favorite-active' : '' ?>"
                                    data-product-id="<?= (int)$p['id'] ?>"
                                    aria-label="Добавить в избранное">
                                <svg viewBox="0 0 24 24">
                                    <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                                </svg>
                            </button>
                            
                            <a href="#" 
                               class="card-image-link" 
                               data-product-id="<?= (int)$p['id'] ?>"
                               data-title="<?= htmlspecialchars($p['title'], ENT_QUOTES) ?>"
                               data-article="<?= htmlspecialchars($p['article'] ?? '', ENT_QUOTES) ?>"
                               data-art-full="<?= htmlspecialchars($p['art_full'] ?? '', ENT_QUOTES) ?>"
                               data-price="<?= (float)$p['price'] ?>"
                               data-dlina="<?= htmlspecialchars((string)($p['dlina'] ?? ''), ENT_QUOTES) ?>"
                               data-shirina="<?= htmlspecialchars((string)($p['shirina'] ?? ''), ENT_QUOTES) ?>"
                               data-vysota="<?= htmlspecialchars((string)($p['vysota'] ?? ''), ENT_QUOTES) ?>"
                               data-brand-name="<?= htmlspecialchars($p['brand_name'] ?? '', ENT_QUOTES) ?>"
                               data-country-name="<?= htmlspecialchars($p['country_name'] ?? '', ENT_QUOTES) ?>"
                               data-moroz="<?= (int)($p['moroz'] ?? 0) ?>"
                               data-description="<?= htmlspecialchars($p['description'] ?? '', ENT_QUOTES) ?>"
                               data-images='<?= json_encode($p['allImages'], JSON_UNESCAPED_SLASHES) ?>'
                               aria-label="Фото товара: <?= htmlspecialchars($p['title'], ENT_QUOTES) ?>">
                                <div class="card-image-area">
                                    <?php if ($p['mainImage']): ?>
                                        <img src="<?= htmlspecialchars($p['mainImage'], ENT_QUOTES) ?>"
                                             alt="<?= htmlspecialchars($p['title'], ENT_QUOTES) ?>"
                                             loading="lazy"
                                             class="card-image-img">
                                    <?php else: ?>
                                        <div class="no-img-placeholder">Нет фото</div>
                                    <?php endif; ?>

                                    <?php if (!empty($p['is_new']) && $p['is_new']): ?>
                                        <span class="badge-new">Новинка</span>
                                    <?php endif; ?>
                                </div>
                            </a>
                            
                            <div class="card-info">
                                <div class="card-top">
                                    <?php if (!empty($p['article'])): ?>
                                        <span class="sku">Арт: <?= htmlspecialchars($p['article'], ENT_QUOTES) ?></span>
                                    <?php endif; ?>
                                    
                                                                        <!-- Ссылка по названию тоже открывает попап (с теми же данными) -->
                                    <a href="#" 
                                       class="product-title-link"
                                       data-product-id="<?= (int)$p['id'] ?>"
                                       data-title="<?= htmlspecialchars($p['title'], ENT_QUOTES) ?>"
                                       data-article="<?= htmlspecialchars($p['article'] ?? '', ENT_QUOTES) ?>"
                                       data-art-full="<?= htmlspecialchars($p['art_full'] ?? '', ENT_QUOTES) ?>"
                                       data-price="<?= (float)$p['price'] ?>"
                                       data-dlina="<?= htmlspecialchars((string)($p['dlina'] ?? ''), ENT_QUOTES) ?>"
                                       data-shirina="<?= htmlspecialchars((string)($p['shirina'] ?? ''), ENT_QUOTES) ?>"
                                       data-vysota="<?= htmlspecialchars((string)($p['vysota'] ?? ''), ENT_QUOTES) ?>"
                                       data-brand-name="<?= htmlspecialchars($p['brand_name'] ?? '', ENT_QUOTES) ?>"
                                       data-country-name="<?= htmlspecialchars($p['country_name'] ?? '', ENT_QUOTES) ?>"
                                       data-moroz="<?= (int)($p['moroz'] ?? 0) ?>"
                                       data-description="<?= htmlspecialchars($p['description'] ?? '', ENT_QUOTES) ?>"
                                       data-images='<?= json_encode($p['allImages'], JSON_UNESCAPED_SLASHES) ?>'
                                       title="<?= htmlspecialchars($p['title'], ENT_QUOTES) ?>">
                                        <h3 class="product-title"><?= htmlspecialchars($p['title'], ENT_QUOTES) ?></h3>
                                    </a>
                                </div>

                                <div class="card-price-block">
                                    <span class="price"><?= number_format($p['price'], 0, '.', ' ') ?> ₽</span>
                                    <?php if ($p['dimensionsStr']): ?>
                                        <div class="dimensions">Габариты: <?= $p['dimensionsStr'] ?></div>
                                    <?php endif; ?>
                                    <?php if ($p['brandCountryStr']): ?>
                                        <div class="brand-country"><?= $p['brandCountryStr'] ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="card-actions">
                                <button type="button" 
                                        class="btn-cart" 
                                        data-product-id="<?= (int)$p['id'] ?>"
                                        title="В корзину: <?= htmlspecialchars($p['title'], ENT_QUOTES) ?>">
                                    В корзину
                                </button>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>

                <!-- Список (List) -->
                <div class="products-list" id="productsList">
                    <?php foreach ($products as $p): ?>
                        <article class="list-item" data-id="<?= (int)$p['id'] ?>">
                            <!-- Кнопка «Избранное» -->
                            <button type="button"
                                    class="card-favorite-btn <?= !empty($p['is_favorite']) ? 'favorite-active' : '' ?>"
                                    data-product-id="<?= (int)$p['id'] ?>"
                                    aria-label="Добавить в избранное">
                                <svg viewBox="0 0 24 24">
                                    <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                                </svg>
                            </button>

                            <div class="list-image-area">
                                <a href="#" 
                                   class="list-image-link"
                                   data-product-id="<?= (int)$p['id'] ?>"
                                   data-title="<?= htmlspecialchars($p['title'], ENT_QUOTES) ?>"
                                   data-article="<?= htmlspecialchars($p['article'] ?? '', ENT_QUOTES) ?>"
                                   data-art-full="<?= htmlspecialchars($p['art_full'] ?? '', ENT_QUOTES) ?>"
                                   data-price="<?= (float)$p['price'] ?>"
                                   data-dlina="<?= htmlspecialchars((string)($p['dlina'] ?? ''), ENT_QUOTES) ?>"
                                   data-shirina="<?= htmlspecialchars((string)($p['shirina'] ?? ''), ENT_QUOTES) ?>"
                                   data-vysota="<?= htmlspecialchars((string)($p['vysota'] ?? ''), ENT_QUOTES) ?>"
                                   data-brand-name="<?= htmlspecialchars($p['brand_name'] ?? '', ENT_QUOTES) ?>"
                                   data-country-name="<?= htmlspecialchars($p['country_name'] ?? '', ENT_QUOTES) ?>"
                                   data-moroz="<?= (int)($p['moroz'] ?? 0) ?>"
                                   data-description="<?= htmlspecialchars($p['description'] ?? '', ENT_QUOTES) ?>"
                                   data-images='<?= json_encode($p['allImages'], JSON_UNESCAPED_SLASHES) ?>'
                                   aria-label="Фото товара">
                                    <?php if ($p['mainImage']): ?>
                                        <img src="<?= htmlspecialchars($p['mainImage'], ENT_QUOTES) ?>"
                                             alt="<?= htmlspecialchars($p['title'], ENT_QUOTES) ?>"
                                             loading="lazy"
                                             class="list-image-img">
                                    <?php else: ?>
                                        <div class="no-img-placeholder">Нет фото</div>
                                    <?php endif; ?>
                                </a>
                            </div>

                            <div class="list-info">
                                <h3 class="list-title">
                                    <a href="#" 
                                       class="list-title-link"
                                       data-product-id="<?= (int)$p['id'] ?>"
                                       data-title="<?= htmlspecialchars($p['title'], ENT_QUOTES) ?>"
                                       data-article="<?= htmlspecialchars($p['article'] ?? '', ENT_QUOTES) ?>"
                                       data-art-full="<?= htmlspecialchars($p['art_full'] ?? '', ENT_QUOTES) ?>"
                                       data-price="<?= (float)$p['price'] ?>"
                                       data-dlina="<?= htmlspecialchars((string)($p['dlina'] ?? ''), ENT_QUOTES) ?>"
                                       data-shirina="<?= htmlspecialchars((string)($p['shirina'] ?? ''), ENT_QUOTES) ?>"
                                       data-vysota="<?= htmlspecialchars((string)($p['vysota'] ?? ''), ENT_QUOTES) ?>"
                                       data-brand-name="<?= htmlspecialchars($p['brand_name'] ?? '', ENT_QUOTES) ?>"
                                       data-country-name="<?= htmlspecialchars($p['country_name'] ?? '', ENT_QUOTES) ?>"
                                       data-moroz="<?= (int)($p['moroz'] ?? 0) ?>"
                                       data-description="<?= htmlspecialchars($p['description'] ?? '', ENT_QUOTES) ?>"
                                       title="<?= htmlspecialchars($p['title'], ENT_QUOTES) ?>">
                                        <?= htmlspecialchars($p['title'], ENT_QUOTES) ?>
                                    </a>
                                </h3>

                                <?php if (!empty($p['article'])): ?>
                                    <span class="list-sku">Арт: <?= htmlspecialchars($p['article'], ENT_QUOTES) ?></span>
                                <?php endif; ?>

                                <div class="list-meta-block">
                                    <span class="list-price"><?= number_format($p['price'], 0, '.', ' ') ?> ₽</span>

                                    <?php if ($p['dimensionsStr']): ?>
                                        <div class="dimensions"><?= $p['dimensionsStr'] ?></div>
                                    <?php endif; ?>
                                    <?php if ($p['brandCountryStr']): ?>
                                        <div class="list-brand-country"><?= $p['brandCountryStr'] ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="list-actions">
                                <button type="button" 
                                        class="list-btn-cart"
                                        data-product-id="<?= (int)$p['id'] ?>"
                                        title="В корзину">
                                    В корзину
                                </button>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>
