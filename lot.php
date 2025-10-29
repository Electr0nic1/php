<?php

require_once("init.php");
require_once("helpers.php");
require_once("functions.php");

$lot_id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);
$categories = get_categories($connection);

if (!$lot_id) {
    show_404($categories, $is_auth, $user_name);
}

$lot = get_lot_by_id($connection, $lot_id);

if (!$lot) {
    show_404($categories, $is_auth, $user_name);
}

$content = include_template("lot_template.php", [
    "lot" => $lot,
    "categories" => $categories
]);

$layout = include_template("layout.php", [
    "content" => $content,
    "is_auth" => $is_auth,
    "user_name" => $user_name,
    "categories" => $categories,
    "title" => $lot["name"]
]);

print($layout);
