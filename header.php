<?php
// Получаем текущий URL для подсветки активного пункта
$current_page = $_SERVER['REQUEST_URI'] ?? '/';

function is_active($path, $current) {
    $path = rtrim($path, '/');
    $current = rtrim($current, '/');

    if ($path === '') $path = '/';

    // Главная страница
    if ($path === '/') {
        return ($current === '/' || strpos($current, '/index.php') === 0) ? 'hp-nav--active' : '';
    }

    return strpos($current, $path) === 0 ? 'hp-nav--active' : '';
}

// Функция получения количества товаров в корзине (для счётчика)
function get_cart_count() {
    session_start();
    $count = 0;
    if (isset($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $item) {
            $count += ($item['qty'] ?? 0);
        }
    }
    return $count;
}
?>

<style>
/* === БАЗОВЫЕ ПЕРЕМЕННЫЕ И СБРОС === */
.hp-nav-wrapper {
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
    --hp-brand: #FF7A45;
    --hp-text: #1A1A1A;
    --hp-text2: #737373;
    --hp-surface: #F7F6F3;
    --hp-r: 12px;
    --hp-rs: 8px;
    box-sizing: border-box;
}

a.hp-nav-link, a.hp-nav-dd-item, a.hp-nav-drawer-link, .hp-nav-mob-item {
    text-decoration: none;
    color: inherit;
}

/* === СКРЫВАЕМ МОБИЛЬНЫЕ БЛОКИ ПО УМОЛЧАНИЮ (ДЛЯ ДЕСКТОПА) === */
.hp-nav-top-bar,
.hp-nav-drawer,
.hp-nav-overlay-area,
.hp-nav-bottom-bar {
    display: none !important;
}

body .page-content, body .main, body .wrapper, body .container {
    /* padding-top теперь зависит от высоты шапки, но не фиксируем жёстко */
    padding-top: 56px !important;
    box-sizing: border-box;
    transition: padding-top 0.3s ease;
}

/* === DESKTOP ВЕРСИЯ === */
.hp-nav-desktop {
    display: flex !important;
    align-items: center;
    justify-content: space-between;
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
    height: 56px;
    background: rgba(255,255,255,0.98);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border-bottom: 1px solid rgba(0,0,0,0.05);
    position: relative;
    z-index: 1000;
}

.hp-nav-logo img {
    height: 38px;
    width: auto;
    display: block;
}

.hp-nav-links {
    display: flex;
    gap: 4px;
}

.hp-nav-link {
    padding: 8px 12px;
    border-radius: var(--hp-rs);
    color: var(--hp-text2);
    font-weight: 500;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.80rem;   
}

.hp-nav-link:hover, .hp-nav-link.hp-nav--active {
    background: var(--hp-surface) !important;
    color: #FF7A45 !important;
}

.hp-nav-link.hp-nav--active img,
.hp-nav-link:hover img {
    filter: brightness(0) invert(0.5) sepia(1) saturate(1000%) hue-rotate(0deg) brightness(1) contrast(95%) !important;
}

/* Кнопка "Ещё" — в том же стиле, что и пункты меню */
.hp-nav-more-wrap { position: relative; }
.hp-nav-more-btn {
    background: none;
    border: none;
    color: var(--hp-text2);
    cursor: pointer;
    padding: 8px 10px;
    border-radius: var(--hp-rs);
    font-weight: 500;
    font-size: inherit;
    line-height: 1;
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 0.92rem;    
    transition: color 0.2s ease, background 0.2s ease;
}
.hp-nav-more-btn:hover,
.hp-nav-more-wrap.is-open .hp-nav-more-btn {
    color: var(--hp-text);
    background: var(--hp-surface);
}
.hp-nav-more-btn svg {
    margin-left: auto;
}
/* Выпадающее меню "Ещё" на десктопе */
.hp-nav-dropdown {
    position: absolute;
    top: 100%;
    right: 0;
    margin-top: 12px;
    min-width: 260px;
    background: #fff;
    border-radius: var(--hp-r);
    box-shadow: 0 10px 30px rgba(0,0,0,0.12);
    padding: 8px;
    opacity: 0;
    visibility: hidden;
    transform: translateY(10px);
    transition: all 0.25s cubic-bezier(0.2, 0.8, 0.2, 1);
    z-index: 999;
}
.hp-nav-more-wrap.is-open .hp-nav-dropdown {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

.hp-nav-dd-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 14px;
    color: var(--hp-text);
    border-radius: var(--hp-rs);
    transition: background 0.2s;
    text-decoration: none;
}
.hp-nav-dd-item:hover {
    background: var(--hp-surface);
}

