<?php
require_once 'includes/config.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

$restaurant_id = $_GET['restaurant_id'] ?? 0;

if (!$restaurant_id) {
    header("Location: restaurants.php");
    exit;
}

// Get restaurant details
$stmt = $pdo->prepare("
    SELECT r.*, 
           (SELECT COUNT(*) FROM reviews WHERE restaurant_id = r.restaurant_id) as review_count,
           (SELECT AVG(rating) FROM reviews WHERE restaurant_id = r.restaurant_id) as avg_rating
    FROM restaurants r 
    WHERE r.restaurant_id = ? AND r.is_active = 1
");
$stmt->execute([$restaurant_id]);
$restaurant = $stmt->fetch();

if (!$restaurant) {
    header("Location: restaurants.php");
    exit;
}

// Get menu categories
$stmt = $pdo->prepare("
    SELECT c.*, 
           (SELECT COUNT(*) FROM menu_items WHERE category_id = c.category_id AND is_available = 1) as item_count
    FROM categories c 
    WHERE c.restaurant_id = ? 
    ORDER BY c.display_order ASC
");
$stmt->execute([$restaurant_id]);
$categories = $stmt->fetchAll();

// Get all menu items or filtered by category
$category_id = $_GET['category'] ?? '';
$where = "mi.restaurant_id = ? AND mi.is_available = 1";
$params = [$restaurant_id];

if ($category_id) {
    $where .= " AND mi.category_id = ?";
    $params[] = $category_id;
}

$stmt = $pdo->prepare("
    SELECT mi.*, c.name as category_name
    FROM menu_items mi
    LEFT JOIN categories c ON mi.category_id = c.category_id
    WHERE $where
    ORDER BY c.display_order ASC, mi.item_id ASC
");
$stmt->execute($params);
$menu_items = $stmt->fetchAll();

// Group menu items by category
$items_by_category = [];
foreach ($menu_items as $item) {
    $category_name = $item['category_name'] ?: 'Zingine';
    if (!isset($items_by_category[$category_name])) {
        $items_by_category[$category_name] = [];
    }
    $items_by_category[$category_name][] = $item;
}
?>
<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($restaurant['name']); ?> - Menu - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/menu.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="menu-page">
        <div class="container">
            <!-- Restaurant Banner -->
            <div class="restaurant-banner" style="background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), 
                url('<?php echo $restaurant['cover_image'] ?: 'assets/images/restaurant-bg.jpg'; ?>');">
                <div class="restaurant-info-container">
                    <div class="restaurant-logo">
                        <img src="<?php echo $restaurant['logo_url'] ?: 'assets/images/restaurant-placeholder.jpg'; ?>" 
                             alt="<?php echo htmlspecialchars($restaurant['name']); ?>">
                    </div>
                    <div class="restaurant-details">
                        <h1><?php echo htmlspecialchars($restaurant['name']); ?></h1>
                        <p class="restaurant-cuisine">
                            <i class="fas fa-utensils"></i> 
                            <?php echo htmlspecialchars($restaurant['cuisine_type'] ?: 'Aina mbalimbali'); ?>
                        </p>
                        
                        <div class="restaurant-meta">
                            <div class="rating-info">
                                <div class="rating">
                                    <?php 
                                    $rating = $restaurant['avg_rating'] ?: 0;
                                    $fullStars = floor($rating);
                                    $halfStar = $rating - $fullStars >= 0.5;
                                    $emptyStars = 5 - $fullStars - ($halfStar ? 1 : 0);
                                    ?>
                                    <?php echo str_repeat('★', $fullStars); ?>
                                    <?php echo $halfStar ? '½' : ''; ?>
                                    <?php echo str_repeat('☆', $emptyStars); ?>
                                    <span class="rating-value"><?php echo number_format($rating, 1); ?></span>
                                </div>
                                <span class="review-count">(Tathmini <?php echo $restaurant['review_count'] ?: 0; ?>)</span>
                            </div>
                            
                            <div class="info-item">
                                <i class="fas fa-clock"></i>
                                <span><?php echo $restaurant['opening_time'] ? date('g:i A', strtotime($restaurant['opening_time'])) : '08:00 AM'; ?> - 
                                      <?php echo $restaurant['closing_time'] ? date('g:i A', strtotime($restaurant['closing_time'])) : '10:00 PM'; ?></span>
                            </div>
                            
                            <div class="info-item">
                                <i class="fas fa-map-marker-alt"></i>
                                <span><?php echo htmlspecialchars($restaurant['address']); ?></span>
                            </div>
                            
                            <div class="info-item">
                                <i class="fas fa-phone"></i>
                                <span><?php echo htmlspecialchars($restaurant['phone']); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="restaurant-actions">
                    <div class="delivery-info">
                        <span class="delivery-time">
                            <i class="fas fa-shipping-fast"></i> 
                            Dakika <?php echo $restaurant['delivery_time'] ?: '30-45'; ?>
                        </span>
                        <span class="delivery-fee">
                            <i class="fas fa-tag"></i> 
                            Ada ya Uwasilishaji: <?php echo CURRENCY_SYMBOL; ?> <?php echo number_format($restaurant['delivery_fee'], 2); ?>
                        </span>
                        <span class="min-order">
                            <i class="fas fa-shopping-basket"></i> 
                            Chini ya: <?php echo CURRENCY_SYMBOL; ?> <?php echo number_format($restaurant['min_order_amount'], 2); ?>
                        </span>
                    </div>
                    
                    <div class="action-buttons">
                        <button class="btn btn-secondary" onclick="shareRestaurant()">
                            <i class="fas fa-share-alt"></i> Shiriki
                        </button>
                        <button class="btn btn-primary" onclick="toggleFavorite()">
                            <i class="far fa-heart"></i> Pendekeza
                        </button>
                    </div>
                </div>
            </div>

            <!-- Menu Container -->
            <div class="menu-container">
                <!-- Categories Sidebar -->
                <aside class="menu-sidebar">
                    <h3><i class="fas fa-bars"></i> Aina za Chakula</h3>
                    <ul class="menu-categories">
                        <li>
                            <a href="menu.php?restaurant_id=<?php echo $restaurant_id; ?>" 
                               class="<?php echo empty($category_id) ? 'active' : ''; ?>">
                                <i class="fas fa-utensils"></i> Vyote
                                <span class="category-count"><?php echo count($menu_items); ?></span>
                            </a>
                        </li>
                        
                        <?php foreach ($categories as $cat): ?>
                        <li>
                            <a href="menu.php?restaurant_id=<?php echo $restaurant_id; ?>&category=<?php echo $cat['category_id']; ?>" 
                               class="<?php echo $category_id == $cat['category_id'] ? 'active' : ''; ?>">
                                <i class="fas fa-<?php echo getCategoryIcon($cat['name']); ?>"></i>
                                <?php echo htmlspecialchars($cat['name']); ?>
                                <span class="category-count"><?php echo $cat['item_count']; ?></span>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    
                    <!-- Filters -->
                    <div class="menu-filters">
                        <h3><i class="fas fa-filter"></i> Chuja</h3>
                        
                        <div class="filter-group">
                            <label>
                                <input type="checkbox" id="filterVeg" onchange="filterMenu()">
                                <span>Mboga Tu</span>
                            </label>
                        </div>
                        
                        <div class="filter-group">
                            <label>
                                <input type="checkbox" id="filterSpicy" onchange="filterMenu()">
                                <span>Vyango</span>
                            </label>
                        </div>
                        
                        <div class="filter-group">
                            <label>
                                <input type="checkbox" id="filterAvailable" checked onchange="filterMenu()">
                                <span>Inapatikana</span>
                            </label>
                        </div>
                    </div>
                    
                    <!-- Price Range -->
                    <div class="price-range-filter">
                        <h3><i class="fas fa-money-bill-wave"></i> Anuwai ya Bei</h3>
                        <input type="range" id="priceRange" min="0" max="50000" step="1000" 
                               value="50000" onchange="filterMenu()">
                        <div class="price-display">
                            <span>Hadi <?php echo CURRENCY_SYMBOL; ?> <span id="maxPrice">50,000</span></span>
                        </div>
                    </div>
                </aside>

                <!-- Menu Content -->
                <div class="menu-content">
                    <!-- Search Bar -->
                    <div class="menu-search">
                        <div class="search-container">
                            <i class="fas fa-search"></i>
                            <input type="text" id="menuSearch" placeholder="Tafuta chakula..." 
                                   onkeyup="filterMenu()">
                            <button class="btn btn-sm" onclick="clearSearch()">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <span class="item-count" id="itemCount"><?php echo count($menu_items); ?> bidhaa</span>
                    </div>
                    
                    <!-- Menu Items by Category -->
                    <?php if (empty($items_by_category)): ?>
                        <div class="no-items">
                            <i class="fas fa-utensils"></i>
                            <h3>Hakuna Chakula Kipatikano</h3>
                            <p>Hakuna vyakula vilivyopatikana kwa aina hii ya chakula.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($items_by_category as $category_name => $items): ?>
                        <div class="menu-section">
                            <h2>
                                <i class="fas fa-<?php echo getCategoryIcon($category_name); ?>"></i>
                                <?php echo htmlspecialchars($category_name); ?>
                                <span class="section-count">(<?php echo count($items); ?>)</span>
                            </h2>
                            
                            <div class="menu-items">
                                <?php foreach ($items as $item): ?>
                                <div class="menu-item" data-item-id="<?php echo $item['item_id']; ?>"
                                     data-veg="<?php echo $item['is_veg'] ? 'true' : 'false'; ?>"
                                     data-spicy="<?php echo strpos(strtolower($item['description']), 'spicy') !== false ? 'true' : 'false'; ?>"
                                     data-price="<?php echo $item['price']; ?>"
                                     data-available="true">
                                    
                                    <?php if ($item['is_veg']): ?>
                                    <div class="item-badge veg-badge">
                                        <i class="fas fa-leaf"></i> Mboga
                                    </div>
                                    <?php else: ?>
                                    <div class="item-badge nonveg-badge">
                                        <i class="fas fa-drumstick-bite"></i> Nyama
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if (strpos(strtolower($item['description']), 'spicy') !== false): ?>
                                    <div class="item-badge spicy-badge">
                                        <i class="fas fa-pepper-hot"></i> Vyango
                                    </div>
                                    <?php endif; ?>
                                    
                                    <div class="item-image">
                                        <img src="<?php echo $item['image_url'] ?: 'assets/images/food-placeholder.jpg'; ?>" 
                                             alt="<?php echo htmlspecialchars($item['name']); ?>">
                                    </div>
                                    
                                    <div class="item-info">
                                        <div class="item-header">
                                            <h3 class="item-name"><?php echo htmlspecialchars($item['name']); ?></h3>
                                            <span class="item-price"><?php echo CURRENCY_SYMBOL; ?> <?php echo number_format($item['price'], 2); ?></span>
                                        </div>
                                        
                                        <p class="item-description">
                                            <?php echo htmlspecialchars($item['description'] ?: 'Chakula kitamu na kilichotengenezwa kwa uangalifu.'); ?>
                                        </p>
                                        
                                        <div class="item-meta">
                                            <span class="prep-time">
                                                <i class="fas fa-clock"></i> 
                                                Dakika <?php echo $item['preparation_time'] ?: 15; ?>
                                            </span>
                                            <?php if ($item['is_available']): ?>
                                            <span class="available-badge">
                                                <i class="fas fa-check-circle"></i> Inapatikana
                                            </span>
                                            <?php else: ?>
                                            <span class="unavailable-badge">
                                                <i class="fas fa-times-circle"></i> Haipatikani
                                            </span>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <div class="item-actions">
                                            <div class="quantity-controls">
                                                <button class="qty-btn decrease-qty">
                                                    <i class="fas fa-minus"></i>
                                                </button>
                                                <input type="number" class="qty-input" value="1" min="1" max="10">
                                                <button class="qty-btn increase-qty">
                                                    <i class="fas fa-plus"></i>
                                                </button>
                                            </div>
                                            
                                            <button class="btn btn-primary add-to-cart-btn" 
                                                    onclick="addToCart(<?php echo $item['item_id']; ?>, 
                                                    '<?php echo htmlspecialchars($item['name'], ENT_QUOTES); ?>', 
                                                    <?php echo $item['price']; ?>, 
                                                    <?php echo $restaurant_id; ?>, 
                                                    '<?php echo $item['image_url'] ?: ''; ?>')">
                                                <i class="fas fa-cart-plus"></i> Ongeza Kagua
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <!-- Cart Sidebar -->
    <div id="cartSidebar" class="cart-sidebar">
        <div class="cart-header">
            <h3><i class="fas fa-shopping-cart"></i> Kagua Langu</h3>
            <button class="close-cart"><i class="fas fa-times"></i></button>
        </div>
        <div class="cart-body">
            <!-- Cart items will be loaded here -->
        </div>
        <div class="cart-footer">
            <div class="cart-total">
                <!-- Total will be calculated here -->
            </div>
            <a href="checkout.php" class="btn btn-primary btn-block">
                <i class="fas fa-check-circle"></i> Endelea Kwenye Malipo
            </a>
        </div>
    </div>
    <div id="cartOverlay" class="cart-overlay"></div>

    <?php include 'includes/footer.php'; ?>

    <script src="assets/js/main.js"></script>
    <script src="assets/js/menu.js"></script>
</body>
</html>

<?php
// Helper function to get category icon
function getCategoryIcon($category_name) {
    $icons = [
        'Nyama Choma' => 'fire',
        'Viazi Karai' => 'cookie-bite',
        'Pizza' => 'pizza-slice',
        'Sandwich' => 'bread-slice',
        'Supu' => 'mug-hot',
        'Mkate' => 'bread-slice',
        'Chai' => 'coffee',
        'Juice' => 'glass-whiskey',
        'Deserts' => 'ice-cream',
        'Zingine' => 'utensils'
    ];
    
    foreach ($icons as $key => $icon) {
        if (stripos($category_name, $key) !== false) {
            return $icon;
        }
    }
    
    return 'utensils';
}
?>