// Restaurants Page JavaScript

document.addEventListener('DOMContentLoaded', function() {
    loadRestaurants();
    loadCategories();
    initFilters();
    initPriceRange();
});

// Load restaurants
async function loadRestaurants() {
    const container = document.getElementById('restaurantsList');
    const pagination = document.getElementById('pagination');
    
    if (!container) return;
    
    // Show loading state
    container.innerHTML = '<div class="loading-spinner"><i class="fas fa-spinner fa-spin"></i> Inapakia mikahawa...</div>';
    
    // Get filters from URL
    const urlParams = new URLSearchParams(window.location.search);
    const filters = {
        search: urlParams.get('search') || '',
        cuisine: urlParams.get('cuisine') || '',
        city: urlParams.get('city') || '',
        delivery: urlParams.get('delivery') || '',
        sort: urlParams.get('sort') || 'rating',
        page: urlParams.get('page') || 1,
        limit: 12
    };
    
    try {
        // Build query string
        const queryString = new URLSearchParams(filters).toString();
        const response = await fetch(`api/get_restaurants.php?${queryString}`);
        const result = await response.json();
        
        if (!result.success || result.data.length === 0) {
            container.innerHTML = `
                <div class="no-results">
                    <i class="fas fa-search"></i>
                    <h3>Hakuna Mikahawa Ilipatikana</h3>
                    <p>Badilisha michujo yako au jaribu kutafuta kitu kingine.</p>
                    <button class="btn btn-primary" onclick="resetFilters()">
                        <i class="fas fa-redo"></i> Ondoa Michujo Yote
                    </button>
                </div>
            `;
            pagination.innerHTML = '';
            return;
        }
        
        // Display restaurants
        container.innerHTML = result.data.map(restaurant => createRestaurantCard(restaurant)).join('');
        
        // Display pagination
        if (result.pagination) {
            displayPagination(result.pagination, filters.page);
        }
        
    } catch (error) {
        console.error('Error loading restaurants:', error);
        container.innerHTML = '<div class="error-message">Hitilafu katika kupakua mikahawa. Tafadhali jaribu tena.</div>';
    }
}

// Create restaurant card HTML
function createRestaurantCard(restaurant) {
    const rating = parseFloat(restaurant.rating) || 0;
    const fullStars = Math.floor(rating);
    const halfStar = rating % 1 >= 0.5;
    const emptyStars = 5 - fullStars - (halfStar ? 1 : 0);
    
    // Determine if restaurant is open
    const now = new Date();
    const currentTime = now.getHours() * 60 + now.getMinutes();
    const isOpen = restaurant.is_open || true; // In real app, check opening hours
    
    // Delivery time range
    const deliveryTime = restaurant.delivery_time || '30-45';
    
    return `
        <div class="restaurant-item">
            <div class="restaurant-badge">
                ${isOpen ? '<span class="badge badge-success"><i class="fas fa-door-open"></i> Wazi</span>' : ''}
                ${restaurant.is_featured ? '<span class="badge badge-primary"><i class="fas fa-crown"></i> Inavutia</span>' : ''}
                ${restaurant.delivery_fee == 0 ? '<span class="badge badge-secondary"><i class="fas fa-truck"></i> Uwasilishaji Bure</span>' : ''}
            </div>
            
            <img src="${restaurant.image_url || 'assets/images/restaurant-placeholder.jpg'}" 
                 alt="${restaurant.name}" class="restaurant-img">
            
            <div class="restaurant-content">
                <div class="restaurant-header">
                    <div class="restaurant-title">
                        <h3>${restaurant.name}</h3>
                        <p>${restaurant.cuisine_type || 'Aina mbalimbali'}</p>
                    </div>
                    <div class="restaurant-rating">
                        <span class="rating-badge">${rating.toFixed(1)}</span>
                        <div class="rating">
                            ${'★'.repeat(fullStars)}${halfStar ? '½' : ''}${'☆'.repeat(emptyStars)}
                        </div>
                    </div>
                </div>
                
                <div class="restaurant-features">
                    <span class="feature">
                        <i class="fas fa-clock"></i> ${deliveryTime} dakika
                    </span>
                    <span class="feature">
                        <i class="fas fa-tag"></i> TSh ${formatPrice(restaurant.min_order_amount || 0)}
                    </span>
                    <span class="feature">
                        <i class="fas fa-truck"></i> TSh ${formatPrice(restaurant.delivery_fee || 1500)}
                    </span>
                </div>
                
                <p class="restaurant-description">${restaurant.description || 'Chakula bora na kilichoandaliwa kwa uangalifu.'}</p>
                
                <div class="restaurant-footer">
                    <span class="delivery-time">
                        <i class="fas fa-motorcycle"></i> ${deliveryTime} dakika
                    </span>
                    <span class="min-order">
                        Chini ya TSh ${formatPrice(restaurant.min_order_amount || 0)}
                    </span>
                    <a href="menu.php?restaurant_id=${restaurant.restaurant_id}" class="btn btn-primary btn-sm">
                        <i class="fas fa-utensils"></i> Agiza Sasa
                    </a>
                </div>
            </div>
        </div>
    `;
}

