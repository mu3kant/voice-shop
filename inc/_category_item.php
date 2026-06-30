<!-- inc/_category_item.php -->
<li class="category-item <?= $cat['id'] == $current_cat_id ? 'active' : '' ?>">
    <?php 
        $queryString = '?cat=' . $cat['id']; 
        // Если есть другие GET параметры (например, sort), их нужно сохранить, но для простоты пока так
    ?>
    <a href="<?= $queryString ?>" class="category-link">
        <?= htmlspecialchars($cat['name']) ?>
    </a>
    <?php if (!empty($cat['children'])): ?>
        <ul class="sub-categories">
            <?php foreach ($cat['children'] as $child): ?>
                <?php include 'inc/_category_item.php'; // Рекурсия ?>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</li>