.hp-nav-dd-divider {
    height: 1px;
    background: #eee;
    margin: 10px 0;
    flex-shrink: 0;
}

/* Правая панель десктопа: избранное и корзина */
.hp-nav-right-panel {
    display: flex;
    align-items: center;
    gap: 16px;
}
.hp-nav-icon-btn {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 6px;
    color: var(--hp-text2);
    text-align: center;
    font-size: 0.72rem;
    cursor: pointer;
    transition: color 0.2s;
    text-decoration: none;
}
.hp-nav-icon-btn:hover,
.hp-nav-icon-btn.hp-nav--active {
    color: var(--hp-text) !important;
}
.hp-nav-icon-btn svg {
    width: 22px;
    height: 22px;
}

/* Счётчик корзины */
.cart-count-badge {
    position: absolute;
    top: -6px;
    right: -6px;
    background: #ff4d4d;
    color: #fff;
    font-size: 11px;
    font-weight: 700;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}
.cart-count-badge.hidden {
    display: none;
}

/* === MOBILE ВЕРСИЯ === */
@media (max-width: 960px) {
    .hp-nav-top-bar,
    .hp-nav-drawer,
    .hp-nav-overlay-area,
    .hp-nav-bottom-bar {
        display: block !important;
    }
    .hp-nav-desktop, .hp-nav-right-panel {
        display: none !important;
    }

    .hp-nav-top-bar {
        position: sticky !important;
        top: 0 !important;
        z-index: 1100 !important;
        height: 56px !important;
        padding: 0 15px !important;
        box-sizing: border-box !important;
        background: rgba(255, 255, 255, 0.92) !important;
        backdrop-filter: blur(10px) !important;
        -webkit-backdrop-filter: blur(10px) !important;
        border-bottom: 1px solid rgba(0, 0, 0, 0.08) !important;
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
    }

    /* Гамбургер */
    .hp-nav-hamburger-wrapper {
        cursor: pointer;
        padding: 6px;
        color: var(--hp-text) !important;
        display: inline-block;
        touch-action: manipulation;
    }
    .hp-nav-bar {
        width: 20px !important;
        height: 3px !important;
        background-color: currentColor !important;
        margin: 5px 0 !important;
        transition: transform 0.35s cubic-bezier(0.34, 1.56, 0.64, 1), opacity 0.35s !important;
        border-radius: 2px !important;
        display: block !important;
    }
    .hp-nav-hamburger-wrapper.change .hp-nav-bar:nth-child(1) {
        transform: rotate(-45deg) translate(-4px, 4px) !important;
    }
    .hp-nav-hamburger-wrapper.change .hp-nav-bar:nth-child(2) {
        opacity: 0 !important;
    }
    .hp-nav-hamburger-wrapper.change .hp-nav-bar:nth-child(3) {
        transform: rotate(45deg) translate(-4px, -4px) !important;
    }

    .hp-nav-logo-center {
        position: absolute !important;
        left: 50% !important;
        transform: translateX(-50%) !important;
        z-index: 2 !important;
        pointer-events: none !important;
    }
    .hp-nav-logo-center img {
        height: 40px !important;
        width: auto !important;
        max-width: 140px !important;
        object-fit: contain !important;
        display: block !important;
    }

    .hp-nav-overlay-area {
        position: fixed !important;
        top: 56px !important;
        left: 0 !important;
        right: 0 !important;
        bottom: 76px !important;
        background: rgba(0, 0, 0, 0.52) !important;
        z-index: 1050 !important;
        opacity: 0 !important;
        pointer-events: none !important;
        transition: opacity 0.3s ease !important;
    }
    .hp-nav-overlay-area.is-visible {
        opacity: 1 !important;
        pointer-events: all !important;
    }

    .hp-nav-drawer {
        position: fixed !important;
        top: 56px !important;
        left: -100% !important;
        width: 100% !important;
        height: calc(100% - 56px - 76px) !important;
        background: #fff !important;
        display: flex !important;
        flex-direction: column !important;
        overflow-y: auto !important;
        -webkit-overflow-scrolling: touch !important;
        transform: translateX(0) !important;
        transition: transform 0.35s cubic-bezier(0.34, 1.56, 0.64, 1) !important;
        z-index: 1060 !important;
        will-change: transform !important;
        box-sizing: border-box !important;
    }
    .hp-nav-drawer.is-open {
        left: 0 !important;
    }

    .hp-nav-drawer-header {
        padding: 14px 20px !important;
        border-bottom: 1px solid #F0EEEB !important;
        display: flex !important;
        justify-content: space-between !important;
        align-items: center !important;
        background: #fff !important;
        position: sticky !important;
        top: 0 !important;
        z-index: 10 !important;
        flex-shrink: 0 !important;
    }
    .hp-nav-drawer-title {
        font-weight: 700 !important;
        color: var(--hp-text) !important;
        font-size: 1.1rem !important;
    }
    .hp-nav-drawer-close {
        background: none !important;
        border: none !important;
        color: var(--hp-text2) !important;
        cursor: pointer !important;
        padding: 8px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
    }

    .hp-nav-drawer-links {
        padding: 16px 20px !important;
        display: flex !important;
        flex-direction: column !important;
        gap: 8px !important;
        width: 100% !important;
    }
    .hp-nav-drawer-link {
        padding: 12px 16px !important;
        color: var(--hp-text) !important;
        border-radius: var(--hp-rs) !important;
        transition: background 0.2s ease, color 0.2s ease !important;
        display: flex !important;
        align-items: center !important;
        gap: 14px !important;
        text-align: left !important;
        text-decoration: none !important;
        width: 100% !important;
        box-sizing: border-box !important;
    }
    .hp-nav-drawer-link:hover,
    .hp-nav-drawer-link.hp-nav--active {
        background: var(--hp-surface) !important;
        color: #FF7A45 !important;
    }
    
    .hp-nav-drawer-link.hp-nav--active img,
    .hp-nav-drawer-link:hover img {
        filter: brightness(0) invert(0.5) sepia(1) saturate(1000%) hue-rotate(0deg) brightness(1) contrast(95%) !important;
    }

    .hp-nav-divider-mobile {
        height: 1px !important;
        background: #eee !important;
        margin: 12px 0 !important;
        flex-shrink: 0 !important;
    }

    /* Нижнее мобильное меню: выравнивание иконок */
    .hp-nav-bottom-bar {
        position: fixed !important;
        bottom: 0 !important;
        left: 0 !important;
        width: 100% !important;
        height: 76px !important;
        background: rgba(255, 255, 255, 0.94) !important;
        backdrop-filter: blur(12px) !important;
        -webkit-backdrop-filter: blur(12px) !important;
        border-top: 1px solid rgba(0, 0, 0, 0.08) !important;
        display: flex !important;
        justify-content: space-around !important;
        align-items: center !important;
        z-index: 1100 !important;
        padding: 0 12px !important;
        box-sizing: border-box !important;
    }

    .hp-nav-mob-item {
        display: flex !important;
        flex-direction: column !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 6px !important;
        color: var(--hp-text2) !important;
        text-align: center !important;
        font-size: 0.72rem !important;
        font-weight: 500 !important;
        transition: color 0.2s ease, transform 0.1s ease !important;
        width: 20% !important;
        cursor: pointer !important;
        white-space: nowrap !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
        text-decoration: none !important;
        padding: 8px 4px !important;
    }

    .hp-nav-mob-item img {
        display: block !important;
        width: 24px !important;
        height: 24px !important;
        object-fit: contain !important;
        margin: 0 auto 4px auto !important;
        filter: grayscale(100%) brightness(0) contrast(0);
        transition: filter 0.2s ease !important;
    }

    .hp-nav-mob-item.hp-nav--active,
    .hp-nav-mob-item:hover {
        color: #FF7A45 !important;
    }

    .hp-nav-mob-item.hp-nav--active img,
    .hp-nav-mob-item:hover img {
        filter: brightness(0) invert(0.5) sepia(1) saturate(1000%) hue-rotate(0deg) brightness(1) contrast(95%) !important;
    }
}
</style>

