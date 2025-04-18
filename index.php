<?php
// Подключаем соединение с базой данных
include('db_connection.php');
session_start();  // Инициализация сессии

// Запрос для получения товаров из базы данных
$query = $pdo->query("SELECT * FROM assortment ORDER BY RAND() LIMIT 5;
"); 
$products = $query->fetchAll(PDO::FETCH_ASSOC);

// Запрос для получения категорий товаров
$query_categories = $pdo->query("SELECT * FROM categories");
$categories = $query_categories->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop Clothes - Главная</title>
    <link rel="stylesheet" href="style.css"> <!-- Подключение CSS файла -->
</head>
<body>

    <!-- Хедер -->
    <?php include('header.php'); ?>

    <!-- Карточки товаров -->
    <section class="product-cards">
        <div class="container">
            <h2>Вам это может понравиться:</h2>
            <div class="products-grid">
                <?php foreach ($products as $product): ?>
                    <div class="product-card">
                        <a href="product.php?id=<?php echo $product['product_id']; ?>">
                            <img src="<?php echo $product['photo']; ?>" alt="<?php echo $product['product_name']; ?>" />
                            <div class="product-info">
                                <h3><?php echo $product['product_name']; ?></h3>
                                <p><?php echo number_format($product['price'], 0, '.', ' '); ?> ₽</p>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Блок с категориями товаров -->
    <section class="categories">
        <div class="container">
            <h2>Доступные категории</h2>
            <div class="categories-grid">
                <?php foreach ($categories as $category): ?>
                    <div class="category-card">
                        <a href="catalog.php?category=<?php echo $category['category_id']; ?>">
                            <img src="images/<?php echo $category['category_name']; ?>.jpg" alt="<?php echo $category['category_name']; ?>" />
                            <div class="category-info">
                                <h3><?php echo $category['category_name']; ?></h3>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Футер -->
    <?php include('footer.php'); ?>

</body>
</html>
