// Homepage Specific JavaScript

document.addEventListener('DOMContentLoaded', function() {
    loadFeaturedRestaurants();
    loadPopularDishes();
    initCategoryFilters();
    initTestimonials();
    initHeroSearch();
});

// Load featured restaurants
async function loadFeaturedRestaurants() {
    const container = document.getElementById('featuredRestaurants');
    if (!container) return;
    
    try {
        const response = await fetch('api/get_restaurants.php?featured=1&limit=8');
        const restaurants = await response.json();
        
        if (!restaurants.success || restaurants.data.length === 0) {
            container.innerHTML = '<p class="text-center">Hakuna mikahawa inayopatikana kwa sasa.</p>';
            return;
        }
        
        container.innerHTML = restaurants.data.map(restaurant => {
            const rating = parseFloat(restaurant.rating) || 0;
            const fullStars = Math.floor(rating);
            const halfStar = rating % 1 >= 0.5;
            const emptyStars = 5 - fullStars - (halfStar ? 1 : 0);
            
            return `
                <div class="restaurant-card">
                    <img src="${restaurant.image_url || 'assets/images/restaurant-placeholder.jpg'}" 
                         alt="${restaurant.name}" class="restaurant-image">
                    <div class="restaurant-info">
                        <h3 class="restaurant-name">${restaurant.name}</h3>
                        <p class="restaurant-cuisine">${restaurant.cuisine_type || 'Aina mbalimbali'}</p>
                        <div class="rating">
                            ${'★'.repeat(fullStars)}${halfStar ? '½' : ''}${'☆'.repeat(emptyStars)}
                            <span class="rating-text">(${rating.toFixed(1)})</span>
                        </div>
                        <div class="restaurant-meta">
                            <span class="delivery-info">
                                <i class="fas fa-clock"></i> ${restaurant.delivery_time || '30-45'} dakika
                            </span>
                            <span class="min-order">
                                <i class="fas fa-tag"></i> Chini ya TSh ${formatPrice(restaurant.min_order_amount || 0)}
                            </span>
                        </div>
                        <a href="menu.php?restaurant_id=${restaurant.restaurant_id}" class="btn btn-primary btn-sm" style="width: 100%; margin-top: 1rem;">
                            <i class="fas fa-utensils"></i> Angalia Menu
                        </a>
                    </div>
                </div>
            `;
        }).join('');
        
    } catch (error) {
        console.error('Error loading featured restaurants:', error);
        container.innerHTML = '<p class="text-center text-danger">Hitilafu katika kupakua mikahawa.</p>';
    }
}

// Load popular dishes
async function loadPopularDishes() {
    const container = document.querySelector('.dishes-grid');
    if (!container) return;
    
    try {
        const response = await fetch('api/get_popular_dishes.php?limit=6');
        const dishes = await response.json();
        
        if (!dishes.success || dishes.data.length === 0) {
            container.innerHTML = '<p class="text-center">Hakuna vyakula maarufu kwa sasa.</p>';
            return;
        }
        
        container.innerHTML = dishes.data.map(dish => {
            return `
                <div class="dish-card">
                    <img src="${dish.image_url || 'assets/images/food-placeholder.jpg'}" 
                         alt="${dish.name}" class="dish-image">
                    <div class="dish-info">
                        <h3 class="dish-name">${dish.name}</h3>
                        <p class="dish-description">${dish.description || 'Chakula kitamu na kilichotengenezwa kwa uangalifu.'}</p>
                        <div class="dish-meta">
                            <span class="dish-price">TSh ${formatPrice(dish.price)}</span>
                            <span class="dish-restaurant">${dish.restaurant_name}</span>
                        </div>
                        <button class="btn btn-secondary btn-sm add-to-cart-btn" 
                                data-dish-id="${dish.item_id}"
                                data-dish-name="${dish.name}"
                                data-dish-price="${dish.price}"
                                data-restaurant-id="${dish.restaurant_id}">
                            <i class="fas fa-cart-plus"></i> Ongeza Kagua
                        </button>
                    </div>
                </div>
            `;
        }).join('');
        
        // Add event listeners to add to cart buttons
        container.querySelectorAll('.add-to-cart-btn').forEach(button => {
            button.addEventListener('click', function() {
                const dish = {
                    id: this.dataset.dishId,
                    restaurant_id: this.dataset.restaurantId,
                    name: this.dataset.dishName,
                    price: parseFloat(this.dataset.dishPrice),
                    quantity: 1
                };
                
                addToCart(dish);
            });
        });
        
    } catch (error) {
        console.error('Error loading popular dishes:', error);
        container.innerHTML = '<p class="text-center text-danger">Hitilafu katika kupakua vyakula.</p>';
    }
}

