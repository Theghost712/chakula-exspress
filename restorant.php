<?php
require_once 'includes/config.php';

// Get filters from URL
$search = $_GET['search'] ?? '';
$cuisine = $_GET['cuisine'] ?? '';
$sort = $_GET['sort'] ?? 'rating';
$page = $_GET['page'] ?? 1;
$limit = 12;
$offset = ($page - 1) * $limit;
?>
<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mikahawa - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/restaurants.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Header -->
    <?php include 'includes/header.php'; ?>

    <main class="restaurants-page">
        <div class="container">
            <!-- Page Header -->
            <div class="page-header">
                <h1>Tafuta Mikahawa</h1>
                <p>Pata mikahawa bora ya kulisha karibu nawe</p>
            </div>

            <!-- Search and Filters -->
            <div class="search-filters">
                <form id="restaurantFilters" method="GET" action="restaurants.php">
                    <div class="filter-grid">
                        <div class="filter-group">
                            <label for="search"><i class="fas fa-search"></i> Tafuta</label>
                            <input type="text" id="search" name="search" 
                                   placeholder="Tafuta mkahawa au chakula..." 
                                   value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                        
                        <div class="filter-group">
                            <label for="cuisine"><i class="fas fa-utensils"></i> Aina ya Chakula</label>
                            <select id="cuisine" name="cuisine">
                                <option value="">Aina Zote</option>
                                <option value="Nyama Choma" <?php echo $cuisine == 'Nyama Choma' ? 'selected' : ''; ?>>Nyama Choma</option>
                                <option value="Viazi Karai" <?php echo $cuisine == 'Viazi Karai' ? 'selected' : ''; ?>>Viazi Karai</option>
                                <option value="Pizza" <?php echo $cuisine == 'Pizza' ? 'selected' : ''; ?>>Pizza</option>
                                <option value="Sandwich" <?php echo $cuisine == 'Sandwich' ? 'selected' : ''; ?>>Sandwich</option>
                                <option value="Mkate" <?php echo $cuisine == 'Mkate' ? 'selected' : ''; ?>>Mkate</option>
                                <option value="Supu" <?php echo $cuisine == 'Supu' ? 'selected' : ''; ?>>Supu</option>
                                <option value="Chai" <?php echo $cuisine == 'Chai' ? 'selected' : ''; ?>>Chai & Vinywaji</option>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <label for="city"><i class="fas fa-map-marker-alt"></i> Jiji</label>
                            <select id="city" name="city">
                                <option value="">Miji Yote</option>
                                <option value="Dar es Salaam">Dar es Salaam</option>
                                <option value="Dodoma">Dodoma</option>
                                <option value="Arusha">Arusha</option>
                                <option value="Mwanza">Mwanza</option>
                                <option value="Mbeya">Mbeya</option>
                                <option value="Morogoro">Morogoro</option>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <label for="delivery"><i class="fas fa-shipping-fast"></i> Uwasilishaji</label>
                            <select id="delivery" name="delivery">
                                <option value="">Yote</option>
                                <option value="fast">Haraka (≺ 30 min)</option>
                                <option value="free">Bure ya Uwasilishaji</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="filter-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter"></i> Chuja
                        </button>
                        <button type="button" class="btn btn-outline" onclick="resetFilters()">
                            <i class="fas fa-redo"></i> Ondoa Michujo
                        </button>
                    </div>
                </form>
            </div>

            <!-- Sort Options -->
            <div class="sort-options">
                <span>Panga kwa:</span>
                <select id="sortBy" onchange="sortRestaurants(this.value)">
                    <option value="rating" <?php echo $sort == 'rating' ? 'selected' : ''; ?>>Makadirio bora</option>
                    <option value="delivery_time" <?php echo $sort == 'delivery_time' ? 'selected' : ''; ?>>Muda mfupi wa uwasilishaji</option>
                    <option value="min_order" <?php echo $sort == 'min_order' ? 'selected' : ''; ?>>Bei ya chini</option>
                    <option value="name" <?php echo $sort == 'name' ? 'selected' : ''; ?>>Jina (A-Z)</option>
                </select>
            </div>

            <div class="restaurants-container">
                <!-- Sidebar -->
                <aside class="restaurants-sidebar">
                    <div class="sidebar-section">
                        <h3><i class="fas fa-filter"></i> Chuja Zaidi</h3>
                        
                        <div class="filter-group">
                            <label>Kiwango cha Bei</label>
                            <div class="price-range">
                                <input type="range" id="priceRange" min="0" max="50000" step="1000" value="25000">
                                <div class="price-display">
                                    <span>TSh <span id="minPrice">0</span> - TSh <span id="maxPrice">25,000</span></span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="filter-group">
                            <label>Makadirio</label>
                            <div class="rating-filters">
                                <label>
                                    <input type="checkbox" name="rating" value="4">
                                    <span class="rating">★★★★★</span> 4.0 & juu
                                </label>
                                <label>
                                    <input type="checkbox" name="rating" value="3">
                                    <span class="rating">★★★★☆</span> 3.0 & juu
                                </label>
                                <label>
                                    <input type="checkbox" name="rating" value="2">
                                    <span class="rating">★★★☆☆</span> 2.0 & juu
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="sidebar-section">
                        <h3><i class="fas fa-tags"></i> Aina za Chakula</h3>
                        <ul class="category-list" id="categoryList">
                            <!-- Categories will be loaded dynamically -->
                        </ul>
                    </div>
                    
                    <div class="sidebar-section">
                        <h3><i class="fas fa-percentage"></i> Punguzo</h3>
                        <label>
                            <input type="checkbox" name="discount" value="yes">
                            <span>Ina Punguzo</span>
                        </label>
                    </div>
                </aside>

                <!-- Restaurants List -->
                <div class="restaurants-content">
                    <div class="restaurants-list" id="restaurantsList">
                        <!-- Restaurants will be loaded dynamically -->
                    </div>
                    
                    <!-- Pagination -->
                    <div class="pagination" id="pagination">
                        <!-- Pagination will be loaded dynamically -->
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

    <!-- Scripts -->
    <script src="assets/js/main.js"></script>
    <script src="assets/js/restaurants.js"></script>
</body>
</html>