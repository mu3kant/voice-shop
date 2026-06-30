document.addEventListener('DOMContentLoaded', () => {
  const popup = document.getElementById('productPopup');
  if (!popup) return;

  const overlay = popup.querySelector('.popup-overlay');
  const closeBtn = popup.querySelector('.popup-close-btn');
  const thumbsContainer = document.getElementById('thumbsContainer');
  const mainImg = document.getElementById('popupMainImg');

  // Поля попапа
  const titleEl = document.getElementById('popupProductTitle');
  const skuEl = document.getElementById('popupSku');
  const artFullEl = document.getElementById('popupArtFull');
  const priceEl = document.getElementById('popupPrice');
  const dimEl = document.getElementById('popupDimensions');
  const brandEl = document.getElementById('popupBrandCountry');
  const morozBlock = document.getElementById('popupMorozBlock');
  const descEl = document.getElementById('popupDescription');
  const addToCartBtn = document.getElementById('popupAddToCart');

  /**
   * Безопасный парсинг JSON из data-атрибута
   */
  function safeJsonParse(str) {
    if (!str) return [];
    try {
      const parsed = JSON.parse(str);
      return Array.isArray(parsed) ? parsed : [];
    } catch (e) {
      console.warn('Не удалось распарсить data-images', e);
      return [];
    }
  }

  /**
   * Рендер бейджей габаритов
   */
  function renderDimensions(dlina, shirina, vysota) {
    dimEl.innerHTML = '';
    const parts = [];
    if (dlina) parts.push(`Д: ${dlina}`);
    if (shirina) parts.push(`Ш: ${shirina}`);
    if (vysota) parts.push(`В: ${vysota}`);

    if (parts.length === 0) {
      dimEl.style.display = 'none';
      return;
    }

    dimEl.style.display = 'flex';
    parts.forEach(text => {
      const span = document.createElement('span');
      span.textContent = text;
      dimEl.appendChild(span);
    });
  }

  /**
   * Открытие попапа
   */
  function openPopup(product) {
    // Валидация
    if (!product || typeof product !== 'object') return;
    if (typeof product.id !== 'number' || product.id <= 0) return;

    // Заголовок
    titleEl.textContent = product.title ? String(product.title).trim() : 'Товар';

    // Артикул и код (если есть — показываем, если нет — скрываем блок или оставляем пустым)
    skuEl.textContent = product.article ? 'Арт: ' + String(product.article).trim() : '';
    artFullEl.textContent = product.art_full ? 'Код: ' + String(product.art_full).trim() : '';

    // Цена
    const priceValue = typeof product.price === 'number' ? product.price : 0;
    priceEl.textContent = priceValue > 0
      ? new Intl.NumberFormat('ru-RU').format(priceValue) + ' ₽'
      : '';

    // Габариты
    renderDimensions(product.dlina, product.shirina, product.vysota);

    // Бренд и страна
    let brandCountryText = '';
    if (product.brand) brandCountryText += product.brand;
    if (product.country) {
      if (brandCountryText) brandCountryText += ', ';
      brandCountryText += product.country;
    }
    brandEl.textContent = brandCountryText;
    brandEl.style.display = brandCountryText ? 'block' : 'none';

    // Морозостойкость
    if (morozBlock) {
      morozBlock.style.display = product.moroz === 1 ? 'flex' : 'none';
    }

    // Описание (защита от XSS)
    descEl.textContent = product.description ? String(product.description).trim() : '';

    // Галерея
    const images = safeJsonParse(product.images);
    const mainImage = images[0] || '';

    mainImg.src = mainImage;
    mainImg.alt = titleEl.textContent;

    // Рендер миниатюр
    thumbsContainer.innerHTML = '';
    images.forEach((img, idx) => {
      if (!img) return;
      const btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'thumb-btn' + (idx === 0 ? ' active' : '');
      btn.setAttribute('aria-label', 'Миниатюра фото');
      btn.dataset.index = idx;

      const imgEl = document.createElement('img');
      imgEl.src = img;
      imgEl.alt = 'Миниатюра';
      imgEl.loading = 'lazy';
      btn.appendChild(imgEl);

      btn.onclick = () => setMainImage(idx, images);
      thumbsContainer.appendChild(btn);
    });

    // Кнопка «В корзину»
    addToCartBtn.dataset.productId = String(product.id);
    addToCartBtn.onclick = handleAddToCart;

    // Сброс скролла внутри попапа
    const inner = popup.querySelector('.popup-inner');
    if (inner) inner.scrollTop = 0;

    // Открытие
    popup.classList.add('active');
    popup.removeAttribute('hidden');
    document.body.style.overflow = 'hidden';
  }

  /**
   * Переключение на выбранную миниатюру + автоскролл к активной
   */
  function setMainImage(index, images) {
    if (!mainImg || index < 0 || index >= images.length) return;
    const img = images[index];
    if (!img) return;

    mainImg.src = img;
    mainImg.alt = titleEl.textContent;

    const btns = thumbsContainer.querySelectorAll('.thumb-btn');
    btns.forEach((b, i) => b.classList.toggle('active', i === index));

    // Автоскролл к активной миниатюре (удобно на мобильных и длинных лентах)
    const activeBtn = thumbsContainer.querySelector('.thumb-btn.active');
    if (activeBtn) {
      activeBtn.scrollIntoView({ behavior: 'smooth', block: 'center', inline: 'center' });
    }
  }

  /**
   * Закрытие попапа
   */
  function closePopup() {
    popup.classList.remove('active');
    setTimeout(() => {
      popup.setAttribute('hidden', 'true');
      document.body.style.overflow = '';
    }, 300);
  }

  /**
   * Обработка добавления в корзину
   */
  function handleAddToCart() {
    const productId = parseInt(this.dataset.productId, 10);
    if (!productId) return;

    console.log('Добавить в корзину:', productId);

    /*
    // Раскомментируй, если нужна реальная отправка:
    fetch('/api/cart/add', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ product_id: productId })
    })
      .then(res => res.ok ? alert('Товар добавлен в корзину') : alert('Ошибка добавления'))
      .catch(err => console.error(err));
    */
  }

  /**
   * Поиск карточек товаров и вешаем открытие попапа
   * Подстраивай селекторы под свой HTML (например .product-card, .card-link и т.д.)
   */
  document.querySelectorAll('[data-product-id]').forEach(el => {
    el.addEventListener('click', (e) => {
      // Если клик был по ссылке — отменяем переход
      if (e.target.tagName === 'A') {
        e.preventDefault();
      }

      const product = {
        id: parseInt(el.dataset.productId, 10),
        title: el.dataset.title || '',
        article: el.dataset.article || '',
        art_full: el.dataset.artFull || '',
        price: parseFloat(el.dataset.price) || 0,
        dlina: el.dataset.dlina || '',
        shirina: el.dataset.shirina || '',
        vysota: el.dataset.vysota || '',
        brand: el.dataset.brand || '',
        country: el.dataset.country || '',
        moroz: parseInt(el.dataset.moroz, 10) || 0,
        description: el.dataset.description || '',
        images: el.dataset.images || '[]'
      };

      openPopup(product);
    });
  });

  // Закрытие по оверлею
  if (overlay) overlay.addEventListener('click', closePopup);

  // Закрытие по кнопке
  if (closeBtn) closeBtn.addEventListener('click', closePopup);

  // Закрытие по Esc
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && popup.classList.contains('active')) {
      closePopup();
    }
  });
});
