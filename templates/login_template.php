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
    <form class="form container <?= !empty($errors) ? 'form--invalid' : null; ?>" action="./login.php" method="post">
        <h2>Вход</h2>
        <div class="form__item <?= isset($errors['email']) ? 'form__item--invalid' : null ?>">
            <label for="email">E-mail <sup>*</sup></label>
            <input id="email" type="text" name="email" placeholder="Введите e-mail" value="<?= htmlspecialchars($login_data['email'] ?? ''); ?>">
            <span class="form__error"><?= $errors['email'] ?? '' ?></span>
        </div>
        <div class="form__item form__item--last <?= isset($errors['password']) ? 'form__item--invalid' : null ?>">
            <label for="password">Пароль <sup>*</sup></label>
            <input id="password" type="password" name="password" placeholder="Введите пароль" value="<?= htmlspecialchars($login_data['password'] ?? ''); ?>">
            <span class="form__error"><?= $errors['password'] ?? '' ?></span>
        </div>
        <span class="form__error form__error--bottom">Пожалуйста, исправьте ошибки в форме.</span>
        <button type="submit" class="button">Войти</button>
    </form>
</main>