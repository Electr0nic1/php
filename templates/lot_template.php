<?php
$time_left = get_dt_range($lot["expiration_date"]);
$hours_left = $time_left[0];
$minutes_left = $time_left[1];
$time_class = ($hours_left < 1) ? "timer--finishing" : null;
?>

<main>
    <nav class="nav">
        <ul class="nav__list container">
            <?php foreach ($categories as $category): ?>
                <li class="nav__item">
                    <a href="pages/all-lots.html"><?= htmlspecialchars($category['name']) ?></a>
                </li>
            <?php endforeach; ?>
        </ul>
    </nav>
    <section class="lot-item container">
        <h2><?= htmlspecialchars($lot['name']) ?></h2>
        <div class="lot-item__content">
            <div class="lot-item__left">
                <div class="lot-item__image">
                    <img src="./<?= htmlspecialchars($lot['image_url']) ?>" width="730" height="548" alt="<?= htmlspecialchars($lot['name']) ?>">
                </div>
                <p class="lot-item__category">Категория: <span><?= htmlspecialchars($lot['category']) ?></span></p>
                <p class="lot-item__description"><?= htmlspecialchars($lot['description']) ?></p>
            </div>
            <div class="lot-item__right">
                <div class="lot-item__state">
                    <div class="lot-item__timer timer <?= $time_class; ?>">
                        <?= $hours_left ?>:<?= $minutes_left ?>
                    </div>
                    <div class="lot-item__cost-state">
                        <div class="lot-item__rate">
                            <span class="lot-item__amount">Текущая цена</span>
                            <span class="lot-item__cost"><?= format_ruble($lot['start_price']) ?></span>
                        </div>
                        <div class="lot-item__min-cost">
                            Мин. ставка <span><?= format_ruble($lot['start_price'] + $lot['step_rate']) ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>