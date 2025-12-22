<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: Thu, 01 Jan 1970 00:00:00 GMT");

require_once __DIR__ . '/../config/database.php';

$database = new Database();
$db = $database->getConnection();

$query = "SELECT category_id, category_name FROM MenuCategories ORDER BY category_id";
$stmt = $db->prepare($query);
$stmt->execute();
$allCats = $stmt->fetchAll(PDO::FETCH_ASSOC);

$data = [
    'Appetizers' => ['Başlangıçlar', '../assets/images/categories/appetizers.jpeg', 1, '#FF6B6B'],
    'Soups' => ['Çorbalar', '../assets/images/categories/soups.webp', 2, '#4ECDC4'],
    'Main Courses' => ['Ana Yemekler', '../assets/images/categories/main-courses.webp', 3, '#45B7D1'],
    'Desserts' => ['Tatlılar', '../assets/images/categories/desserts.webp', 4, '#FFA07A'],
    'Beverages' => ['İçecekler', '../assets/images/categories/beverages.webp', 5, '#98D8C8'],
];

$categories = [];
foreach ($allCats as $cat) {
    if (isset($data[$cat['category_name']])) {
        $info = $data[$cat['category_name']];
        $categories[] = [
            'category_id' => $cat['category_id'],
            'category_name' => $cat['category_name'],
            'tr_name' => $info[0],
            'icon' => $info[1],
            'order' => $info[2],
            'color' => $info[3],
        ];
    }
}

usort($categories, function($a, $b) {
    return $a['order'] - $b['order'];
});

$title = 'Menü - Restoran';
$bodyClass = 'page-customer';
require_once __DIR__ . '/../includes/layout/top.php';
?>

<main class="app">
    <div class="customer-container">
        <!-- Header -->
        <header class="customer-header">
            <div class="header-top" style="padding: 16px 20px !important; text-align: center; display: flex; justify-content: center; align-items: center;">
                <div class="header-greeting" style="text-align: center;">
                    <h1>Restoran Menüsü</h1>
                </div>
            </div>
        </header>

        <!-- Kategoriler Grid -->
        <div class="menu-grid">
            <?php if (empty($categories)): ?>
                <div class="empty-state">
                    <p>📭 Kategori bulunamadı.</p>
                </div>
            <?php else: ?>
                <?php foreach ($categories as $cat): ?>
                    <a href="category.php?category_id=<?= (int)$cat['category_id'] ?>" class="category-card" style="--card-color: <?= $cat['color'] ?>;">
                        <div class="category-card__icon"><img src="<?= $cat['icon'] ?>" alt="<?= htmlspecialchars($cat['tr_name']) ?>" class="category-card__img"></div>
                        <h3 class="category-card__title"><?= htmlspecialchars($cat['tr_name']) ?></h3>
                        <p class="category-card__desc">Ürünleri görüntüle →</p>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</main>

<style>
    .customer-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 24px;
    }

    .customer-header {
        margin-bottom: 24px;
    }

    .header-top {
        padding: 20px;
        background: linear-gradient(135deg, rgba(37, 99, 235, 0.15) 0%, rgba(16, 185, 129, 0.15) 100%);
        border-radius: 24px;
        border: 1px solid rgba(148, 163, 184, 0.2);
        box-shadow: 0 8px 32px rgba(37, 99, 235, 0.08);
        max-width: 600px;
        margin: 0 auto;
    }

    .header-greeting h1 {
        margin: 0 0 12px;
        font-size: 22px;
        font-weight: 700;
        text-align: center;
        background: linear-gradient(135deg, #2563eb, #10b981);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .header-subtitle {
        margin: 0;
        font-size: 18px;
        color: var(--muted);
        font-weight: 500;
    }

    .menu-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 16px;
        margin-bottom: 32px;
        max-width: 500px;
        margin-left: auto;
        margin-right: auto;
    }

    .empty-state {
        grid-column: 1 / -1;
        text-align: center;
        padding: 64px 24px;
        color: var(--muted);
        font-size: 20px;
    }

    .category-card {
        background: linear-gradient(135deg, rgba(255,255,255,0.05) 0%, rgba(255,255,255,0.02) 100%);
        border: 1px solid var(--line);
        border-radius: 16px;
        box-shadow: 0 4px 24px rgba(0, 0, 0, 0.12);
        padding: 16px 12px;
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        transition: all 0.35s cubic-bezier(0.23, 1, 0.320, 1);
        cursor: pointer;
        text-decoration: none;
        position: relative;
        overflow: hidden;
    }

    .category-card::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, var(--card-color, #2563eb) 0%, transparent 70%);
        opacity: 0;
        transition: opacity 0.35s ease;
        pointer-events: none;
    }

    .category-card:hover {
        transform: translateY(-8px) scale(1.02);
        box-shadow: 0 16px 48px rgba(0, 0, 0, 0.2);
        border-color: var(--card-color, #2563eb);
    }

    .category-card:hover::before {
        opacity: 0.1;
    }

    .category-card__icon {
        width: 80px;
        height: 80px;
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        overflow: hidden;
        background: rgba(0, 0, 0, 0.1);
    }

    .category-card__img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
    }

    .category-card:hover .category-card__img {
        transform: scale(1.1);
    }

    .category-card__title {
        font-size: 16px;
        font-weight: 700;
        margin: 0 0 4px;
        color: var(--text);
        position: relative;
        z-index: 1;
    }

    .category-card__desc {
        font-size: 12px;
        color: var(--muted);
        margin: 0;
        position: relative;
        z-index: 1;
        font-weight: 500;
        letter-spacing: 0.3px;
    }

    @keyframes bounce {
        0%, 100% { transform: translateY(0) scale(1); }
        50% { transform: translateY(-8px) scale(1.1); }
    }

    @media (max-width: 768px) {
        .customer-container {
            padding: 16px;
        }

        .header-top {
            padding: 32px 20px;
        }

        .header-greeting h1 {
            font-size: 28px;
        }

        .header-subtitle {
            font-size: 16px;
        }

        .menu-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
        }

        .category-card {
            padding: 16px 12px;
        }

        .category-card__icon {
            font-size: 40px;
        }

        .category-card__title {
            font-size: 16px;
        }
    }
</style>

<?php require_once __DIR__ . '/../includes/layout/bottom.php'; ?>
