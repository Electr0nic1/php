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
    <section class="rates container">
        <h2>Мои ставки</h2>
        <table class="rates__list">
            <?php foreach ($my_bets as $bet):
                $time_left = get_dt_range($bet["expiration_date"]);
                $hours_left = $time_left[0];
                $minutes_left = $time_left[1];
                $time_class = ($hours_left < 1) ? "timer--finishing" : null;

                $is_winner = (int)($bet['winner_id'] ?? 0) === (int)($_SESSION['user']['id'] ?? 0);
                $is_ended = ((int)$hours_left === 0) && ((int)$minutes_left === 0);
                $win_class = $is_winner ? 'rates__item--win' : '';
                $end_class = !$is_winner && $is_ended ? 'rates__item--end' : '';
            ?>
                <tr class="rates__item <?= $win_class ?> <?= $end_class ?>">
                    <td class="rates__info">
                        <div class="rates__img">
                            <img src=<?= $bet['image_url'] ?> width="54" height="40" alt="Сноуборд">
                        </div>
                        <h3 class="rates__title"><a href="lot.php?id=<?= $bet['lot_id'] ?>"><?= $bet['lot_name'] ?></a></h3>
                    </td>
                    <td class="rates__category">
                        <?= $bet['category_name'] ?>
                    </td>
                    <td class="rates__timer">
                        <?php if ($is_winner): ?>
                            <div class="timer timer--win">Ставка выиграла</div>
                        <?php elseif ($is_ended): ?>
                            <div class="timer timer--end">Торги окончены</div>
                        <?php else: ?>
                            <div class="timer <?= $time_class; ?>"><?= $hours_left ?>:<?= $minutes_left ?></div>
                        <?php endif; ?>
                    </td>
                    <td class="rates__price">
                        <?= format_ruble($bet['sum']) ?>
                    </td>
                    <td class="rates__time">
                        <?= time_ago($bet['date_placed']) ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    </section>
</main>