<?php
include('db_connection.php');
include('header.php');

// SQL-запрос для получения новостей
$query_news = "SELECT * FROM News ORDER BY publish_date DESC, publish_time DESC";
$stmt_news = $pdo->prepare($query_news);
$stmt_news->execute();
$news = $stmt_news->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Новости - ClothesForU</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .news-container {
            display: flex;
            flex-direction: column;
            gap: 40px;
            padding: 40px 20px;
        }
        .news-card {
            display: flex;
            align-items: center;
            background-color: white;
            border-radius: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: transform 0.3s ease;
            padding: 20px;
            position: relative;
        }
        .news-card:hover {
            transform: scale(1.02);
        }
        .news-card img {
            width: 250px;
            height: 350px;
            object-fit: cover;
            border-radius: 10px;
            margin-right: 20px;
        }
        .news-card-content {
            flex-grow: 1;
            padding: 10px;
        }
        .news-card h2 {
            font-size: 28px;
            color: #e4c9f7;
            margin-bottom: 15px;
            font-weight: bold;
        }
        .news-card p {
            font-size: 18px;
            color: #555;
            margin-bottom: 20px;
            line-height: 1.6;
            text-align: justify;
        }
        .news-card .read-more {
            font-size: 20px;
            color: #e089f5;
            background-color: transparent;
            border: 2px solid #e089f5;
            padding: 12px 25px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            display: inline-block;
        }
        .news-card .read-more:hover {
            background-color: #e089f5;
            color: white;
        }
        .news-date {
            position: absolute;
            bottom: 15px;
            right: 20px;
            font-size: 18px;
            font-weight: bold;
            color: #e089f5;
        }
    </style>
</head>
<body>

<main>
    <div class="news-container">
        <?php foreach ($news as $item): ?>
            <div class="news-card">
                <img src="/images/<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['title']) ?>">
                <div class="news-card-content">
                    <h2><?= htmlspecialchars($item['title']) ?></h2>
                    <p><?= mb_substr(strip_tags($item['content']), 0, 150) ?>...</p>
                    <a href="news_detail.php?id=<?= $item['news_id'] ?>" class="read-more">Читать далее</a>
                    <div class="news-date">
                        <?= date('d.m.Y H:i', strtotime($item['publish_date'] . ' ' . $item['publish_time'])) ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</main>

</body>
</html>
<?php include('footer.php'); ?>
