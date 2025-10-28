<?php 

$is_auth = rand(0, 1);
$user_name = "Никита";
$title = "Главная";

define("DB_HOST", "localhost");
define("DB_USER", "root");
define("DB_PASSWORD", "");
define("DB_NAME", "php");
define("DB_CHARSET", "utf8");

$connection = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

mysqli_set_charset($connection, DB_CHARSET);