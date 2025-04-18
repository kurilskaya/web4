<?php
include('db_connection.php');
include('header.php');

// Проверяем, есть ли параметр id в URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Ошибка: Новость не найдена!");
}

$news_id = intval($_GET['id']);

// SQL-запрос для получения новости
$query = "SELECT * FROM News WHERE news_id = :news_id";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':news_id', $news_id, PDO::PARAM_INT);
$stmt->execute();
$news_item = $stmt->fetch(PDO::FETCH_ASSOC);

// Если новость не найдена
if (!$news_item) {
    die("Ошибка: Новость не найдена!");
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($news_item['title']) ?> - Новости</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            background-color: #f8eefb;
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
        }
        .news-detail-container {
            max-width: 900px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            padding: 30px;
            margin: 40px 0;
        }
        .news-title-container {
            background: linear-gradient(to right, #e089f5, #f5b1f7);
            padding: 20px;
            border-radius: 15px;
            text-align: center;
            margin-bottom: 25px;
        }
        .news-title {
            font-size: 32px;
            font-weight: bold;
            color: white;
        }
        .news-image {
            width: 100%;
            height: auto;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .news-text {
            font-size: 18px;
            color: #333;
            line-height: 1.8;
            text-align: justify;
        }
        .news-date {
            font-size: 18px;
            font-weight: bold;
            color: #e089f5;
            text-align: right;
            margin-top: 20px;
        }
        .back-button {
            display: inline-block;
            background: #e089f5;
            color: white;
            text-decoration: none;
            font-size: 18px;
            padding: 12px 25px;
            border-radius: 10px;
            transition: background 0.3s ease;
            margin-top: 20px;
            display: flex;
            align-items: center;
            width: fit-content;
        }
        .back-button:hover {
            background: #d672ea;
        }
        .back-button svg {
            width: 20px;
            height: 20px;
            margin-right: 10px;
            fill: white;
        }
    </style>
</head>
<body>

<div class="news-detail-container">
    <div class="news-title-container">
        <h1 class="news-title"><?= htmlspecialchars($news_item['title']) ?></h1>
    </div>

    <img src="/images/<?= htmlspecialchars($news_item['image']) ?>" alt="<?= htmlspecialchars($news_item['title']) ?>" class="news-image">

    <p class="news-text"><?= nl2br(htmlspecialchars($news_item['content'])) ?></p>

    <div class="news-date">
        Опубликовано: <?= date('d.m.Y H:i', strtotime($news_item['publish_date'] . ' ' . $news_item['publish_time'])) ?>
    </div>

    <a href="news.php" class="back-button">
        <svg viewBox="0 0 24 24">
            <path d="M15.41 16.59L10.83 12l4.58-4.59L14 6l-6 6 6 6z"/>
        </svg>
        Назад к новостям
    </a>
</div>

</body>
</html>
