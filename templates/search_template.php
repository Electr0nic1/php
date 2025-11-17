<main>
    <nav class="nav">
        <ul class="nav__list container">
            <?php foreach ($categories as $category): ?>
                <li class="nav__item">
                    <a href="all-lots.php?id=<?= $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></a>
                </li>
            <?php endforeach; ?>
        </ul>
    </nav>
    <div class="container">
        <section class="lots">
            <h2>Результаты поиска по запросу «<span><?= htmlspecialchars($query) ?></span>»</h2>
            <ul class="lots__list">
                <?php foreach ($lots as $lot):
                    $time_left = get_dt_range($lot["expiration_date"]);
                    $hours_left = $time_left[0];
                    $minutes_left = $time_left[1];
                    $time_class = ($hours_left < 1) ? "timer--finishing" : null;
                ?>
                    <li class="lots__item lot">
                        <div class="lot__image">
                            <img src=<?= htmlspecialchars($lot['image_url']) ?> width="350" height="260" alt="Сноуборд">
                        </div>
                        <div class="lot__info">
                            <span class="lot__category"><?= htmlspecialchars($lot['category']) ?></span>
                            <h3 class="lot__title"><a class="text-link" href="lot.php?id=<?= $lot['id'] ?>"><?= htmlspecialchars($lot['name']) ?></a></h3>
                            <div class="lot__state">
                                <div class="lot__rate">
                                    <span class="lot__amount">Стартовая цена</span>
                                    <span class="lot__cost"><?= htmlspecialchars(format_ruble($lot["start_price"])) ?></span>
                                </div>
                                <div class="lot__timer timer <?= $time_class; ?>">
                                    <?= $hours_left ?>:<?= $minutes_left ?>
                                </div>
                            </div>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>

        <?php if ($total_pages > 1): ?>
            <ul class="pagination-list">

                <li class="pagination-item pagination-item-prev <?= $page <= 1 ? 'disabled' : '' ?>">
                    <?php if ($page > 1): ?>
                        <a href="?search=<?= urlencode($query) ?>&page=<?= $page - 1 ?>">Назад</a>
                    <?php else: ?>
                        <span>Назад</span>
                    <?php endif; ?>
                </li>
            
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="pagination-item <?= $i === $page ? 'pagination-item-active' : '' ?>">
                        <a href="?search=<?= urlencode($query) ?>&page=<?= $i ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>

                <li class="pagination-item pagination-item-next <?= $page >= $total_pages ? 'disabled' : '' ?>">
                    <?php if ($page < $total_pages): ?>
                        <a href="?search=<?= urlencode($query) ?>&page=<?= $page + 1 ?>">Вперед</a>
                    <?php else: ?>
                        <span>Вперед</span>
                    <?php endif; ?>
                </li>

            </ul>
        <?php endif; ?>

    </div>
</main>