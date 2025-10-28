<?php

date_default_timezone_set("Asia/Yekaterinburg");
define("seconds_in_hour", 3600);
define("seconds_in_minute", 60);

function format_ruble ($num): string {
    return number_format($num, 0, ".", " ") . " ₽";
}

function get_dt_range ($date)  {

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

function get_new_lots (mysqli $connection) {
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

function get_categories (mysqli $connection) {
    $sql = "SELECT * FROM categories;";
    $result = mysqli_query($connection, $sql);
    $categories = mysqli_fetch_all($result, MYSQLI_ASSOC);
    return $categories;
}

?>