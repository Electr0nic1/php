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
$sign_up_data = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sign_up_data = $_POST;

    $rules = [
        'email' => fn($value) => validate_email_format($value) ?: check_email_in_db($value, $connection, false),
        'password' => fn($value) => validate_length($value, 8, 255),
        'name' => fn($value) => validate_length($value, 2, 255),
        'message' => fn($value) => validate_length($value, 5, 255),
    ];

    foreach ($rules as $field => $validator) {
        $errors[$field] = isset($sign_up_data[$field])
            ? $validator($sign_up_data[$field]) ?? null
            : "Поле обязательно для заполнения";
    }

    $errors = array_filter($errors);

    if (empty($errors)) {
        $password_hash = password_hash($sign_up_data['password'], PASSWORD_DEFAULT);

        $sql = "INSERT INTO users (email, password, name, contacts) VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($connection, $sql);
        mysqli_stmt_bind_param(
            $stmt,
            "ssss",
            $sign_up_data['email'],
            $password_hash,
            $sign_up_data['name'],
            $sign_up_data['message']
        );
        mysqli_stmt_execute($stmt);

        if (mysqli_stmt_affected_rows($stmt) > 0) {
            header("Location: login.php");
            exit();
        } else {
            $errors['db'] = 'Ошибка при сохранении пользователя в базу данных';
        }
    }
}

$content = include_template("sign-up_template.php", [
    "categories" => $categories,
    "errors" => $errors,
    "sign_up_data" => $sign_up_data
]);

$layout = include_template("layout.php", [
    "content" => $content,
    "is_auth" => $is_auth,
    "user_name" => $user_name,
    "categories" => $categories,
    "title" => "Регистрация"
]);

print($layout);
