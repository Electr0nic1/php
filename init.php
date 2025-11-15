<?php

session_start();

$is_auth = false;
$user_name = "";
$title = "Главная страница";

define("DB_HOST", "localhost");
define("DB_USER", "root");
define("DB_PASSWORD", "");
define("DB_NAME", "php");
define("DB_CHARSET", "utf8");

$connection = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

mysqli_set_charset($connection, DB_CHARSET);

if (isset($_SESSION['user'])) {
    $is_auth = true;
    $user_name = $_SESSION['user']['name'];
}

include_once 'winners.php';