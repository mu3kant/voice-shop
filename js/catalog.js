document.addEventListener('DOMContentLoaded', () => {
    // =========================================
    // 1. Popup категорий (сайдбар)
    // =========================================
    const btnToggle = document.getElementById('btnCategoriesToggle');
    const popup = document.getElementById('categories-popup');
    const overlay = popup ? popup.querySelector('.popup-overlay') : null;
    const closeBtn = popup ? popup.querySelector('.popup-close') : null;

    function togglePopup() {
        if (!popup || !btnToggle) return;
        const isActive = popup.classList.contains('active');

        if (isActive) {
            popup.classList.remove('active');
            btnToggle.setAttribute('aria-expanded', 'false');
            document.body.style.overflow = '';
        } else {
            popup.classList.add('active');
            btnToggle.setAttribute('aria-expanded', 'true');
            document.body.style.overflow = 'hidden';
        }
    }

    if (btnToggle) btnToggle.addEventListener('click', togglePopup);
    if (overlay) overlay.addEventListener('click', togglePopup);
    if (closeBtn) closeBtn.addEventListener('click', togglePopup);

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && popup && popup.classList.contains('active')) {
            togglePopup();
        }
    });

    // =========================================
    // 2. Дерево категорий: раскрытие/сворачивание
    // =========================================
    document.querySelectorAll('.cat-toggle-wrapper, .cat-toggle-icon').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const node = btn.closest('.cat-node');
            if (!node) return;

            const isOpen = node.classList.toggle('open');
            const link = node.querySelector('.cat-row');
            if (link) {
                link.setAttribute('aria-expanded', String(isOpen));
            }
        });
    });

    // При клике по ссылке в дереве — закрываем popup
    document.querySelectorAll('.categories-tree .cat-link').forEach(link => {
        link.addEventListener('click', (e) => {
            if (popup && popup.classList.contains('active')) {
                togglePopup();
            }
        });
    });

    // =========================================
    // 3. Обновление текста кнопки «Категории»
    // =========================================
    function updateCategoryButtonText() {
        const currentLink = document.querySelector('.categories-tree .cat-link.current-category');
        const btnTextEl = document.getElementById('btnCategoriesText');
        if (btnTextEl) {
            btnTextEl.textContent = currentLink ? currentLink.textContent.trim() : 'Категории';
        }
    }
    updateCategoryButtonText();

    // =========================================
    // 4. Переключатель вида: плитка / список
    // =========================================
    const viewSwitcher = document.querySelector('.view-switcher');
    if (!viewSwitcher) return; // Если переключателя нет — выходим

    const container = document.querySelector('.products-container');
    const viewBtns = viewSwitcher.querySelectorAll('.view-btn');

    function syncViewButtons() {
        viewBtns.forEach(btn => btn.classList.remove('active'));
        const currentView = container ? container.getAttribute('data-view') : 'grid';
        const activeBtn = Array.from(viewBtns).find(btn => btn.dataset.view === currentView);
        if (activeBtn) activeBtn.classList.add('active');
    }

    viewBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const newView = btn.dataset.view;
            if (!newView || !container) return;
            container.setAttribute('data-view', newView);
            syncViewButtons();
        });
    });

    syncViewButtons();


    // =========================================
    // 6. Избранное (сердечко)
    // =========================================
    document.addEventListener('click', async (e) => {
        const btn = e.target.closest('.card-favorite-btn');
        if (!btn) return;

        e.preventDefault();
        const productId = parseInt(btn.dataset.productId, 10);
        const isActive = btn.classList.contains('favorite-active');

        // Сразу меняем UI для отзывчивости
        btn.classList.toggle('favorite-active', !isActive);
        btn.setAttribute('aria-pressed', String(!isActive));
        btn.disabled = true; // Блокируем кнопку на время запроса

        try {
            const response = await fetch('/api/favorite/toggle', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ product_id: productId })
            });

            if (!response.ok) {
                // Если сервер вернул ошибку — откатываем UI назад
                btn.classList.toggle('favorite-active', isActive);
                btn.setAttribute('aria-pressed', String(isActive));
                throw new Error('Ошибка сохранения избранного');
            }
            // Успех — ничего не делаем, UI уже обновлен.
        } catch (err) {
            console.error(err);
            // Можно показать уведомление пользователю об ошибке.
        } finally {
            btn.disabled = false; // Разблокируем кнопку в любом случае
        }
    });

    // =========================================
    // 7. Корзина - AJAX + UI
    // =========================================
    document.addEventListener('click', async (e) => {
        const btn = e.target.closest('.btn-cart');
        if (!btn || btn.disabled || btn.classList.contains('in-cart')) return;

        e.preventDefault();
        const productId = parseInt(btn.dataset.productId, 10);
        const originalText = btn.textContent.trim();

        btn.disabled = true;
        btn.textContent = 'Добавляю...';

        try {
            const response = await fetch('/api/cart/add', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ product_id: productId })
            });

            if (!response.ok) throw new Error('Ошибка добавления в корзину');

            btn.textContent = 'В корзине';
            btn.classList.add('in-cart'); // Меняем стиль кнопки

            // Опционально: обновляем счетчик в шапке сайта, если он есть
            const cartCounter = document.querySelector('.hp-nav-icon-btn[href="/cart"] .cart-count');
            if (cartCounter) {
                let count = parseInt(cartCounter.textContent || '0', 10);
                cartCounter.textContent = count + 1;
                cartCounter.style.display = 'block';
            }

        } catch (err) {
            console.error(err);
            btn.textContent = originalText; // Возвращаем старый текст в случае ошибки
        } finally {
            btn.disabled = false; // Разблокируем кнопку в любом случае
        }
    });
});