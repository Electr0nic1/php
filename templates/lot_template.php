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
                            <span class="lot-item__cost"><?= format_ruble($current_price) ?></span>
                        </div>
                        <div class="lot-item__min-cost">
                            Мин. ставка <span><?= format_ruble($current_price + $lot['step_rate']) ?></span>
                        </div>
                    </div>
                    <?php if ($show_bet_form): ?>
                        <form class="lot-item__form <?= !empty($errors) ? 'form--invalid' : null; ?>" action="./lot.php?id=<?= $lot['id'] ?>" method="post" autocomplete="off">
                            <p class="lot-item__form-item form__item <?= isset($errors['cost']) ? 'form__item--invalid' : null ?>">
                                <label for="cost">Ваша ставка</label>
                                <input id="cost" type="text" name="cost" placeholder="<?= format_ruble($current_price + $lot['step_rate']) ?>" value="<?= htmlspecialchars($bet_data['cost'] ?? ''); ?>">
                                <span class="form__error"><?= $errors['cost'] ?? '' ?></span>
                            </p>
                            <?= isset($errors['db']) ? '<span class="form__error form__error--bottom">' . htmlspecialchars($errors['db']) . '</span>' : ''; ?>
                            <button type="submit" class="button">Сделать ставку</button>
                        </form>
                    <?php endif; ?>
                </div>
                <div class="history">
                    <h3>История ставок</h3>
                    <?php if (!empty($bets)): ?>
                        <table class="history__list">
                            <?php foreach ($bets as $bet): ?>
                                <tr class="history__item">
                                    <td class="history__name"><?= htmlspecialchars($bet['user_name']) ?></td>
                                    <td class="history__price"><?= htmlspecialchars(format_ruble($bet["sum"])) ?></td>
                                    <td class="history__time"><?= time_ago($bet['date_placed']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    <?php else: ?>
                        <p>Еще нет ставок</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
</main>