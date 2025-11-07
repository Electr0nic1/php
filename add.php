<?php

require_once("init.php");
require_once("helpers.php");
require_once("functions.php");

$categories = get_categories($connection);

if (!isset($_SESSION['user'])) {
    show_error(
        403,
        $categories,
        $is_auth,
        $user_name,
        "403 Доступ запрещён",
        "У вас нет прав для добавления лота."
    );
}

$errors = [];
$lot_data = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lot_data = $_POST;

    $errors = validateLotForm($lot_data, $_FILES, $categories);

    if (empty($errors)) {
        $file_url = saveLotImage($_FILES['lot-img']);
        $author_id = $_SESSION['user']['id'];
        $lot_id = saveLotToDb($connection, $lot_data, $file_url, $categories, $author_id);

        if ($lot_id) {
            header("Location: lot.php?id=" . $lot_id);
            exit();
        } else {
            $errors['db'] = 'Ошибка при сохранении лота в базу данных';
        }
    }
}

$content = include_template("add_template.php", [
    "categories" => $categories,
    "errors" => $errors,
    "lot_data" => $lot_data
]);

$layout = include_template("layout.php", [
    "content" => $content,
    "is_auth" => $is_auth,
    "user_name" => $user_name,
    "categories" => $categories,
    "title" => "Добавление лота"
]);

print($layout);
