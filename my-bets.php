<?php

require_once("init.php");
require_once("helpers.php");
require_once("functions.php");

$categories = get_categories($connection);
$my_bets = get_bets_by_user_id($connection, $_SESSION['user']['id']);

$content = include_template("my-bets_template.php", [
    "categories" => $categories,
    "my_bets" => $my_bets
]);

$layout = include_template("layout.php", [
    "content" => $content,
    "is_auth" => $is_auth,
    "user_name" => $user_name,
    "categories" => $categories,
    "title" => "Мои ставки"
]);

print($layout);
