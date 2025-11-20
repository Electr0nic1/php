<?php

date_default_timezone_set("Asia/Yekaterinburg");
define("seconds_in_hour", 3600);
define("seconds_in_minute", 60);

/**
 * Форматирует число в рубли с пробелами и символом ₽.
 *
 * @param float|int $num Число для форматирования.
 * @return string Отформатированная строка с рублями.
 */
function format_ruble($num): string
{
    return number_format($num, 0, ".", " ") . " ₽";
}

/**
 * Получает оставшееся время до указанной даты в часах и минутах.
 *
 * @param string $date Дата в формате 'YYYY-MM-DD'.
 * @return array Возвращает массив из двух элементов: [часы, минуты], оба в формате '00'.
 */
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

/**
 * Получает список всех новых лотов, которые еще не истекли.
 *
 * @param mysqli $connection Соединение с базой данных.
 * @return array Массив ассоциативных массивов с информацией о лотах.
 */
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

/**
 * Получает список всех категорий лотов.
 *
 * @param mysqli $connection Соединение с базой данных.
 * @return array Массив ассоциативных массивов с категориями.
 */
function get_categories(mysqli $connection)
{
    $sql = "SELECT * FROM categories;";
    $result = mysqli_query($connection, $sql);
    $categories = mysqli_fetch_all($result, MYSQLI_ASSOC);
    return $categories;
}

/**
 * Получает последние 10 ставок для конкретного лота.
 *
 * @param mysqli $connection Соединение с базой данных.
 * @param int $lot_id Идентификатор лота.
 * @return array Массив ставок с информацией о пользователях.
 */
function get_bets_by_lot_id(mysqli $connection, int $lot_id)
{
    $sql = "SELECT 
                bets.sum,
                bets.date_placed,
                bets.user_id,
                users.name AS user_name
            FROM bets
            JOIN users ON bets.user_id = users.id
            WHERE bets.lot_id = ?
            ORDER BY bets.date_placed DESC LIMIT 10;";
    $stmt = mysqli_prepare($connection, $sql);
    mysqli_stmt_bind_param($stmt, "i", $lot_id);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $bets = mysqli_fetch_all($result, MYSQLI_ASSOC);
    return $bets;
}

/**
 * Получает все ставки конкретного пользователя с информацией о лотах.
 *
 * @param mysqli $connection Соединение с базой данных.
 * @param int $user_id Идентификатор пользователя.
 * @return array Массив ставок с деталями лотов и контактов авторов.
 */
function get_bets_by_user_id(mysqli $connection, int $user_id)
{
    $sql = "SELECT 
                bets.id AS bet_id,
                bets.sum,
                bets.date_placed,
                
                lots.id AS lot_id,
                lots.name AS lot_name,
                lots.image_url,
                lots.expiration_date,
                lots.winner_id,
                lots.author_id,
                
                categories.name AS category_name,
                
                users.contacts AS author_contacts
            FROM bets
            JOIN lots ON bets.lot_id = lots.id
            JOIN categories ON lots.category_id = categories.id
            JOIN users ON lots.author_id = users.id
            WHERE bets.user_id = ?
            ORDER BY bets.date_placed DESC;";

    $stmt = mysqli_prepare($connection, $sql);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

/**
 * Получает данные одного лота по его идентификатору.
 *
 * @param mysqli $connection Соединение с базой данных.
 * @param int $lot_id Идентификатор лота.
 * @return array|null Ассоциативный массив с информацией о лоте или null, если лот не найден.
 */
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
                lots.author_id,
                categories.name AS category
            FROM lots
            JOIN categories ON lots.category_id = categories.id
            WHERE lots.id = ?;";
    $stmt = mysqli_prepare($connection, $sql);
    mysqli_stmt_bind_param($stmt, "i", $lot_id);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $lot = mysqli_fetch_assoc($result);
    return $lot;
}

