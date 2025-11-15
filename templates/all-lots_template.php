<main>
    <nav class="nav">
        <ul class="nav__list container">
            <?php foreach ($categories as $category): ?>
                <?php
                    $is_active = ($category['id'] == $category_id) ? 'nav__item--current' : '';
                ?>
                <li class="nav__item <?= $is_active ?>">
                    <a href="all-lots.php?id=<?= $category['id'] ?>">
                        <?= htmlspecialchars($category['name']) ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </nav>

    <div class="container">
        <section class="lots">
            <h2>Все лоты в категории <span>«<?= $title ?>»</span></h2>
            <?php if (empty($lots)): ?>
                <p>В этой категории пока нет лотов.</p>
            <?php else: ?>
                <ul class="lots__list">
                    <?php foreach ($lots as $lot):
                        $time_left = get_dt_range($lot["expiration_date"]);
                        $hours_left = $time_left[0];
                        $minutes_left = $time_left[1];
                        $time_class = ($hours_left < 1) ? "timer--finishing" : null;
                    ?>
                        <li class="lots__item lot">
                            <div class="lot__image">
                                <img src=<?= htmlspecialchars($lot["image_url"]) ?> width="350" height="260" alt="Сноуборд">
                            </div>
                            <div class="lot__info">
                                <span class="lot__category"><?= htmlspecialchars($lot["category"]) ?></span>
                                <h3 class="lot__title"><a class="text-link" href="lot.php?id=<?= $lot['id'] ?>"><?= htmlspecialchars($lot["name"]) ?></a></h3>
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
            <?php endif; ?>

        </section>
        <!-- <ul class="pagination-list">
            <li class="pagination-item pagination-item-prev"><a>Назад</a></li>
            <li class="pagination-item pagination-item-active"><a>1</a></li>
            <li class="pagination-item"><a href="#">2</a></li>
            <li class="pagination-item"><a href="#">3</a></li>
            <li class="pagination-item"><a href="#">4</a></li>
            <li class="pagination-item pagination-item-next"><a href="#">Вперед</a></li>
        </ul> -->
    </div>
</main>