// Load featured restaurants
async function loadFeaturedRestaurants() {
    const container = document.getElementById('featuredRestaurants');
    if (!container) return;
    
    try {
        const restaurants = await fetchRestaurants({ featured: true, limit: 8 });
        
        if (restaurants.length === 0) {
            container.innerHTML = '<p class="text-center">Hakuna mikahawa inayopatikana kwa sasa.</p>';
            return;
        }
        
        container.innerHTML = restaurants.map(restaurant => {
            const rating = restaurant.rating || 0;
            const fullStars = Math.floor(rating);
            const halfStar = rating % 1 >= 0.5;
            const emptyStars = 5 - fullStars - (halfStar ? 1 : 0);
            
            return `
                <div class="restaurant-card">
                    <img src="${restaurant.image || 'assets/images/restaurant-placeholder.jpg'}" 
                         alt="${restaurant.name}" class="restaurant-image">
                    <div class="restaurant-info">
                        <h3 class="restaurant-name">${restaurant.name}</h3>
                        <p class="restaurant-cuisine">${restaurant.cuisine || 'Aina mbalimbali'}</p>
                        <div class="rating">
                            ${'★'.repeat(fullStars)}${halfStar ? '½' : ''}${'☆'.repeat(emptyStars)}
                            <span class="rating-text">(${rating.toFixed(1)})</span>
                        </div>
                        <div class="restaurant-meta">
                            <span class="delivery-info">
                                <i class="fas fa-clock"></i> ${restaurant.delivery_time || '30-45'} min
                            </span>
                            <span class="min-order">
                                <i class="fas fa-tag"></i> TSh ${formatPrice(restaurant.min_order || 0)}
                            </span>
                        </div>
                        <a href="menu.php?restaurant_id=${restaurant.id}" class="btn btn-primary btn-sm" style="width: 100%; margin-top: 1rem;">
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