<div class="hp-nav-wrapper">
    <!-- DESKTOP МЕНЮ -->
    <div class="hp-nav-desktop">
        <div class="hp-nav-logo">
            <a href="/"><img src="/img/logo.png" alt="Logo"></a>
        </div>

        <nav class="hp-nav-links">
            <a href="/" class="hp-nav-link <?php echo is_active('/', $current_page); ?>">
                <img src="/icon/home.svg" alt="" class="hp-nav-mob-icon" style="width:16px; height:16px; vertical-align:middle; margin-right:6px;">
                Главная
            </a>
            <a href="/catalog" class="hp-nav-link <?php echo is_active('/catalog', $current_page); ?>">
                <img src="/icon/catalog.svg" alt="" class="hp-nav-mob-icon" style="width:16px; height:16px; vertical-align:middle; margin-right:6px;">
                Каталог
            </a>
            <a href="/stock" class="hp-nav-link <?php echo is_active('/stock', $current_page); ?>">
                <img src="/icon/akcii.svg" alt="" class="hp-nav-mob-icon" style="width:16px; height:16px; vertical-align:middle; margin-right:6px;">
                Акции
            </a>
            <a href="/about" class="hp-nav-link <?php echo is_active('/about', $current_page); ?>">
                <img src="/icon/store.svg" alt="" class="hp-nav-mob-icon" style="width:16px; height:16px; vertical-align:middle; margin-right:6px;">
                О магазине
            </a>
            <a href="/contacts" class="hp-nav-link <?php echo is_active('/contacts', $current_page); ?>">
                <img src="/icon/contacts.svg" alt="" class="hp-nav-mob-icon" style="width:16px; height:16px; vertical-align:middle; margin-right:6px;">
                Контакты
            </a>

            <!-- Кнопка "Ещё" -->
            <div class="hp-nav-more-wrap" id="hp-more-wrap">
                <button class="hp-nav-more-btn" id="hp-more-btn">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="4" cy="12" r="1"></circle>
                        <circle cx="12" cy="12" r="1"></circle>
                        <circle cx="20" cy="12" r="1"></circle>
                    </svg>
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-left:auto;">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                </button>
                <div class="hp-nav-dropdown">
                    <a href="/game" class="hp-nav-dd-item <?php echo is_active('/game', $current_page); ?>">
                        <img src="/icon/game.svg" alt="" class="hp-nav-mob-icon" style="width:16px; height:16px; vertical-align:middle; margin-right:8px;">
                        Игровая зона
                    </a>
                    <a href="/delivery" class="hp-nav-dd-item <?php echo is_active('/delivery', $current_page); ?>">
                        <img src="/icon/deliver.svg" alt="" class="hp-nav-mob-icon" style="width:16px; height:16px; vertical-align:middle; margin-right:8px;">
                        Доставка
                    </a>
                    <a href="/pay" class="hp-nav-dd-item <?php echo is_active('/pay', $current_page); ?>">
                        <img src="/icon/pay.svg" alt="" class="hp-nav-mob-icon" style="width:16px; height:16px; vertical-align:middle; margin-right:8px;">
                        Оплата
                    </a>

                    <div class="hp-nav-dd-divider"></div>

                    <a href="/policy" class="hp-nav-dd-item <?php echo is_active('/policy', $current_page); ?>">
                        Политика конфиденциальности
                    </a>
                    <a href="/offer" class="hp-nav-dd-item <?php echo is_active('/offer', $current_page); ?>">
                        Публичная оферта
                    </a>
                </div>
            </div>
        </nav>

        <!-- Правая панель: избранное и корзина (со счётчиком) -->
        <div class="hp-nav-right-panel">
            <a href="/favorites" class="hp-nav-icon-btn <?php echo is_active('/favorites', $current_page); ?>">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                </svg>
                Избранное
            </a>

            <a href="/cart" class="hp-nav-icon-btn <?php echo is_active('/cart', $current_page); ?>">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="9" cy="21" r="1"></circle>
                    <circle cx="20" cy="21" r="1"></circle>
                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                </svg>
                Корзина
                <!-- Счётчик корзины (показываем только если >0) -->
                <?php
                $cartCount = get_cart_count();
                if ($cartCount > 0): ?>
                    <span class="cart-count-badge"><?php echo $cartCount; ?></span>
                <?php else: ?>
                    <span class="cart-count-badge hidden"></span>
                <?php endif; ?>
            </a>
        </div>
    </div>

    <!-- MOBILE: Верхняя панель (бургер) -->
    <div class="hp-nav-top-bar">
        <div class="hp-nav-hamburger-wrapper" id="hp-burger">
            <span class="hp-nav-bar"></span>
            <span class="hp-nav-bar"></span>
            <span class="hp-nav-bar"></span>
        </div>
        <div class="hp-nav-logo-center">
            <a href="/"><img src="/img/logo.png" alt="Logo"></a>
        </div>
    </div>

    <!-- MOBILE: Оверлей -->
    <div class="hp-nav-overlay-area" id="hp-overlay"></div>

    <!-- MOBILE: Выезжающее меню (Drawer) -->
    <div class="hp-nav-drawer" id="hp-drawer">
        <div class="hp-nav-drawer-header">
            <div class="hp-nav-drawer-title">Меню</div>
            <button class="hp-nav-drawer-close" id="hp-drawer-close">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>

        <div class="hp-nav-drawer-links">
            <a href="/" class="hp-nav-drawer-link <?php echo is_active('/', $current_page); ?>">
                <img src="/icon/home.svg" alt="" class="hp-nav-mob-icon">
                Главная
            </a>
            <a href="/catalog" class="hp-nav-drawer-link <?php echo is_active('/catalog', $current_page); ?>">
                <img src="/icon/catalog.svg" alt="" class="hp-nav-mob-icon">
                Каталог
            </a>
            <a href="/stock" class="hp-nav-drawer-link <?php echo is_active('/stock', $current_page); ?>">
                <img src="/icon/akcii.svg" alt="" class="hp-nav-mob-icon">
                Акции
            </a>
            <a href="/about" class="hp-nav-drawer-link <?php echo is_active('/about', $current_page); ?>">
                <img src="/icon/store.svg" alt="" class="hp-nav-mob-icon">
                О магазине
            </a>
            <a href="/contacts" class="hp-nav-drawer-link <?php echo is_active('/contacts', $current_page); ?>">
                <img src="/icon/contacts.svg" alt="" class="hp-nav-mob-icon">
                Контакты
            </a>
            <a href="/game" class="hp-nav-drawer-link <?php echo is_active('/game', $current_page); ?>">
                <img src="/icon/game.svg" alt="" class="hp-nav-mob-icon">
                Игровая зона
            </a>
            <a href="/delivery" class="hp-nav-drawer-link <?php echo is_active('/delivery', $current_page); ?>">
                <img src="/icon/deliver.svg" alt="" class="hp-nav-mob-icon">
                Доставка
            </a>
            <a href="/pay" class="hp-nav-drawer-link <?php echo is_active('/pay', $current_page); ?>">
                <img src="/icon/pay.svg" alt="" class="hp-nav-mob-icon">
                Оплата
            </a>

            <div class="hp-nav-divider-mobile"></div>

            <a href="/policy" class="hp-nav-drawer-link <?php echo is_active('/policy', $current_page); ?>">
                Политика конфиденциальности
            </a>
            <a href="/offer" class="hp-nav-drawer-link <?php echo is_active('/offer', $current_page); ?>">
                Публичная оферта
            </a>
        </div>
    </div>

    <!-- MOBILE: Нижняя панель быстрых действий -->
    <div class="hp-nav-bottom-bar">
        <!-- Главная -->
        <a href="/" class="hp-nav-mob-item <?php echo is_active('/', $current_page); ?>">
            <img src="/icon/home.svg" alt="" width="24" height="24">
            Главная
        </a>

        <!-- О магазине -->
        <a href="/about" class="hp-nav-mob-item <?php echo is_active('/about', $current_page); ?>">
            <img src="/icon/store.svg" alt="" width="24" height="24">
            О магазине
        </a>

        <!-- Каталог -->
        <a href="/catalog" class="hp-nav-mob-item <?php echo is_active('/catalog', $current_page); ?>">
            <img src="/icon/catalog.svg" alt="" width="24" height="24">
            Каталог
        </a>

        <!-- Избранное -->
        <a href="/favorites" class="hp-nav-mob-item <?php echo is_active('/favorites', $current_page); ?>">
            <img src="/icon/favorite.svg" alt="" width="24" height="24">
            Избранное
        </a>

        <!-- Корзина (со счётчиком в нижнем баре) -->
        <a href="/cart" class="hp-nav-mob-item <?php echo is_active('/cart', $current_page); ?>">
            <img src="/icon/ba.svg" alt="" width="24" height="24">
            Корзина
            <?php if ($cartCount > 0): ?>
                <span class="cart-count-badge"><?php echo $cartCount; ?></span>
            <?php else: ?>
                <span class="cart-count-badge hidden"></span>
            <?php endif; ?>
        </a>
    </div>