// Load categories
async function loadCategories() {
    const container = document.getElementById('categoryList');
    if (!container) return;
    
    try {
        const response = await fetch('api/get_categories.php');
        const result = await response.json();
        
        if (result.success && result.data.length > 0) {
            container.innerHTML = result.data.map(category => `
                <li>
                    <label>
                        <input type="checkbox" name="category" value="${category.category_id}">
                        <span>${category.name}</span>
                        <span class="category-count">(${category.restaurant_count || 0})</span>
                    </label>
                </li>
            `).join('');
        } else {
            container.innerHTML = '<li>Hakuna aina za chakula zilizopatikana</li>';
        }
    } catch (error) {
        console.error('Error loading categories:', error);
        container.innerHTML = '<li>Hitilafu katika kupakua aina</li>';
    }
}

// Initialize filters
function initFilters() {
    // Real-time search
    const searchInput = document.getElementById('search');
    if (searchInput) {
        let timeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(timeout);
            timeout = setTimeout(() => {
                applyFilters();
            }, 500);
        });
    }
    
    // Other filters
    document.querySelectorAll('#cuisine, #city, #delivery, #sortBy').forEach(select => {
        select.addEventListener('change', function() {
            applyFilters();
        });
    });
    
    // Checkboxes
    document.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            applyFilters();
        });
    });
}

// Apply filters
function applyFilters() {
    const form = document.getElementById('restaurantFilters');
    const formData = new FormData(form);
    const params = new URLSearchParams();
    
    // Add form data
    for (let [key, value] of formData.entries()) {
        if (value.trim()) {
            params.append(key, value);
        }
    }
    
    // Add checkbox values
    document.querySelectorAll('input[type="checkbox"]:checked').forEach(checkbox => {
        params.append(checkbox.name, checkbox.value);
    });
    
    // Add sort value
    const sortBy = document.getElementById('sortBy');
    if (sortBy) {
        params.append('sort', sortBy.value);
    }
    
    // Add price range
    const priceRange = document.getElementById('priceRange');
    if (priceRange) {
        params.append('max_price', priceRange.value);
    }
    
    // Update URL without reloading page (for single page application feel)
    const newUrl = `restaurants.php?${params.toString()}`;
    window.history.pushState({}, '', newUrl);
    
    // Reload restaurants
    loadRestaurants();
}

// Reset filters
function resetFilters() {
    // Clear all form inputs
    document.querySelectorAll('input[type="text"]').forEach(input => input.value = '');
    document.querySelectorAll('select').forEach(select => select.selectedIndex = 0);
    document.querySelectorAll('input[type="checkbox"]').forEach(checkbox => checkbox.checked = false);
    
    // Reset price range
    const priceRange = document.getElementById('priceRange');
    if (priceRange) {
        priceRange.value = 25000;
        updatePriceDisplay();
    }
    
    // Reset URL
    window.history.pushState({}, '', 'restaurants.php');
    
    // Reload restaurants
    loadRestaurants();
}

