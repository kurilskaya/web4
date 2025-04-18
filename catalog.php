<?php
include('db_connection.php');
include('header.php'); 
session_start();  // Инициализация сессии

// Получение параметров фильтрации
$selected_color = $_GET['color'] ?? '';
$selected_size = $_GET['size'] ?? '';
$selected_category = $_GET['category'] ?? '';

// SQL-запрос для получения всех категорий
$query_categories = "SELECT * FROM categories";
$categories = $pdo->query($query_categories)->fetchAll(PDO::FETCH_ASSOC);

// SQL-запрос для получения всех доступных размеров
$query_sizes = "SELECT * FROM sizes";
$sizes = $pdo->query($query_sizes)->fetchAll(PDO::FETCH_ASSOC);

// SQL-запрос для получения всех доступных цветов
$query_colors = "SELECT DISTINCT color FROM assortment WHERE color IS NOT NULL";
$colors = $pdo->query($query_colors)->fetchAll(PDO::FETCH_ASSOC);

// SQL-запрос для получения товаров с учетом фильтрации
$query_products = "
    SELECT a.product_id, a.product_name, a.price, a.color, a.photo, c.category_name
    FROM assortment a
    LEFT JOIN categories c ON a.category_id = c.category_id
    WHERE 1=1";

$params = [];

// Фильтрация по цвету
if (!empty($selected_color)) {
    $query_products .= " AND a.color = :color";
    $params[':color'] = $selected_color;
}

// Фильтрация по категории
if (!empty($selected_category)) {
    $query_products .= " AND a.category_id = :category";
    $params[':category'] = $selected_category;
}

// Фильтрация по размеру (проверка через таблицу product_sizes)
if (!empty($selected_size)) {
    $query_products .= " AND a.product_id IN (
        SELECT product_id FROM product_sizes WHERE size_id = :size_id
    )";
    $params[':size_id'] = $selected_size;
}

$stmt_products = $pdo->prepare($query_products);
$stmt_products->execute($params);
$products = $stmt_products->fetchAll(PDO::FETCH_ASSOC);

// Проверяем, авторизован ли пользователь
$user_logged_in = isset($_SESSION['user_id']);

?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Каталог - ClothesForU</title>
    <link rel="stylesheet" href="style.css">
    <style>
		body {
            background-color: #f8f8f8;
            position: relative;
        }
        .catalog-wrapper {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
            background-color: #f9f9f9;
        }
        .filters-container {
            background: white;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
        }
        .filters-container select, .filters-container button {
            padding: 7px;
            font-size: 16px;
        }
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            width: 100%;
            max-width: 1200px;
        }
        .product-card {
            position: relative;
            background: white;
            padding: 10px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
            overflow: hidden;
            cursor: pointer;
        }
        .product-card img {
            width: 100%;
            border-radius: 10px;
            transition: transform 0.3s ease-in-out;
        }
        .product-card:hover img {
            transform: scale(1.05);
        }
        .size-overlay {
            position: absolute;
            bottom: -50px;
            left: 0;
            width: 100%;
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 5px;
            transition: bottom 0.3s ease-in-out;
        }
        .product-card:hover .size-overlay {
            bottom: 0;
        }
        .favorite-icon {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 24px;
            cursor: pointer;
        }
		
		.favorite-icon img {
    width: 24px;
    height: 24px;
    transition: transform 0.2s ease-in-out;
}
.favorite-icon:hover img {
    transform: scale(1.1);
}

    </style>
</head>
<body>

<main>
    <div class="catalog-wrapper">
        <h2>Каталог</h2>

        <form method="GET" class="filters-container">
            <select name="color">
                <option value="">Выберите цвет</option>
                <?php foreach ($colors as $color): ?>
                    <option value="<?= htmlspecialchars($color['color']) ?>" <?= ($selected_color == $color['color']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($color['color']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <select name="size">
                <option value="">Выберите размер</option>
                <?php foreach ($sizes as $size): ?>
                    <option value="<?= $size['size_id'] ?>" <?= ($selected_size == $size['size_id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($size['size_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <select name="category">
                <option value="">Выберите категорию</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= $category['category_id'] ?>" <?= ($selected_category == $category['category_id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($category['category_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="submit">Фильтровать</button>
            <button type="button" onclick="window.location.href='catalog.php'">Сбросить</button>
        </form>

        <div class="product-grid">
            <?php foreach ($products as $product): ?>
                <?php 
                // Получаем доступные размеры для товара
                $query_sizes = "
                    SELECT s.size_name FROM product_sizes ps
                    JOIN sizes s ON ps.size_id = s.size_id
                    WHERE ps.product_id = :product_id";
                
                $stmt_sizes = $pdo->prepare($query_sizes);
                $stmt_sizes->execute([':product_id' => $product['product_id']]);
                $sizes = $stmt_sizes->fetchAll(PDO::FETCH_ASSOC);

                // Проверка на избранное
			
				$is_favorite = false;
				if ($user_logged_in) {
					$stmt_favorite = $pdo->prepare("SELECT * FROM favorites WHERE user_id = :user_id AND product_id = :product_id");
					$stmt_favorite->execute([':user_id' => $_SESSION['user_id'], ':product_id' => $product['product_id']]);
					$is_favorite = $stmt_favorite->rowCount() > 0;
				}
				

                ?>

                <div class="product-card" onclick="window.location.href='product.php?id=<?= $product['product_id'] ?>'">
					<img src="<?= $product['photo'] ?>" alt="<?= htmlspecialchars($product['product_name']) ?>">
					<div class="favorite-icon" onclick="toggleFavorite(<?= $product['product_id'] ?>, this, event)">
						<img class="heart-icon" src="<?= $is_favorite ? 'heart_liked.svg' : 'heart_unliked.svg' ?>" alt="<?= $is_favorite ? 'В избранном' : 'Не в избранном' ?>">
					</div>
					<h3><?= htmlspecialchars($product['product_name']) ?></h3>
					<p><?= number_format($product['price'], 2, ',', ' ') ?> ₽</p>
					<div class="size-overlay">
						<?php foreach ($sizes as $size): ?>
							<span><?= htmlspecialchars($size['size_name']) ?></span>
						<?php endforeach; ?>
					</div>
				</div>


            <?php endforeach; ?>
        </div>
    </div>
</main>

<script>
function toggleFavorite(productId, iconContainer, event) {
    // Останавливаем всплытие события, чтобы клик по сердечку не вызывал переход на карточку товара
    event.stopPropagation();

    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'toggle_favorite.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function () {
        if (xhr.status === 200) {
            // Получаем иконку внутри контейнера
            const icon = iconContainer.querySelector('.heart-icon');
            
            // Переключаем изображение сердечка
            if (icon.src.includes('heart_unliked.svg')) {
                icon.src = 'heart_liked.svg';
            } else {
                icon.src = 'heart_unliked.svg';
            }
        }
    };
    xhr.send('product_id=' + productId);
}



</script>

</body>
</html>
<?php include('footer.php'); ?>
