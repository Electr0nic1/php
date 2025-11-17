<?php

require_once("init.php");
require_once("helpers.php");
require_once("functions.php");

$categories = get_categories($connection);

if (isset($_SESSION['user'])) {
    show_error(
        403,
        $categories,
        $is_auth,
        $user_name,
        "403 Доступ запрещён",
        "Вы уже авторизованы и не можете перейти на эту страницу."
    );
}

$errors = [];
$login_data = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login_data = $_POST;

    $rules = [
        'email' => fn($value) => validate_email_format($value),
        'password' => fn($value) => validate_length($value, 8, 255),
    ];

    foreach ($rules as $field => $validator) {
        if (!isset($login_data[$field]) || trim($login_data[$field]) === '') {
            $errors[$field] = "Поле обязательно для заполнения";
        } else {
            $errors[$field] = $validator($login_data[$field]) ?? null;
        }
    }

    $errors = array_filter($errors);

    if (empty($errors)) {
        $sql = "SELECT * FROM users WHERE email = ?";
        $stmt = mysqli_prepare($connection, $sql);
        mysqli_stmt_bind_param($stmt, "s", $login_data['email']);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        $login_error = "Неверный логин или пароль";

        if ($result && mysqli_num_rows($result) > 0) {
            $user = mysqli_fetch_assoc($result);

            if (password_verify($login_data['password'], $user['password'])) {
                $_SESSION['user'] = $user;
                header("Location: index.php");
                exit();
            } else {
                $errors['form'] = $login_error;
            }
        } else {
            $errors['form'] = $login_error;
        }
    }
}

$content = include_template("login_template.php", [
    "categories" => $categories,
    "errors" => $errors,
    "login_data" => $login_data
]);

$layout = include_template("layout.php", [
    "content" => $content,
    "is_auth" => $is_auth,
    "user_name" => $user_name,
    "categories" => $categories,
    "title" => "Вход"
]);

print($layout);