/**
 * Отображает страницу ошибки с указанным кодом и завершает выполнение скрипта.
 *
 * @param int    $code             HTTP-код ошибки.
 * @param array  $categories       Список категорий для меню.
 * @param bool   $is_auth          Статус авторизации пользователя.
 * @param string $user_name        Имя текущего пользователя.
 * @param string $error_message    Краткое сообщение об ошибке.
 * @param string $error_description Подробное описание ошибки.
 *
 * @return void
 */
function show_error(
    int $code,
    array $categories,
    bool $is_auth,
    string $user_name,
    string $error_message,
    string $error_description
) {
    http_response_code($code);

    $content = include_template("error.php", [
        "categories" => $categories,
        "error_message" => $error_message,
        "error_description" => $error_description
    ]);

    $layout = include_template("layout.php", [
        "content" => $content,
        "is_auth" => $is_auth,
        "user_name" => $user_name,
        "categories" => $categories,
        "title" => $error_message
    ]);

    print($layout);
    exit();
}

/**
 * Проверяет длину строки и возвращает текст ошибки или null.
 *
 * @param string $value Значение для проверки.
 * @param int    $min   Минимальная длина.
 * @param int    $max   Максимальная длина.
 *
 * @return string|null Текст ошибки или null, если ошибок нет.
 */
function validate_length($value, int $min = 1, int $max = 255)
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

/**
 * Проверяет корректность выбранной категории.
 *
 * @param string $value      Название категории.
 * @param array  $categories Список категорий из БД.
 *
 * @return string|null Сообщение об ошибке или null при валидном значении.
 */
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

/**
 * Проверяет, что значение является положительным числом.
 *
 * @param mixed  $value      Значение для проверки.
 * @param string $field_name Название поля в сообщениях.
 *
 * @return string|null Текст ошибки либо null при корректном значении.
 */
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

/**
 * Проверяет, что значение является положительным целым числом.
 *
 * @param mixed  $value      Значение для проверки.
 * @param string $field_name Название поля в сообщениях.
 *
 * @return string|null Ошибка или null при успехе.
 */
function validate_positive_integer($value, $field_name = "Поле")
{
    if ($value === null || $value === '') {
        return "$field_name обязательно для заполнения";
    }

    if (filter_var($value, FILTER_VALIDATE_INT) === false) {
        return "$field_name должно быть целым числом";
    }

    $number = (int)$value;

    if ($number <= 0) {
        return "$field_name должно быть положительным числом";
    }

    return null;
}

/**
 * Валидирует дату: формат ГГГГ-ММ-ДД и дата должна быть в будущем.
 *
 * @param string $value Дата для проверки.
 *
 * @return string|null Сообщение об ошибке или null.
 */
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

/**
 * Проверяет загруженное изображение на тип и размер.
 *
 * @param array $file Данные о загруженном файле из $_FILES.
 *
 * @return true|string Возвращает true при успехе или текст ошибки.
 */
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

/**
 * Валидирует форму создания лота.
 *
 * @param array $data Данные из POST.
 * @param array $files Данные из $_FILES.
 * @param array $categories Список категорий из БД.
 *
 * @return array Ассоциативный массив ошибок (только поля с ошибками).
 */
