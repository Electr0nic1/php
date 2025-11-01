<?php

date_default_timezone_set("Asia/Yekaterinburg");
define("seconds_in_hour", 3600);
define("seconds_in_minute", 60);

function format_ruble($num): string
{
    return number_format($num, 0, ".", " ") . " ₽";
}

function get_dt_range($date)
{
    $now = time();
    $expire = strtotime($date . "23:59:59");
    $diff = $expire - $now;

    if ($diff <= 0) {
        return ['00', '00'];
    }

    $hours = floor($diff / seconds_in_hour);
    $minutes = floor(($diff % seconds_in_hour) / seconds_in_minute);

    $formatted_hours = str_pad($hours, 2, "0", STR_PAD_LEFT);
    $formatted_minutes = str_pad($minutes, 2, "0", STR_PAD_LEFT);

    return [$formatted_hours, $formatted_minutes];
}

function show_404(array $categories, bool $is_auth, string $user_name)
{
    http_response_code(404);

    $content = include_template("404.php", [
        "categories" => $categories
    ]);

    $layout = include_template("layout.php", [
        "content" => $content,
        "is_auth" => $is_auth,
        "user_name" => $user_name,
        "categories" => $categories,
        "title" => "Лот не найден"
    ]);

    print($layout);
    exit();
}

function get_new_lots(mysqli $connection)
{
    $sql = "SELECT 
                lots.id,
                lots.date_created,
                lots.name,
                lots.image_url,
                lots.start_price,
                lots.expiration_date,
                categories.name AS category
            FROM lots
            JOIN categories ON lots.category_id = categories.id
            WHERE lots.expiration_date >= CURDATE()
            ORDER BY lots.date_created DESC;";
    $result = mysqli_query($connection, $sql);
    $lots = mysqli_fetch_all($result, MYSQLI_ASSOC);
    return $lots;
}

function get_categories(mysqli $connection)
{
    $sql = "SELECT * FROM categories;";
    $result = mysqli_query($connection, $sql);
    $categories = mysqli_fetch_all($result, MYSQLI_ASSOC);
    return $categories;
}

function get_lot_by_id(mysqli $connection, int $lot_id)
{
    $sql = "SELECT 
                lots.id,
                lots.date_created,
                lots.name,
                lots.image_url,
                lots.description,
                lots.step_rate,
                lots.start_price,
                lots.expiration_date,
                categories.name AS category
            FROM lots
            JOIN categories ON lots.category_id = categories.id
            WHERE lots.id = ?;";
    $stmt = db_get_prepare_stmt($connection, $sql, [$lot_id]);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $lot = mysqli_fetch_assoc($result);
    return $lot;
}

function validate_length($value, $min = 1, $max = 255)
{
    $value = trim($value);

    if ($value === '') {
        return "Поле обязательно для заполнения";
    }

    $length = mb_strlen($value);

    if ($length < $min) {
        return "Минимальная длина поля — $min символов";
    }

    if ($length > $max) {
        return "Максимальная длина поля — $max символов";
    }

    return null;
}

function validate_category($value, $categories)
{
    if (empty($value)) {
        return "Выберите категорию";
    }

    $valid_categories = array_column($categories, 'name');
    if (!in_array($value, $valid_categories)) {
        return "Выберите корректную категорию";
    }

    return null;
}

function validate_positive_number($value, $field_name = "Поле")
{
    if ($value === null || $value === '') {
        return "$field_name обязательно для заполнения";
    }

    if (!is_numeric($value)) {
        return "$field_name должно быть числом";
    }

    $number = $value + 0;
    if ($number <= 0) {
        return "$field_name должно быть положительным числом";
    }

    return null;
}

function validate_date($value)
{
    if (empty($value)) {
        return "Заполните это поле";
    }

    if (!is_date_valid($value)) {
        return "Введите дату в формате ГГГГ-ММ-ДД";
    }

    $current_date = date('Y-m-d');
    if (strtotime($value) <= strtotime($current_date)) {
        return "Дата должна быть больше текущей";
    }

    return null;
}

function validate_image($file)
{
    $allowed_types = ['image/jpeg', 'image/png'];
    $max_size = 5 * 1024 * 1024;

    if ($file['size'] > $max_size) {
        return "Размер файла не должен превышать 5MB";
    }

    $file_info = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($file_info, $file['tmp_name']);
    finfo_close($file_info);

    if (!in_array($mime_type, $allowed_types)) {
        return "Допустимы только файлы изображений (JPEG, PNG)";
    }

    return true;
}


function validateLotForm($data, $files, $categories)
{
    $errors = [];

    $rules = [
        'lot-name' => fn($value) => validate_length($value, 5, 255),
        'category' => fn($value) => validate_category($value, $categories),
        'message' => fn($value) => validate_length($value, 20, 1023),
        'lot-rate' => fn($value) => validate_positive_number($value),
        'lot-step' => fn($value) => validate_positive_number($value),
        'lot-date' => fn($value) => validate_date($value),
    ];

    foreach ($rules as $field => $validator) {
        $errors[$field] = isset($data[$field])
            ? $validator($data[$field]) ?? null
            : "Поле обязательно для заполнения";
    }

    if (!isset($files['lot-img']) || $files['lot-img']['error'] !== UPLOAD_ERR_OK) {
        $errors['lot-img'] = 'Добавьте изображение лота';
    } elseif (($fileError = validate_image($files['lot-img'])) !== true) {
        $errors['lot-img'] = $fileError;
    }

    // var_dump($errors);

    return array_filter($errors);
}

function saveLotImage($file): string
{
    $file_name = uniqid() . '_' . $file['name'];
    $file_path = __DIR__ . '/uploads/';
    if (!is_dir($file_path)) mkdir($file_path, 0755, true);
    move_uploaded_file($file['tmp_name'], $file_path . $file_name);
    return 'uploads/' . $file_name;
}

function saveLotToDb($con, $lot, $file_url, $categories, $author_id)
{
    $category_id = null;
    foreach ($categories as $category) {
        if ($category['name'] === $lot['category']) {
            $category_id = $category['id'];
            break;
        }
    }

    $sql = "INSERT INTO lots 
            (name, description, image_url, start_price, step_rate, expiration_date, category_id, author_id) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

    $data = [
        $lot['lot-name'],
        $lot['message'],
        $file_url,
        $lot['lot-rate'],
        $lot['lot-step'],
        $lot['lot-date'],
        $category_id,
        $author_id
    ];

    $stmt = db_get_prepare_stmt($con, $sql, $data);
    mysqli_stmt_execute($stmt);

    return mysqli_stmt_affected_rows($stmt) > 0 ? mysqli_insert_id($con) : null;
}

