INSERT INTO categories (name, symbol_code) VALUES
("Доски и лыжи", "boards"),
("Крепления", "attachment"),
("Ботинки", "boots"),
("Одежда", "clothing"),
("Инструменты", "tools"),
("Разное", "other");

INSERT INTO users (email, name, password, contacts) VALUES
("user1@example.com", "Иван Иванов", "qwerty123", "тел: +7-911-111-99-99"),
("user2@example.com", "Петр Петров", "asdfzx12", "тел: +7-922-222-22-22"),
("king_super@example.com", "Король", "king_the_best", "тел: +7-999-999-99-33");

INSERT INTO lots (name, description, image_url, start_price, expiration_date, step_rate, author_id, category_id) VALUES
("2014 Rossignol District Snowboard", "Отличный сноуборд для начинающих и продвинутых райдеров. Идеальное состояние.", "img/lot-1.jpg", 10999, "2025-09-17", 500, 1, 1),
("DC Ply Mens 2016/2017 Snowboard", "Профессиональный сноуборд от известного бренда DC. Премиальное качество.", "img/lot-2.jpg", 159999, "2025-09-22", 1000, 2, 1),
("Крепления Union Contact Pro 2015 года размер L/XL", "Надежные крепления для сноуборда. Подходят для любого типа ботинок.", "img/lot-3.jpg", 8000, "2025-09-19", 300, 1, 2),
("Ботинки для сноуборда DC Mutiny Charocal", "Комфортные и теплые ботинки для зимнего сезона. Размер 42.", "img/lot-4.jpg", 10999, "2025-09-27", 400, 2, 3),
("Куртка для сноуборда DC Mutiny Charocal", "Стильная и водонепроницаемая куртка. Защита от ветра и влаги.", "img/lot-5.jpg", 7500, "2025-10-20", 250, 3, 4),
("Маска Oakley Canopy", "Качественная маска с защитой от запотевания. Отличная видимость.", "img/lot-6.jpg", 5400, "2025-11-01", 200, 3, 6);

INSERT INTO bets (sum, user_id, lot_id,date_placed ) VALUES
(11500, 2, 1,DEFAULT),   
(12000, 3, 1, "2025-10-22 13:30:10"),    
(165000, 1, 2,"2025-08-05 13:30:10"),   
(8500, 2, 3, "2025-07-25 13:30:10"),   
(11500, 3, 4, "2025-08-17 13:30:10"); 

-- получить список всех категорий;
SELECT * FROM categories;

-- получить cписок лотов, которые еще не истекли отсортированных по дате публикации, от новых к старым. 
-- Каждый лот должен включать название, 
-- стартовую цену, ссылку на изображение, название категории и дату окончания торгов;
SELECT 
    l.name,
    l.start_price,
    l.image_url,
    c.name AS category_name,
    l.expiration_date
FROM lots l
JOIN categories c ON l.category_id = c.id
WHERE l.expiration_date > CURDATE()
ORDER BY l.date_created DESC;

-- показать информацию о лоте по его ID. 
-- Вместо id категории должно выводиться  название категории, к которой принадлежит лот из таблицы категорий;
SELECT l.id,
    l.name,
    l.description,
    l.image_url,
    l.start_price,
    l.expiration_date,
    l.step_rate,
    l.date_created,
    c.name AS category_name,
    u.name AS author_name,
    us.name AS winner_name
FROM lots l
JOIN categories c ON l.category_id = c.id
JOIN users u ON l.author_id = u.id
LEFT JOIN users us on l.winner_id=us.id
WHERE l.id = 1;


-- обновить название лота по его идентификатору;
UPDATE lots 
SET name = "Sport Snowboard (обновленная модель)" 
WHERE id = 1;

-- получить список ставок для лота по его идентификатору с сортировкой по дате. 
-- Список должен содержать дату и время размещения ставки, цену, 
-- по которой пользователь готов приобрести лот, название лота и имя пользователя, сделавшего ставку
SELECT 
    b.date_placed,
    b.sum,
    l.name AS lot_name,
    u.name AS user_name
FROM bets b
JOIN lots l ON b.lot_id = l.id
JOIN users u ON b.user_id = u.id
WHERE b.lot_id = 1 
ORDER BY b.date_placed DESC;

-- добавить новый лот в таблицу lots;
INSERT INTO lots (
    name,
    description,
    image_url,
    start_price,
    expiration_date,
    step_rate,
    author_id,
    category_id
)
VALUES (
    'Горнолыжные палки Atomic AMT Carbon',
    'Легкие и прочные палки из углеродного волокна. Отличный выбор для любителей горных склонов.',
    'img/lot-7.jpg',
    10000,
    DATE_ADD(CURDATE(), INTERVAL 10 DAY),
    500,
    1,
    6
);