function validate_lot_form($data, $files, $categories)
{
    $errors = [];

    $rules = [
        'lot-name' => fn($value) => validate_length($value, 5, 255),
        'category' => fn($value) => validate_category($value, $categories),
        'message' => fn($value) => validate_length($value, 20, 1023),
        'lot-rate' => fn($value) => validate_positive_number($value),
        'lot-step' => fn($value) => validate_positive_integer($value),
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

    return array_filter($errors);
}

/**
 * Сохраняет файл изображения в локальную директорию uploads/.
 *
 * @param array $file Массив из $_FILES с ключами tmp_name и name.
 *
 * @return string Путь к сохранённому файлу относительно корня проекта.
 */
function save_lot_image($file): string
{
    $file_name = uniqid() . '_' . $file['name'];
    $file_path = __DIR__ . '/uploads/';
    if (!is_dir($file_path)) mkdir($file_path, 0755, true);
    move_uploaded_file($file['tmp_name'], $file_path . $file_name);
    return 'uploads/' . $file_name;
}

/**
 * Добавляет новый лот в базу данных.
 *
 * @param mysqli $con Подключение к БД.
 * @param array $lot Ассоциативный массив данных формы.
 * @param string $file_url Путь к сохранённому изображению.
 * @param array $categories Список категорий.
 * @param int $author_id ID автора лота.
 *
 * @return int|null ID вставленного лота или null при ошибке.
 */
function save_lot_to_db($con, $lot, $file_url, $categories, $author_id)
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

/**
 * Проверяет email на формат, длину и корректность.
 *
 * @param string $email Email пользователя.
 * @param int $min Минимальная длина.
 * @param int $max Максимальная длина.
 *
 * @return string|null Ошибка или null при успехе.
 */
function validate_email_format(string $email, int $min = 5, int $max = 320): ?string
{
    $email = trim($email);

    if ($email === '') {
        return "Поле обязательно для заполнения";
    }

    $length = mb_strlen($email);

    if ($length < $min) {
        return "Минимальная длина email — $min символов";
    }

    if ($length > $max) {
        return "Максимальная длина email — $max символов";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return "Введите корректный e-mail";
    }

    return null;
}

/**
 * Проверяет существование email в базе данных.
 *
 * @param string $email Проверяемый email.
 * @param mysqli $connection Подключение к базе.
 *
 * @return string|null Текст ошибки или null, если email свободен.
 */
function check_email_in_db(string $email, mysqli $connection): ?string
{
    $sql = "SELECT id FROM users WHERE email = ?";
    $stmt = mysqli_prepare($connection, $sql);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (!$result) {
        return "Ошибка проверки email";
    }

    $exists = mysqli_num_rows($result) > 0;

    if ($exists) {
        return "Пользователь с таким email уже существует";
    }

    return null;
}

/**
 * Выводит относительное время "N минут/часов/дней назад".
 *
 * @param string $datetime Дата в формате Y-m-d H:i:s.
 *
 * @return string Готовая строка с относительным временем.
 */
function time_ago($datetime)
{
    $now = new DateTime();
    $then = new DateTime($datetime);
    $diff = $now->getTimestamp() - $then->getTimestamp();

    if ($diff < 60) {
        $seconds = $diff;
        return $seconds . ' ' . get_noun_plural_form($seconds, 'секунду', 'секунды', 'секунд') . ' назад';
    } elseif ($diff < 3600) {
        $minutes = floor($diff / 60);
        return $minutes . ' ' . get_noun_plural_form($minutes, 'минуту', 'минуты', 'минут') . ' назад';
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . ' ' . get_noun_plural_form($hours, 'час', 'часа', 'часов') . ' назад';
    } else {
        $days = floor($diff / 86400);
        return $days . ' ' . get_noun_plural_form($days, 'день', 'дня', 'дней') . ' назад';
    }
}

/**
 * Возвращает текущую цену лота (последняя ставка или стартовая цена).
 *
 * @param mysqli $connection Подключение к БД.
 * @param int $lot_id ID лота.
 * @param int $start_price Стартовая цена.
 *
 * @return int Актуальная цена.
 */
function get_current_price($connection, $lot_id, $start_price)
{
    $sql = "SELECT sum FROM bets WHERE lot_id = ? ORDER BY date_placed DESC LIMIT 1";
    $stmt = mysqli_prepare($connection, $sql);
    mysqli_stmt_bind_param($stmt, "i", $lot_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $last_sum);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);

    return $last_sum ?? $start_price;
}

/**
 * Проверяет, может ли пользователь сделать ставку.
 *
 * @param int|null $user_id ID пользователя (null если не авторизован).
 * @param array $lot Данные лота.
 * @param array|null $last_bets Последние ставки по лоту.
 *
 * @return bool true, если пользователь имеет право поставить.
 */
