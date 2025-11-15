<?php

require_once("init.php");
require_once("helpers.php");
require_once("functions.php");

$category_id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);
$categories = get_categories($connection);
$title = getCategoryNameById($categories, $category_id);

if (!$category_id) {
    show_error(404, $categories, $is_auth, $user_name, "404 Категория не найдена", "Категорияы с указанным идентификатором не существует.");
}

$lots = get_lots_by_category($connection, $category_id);

$content = include_template("all-lots_template.php", [
    "category_id" => $category_id,
    "lots" => $lots,
    "categories" => $categories,
    "title" => $title
]);

$layout = include_template("layout.php", [
    "content" => $content,
    "is_auth" => $is_auth,
    "user_name" => $user_name,
    "categories" => $categories,
    "title" => $title
]);

print($layout);