// Initialize category filters
function initCategoryFilters() {
    const categoryCards = document.querySelectorAll('.category-card');
    categoryCards.forEach(card => {
        card.addEventListener('click', function() {
            const category = this.querySelector('h3').textContent;
            filterByCategory(category);
        });
    });
}

// Filter by category
function filterByCategory(category) {
    // Remove active class from all categories
    document.querySelectorAll('.category-card').forEach(card => {
        card.classList.remove('active');
    });
    
    // Add active class to selected category
    const selectedCard = Array.from(document.querySelectorAll('.category-card')).find(card => 
        card.querySelector('h3').textContent === category
    );
    
    if (selectedCard) {
        selectedCard.classList.add('active');
    }
    
    // Show loading state
    const restaurantsGrid = document.querySelector('.restaurants-grid');
    if (restaurantsGrid) {
        restaurantsGrid.innerHTML = '<div class="loading-spinner"><i class="fas fa-spinner fa-spin"></i> Inapakia...</div>';
    }
    
    // Filter restaurants (in a real app, this would be an API call)
    setTimeout(() => {
        // Simulate API call
        showNotification(`Inaonyesha mikahawa ya ${category}`, 'info');
        
        // In production, you would fetch filtered restaurants from API
        // fetch(`api/restaurants.php?category=${encodeURIComponent(category)}`)
        //     .then(response => response.json())
        //     .then(data => {
        //         // Update restaurants grid
        //     });
    }, 1000);
}

// Initialize testimonials carousel
function initTestimonials() {
    if (typeof $.fn.owlCarousel !== 'undefined') {
        $('.testimonials-slider').owlCarousel({
            loop: true,
            margin: 30,
            nav: true,
            dots: false,
            autoplay: true,
            autoplayTimeout: 5000,
            autoplayHoverPause: true,
            responsive: {
                0: { items: 1 },
                768: { items: 2 },
                992: { items: 3 }
            }
        });
    }
}

// Initialize hero search
function initHeroSearch() {
    const searchBtn = document.querySelector('.btn-search');
    const locationInput = document.getElementById('locationSearch');
    
    if (searchBtn && locationInput) {
        searchBtn.addEventListener('click', function() {
            const location = locationInput.value.trim();
            
            if (!location) {
                showNotification('Tafadhali weka eneo lako', 'warning');
                locationInput.focus();
                return;
            }
            
            // Save location
            localStorage.setItem('chakula_location', location);
            
            // Redirect to restaurants page with location filter
            window.location.href = `restaurants.php?location=${encodeURIComponent(location)}`;
        });
        
        // Allow Enter key to trigger search
        locationInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                searchBtn.click();
            }
        });
    }
}

// Load user's location if available
function loadUserLocation() {
    const savedLocation = localStorage.getItem('chakula_location');
    const locationInput = document.getElementById('locationSearch');
    
    if (savedLocation && locationInput) {
        locationInput.value = savedLocation;
    }
}

// Initialize when page loads
loadUserLocation();

// Add smooth scroll for anchor links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
        e.preventDefault();
        
        const targetId = this.getAttribute('href');
        if (targetId === '#') return;
        
        const targetElement = document.querySelector(targetId);
        if (targetElement) {
            window.scrollTo({
                top: targetElement.offsetTop - 80,
                behavior: 'smooth'
            });
        }
    });
});

// Add scroll animations
function initScrollAnimations() {
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-in');
            }
        });
    }, observerOptions);
    
    // Observe elements for animation
    document.querySelectorAll('.step-card, .category-card, .dish-card').forEach(el => {
        observer.observe(el);
    });
}

// Add CSS for animations
const animationStyles = document.createElement('style');
animationStyles.textContent = `
    .step-card, .category-card, .dish-card {
        opacity: 0;
        transform: translateY(20px);
        transition: opacity 0.6s ease, transform 0.6s ease;
    }
    
    .step-card.animate-in,
    .category-card.animate-in,
    .dish-card.animate-in {
        opacity: 1;
        transform: translateY(0);
    }
    
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .animate-in {
        animation: fadeInUp 0.6s ease forwards;
    }
`;
document.head.appendChild(animationStyles);

// Initialize animations
initScrollAnimations();

// Add loading spinner styles
const spinnerStyles = document.createElement('style');
spinnerStyles.textContent = `
    .loading-spinner {
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 3rem;
        font-size: 1.5rem;
        color: var(--primary-color);
        grid-column: 1 / -1;
    }
    
    .fa-spinner {
        margin-right: 1rem;
    }
`;
document.head.appendChild(spinnerStyles);