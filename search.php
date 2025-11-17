<?php

require_once("init.php");
require_once("helpers.php");
require_once("functions.php");

$categories = get_categories($connection);

$query = filter_input(INPUT_GET, 'search', FILTER_SANITIZE_URL) ?: '';

$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 9;
$offset = ($page - 1) * $limit;

$search_result = search_lots($connection, $query, $limit, $offset);

$lots = $search_result['lots'];
$total_lots = $search_result['total'];
$total_pages = ceil($total_lots / $limit);

$content = include_template("search_template.php", [
    "categories" => $categories,
    "lots" => $lots,
    "query" => $query,
    "total_pages" => $total_pages,
    "page" => $page
]);

$layout = include_template("layout.php", [
    "content" => $content,
    "is_auth" => $is_auth,
    "user_name" => $user_name,
    "categories" => $categories,
    "title" => "Результаты поиска"
]);

print($layout);