// Initialize price range
function initPriceRange() {
    const priceRange = document.getElementById('priceRange');
    if (priceRange) {
        priceRange.addEventListener('input', function() {
            updatePriceDisplay();
            // Debounce the filter application
            clearTimeout(this.timeout);
            this.timeout = setTimeout(() => {
                applyFilters();
            }, 500);
        });
        updatePriceDisplay();
    }
}

// Update price display
function updatePriceDisplay() {
    const priceRange = document.getElementById('priceRange');
    const maxPrice = document.getElementById('maxPrice');
    
    if (priceRange && maxPrice) {
        const value = parseInt(priceRange.value);
        maxPrice.textContent = formatPrice(value);
    }
}

// Sort restaurants
function sortRestaurants(sortBy) {
    const params = new URLSearchParams(window.location.search);
    params.set('sort', sortBy);
    window.location.href = `restaurants.php?${params.toString()}`;
}

// Display pagination
function displayPagination(pagination, currentPage) {
    const container = document.getElementById('pagination');
    if (!container) return;
    
    const totalPages = Math.ceil(pagination.total / pagination.limit);
    currentPage = parseInt(currentPage);
    
    if (totalPages <= 1) {
        container.innerHTML = '';
        return;
    }
    
    let html = '';
    
    // Previous button
    html += `
        <a href="restaurants.php?page=${currentPage - 1}" 
           class="page-link ${currentPage <= 1 ? 'disabled' : ''}">
            <i class="fas fa-chevron-left"></i>
        </a>
    `;
    
    // Page numbers
    const maxPagesToShow = 5;
    let startPage = Math.max(1, currentPage - Math.floor(maxPagesToShow / 2));
    let endPage = Math.min(totalPages, startPage + maxPagesToShow - 1);
    
    if (endPage - startPage + 1 < maxPagesToShow) {
        startPage = Math.max(1, endPage - maxPagesToShow + 1);
    }
    
    for (let i = startPage; i <= endPage; i++) {
        html += `
            <a href="restaurants.php?page=${i}" 
               class="page-link ${i === currentPage ? 'active' : ''}">
                ${i}
            </a>
        `;
    }
    
    // Next button
    html += `
        <a href="restaurants.php?page=${currentPage + 1}" 
           class="page-link ${currentPage >= totalPages ? 'disabled' : ''}">
            <i class="fas fa-chevron-right"></i>
        </a>
    `;
    
    container.innerHTML = html;
}

// Add styles for pagination
const paginationStyles = document.createElement('style');
paginationStyles.textContent = `
    .no-results {
        text-align: center;
        padding: 3rem;
        grid-column: 1 / -1;
    }
    
    .no-results i {
        font-size: 4rem;
        color: #ddd;
        margin-bottom: 1rem;
    }
    
    .no-results h3 {
        color: var(--dark);
        margin-bottom: 0.5rem;
    }
    
    .no-results p {
        color: var(--gray);
        margin-bottom: 1.5rem;
    }
    
    .restaurant-badge {
        position: absolute;
        top: 1rem;
        left: 1rem;
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }
    
    .restaurant-item {
        position: relative;
    }
    
    .restaurant-description {
        color: var(--gray);
        font-size: 0.9rem;
        margin: 1rem 0;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    
    .rating-filters label {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 0.5rem;
        cursor: pointer;
    }
    
    .price-display {
        margin-top: 0.5rem;
        font-weight: 500;
        color: var(--primary-color);
    }
    
    input[type="range"] {
        width: 100%;
        height: 5px;
        border-radius: 5px;
        background: var(--light);
        outline: none;
        opacity: 0.7;
        transition: opacity .2s;
    }
    
    input[type="range"]:hover {
        opacity: 1;
    }
`;
document.head.appendChild(paginationStyles);