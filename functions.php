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
