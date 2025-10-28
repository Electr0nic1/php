<?php 

require_once("init.php");
require_once("helpers.php");
require_once("functions.php");

$categories = get_categories($connection);
$lots = get_new_lots($connection);


$main = include_template("main.php", [
    "categories" => $categories, 
    "lots" => $lots
]);

$layout = include_template("layout.php", [
    "main" => $main,
    "is_auth" => $is_auth, 
    "user_name" => $user_name, 
    "categories" => $categories, 
    "title" => $title
]);

print($layout);

?>