function can_place_bet(?int $user_id, array $lot, ?array $last_bets): bool
{
    if (!$user_id) {
        return false;
    }

    $time_left = get_dt_range($lot['expiration_date']);
    $hours_left = $time_left[0];
    $minutes_left = $time_left[1];
    if ($hours_left <= 0 && $minutes_left <= 0) {
        return false;
    }

    if ($user_id === $lot['author_id']) {
        return false;
    }

    if ($last_bets && $user_id === $last_bets[0]['user_id']) {
        return false;
    }

    return true;
}

/**
 * Получает лоты по категории с пагинацией.
 *
 * @param mysqli $connection Подключение к БД.
 * @param int $category_id ID категории.
 * @param int $limit Количество записей.
 * @param int $offset Смещение.
 *
 * @return array ['lots' => array, 'total' => int]
 */
function get_lots_by_category(mysqli $connection, int $category_id, int $limit, int $offset): array
{
    $sql = "SELECT SQL_CALC_FOUND_ROWS
                lots.id,
                lots.date_created,
                lots.name,
                lots.image_url,
                lots.start_price,
                lots.expiration_date,
                categories.name AS category
            FROM lots
            JOIN categories ON lots.category_id = categories.id
            WHERE lots.category_id = ?
              AND lots.expiration_date >= CURDATE()
            ORDER BY lots.date_created DESC
            LIMIT ? OFFSET ?";

    $stmt = mysqli_prepare($connection, $sql);
    if (!$stmt) {
        die('Ошибка подготовки запроса: ' . mysqli_error($connection));
    }

    mysqli_stmt_bind_param($stmt, "iii", $category_id, $limit, $offset);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $lots = mysqli_fetch_all($result, MYSQLI_ASSOC);

    $total_result = mysqli_query($connection, "SELECT FOUND_ROWS() AS total");
    $total = mysqli_fetch_assoc($total_result)['total'];

    return [
        'lots' => $lots,
        'total' => $total
    ];
}

/**
 * Возвращает имя категории по её ID.
 *
 * @param array $categories Список категорий.
 * @param int $category_id Идентификатор категории.
 *
 * @return string|null Название категории или null, если не найдено.
 */
function get_category_name_by_id(array $categories, int $category_id): ?string
{
    foreach ($categories as $category) {
        if ((int)$category["id"] === $category_id) {
            return $category["name"];
        }
    }
    return null;
}

/**
 * Поиск лотов по текстовому запросу (FULLTEXT).
 *
 * @param mysqli $connection Подключение к БД.
 * @param string $query Поисковая строка.
 * @param int $limit Количество результатов.
 * @param int $offset Смещение.
 *
 * @return array ['lots' => array, 'total' => int]
 */
function search_lots(mysqli $connection, string $query, int $limit, int $offset): array
{
    $query = trim($query);
    if ($query === '') {
        return ['lots' => [], 'total' => 0];
    }

    $words = explode(' ', $query);
    $ft_query = '';
    foreach ($words as $word) {
        if ($word !== '') {
            $ft_query .= $word . '* ';
        }
    }
    $ft_query = trim($ft_query);

    $sql = "SELECT SQL_CALC_FOUND_ROWS 
                lots.id,
                lots.date_created,
                lots.name,
                lots.image_url,
                lots.description,
                lots.step_rate,
                lots.start_price,
                lots.expiration_date,
                lots.author_id,
                categories.name AS category,
                MATCH(lots.name, lots.description) AGAINST(? IN BOOLEAN MODE)
            FROM lots
            JOIN categories ON lots.category_id = categories.id
            WHERE lots.expiration_date >= CURDATE()
              AND MATCH(lots.name, lots.description) AGAINST(? IN BOOLEAN MODE)
            ORDER BY lots.date_created DESC
            LIMIT ? OFFSET ?";

    $stmt = mysqli_prepare($connection, $sql);
    mysqli_stmt_bind_param($stmt, "ssii", $ft_query, $ft_query, $limit, $offset);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $lots = mysqli_fetch_all($result, MYSQLI_ASSOC);

    $total_result = mysqli_query($connection, "SELECT FOUND_ROWS() AS total");
    $total = mysqli_fetch_assoc($total_result)['total'];

    return [
        'lots' => $lots,
        'total' => $total
    ];
}