</div>

<script>
(function() {
    // --- Мобильное меню (бургер + drawer) ---
    const burger = document.getElementById('hp-burger');
    const drawer = document.getElementById('hp-drawer');
    const overlay = document.getElementById('hp-overlay');
    const drawerClose = document.getElementById('hp-drawer-close');

    function openDrawer() {
        if (drawer && overlay) {
            drawer.classList.add('is-open');
            overlay.classList.add('is-visible');
            if (burger) burger.classList.add('change');
            // Блокируем скролл страницы, пока открыто меню
            document.body.style.overflow = 'hidden';
        }
    }

    function closeDrawer() {
        if (drawer && overlay) {
            drawer.classList.remove('is-open');
            overlay.classList.remove('is-visible');
            if (burger) burger.classList.remove('change');
            // Возвращаем скролл
            document.body.style.overflow = '';
        }
    }

    if (burger) {
        burger.addEventListener('click', () => {
            if (drawer.classList.contains('is-open')) {
                closeDrawer();
            } else {
                openDrawer();
            }
        });
    }

    if (drawerClose) {
        drawerClose.addEventListener('click', closeDrawer);
    }

    if (overlay) {
        overlay.addEventListener('click', closeDrawer);
    }

    // Закрываем меню при клике по ссылке внутри drawer (UX для мобильных)
    const drawerLinks = document.querySelectorAll('.hp-nav-drawer-link');
    drawerLinks.forEach(link => {
        link.addEventListener('click', closeDrawer);
    });


    // --- Выпадающее меню «Ещё» на десктопе ---
    const moreWrap = document.getElementById('hp-more-wrap');
    const moreBtn = document.getElementById('hp-more-btn');

    function toggleDropdown() {
        if (!moreWrap) return;
        const isOpen = moreWrap.classList.toggle('is-open');
        // Опционально: можно закрывать при клике вне
        if (isOpen) {
            setTimeout(() => {
                const handleOutside = (e) => {
                    if (!moreWrap.contains(e.target)) {
                        moreWrap.classList.remove('is-open');
                        document.removeEventListener('click', handleOutside);
                    }
                };
                document.addEventListener('click', handleOutside, { once: true });
            }, 0);
        }
    }

    if (moreBtn) {
        moreBtn.addEventListener('click', (e) => {
            e.stopPropagation(); // чтобы не сработал «клик вне» раньше времени
            toggleDropdown();
        });
    }


    // --- Счётчик корзины: автообновление без перезагрузки ---
    // Если у тебя корзина обновляется через AJAX (например, в catalog.js),
    // то там нужно вызывать updateCartCount(newCount).
    // Здесь — логика отображения и простой опрос (опционально).
    function updateCartCount(count) {
        const badges = document.querySelectorAll('.cart-count-badge');
        badges.forEach(badge => {
            if (count > 0) {
                badge.textContent = count;
                badge.classList.remove('hidden');
            } else {
                badge.classList.add('hidden');
            }
        });
    }

    // Инициализация при загрузке
    updateCartCount(<?php echo $cartCount; ?>);

    // Пример: если корзина обновляется через fetch/AJAX, экспортируем функцию в глобальную область
    window.updateCartCount = updateCartCount;


    // --- Прилипание шапки (sticky header) и корректный padding-top ---
    // Мы делаем шапку sticky, но чтобы контент не перекрывался,
    // динамически ставим padding-top у body/обёртки равным высоте шапки.
    function setHeaderSticky() {
        const wrapper = document.querySelector('.hp-nav-wrapper');
        const desktop = document.querySelector('.hp-nav-desktop');
        const topBar = document.querySelector('.hp-nav-top-bar');

        if (!wrapper) return;

        const getHeaderHeight = () => {
            if (window.innerWidth <= 960 && topBar) {
                return topBar.offsetHeight;
            }
            if (desktop) {
                return desktop.offsetHeight;
            }
            return 56; // fallback
        };

        const height = getHeaderHeight();
        wrapper.style.setProperty('--hp-header-height', height + 'px');

        // Применяем padding-top к основному контенту.
        // Подбирай селектор под свою верстку: .page-content / .main / .container
        const targets = document.querySelectorAll('.page-content, .main, .wrapper, .container');
        targets.forEach(el => {
            el.style.paddingTop = height + 'px';
        });
    }

    setHeaderSticky();
    window.addEventListener('resize', setHeaderSticky);


    // --- Исправление проблемы: мобильные пункты на десктопе не должны мешать ---
    // Это уже решено через @media (max-width: 960px) в CSS,
    // но добавим страховку: принудительно скрываем drawer/overlay/bottom-bar на больших экранах.
    function enforceMobileVisibility() {
        const mobileBlocks = [
            '.hp-nav-top-bar',
            '.hp-nav-drawer',
            '.hp-nav-overlay-area',
            '.hp-nav-bottom-bar'
        ];

        mobileBlocks.forEach(selector => {
            const els = document.querySelectorAll(selector);
            els.forEach(el => {
                if (window.innerWidth > 960) {
                    el.style.display = 'none !important';
                } else {
                    el.style.display = 'block !important';
                }
            });
        });

        const desktopBlocks = ['.hp-nav-desktop', '.hp-nav-right-panel'];
        desktopBlocks.forEach(selector => {
            const els = document.querySelectorAll(selector);
            els.forEach(el => {
                if (window.innerWidth > 960) {
                    el.style.display = 'flex !important';
                } else {
                    el.style.display = 'none !important';
                }
            });
        });
    }

    enforceMobileVisibility();
    window.addEventListener('resize', enforceMobileVisibility);

})();
