<?php

require_once("init.php");
require_once("helpers.php");
require_once("functions.php");

$lot_id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);
$categories = get_categories($connection);

if (!$lot_id) {
    show_error(404, $categories, $is_auth, $user_name, "404 Лот не найден", "Лот с указанным идентификатором не существует.");
}

$lot = get_lot_by_id($connection, $lot_id);

if (!$lot) {
    show_error(404, $categories, $is_auth, $user_name, "404 Лот не найден", "Лот с указанным идентификатором не существует.");
}

$bets = get_bets_by_lot_id($connection, $lot_id);
$current_price = get_current_price($connection, $lot_id, $lot['start_price']);
$show_bet_form = can_place_bet($_SESSION['user']['id'] ?? null, $lot, $bets);

$bet_data = [];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['user'])) {
        show_error(
            403,
            $categories,
            $is_auth,
            $user_name,
            "403 Доступ запрещён",
            "У вас нет прав на добавление ставки."
        );
    }

    $bet_data = $_POST;

    $errors['cost'] = isset($bet_data['cost'])
        ? validate_positive_integer($bet_data['cost'], "Стоимость")
        : "Поле обязательно для заполнения";

    if (empty($errors['cost'])) {
        $min_bid = $current_price + $lot['step_rate'];
        if ((int)$bet_data['cost'] < $min_bid) {
            $errors['cost'] = "Ставка должна быть не меньше " . $min_bid;
        }
    }

    $errors = array_filter($errors);

    if (empty($errors)) {
        $user_id = $_SESSION['user']['id'];
        $sum = (int)$bet_data['cost'];


        $sql = "INSERT INTO bets (lot_id, user_id, sum) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($connection, $sql);

        if (!$stmt) {
            $errors['db'] = "Ошибка подготовки запроса: " . mysqli_error($connection);
        } else {
            mysqli_stmt_bind_param($stmt, "iii", $lot_id, $user_id, $sum);

            if (mysqli_stmt_execute($stmt)) {
                if (mysqli_stmt_affected_rows($stmt) > 0) {
                    header("Location: lot.php?id=" . $lot_id);
                    exit();
                } else {
                    $errors['db'] = 'Ставка не была сохранена';
                }
            } else {
                $errors['db'] = "Ошибка выполнения запроса: " . mysqli_stmt_error($stmt);
            }
        }
    }
}

$content = include_template("lot_template.php", [
    "lot" => $lot,
    "categories" => $categories,
    "bets" => $bets,
    "errors" => $errors,
    "bet_data" => $bet_data,
    "current_price" => $current_price,
    "show_bet_form" => $show_bet_form
]);

$layout = include_template("layout.php", [
    "content" => $content,
    "is_auth" => $is_auth,
    "user_name" => $user_name,
    "categories" => $categories,
    "title" => $lot["name"]
]);

print($layout);
