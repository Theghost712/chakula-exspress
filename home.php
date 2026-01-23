<?php
require_once 'includes/config.php';
?>
<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chakula Express - Chakula Bora Kwa Mlango Wako</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/home.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.theme.default.min.css">
</head>
<body>
    <!-- Header -->
    <?php include 'includes/header.php'; ?>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="hero-content">
                <h1>Chakula Bora <span class="highlight">Kwa Mlango Wako</span></h1>
                <p>Agiza chakula kutoka kwa mikahawa bora ya Tanzania. Uwasilishaji wa haraka na wa uhakika.</p>
                
                <div class="search-box">
                    <div class="search-input">
                        <i class="fas fa-search"></i>
                        <input type="text" id="locationSearch" placeholder="Weka eneo lako au ruhusu GPS">
                        <button class="btn-locate"><i class="fas fa-map-marker-alt"></i> Tumia Eneo Langu</button>
                    </div>
                    <button class="btn-search">Tafuta Mikahawa</button>
                </div>
                
                <div class="hero-stats">
                    <div class="stat">
                        <i class="fas fa-utensils"></i>
                        <div>
                            <h3>500+</h3>
                            <p>Mikahawa</p>
                        </div>
                    </div>
                    <div class="stat">
                        <i class="fas fa-users"></i>
                        <div>
                            <h3>50,000+</h3>
                            <p>Wateja Walioaamini</p>
                        </div>
                    </div>
                    <div class="stat">
                        <i class="fas fa-shipping-fast"></i>
                        <div>
                            <h3>15-45</h3>
                            <p>Dakika za Uwasilishaji</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="hero-image">
                <img src="assets/images/hero-food.png" alt="Chakula Bora">
            </div>
        </div>
    </section>

    <!-- Featured Restaurants -->
    <section class="featured-section">
        <div class="container">
            <div class="section-header">
                <h2>Mikahawa Inayovutia</h2>
                <a href="restaurants.php" class="view-all">Ona Yote <i class="fas fa-arrow-right"></i></a>
            </div>
            
            <div class="restaurants-grid owl-carousel" id="featuredRestaurants">
                <!-- Dynamic content from API -->
            </div>
        </div>
    </section>

    <!-- How it Works -->
    <section class="how-it-works">
        <div class="container">
            <h2>Jinsi Inavyofanya Kazi</h2>
            <p class="section-subtitle">Agiza chakula chako kwa hatua 4 rahisi</p>
            
            <div class="steps-grid">
                <div class="step-card">
                    <div class="step-icon">1</div>
                    <h3>Chagua Eneo</h3>
                    <p>Weka eneo lako au tumia GPS kuona mikahawa inayopatikana</p>
                </div>
                <div class="step-card">
                    <div class="step-icon">2</div>
                    <h3>Tafuta Chakula</h3>
                    <p>Pitia menu, pima bei, soma makadirio na uchague unachopenda</p>
                </div>
                <div class="step-card">
                    <div class="step-icon">3</div>
                    <h3>Fanya Malipo</h3>
                    <p>Lipa kwa M-Pesa, Tigo Pesa, Airtel Money au pesa taslimu</p>
                </div>
                <div class="step-card">
                    <div class="step-icon">4</div>
                    <h3>Pokea Oda</h3>
                    <p>Fuata oda yako kwa wakati halisi na upokee mlangoni</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Food Categories -->
    <section class="categories-section">
        <div class="container">
            <h2>Explore by Category</h2>
            <div class="categories-grid">
                <div class="category-card" onclick="filterByCategory('Nyama Choma')">
                    <img src="assets/images/categories/nyama-choma.jpg" alt="Nyama Choma">
                    <h3>Nyama Choma</h3>
                </div>
                <div class="category-card" onclick="filterByCategory('Viazi Karai')">
                    <img src="assets/images/categories/viazi-karai.jpg" alt="Viazi Karai">
                    <h3>Viazi Karai</h3>
                </div>
                <div class="category-card" onclick="filterByCategory('Pizza')">
                    <img src="assets/images/categories/pizza.jpg" alt="Pizza">
                    <h3>Pizza</h3>
                </div>
                <div class="category-card" onclick="filterByCategory('Sandwich')">
                    <img src="assets/images/categories/sandwich.jpg" alt="Sandwich">
                    <h3>Sandwich</h3>
                </div>
                <div class="category-card" onclick="filterByCategory('Juice')">
                    <img src="assets/images/categories/juice.jpg" alt="Juice">
                    <h3>Juice & Smoothies</h3>
                </div>
                <div class="category-card" onclick="filterByCategory('Deserts')">
                    <img src="assets/images/categories/deserts.jpg" alt="Deserts">
                    <h3>Deserts</h3>
                </div>
            </div>
        </div>
    </section>

    <!-- Popular Dishes -->
    <section class="popular-dishes">
        <div class="container">
            <div class="section-header">
                <h2>Vyakula Maarufu</h2>
                <a href="menu.php" class="view-all">Ona Yote <i class="fas fa-arrow-right"></i></a>
            </div>
            
            <div class="dishes-grid">
                <!-- Dynamic content from API -->
            </div>
        </div>
    </section>

    <!-- Mobile App Banner -->
    <section class="app-banner">
        <div class="container">
            <div class="app-content">
                <h2>Pakua App Yetu ya Rununu</h2>
                <p>Pata uzoefu bora wa kuagiza chakula kupitia programu yetu ya simu. Pata ofa za kipekee na uwasilishaji wa haraka.</p>
                
                <div class="app-buttons">
                    <a href="#" class="app-store">
                        <i class="fab fa-apple"></i>
                        <div>
                            <small>Pakua kwenye</small>
                            <span>App Store</span>
                        </div>
                    </a>
                    <a href="#" class="google-play">
                        <i class="fab fa-google-play"></i>
                        <div>
                            <small>Pakua kwenye</small>
                            <span>Google Play</span>
                        </div>
                    </a>
                </div>
            </div>
            <div class="app-image">
                <img src="assets/images/app-mockup.png" alt="Chakula Express App">
            </div>
        </div>
    </section>

    <!-- Testimonials -->
    <section class="testimonials">
        <div class="container">
            <h2>Wateja Wanachosema</h2>
            <p class="section-subtitle">Maoni kutoka kwa wateja wetu waaminifu</p>
            
            <div class="testimonials-slider owl-carousel">
                <div class="testimonial-card">
                    <div class="rating">★★★★★</div>
                    <p>"Bora kabisa! Chakula kinafika kwa wakati na katika hali nzuri. Nafanya maagizo mara kwa mara."</p>
                    <div class="customer">
                        <img src="assets/images/customers/john.jpg" alt="John">
                        <div>
                            <h4>John Mushi</h4>
                            <small>Dar es Salaam</small>
                        </div>
                    </div>
                </div>
                <div class="testimonial-card">
                    <div class="rating">★★★★★</div>
                    <p>"Rahisi kutumia na malipo ya M-Pesa yanafanya kazi vizuri. Hakuna wasiwasi wa pesa taslimu."</p>
                    <div class="customer">
                        <img src="assets/images/customers/sarah.jpg" alt="Sarah">
                        <div>
                            <h4>Sarah Juma</h4>
                            <small>Arusha</small>
                        </div>
                    </div>
                </div>
                <div class="testimonial-card">
                    <div class="rating">★★★★☆</div>
                    <p>"Chakula kitamu na bei nafuu. Nafanya maagizo kila jumapili kwa familia yangu."</p>
                    <div class="customer">
                        <img src="assets/images/customers/ali.jpg" alt="Ali">
                        <div>
                            <h4>Ali Hassan</h4>
                            <small>Mwanza</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js"></script>
    <script src="assets/js/main.js"></script>
    <script src="assets/js/home.js"></script>
</body>
</html>