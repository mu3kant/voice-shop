<div id="productPopup" class="product-popup" role="dialog" aria-modal="true" hidden>
    <div class="popup-overlay"></div>

    <div class="popup-content">
        <!-- Кнопка закрытия -->
        <button type="button" class="popup-close-btn" aria-label="Закрыть">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </button>

        <div class="popup-inner">
            <!-- Левая часть: Галерея -->
            <div class="popup-gallery-section">
                <!-- Главное фото -->
                <div class="gallery-main">
                    <img id="popupMainImg" class="gallery-main-img" src="" alt="" loading="eager">
                </div>

                <!-- Миниатюры (теперь это горизонтальная лента) -->
                <div class="gallery-thumbs-wrapper">
                    <div class="gallery-thumbs-scroll">
                        <div id="thumbsContainer" class="gallery-thumbs-column"></div>
                    </div>
                    <!-- Стрелки навигации (опционально, можно скрыть через CSS если не нужны) -->
                    <button type="button" class="thumb-nav-btn prev" aria-label="Назад">
                        &#10094;
                    </button>
                    <button type="button" class="thumb-nav-btn next" aria-label="Вперед">
                        &#10095;
                    </button>
                </div>
            </div>

            <!-- Правая часть: Информация -->
            <div class="popup-info-section">
                <h2 id="popupProductTitle" class="popup-title"></h2>

                <div class="popup-meta-row">
                    <span id="popupSku" class="meta-item"></span>
                    <span id="popupArtFull" class="meta-item"></span>
                </div>

                <div class="popup-price-block">
                    <span id="popupPrice" class="popup-price"></span>
                </div>

                <!-- Габариты (Бейджи) -->
                <div id="popupDimensions" class="tags-container"></div>

                <!-- Бренд и страна -->
                <div id="popupBrandCountry" class="meta-text"></div>

                <!-- Морозостойкость (пример доп. атрибута) -->
                <div id="popupMorozBlock" class="badge-warning">
                    <svg class="icon-cold" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V7a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/><circle cx="7" cy="17" r="1"/><circle cx="12" cy="18" r="1"/><circle cx="17" cy="17" r="1"/></svg>
                    Требуется специальный температурный режим
                </div>

                <div id="popupDescription" class="popup-description"></div>

                <div class="popup-actions">
                    <button type="button" id="popupAddToCart" class="btn-cart-large">В корзину</button>
                </div>
            </div>
        </div>
    </div>
</div>
