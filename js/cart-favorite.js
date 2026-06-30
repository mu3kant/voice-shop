// -----------------------------------------
// КОРЗИНА
// -----------------------------------------
    cartBtns.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            const productId = parseInt(btn.dataset.productId, 10);
            if (!productId) return;

            // Визуально показываем активность
            const originalText = btn.innerText;
            btn.innerText = 'Добавлено';
            btn.style.opacity = '0.7';

            fetch('/php/cart-favorite-handler.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=add_to_cart&product_id=${productId}`
            })
            .then(response => {
                if (!response.ok) throw new Error('Ошибка сервера');
                // Опционально: обновить счётчик корзины в шапке
                updateCartCount();
            })
            .catch(err => {
                console.error('Ошибка корзины:', err);
                btn.innerText = originalText;
                btn.style.opacity = '1';
            });
        });
    });

    // -----------------------------------------
    // ОБНОВЛЕНИЕ СЧЁТЧИКА КОРЗИНЫ (если есть в header)
    // -----------------------------------------
    function updateCartCount() {
        const countEl = document.querySelector('.cart-count, .header-cart-count');
        if (!countEl) return;

        fetch('/php/cart-favorite-handler.php?action=get_cart_count')
            .then(res => res.json())
            .then(data => {
                const count = data.count ?? 0;
                countEl.textContent = count;
                countEl.style.display = count > 0 ? 'block' : 'none';
            })
            .catch(() => {
                // Если сервер недоступен — ничего не делаем
            });
    }

    // При загрузке страницы сразу подгрузить счётчик
    updateCartCount();
});
